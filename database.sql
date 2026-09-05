-- Modern Online Male Boutique Store Database Schema & Sample Data
-- Database Name: boutique_db

CREATE DATABASE IF NOT EXISTS `boutique_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `boutique_db`;

-- Drop existing tables if re-importing
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `banners`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  `status` ENUM('active', 'locked', 'suspended') NOT NULL DEFAULT 'active',
  `failed_login_attempts` TINYINT UNSIGNED DEFAULT 0,
  `lockout_time` DATETIME DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets Table
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`token`),
  INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `sale_price` DECIMAL(10, 2) DEFAULT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 10,
  `sizes` VARCHAR(255) DEFAULT 'S,M,L,XL,XXL', -- comma separated
  `colors` VARCHAR(255) DEFAULT 'Black,White,Gold,Emerald,Navy', -- comma separated
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_bestseller` TINYINT(1) DEFAULT 0,
  `is_new` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `views_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Images Table
CREATE TABLE `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `delivery_city` VARCHAR(100) NOT NULL,
  `order_notes` TEXT DEFAULT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
  `whatsapp_sent` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `size` VARCHAR(20) DEFAULT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlist Table
CREATE TABLE `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banners Table
CREATE TABLE `banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `button_text` VARCHAR(50) DEFAULT 'Shop Now',
  `button_link` VARCHAR(255) DEFAULT 'shop.php',
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs Table
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts Table
CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_success` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =======================================================
-- SAMPLE DATA INSERTION
-- Password for all sample users is: AdminPassword123!
-- Hash generated using password_hash('AdminPassword123!', PASSWORD_BCRYPT)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi
-- =======================================================

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `address`, `city`) VALUES
(1, 'Boutique Administrator', 'admin@omoja.shop', '+233500000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 'admin', 'active', '12 Oxford Street, Osu', 'Accra'),
(2, 'Kofi Mensah', 'kofi.mensah@example.com', '+233241234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 'customer', 'active', '45 Independence Avenue', 'Accra'),
(3, 'Kwame Osei', 'kwame.osei@example.com', '+233209876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.RAW2e1gOi', 'customer', 'active', '18 Ahodwo Roundabout', 'Kumasi');

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`) VALUES
(1, 'Traditional Kaftans', 'traditional-kaftans', 'Handcrafted royal African kaftans tailored for the modern gentleman.', 'assets/images/categories/kaftans.jpg'),
(2, 'Agbadas & Ceremonial', 'agbadas-ceremonial', 'Majestic Agbada suits and ceremonial wear crafted with premium embroidery.', 'assets/images/categories/agbada.jpg'),
(3, 'Modern Dashikis', 'modern-dashikis', 'Contemporary fitted dashikis and luxury printed tunic shirts.', 'assets/images/categories/dashikis.jpg'),
(4, 'Bespoke Suits & Shirts', 'bespoke-suits-shirts', 'Precision fitted executive suits, native blazers, and luxury cotton shirts.', 'assets/images/categories/suits.jpg'),
(5, 'Luxury Footwear & Caps', 'footwear-caps', 'Handmade leather shoes, velvet caps, and handcrafted accessories.', 'assets/images/categories/accessories.jpg');

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_description`, `price`, `sale_price`, `stock_quantity`, `sizes`, `colors`, `is_featured`, `is_bestseller`, `is_new`) VALUES
(1, 1, 'Royal Gold Embroidered Kaftan', 'royal-gold-embroidered-kaftan', 'KAF-GOLD-001', 'Elevate your wardrobe with the Royal Gold Embroidered Kaftan. Carefully crafted with premium polished cotton and high-density metallic thread embroidery along the chest and cuffs.', 'Luxury polished cotton kaftan with metallic gold embroidery.', 450.00, 399.00, 15, 'M,L,XL,XXL', 'Gold,Emerald Green,Black,White', 1, 1, 1),
(2, 1, 'Emerald Imperial Senate Kaftan', 'emerald-imperial-senate-kaftan', 'KAF-EME-002', 'Designed for leaders and kings. This Senate style Kaftan features clean lines, hidden placket buttons, and a structured high mandarin collar.', 'Tailored emerald green Senate kaftan with minimalist gold accent.', 380.00, NULL, 20, 'S,M,L,XL,XXL', 'Emerald Green,Navy Blue,Charcoal', 1, 1, 0),
(3, 2, 'Sovereign Grand Agbada 3-Piece', 'sovereign-grand-agbada-3-piece', 'AGB-SOV-001', 'The ultimate statement in African luxury. Includes embroidered wide-sleeve robe, fitted long sleeve inner shirt, and matching trousers made from high-grade cashmere wool mix.', '3-Piece royal Agbada set with intricate chest embroidery.', 850.00, 799.00, 8, 'M,L,XL,XXL', 'Burgundy,Royal Blue,Black,Gold', 1, 1, 1),
(4, 3, 'Urban Luxe Fitted Dashiki Shirt', 'urban-luxe-fitted-dashiki-shirt', 'DSH-URB-001', 'A modern twist on classic heritage. Breathable cotton linen fabric featuring subtle geometrical cuff trim and asymmetric zip neckline.', 'Modern tailored fitted dashiki in breathable luxury linen.', 220.00, 185.00, 25, 'S,M,L,XL', 'White,Black,Olive,Beige', 0, 1, 1),
(5, 4, 'Executive Native Tuxedo Blazer', 'executive-native-tuxedo-blazer', 'SUT-TUX-001', 'Blend Western sharp tailoring with African elegance. Silk lapel trim on high-grade damask woven fabric with structured shoulders.', 'Sharp tailored native tuxedo blazer with silk lapel details.', 620.00, NULL, 12, '38R,40R,42R,44R,46R', 'Black,Midnight Blue', 1, 0, 1),
(6, 5, 'Handmade Velvet Fila Cap & Cufflinks', 'handmade-velvet-fila-cap-cufflinks', 'ACC-CAP-001', 'Complete your ceremonial look with a plush velvet Fila cap and custom brass African map cufflinks.', 'Handcrafted velvet cap with gold metallic trim and matching cufflinks.', 120.00, 95.00, 30, 'One Size', 'Burgundy,Emerald Green,Black,Gold', 0, 0, 0);

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`) VALUES
(1, 1, 'assets/images/products/kaftan-gold-1.jpg', 1),
(2, 1, 'assets/images/products/kaftan-gold-2.jpg', 0),
(3, 2, 'assets/images/products/kaftan-emerald-1.jpg', 1),
(4, 3, 'assets/images/products/agbada-royal-1.jpg', 1),
(5, 4, 'assets/images/products/dashiki-white-1.jpg', 1),
(6, 5, 'assets/images/products/blazer-black-1.jpg', 1),
(7, 6, 'assets/images/products/cap-velvet-1.jpg', 1);

INSERT INTO `banners` (`id`, `title`, `subtitle`, `button_text`, `button_link`, `image_path`, `sort_order`, `is_active`) VALUES
(1, 'Authentic African Male Luxury', 'Discover handcrafted Kaftans, Agbadas & bespoke shirts designed for the modern gentleman.', 'Explore Collection', 'shop.php', 'assets/images/banners/hero-1.jpg', 1, 1),
(2, 'The Imperial Royal Kaftan Series', 'Premium polished cotton with hand-detailed gold embroidery.', 'Shop Kaftans', 'shop.php?category=traditional-kaftans', 'assets/images/banners/hero-2.jpg', 2, 1);

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `delivery_city`, `order_notes`, `total_amount`, `status`, `whatsapp_sent`) VALUES
(1, 'ORD-20260905-001', 2, 'Kofi Mensah', 'kofi.mensah@example.com', '+233241234567', '45 Independence Avenue', 'Accra', 'Please deliver after 2 PM', 399.00, 'completed', 1),
(2, 'ORD-20260905-002', 3, 'Kwame Osei', 'kwame.osei@example.com', '+233209876543', '18 Ahodwo Roundabout', 'Kumasi', 'Wrap as gift', 850.00, 'processing', 1);

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `size`, `color`, `subtotal`) VALUES
(1, 1, 1, 'Royal Gold Embroidered Kaftan', 399.00, 1, 'XL', 'Gold', 399.00),
(2, 2, 3, 'Sovereign Grand Agbada 3-Piece', 850.00, 1, 'XXL', 'Burgundy', 850.00);

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Omoja Male Boutique'),
('store_tagline', 'Authentic African Male Style & Bespoke Apparel'),
('whatsapp_number', '233500000000'),
('contact_email', 'sales@omoja.shop'),
('contact_phone', '+233 50 000 0000'),
('currency_symbol', 'GH¢'),
('store_address', '12 Oxford Street, Osu, Accra, Ghana'),
('allow_guest_checkout', '1'),
('max_login_attempts', '5'),
('lockout_duration_minutes', '15'),
('session_timeout_minutes', '30');
