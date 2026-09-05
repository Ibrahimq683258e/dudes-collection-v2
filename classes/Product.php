<?php
/**
 * Product OOP Model
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllActive($limit = null, $offset = null, $categoryId = null, $search = null, $sort = 'newest') {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1";

        $params = [];

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search OR p.sku LIKE :search OR c.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'popular':
                $sql .= " ORDER BY p.views_count DESC";
                break;
            default:
                $sql .= " ORDER BY p.id DESC";
                break;
        }

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getFeatured($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function getBestSellers($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 AND p.is_bestseller = 1
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function getNewArrivals($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 AND p.is_new = 1
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        if ($product) {
            $this->incrementViews($id);
            $product['images'] = $this->getProductImages($id);
        }
        return $product;
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch();

        if ($product) {
            $this->incrementViews($product['id']);
            $product['images'] = $this->getProductImages($product['id']);
        }
        return $product;
    }

    public function getProductImages($productId) {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC, id ASC");
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    public function getRelatedProducts($categoryId, $currentProductId, $limit = 4) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $randFunc = ($driver === 'sqlite') ? 'RANDOM()' : 'RAND()';

        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 AND p.category_id = :category_id AND p.id != :current_id
                ORDER BY {$randFunc} LIMIT " . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $categoryId, ':current_id' => $currentProductId]);
        return $stmt->fetchAll();
    }

    private function incrementViews($productId) {
        $stmt = $this->db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = :id");
        $stmt->execute([':id' => $productId]);
    }

    public function create($data) {
        $sql = "INSERT INTO products (category_id, name, slug, sku, description, short_description, price, sale_price, stock_quantity, sizes, colors, is_featured, is_bestseller, is_new, is_active)
                VALUES (:category_id, :name, :slug, :sku, :description, :short_description, :price, :sale_price, :stock_quantity, :sizes, :colors, :is_featured, :is_bestseller, :is_new, :is_active)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':sku' => $data['sku'],
            ':description' => $data['description'],
            ':short_description' => $data['short_description'] ?? '',
            ':price' => $data['price'],
            ':sale_price' => $data['sale_price'] ?? null,
            ':stock_quantity' => $data['stock_quantity'] ?? 10,
            ':sizes' => $data['sizes'] ?? 'S,M,L,XL,XXL',
            ':colors' => $data['colors'] ?? 'Black,White,Gold',
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_bestseller' => $data['is_bestseller'] ?? 0,
            ':is_new' => $data['is_new'] ?? 1,
            ':is_active' => $data['is_active'] ?? 1
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE products SET category_id = :category_id, name = :name, slug = :slug, sku = :sku, description = :description,
                short_description = :short_description, price = :price, sale_price = :sale_price, stock_quantity = :stock_quantity,
                sizes = :sizes, colors = :colors, is_featured = :is_featured, is_bestseller = :is_bestseller, is_new = :is_new, is_active = :is_active
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':sku' => $data['sku'],
            ':description' => $data['description'],
            ':short_description' => $data['short_description'] ?? '',
            ':price' => $data['price'],
            ':sale_price' => $data['sale_price'] ?? null,
            ':stock_quantity' => $data['stock_quantity'],
            ':sizes' => $data['sizes'],
            ':colors' => $data['colors'],
            ':is_featured' => $data['is_featured'],
            ':is_bestseller' => $data['is_bestseller'],
            ':is_new' => $data['is_new'],
            ':is_active' => $data['is_active'],
            ':id' => $id
        ]);
    }

    public function addImage($productId, $imagePath, $isPrimary = 0) {
        if ($isPrimary) {
            // Unset previous primary image
            $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :pid")->execute([':pid' => $productId]);
        }
        $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (:product_id, :path, :is_primary)");
        return $stmt->execute([':product_id' => $productId, ':path' => $imagePath, ':is_primary' => $isPrimary]);
    }

    public function deleteImage($imageId) {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
        return $stmt->execute([':id' => $imageId]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getAllAdmin() {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTotalCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }
}
