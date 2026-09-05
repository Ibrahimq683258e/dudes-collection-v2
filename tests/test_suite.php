<?php
/**
 * Automated PHP Integration & Functional Verification Suite
 */

define('USE_SQLITE_TESTING', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Models.php';

echo "=== STARTING OMOJA BOUTIQUE TEST SUITE ===\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✔ Database connection initialized successfully (SQLite Driver test mode).\n";

    // Initialize Schema in SQLite Test Mode
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT, email TEXT UNIQUE, phone TEXT, password_hash TEXT, role TEXT, status TEXT DEFAULT 'active', failed_login_attempts INTEGER DEFAULT 0, lockout_time DATETIME, address TEXT, city TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, slug TEXT UNIQUE, description TEXT, image TEXT, is_active INTEGER DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, name TEXT, slug TEXT UNIQUE, sku TEXT UNIQUE, description TEXT, short_description TEXT, price REAL, sale_price REAL, stock_quantity INTEGER, sizes TEXT, colors TEXT, is_featured INTEGER, is_bestseller INTEGER, is_new INTEGER, is_active INTEGER DEFAULT 1, views_count INTEGER DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT, product_id INTEGER, image_path TEXT, is_primary INTEGER DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT, order_number TEXT UNIQUE, user_id INTEGER, customer_name TEXT, customer_email TEXT, customer_phone TEXT, delivery_address TEXT, delivery_city TEXT, order_notes TEXT, total_amount REAL, status TEXT, whatsapp_sent INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER, product_id INTEGER, product_name TEXT, price REAL, quantity INTEGER, size TEXT, color TEXT, subtotal REAL
        );
        CREATE TABLE IF NOT EXISTS banners (
            id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, subtitle TEXT, button_text TEXT, button_link TEXT, image_path TEXT, sort_order INTEGER, is_active INTEGER DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, details TEXT, ip_address TEXT, user_agent TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT, ip_address TEXT, email TEXT, attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP, is_success INTEGER DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT UNIQUE, setting_value TEXT
        );
        CREATE TABLE IF NOT EXISTS user_cart (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, item_key TEXT, product_id INTEGER, quantity INTEGER, size TEXT, color TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS popups (
            id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, subtitle TEXT, image_path TEXT, button_text TEXT, button_link TEXT, is_active INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 1. Test User Registration & Login
    $userModel = new User();
    $reg = $userModel->register('Test Gentleman', 'gentleman@example.com', '+233201112223', 'Password123!', '10 Ring Road', 'Accra');
    assert($reg['status'] === true, 'User registration failed.');
    echo "✔ User Registration Test Passed.\n";

    $login = $userModel->login('gentleman@example.com', 'Password123!');
    assert($login['status'] === true, 'User login failed.');
    echo "✔ User Login & Password Hashing Test Passed.\n";

    // 2. Test Category & Product Models
    $catModel = new Category();
    $testSlug = 'royal-kaftans-' . time();
    $catModel->save(['name' => 'Royal Kaftans', 'slug' => $testSlug, 'description' => 'Luxury Kaftans']);
    $categories = $catModel->getAllActive();
    assert(count($categories) >= 1, 'Category creation failed.');
    echo "✔ Category Creation & Fetch Test Passed.\n";

    $productModel = new Product();
    $productSlug = 'royal-emerald-senate-kaftan-' . time();
    $productSku = 'KAF-EME-' . rand(1000, 9999);
    $pid = $productModel->create([
        'category_id' => $categories[0]['id'],
        'name' => 'Royal Emerald Senate Kaftan',
        'slug' => $productSlug,
        'sku' => $productSku,
        'price' => 450.00,
        'sale_price' => 399.00,
        'stock_quantity' => 20,
        'description' => 'Fine polished cotton Senate kaftan.',
        'short_description' => 'Senate kaftan with gold embroidery.',
        'sizes' => 'M,L,XL,XXL',
        'colors' => 'Emerald Green,Gold',
        'is_featured' => 1,
        'is_bestseller' => 1,
        'is_new' => 1,
        'is_active' => 1
    ]);
    assert($pid > 0, 'Product creation failed.');
    echo "✔ Product Creation Test Passed.\n";

    // 3. Test Cart Management
    Cart::clear();
    Cart::add($pid, 2, 'XL', 'Emerald Green');
    assert(Cart::getItemCount() === 2, 'Cart count mismatch.');
    assert(Cart::getTotal() === 798.00, 'Cart total price mismatch.');
    echo "✔ Shopping Cart Logic Test Passed.\n";

    // 4. Test WhatsApp Order Creation
    $orderModel = new Order();
    $orderRes = $orderModel->createOrder([
        'name' => 'Test Gentleman',
        'email' => 'gentleman@example.com',
        'phone' => '+233201112223',
        'address' => '10 Ring Road',
        'city' => 'Accra',
        'notes' => 'Custom sleeve length 25 inches'
    ], Cart::getCart(), Cart::getTotal(), 1);

    assert($orderRes['status'] === true, 'Order creation failed.');
    assert(strpos($orderRes['order_number'], 'ORD-') === 0, 'Order number format mismatch.');
    echo "✔ WhatsApp Direct Order Processing Test Passed (Ref: " . $orderRes['order_number'] . ").\n";

    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
} catch (Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
