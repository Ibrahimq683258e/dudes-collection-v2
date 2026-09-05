<?php
/**
 * Admin Products Management Module (CRUD & Multi-Image Upload)
 */

$pageTitle = "Manage Boutique Products";

require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$productModel = new Product();
$categoryModel = new Category();

$categories = $categoryModel->getAllActive();
$errors = [];

// Handle Actions
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);
$editProduct = $editId ? $productModel->getById($editId) : null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } else {
        $postAction = $_POST['post_action'] ?? '';

        if ($postAction === 'save_product') {
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = sanitize_input($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            $sku = sanitize_input($_POST['sku'] ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
            $stock = (int)($_POST['stock_quantity'] ?? 10);
            $description = sanitize_input($_POST['description'] ?? '');
            $shortDescription = sanitize_input($_POST['short_description'] ?? '');
            $sizes = sanitize_input($_POST['sizes'] ?? 'S,M,L,XL,XXL');
            $colors = sanitize_input($_POST['colors'] ?? 'Gold,Emerald Green,Black,White');
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
            $isNew = isset($_POST['is_new']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($sku) || $price <= 0 || $categoryId <= 0) {
                $errors[] = "Please fill in all mandatory product fields correctly.";
            }

            if (empty($errors)) {
                $productData = [
                    'category_id' => $categoryId,
                    'name' => $name,
                    'slug' => $slug,
                    'sku' => $sku,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock_quantity' => $stock,
                    'description' => $description,
                    'short_description' => $shortDescription,
                    'sizes' => $sizes,
                    'colors' => $colors,
                    'is_featured' => $isFeatured,
                    'is_bestseller' => $isBestseller,
                    'is_new' => $isNew,
                    'is_active' => $isActive
                ];

                if (!empty($_POST['product_id'])) {
                    $pid = (int)$_POST['product_id'];
                    $productModel->update($pid, $productData);
                    AuditLog::log('Update Product', "Updated product #{$pid}: {$name}");
                    set_flash_message('success', "Product '{$name}' updated successfully.");
                } else {
                    $pid = $productModel->create($productData);
                    AuditLog::log('Create Product', "Created new product #{$pid}: {$name}");
                    set_flash_message('success', "New product '{$name}' created successfully.");
                }

                // Handle Image Upload if file provided
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadRes = FileUploader::uploadImage('product_image');
                    if ($uploadRes['status']) {
                        $productModel->addImage($pid, $uploadRes['file_path'], 1);
                    } else {
                        set_flash_message('warning', "Product saved, but image upload failed: " . $uploadRes['message']);
                    }
                }

                header("Location: products.php");
                exit();
            }
        } elseif ($postAction === 'delete_product') {
            $pid = (int)($_POST['product_id'] ?? 0);
            if ($pid) {
                $productModel->delete($pid);
                AuditLog::log('Delete Product', "Deleted product #{$pid}");
                set_flash_message('success', "Product deleted successfully.");
            }
            header("Location: products.php");
            exit();
        }
    }
}

$allProducts = $productModel->getAllAdmin();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Product Catalog Management</h1>
        <p class="text-xs text-stone-400 mt-1">Add, edit, or remove traditional Kaftans and Agbada robes.</p>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="products.php?action=create" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-4 py-2.5 rounded-xl text-xs flex items-center transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Add New Product
        </a>
    <?php else: ?>
        <a href="products.php" class="bg-stone-800 hover:bg-stone-700 text-stone-200 font-bold px-4 py-2.5 rounded-xl text-xs flex items-center transition-colors">
            &larr; Back to Products List
        </a>
    <?php endif; ?>
</div>

<?php if (!empty($errors)): ?>
    <div class="bg-red-900/80 text-white p-4 rounded-xl text-xs space-y-1 mb-6">
        <?php foreach ($errors as $e): ?>
            <div>• <?= escape_output($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <!-- Product Create/Edit Form -->
    <div class="bg-stone-900 border border-stone-800 rounded-3xl p-8 shadow-xl max-w-4xl">
        <h3 class="font-serif font-bold text-xl text-gold-400 mb-6 border-b border-stone-800 pb-3">
            <?= $editProduct ? 'Edit Product Garment' : 'Add New Boutique Garment' ?>
        </h3>

        <form action="products.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save_product">
            <?php if ($editProduct): ?>
                <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Product Name *</label>
                    <input type="text" name="name" value="<?= escape_output($_POST['name'] ?? $editProduct['name'] ?? '') ?>" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">SKU Code *</label>
                    <input type="text" name="sku" value="<?= escape_output($_POST['sku'] ?? $editProduct['sku'] ?? '') ?>" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Category *</label>
                    <select name="category_id" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= escape_output($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Regular Price (<?= CURRENCY_SYMBOL ?>) *</label>
                    <input type="number" step="0.01" name="price" value="<?= escape_output($_POST['price'] ?? $editProduct['price'] ?? '') ?>" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Sale Price (Optional)</label>
                    <input type="number" step="0.01" name="sale_price" value="<?= escape_output($_POST['sale_price'] ?? $editProduct['sale_price'] ?? '') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Stock Quantity</label>
                    <input type="number" name="stock_quantity" value="<?= (int)($_POST['stock_quantity'] ?? $editProduct['stock_quantity'] ?? 15) ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Available Sizes</label>
                    <input type="text" name="sizes" value="<?= escape_output($_POST['sizes'] ?? $editProduct['sizes'] ?? 'S,M,L,XL,XXL') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Available Colors</label>
                    <input type="text" name="colors" value="<?= escape_output($_POST['colors'] ?? $editProduct['colors'] ?? 'Gold,Emerald Green,Black,White') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Short Description</label>
                <input type="text" name="short_description" value="<?= escape_output($_POST['short_description'] ?? $editProduct['short_description'] ?? '') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Full Description & Craftsmanship</label>
                <textarea name="description" rows="4" required class="w-full bg-stone-800 border border-stone-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-gold-500"><?= escape_output($_POST['description'] ?? $editProduct['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Primary Garment Image Upload</label>
                <input type="file" name="product_image" accept="image/*" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2 text-xs text-stone-300">
            </div>

            <div class="flex flex-wrap gap-6 pt-2 text-xs">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" <?= (!empty($editProduct['is_featured']) ? 'checked' : '') ?> class="rounded text-gold-500 focus:ring-0">
                    <span class="text-stone-300">Featured</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_bestseller" value="1" <?= (!empty($editProduct['is_bestseller']) ? 'checked' : '') ?> class="rounded text-gold-500 focus:ring-0">
                    <span class="text-stone-300">Bestseller</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_new" value="1" <?= (!isset($editProduct) || !empty($editProduct['is_new']) ? 'checked' : '') ?> class="rounded text-gold-500 focus:ring-0">
                    <span class="text-stone-300">New Arrival</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($editProduct) || !empty($editProduct['is_active']) ? 'checked' : '') ?> class="rounded text-gold-500 focus:ring-0">
                    <span class="text-stone-300">Active Listing</span>
                </label>
            </div>

            <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3.5 rounded-2xl text-xs uppercase tracking-wider shadow-lg transition-colors">
                Save Garment Listing
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- Products List Table -->
    <div class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-300">
                <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="p-4">Garment</th>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800">
                    <?php foreach ($allProducts as $p): ?>
                        <tr class="hover:bg-stone-800/40">
                            <td class="p-4 flex items-center space-x-3">
                                <?php
                                    $imgSrc = $p['primary_image'] ?? '';
                                    if ($imgSrc && strpos($imgSrc, '../') !== 0 && strpos($imgSrc, 'http') !== 0) {
                                        $imgSrc = '../' . $imgSrc;
                                    }
                                ?>
                                <img src="<?= escape_output($imgSrc ?: '../assets/images/placeholder.jpg') ?>"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=200&q=80';" class="w-10 h-12 rounded object-cover">
                                <span class="font-bold text-white"><?= escape_output($p['name']) ?></span>
                            </td>
                            <td class="p-4 font-mono text-[11px]"><?= escape_output($p['sku']) ?></td>
                            <td class="p-4"><?= escape_output($p['category_name']) ?></td>
                            <td class="p-4 font-serif font-bold text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($p['price'], 2) ?></td>
                            <td class="p-4"><?= (int)$p['stock_quantity'] ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?= $p['is_active'] ? 'bg-emerald-950 text-emerald-400' : 'bg-stone-800 text-stone-500' ?>">
                                    <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="text-gold-400 hover:underline"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <form action="products.php" method="POST" class="inline" onsubmit="return confirm('Delete this garment?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="post_action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="text-red-400 hover:underline"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
