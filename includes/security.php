<?php
/**
 * Security Middleware & Utility Helpers
 * Enforces security headers, session security, CSRF protection, input sanitization, and rate limiting.
 */

require_once __DIR__ . '/../config/config.php';

// 1. Set Security Headers
function set_security_headers() {
    if (!headers_sent()) {
        header("X-Frame-Options: SAMEORIGIN"); // Prevent Clickjacking
        header("X-Content-Type-Options: nosniff"); // Prevent MIME type sniffing
        header("X-XSS-Protection: 1; mode=block"); // Cross-site scripting filter
        header("Referrer-Policy: strict-origin-when-cross-origin"); // Referrer Policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()"); // Restrict features
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:;");
    }
}
set_security_headers();

// 2. Secure Session Initialization
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true, // Guard against XSS cookie theft
            'samesite' => 'Lax' // Guard against CSRF
        ]);

        session_start();
    }

    // Enforce session expiration / idle timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'Session expired due to inactivity. Please log in again.'];
    }
    $_SESSION['last_activity'] = time();

    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created_time'])) {
        $_SESSION['created_time'] = time();
    } else if (time() - $_SESSION['created_time'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created_time'] = time();
    }
}
init_secure_session();

// 3. XSS Output Escaping
function escape_output($string) {
    if (is_null($string)) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Short alias function for output escaping
function sanitize($string) {
    return escape_output($string);
}

// 4. CSRF Token Protection
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . escape_output($token) . '">';
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 5. Input Validation Helpers
function sanitize_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }
        return $data;
    }
    return trim((string)$data);
}

function validate_password_strength($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return "Password must contain at least one special character.";
    }
    return true;
}

// 6. Rate Limiting Helper
function check_rate_limit($action_key, $max_requests = 10, $time_frame_seconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $session_key = "rate_limit_{$action_key}_{$ip}";

    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = ['count' => 1, 'start_time' => time()];
        return true;
    }

    $time_passed = time() - $_SESSION[$session_key]['start_time'];

    if ($time_passed > $time_frame_seconds) {
        $_SESSION[$session_key] = ['count' => 1, 'start_time' => time()];
        return true;
    }

    if ($_SESSION[$session_key]['count'] >= $max_requests) {
        return false;
    }

    $_SESSION[$session_key]['count']++;
    return true;
}

// 7. Flash Messages Helper
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, danger, warning, info
        'text' => $message
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);

        $bgColor = $msg['type'] === 'success' ? 'bg-emerald-800 text-white' : ($msg['type'] === 'danger' ? 'bg-red-800 text-white' : 'bg-amber-800 text-white');

        return '<div class="p-4 mb-4 rounded-lg shadow ' . $bgColor . ' flex justify-between items-center position-relative" role="alert">
                    <span>' . escape_output($msg['text']) . '</span>
                    <button type="button" class="ml-4 text-white font-bold" onclick="this.parentElement.remove();">&times;</button>
                </div>';
    }
    return '';
}
