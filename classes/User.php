<?php
/**
 * User OOP Model
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($name, $email, $phone, $password, $address = null, $city = null) {
        // Validate password strength
        $pwCheck = validate_password_strength($password);
        if ($pwCheck !== true) {
            return ['status' => false, 'message' => $pwCheck];
        }

        // Check existing user
        if ($this->findByEmail($email)) {
            return ['status' => false, 'message' => 'An account with this email address already exists.'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, email, phone, password_hash, role, address, city) VALUES (:name, :email, :phone, :hash, 'customer', :address, :city)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':hash' => $passwordHash,
            ':address' => $address,
            ':city' => $city
        ]);

        if ($result) {
            return ['status' => true, 'user_id' => $this->db->lastInsertId(), 'message' => 'Registration successful. You can now login.'];
        }

        return ['status' => false, 'message' => 'Registration failed due to a system error.'];
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, phone, role, status, address, city, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function login($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['status' => false, 'message' => 'Invalid email or password.'];
        }

        // Check lockout status
        if ($user['status'] === 'locked') {
            if ($user['lockout_time'] && (time() - strtotime($user['lockout_time']) < (LOCKOUT_DURATION_MINUTES * 60))) {
                return ['status' => false, 'message' => 'Account is locked due to multiple failed login attempts. Try again in ' . LOCKOUT_DURATION_MINUTES . ' minutes.'];
            } else {
                // Reset lockout after duration passed
                $this->resetLockout($user['id']);
            }
        }

        if ($user['status'] === 'suspended') {
            return ['status' => false, 'message' => 'Your account has been suspended. Please contact customer support.'];
        }

        if (password_verify($password, $user['password_hash'])) {
            // Success: Reset failed attempts
            $this->resetLockout($user['id']);

            // Log successful attempt
            $this->logLoginAttempt($email, true);

            // Set Session Data
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            return ['status' => true, 'user' => $user];
        } else {
            // Increment failed attempts
            $failedCount = $user['failed_login_attempts'] + 1;
            $this->logLoginAttempt($email, false);

            if ($failedCount >= MAX_LOGIN_ATTEMPTS) {
                $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = :count, status = 'locked', lockout_time = CURRENT_TIMESTAMP WHERE id = :id");
                $stmt->execute([':count' => $failedCount, ':id' => $user['id']]);
                return ['status' => false, 'message' => 'Account locked due to ' . MAX_LOGIN_ATTEMPTS . ' consecutive failed login attempts.'];
            } else {
                $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = :count WHERE id = :id");
                $stmt->execute([':count' => $failedCount, ':id' => $user['id']]);
                return ['status' => false, 'message' => 'Invalid email or password. ' . (MAX_LOGIN_ATTEMPTS - $failedCount) . ' attempts remaining.'];
            }
        }
    }

    private function resetLockout($userId) {
        $stmt = $this->db->prepare("UPDATE users SET failed_login_attempts = 0, status = 'active', lockout_time = NULL WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    private function logLoginAttempt($email, $isSuccess) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $this->db->prepare("INSERT INTO login_attempts (ip_address, email, is_success) VALUES (:ip, :email, :success)");
        $stmt->execute([':ip' => $ip, ':email' => $email, ':success' => $isSuccess ? 1 : 0]);
    }

    public function createPasswordResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
        $stmt->execute([':email' => $email, ':token' => $token, ':expires' => $expiresAt]);

        return $token;
    }

    public function verifyResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > CURRENT_TIMESTAMP ORDER BY id DESC LIMIT 1");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function resetPasswordWithToken($token, $newPassword) {
        $reset = $this->verifyResetToken($token);
        if (!$reset) {
            return ['status' => false, 'message' => 'Invalid or expired password reset token.'];
        }

        $pwCheck = validate_password_strength($newPassword);
        if ($pwCheck !== true) {
            return ['status' => false, 'message' => $pwCheck];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
        $stmt->execute([':hash' => $hash, ':email' => $reset['email']]);

        // Delete used reset tokens for this email
        $stmtDel = $this->db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmtDel->execute([':email' => $reset['email']]);

        return ['status' => true, 'message' => 'Your password has been successfully reset. You can now log in.'];
    }

    public function updateProfile($userId, $name, $phone, $address, $city) {
        $stmt = $this->db->prepare("UPDATE users SET name = :name, phone = :phone, address = :address, city = :city WHERE id = :id");
        return $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':city' => $city,
            ':id' => $userId
        ]);
    }

    public function getAllCustomers() {
        $stmt = $this->db->query("SELECT id, name, email, phone, role, status, created_at FROM users WHERE role = 'customer' ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function toggleUserStatus($userId, $status) {
        $stmt = $this->db->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $userId]);
    }
}
