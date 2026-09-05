<?php
/**
 * Category, Order, Cart, Wishlist, Banner & AuditLog Models
 */

require_once __DIR__ . '/../config/database.php';

// Category Model
class Category {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllActive() {
        $stmt = $this->db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) as product_count FROM categories c WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }

    public function save($data) {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare("UPDATE categories SET name = :name, slug = :slug, description = :description, image = :image, is_active = :is_active WHERE id = :id");
            return $stmt->execute([
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? '',
                ':image' => $data['image'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $data['id']
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO categories (name, slug, description, image, is_active) VALUES (:name, :slug, :description, :image, :is_active)");
            return $stmt->execute([
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? '',
                ':image' => $data['image'] ?? null,
                ':is_active' => $data['is_active'] ?? 1
            ]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

// Cart Management Class
class Cart {
    public static function getCart() {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    public static function add($productId, $quantity = 1, $size = 'M', $color = 'Default') {
        $cart = self::getCart();
        $itemKey = $productId . '_' . $size . '_' . $color;

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $productModel = new Product();
            $product = $productModel->getById($productId);
            if (!$product) return false;

            $cart[$itemKey] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['sale_price'] ? $product['sale_price'] : $product['price'],
                'quantity' => (int)$quantity,
                'size' => $size,
                'color' => $color,
                'image' => !empty($product['images'][0]['image_path']) ? $product['images'][0]['image_path'] : 'assets/images/placeholder.jpg'
            ];
        }

        $_SESSION['cart'] = $cart;
        return true;
    }

    public static function update($itemKey, $quantity) {
        $cart = self::getCart();
        if (isset($cart[$itemKey])) {
            if ($quantity <= 0) {
                unset($cart[$itemKey]);
            } else {
                $cart[$itemKey]['quantity'] = (int)$quantity;
            }
            $_SESSION['cart'] = $cart;
            return true;
        }
        return false;
    }

    public static function remove($itemKey) {
        $cart = self::getCart();
        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            $_SESSION['cart'] = $cart;
            return true;
        }
        return false;
    }

    public static function clear() {
        $_SESSION['cart'] = [];
    }

    public static function getTotal() {
        $cart = self::getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public static function getItemCount() {
        $cart = self::getCart();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}

// Order OOP Model
class Order {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createOrder($customerData, $cartItems, $totalAmount, $userId = null) {
        $this->db->beginTransaction();

        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $sql = "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, delivery_address, delivery_city, order_notes, total_amount, status, whatsapp_sent)
                    VALUES (:order_number, :user_id, :name, :email, :phone, :address, :city, :notes, :total, 'pending', 1)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_number' => $orderNumber,
                ':user_id' => $userId,
                ':name' => $customerData['name'],
                ':email' => $customerData['email'],
                ':phone' => $customerData['phone'],
                ':address' => $customerData['address'],
                ':city' => $customerData['city'],
                ':notes' => $customerData['notes'] ?? '',
                ':total' => $totalAmount
            ]);

            $orderId = $this->db->lastInsertId();

            // Insert Order Items
            $itemStmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, size, color, subtotal) VALUES (:order_id, :product_id, :product_name, :price, :quantity, :size, :color, :subtotal)");

            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['name'],
                    ':price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':size' => $item['size'],
                    ':color' => $item['color'],
                    ':subtotal' => $subtotal
                ]);
            }

            $this->db->commit();
            return ['status' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        if ($order) {
            $order['items'] = $this->getOrderItems($id);
        }
        return $order;
    }

    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function getOrdersByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllOrders() {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getTotalRevenue() {
        return (float)$this->db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn();
    }

    public function getTotalOrderCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }
}

// Wishlist OOP Model
class Wishlist {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function toggle($userId, $productId) {
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid");
        $stmt->execute([':uid' => $userId, ':pid' => $productId]);
        if ($stmt->fetch()) {
            $del = $this->db->prepare("DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid");
            $del->execute([':uid' => $userId, ':pid' => $productId]);
            return 'removed';
        } else {
            $ins = $this->db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (:uid, :pid)");
            $ins->execute([':uid' => $userId, ':pid' => $productId]);
            return 'added';
        }
    }

    public function getUserWishlist($userId) {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = :uid
                ORDER BY w.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}

// Banner OOP Model
class Banner {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getActiveBanners() {
        $stmt = $this->db->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    public function save($data) {
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare("UPDATE banners SET title = :title, subtitle = :subtitle, button_text = :button_text, button_link = :button_link, image_path = :image_path, sort_order = :sort_order, is_active = :is_active WHERE id = :id");
            return $stmt->execute([
                ':title' => $data['title'],
                ':subtitle' => $data['subtitle'],
                ':button_text' => $data['button_text'],
                ':button_link' => $data['button_link'],
                ':image_path' => $data['image_path'],
                ':sort_order' => $data['sort_order'] ?? 0,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $data['id']
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO banners (title, subtitle, button_text, button_link, image_path, sort_order, is_active) VALUES (:title, :subtitle, :button_text, :button_link, :image_path, :sort_order, :is_active)");
            return $stmt->execute([
                ':title' => $data['title'],
                ':subtitle' => $data['subtitle'],
                ':button_text' => $data['button_text'],
                ':button_link' => $data['button_link'],
                ':image_path' => $data['image_path'],
                ':sort_order' => $data['sort_order'] ?? 0,
                ':is_active' => $data['is_active'] ?? 1
            ]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM banners WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

// AuditLog OOP Model
class AuditLog {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function log($action, $details = null) {
        $db = Database::getInstance()->getConnection();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent) VALUES (:uid, :action, :details, :ip, :ua)");
        $stmt->execute([
            ':uid' => $userId,
            ':action' => $action,
            ':details' => $details,
            ':ip' => $ip,
            ':ua' => substr($ua, 0, 255)
        ]);
    }

    public function getAllLogs($limit = 100) {
        $stmt = $this->db->prepare("SELECT a.*, u.name as user_name, u.email as user_email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT " . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Secure File Upload Handler
class FileUploader {
    public static function uploadImage($fileKey, $targetDir = 'uploads/products/') {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'No file uploaded or file upload error.'];
        }

        $file = $_FILES[$fileKey];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['status' => false, 'message' => 'File exceeds maximum allowed size of 5MB.'];
        }

        // Validate MIME type & extension
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['status' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP images are allowed.'];
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(16)) . '.' . strtolower($extension);

        $fullTargetDir = APP_ROOT . '/' . $targetDir;
        if (!file_exists($fullTargetDir)) {
            @mkdir($fullTargetDir, 0755, true);
        }

        $destination = $fullTargetDir . $safeName;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['status' => true, 'file_path' => $targetDir . $safeName];
        }

        return ['status' => false, 'message' => 'Failed to save uploaded file to disk.'];
    }
}
