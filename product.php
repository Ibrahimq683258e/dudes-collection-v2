<?php
/**
 * Product Detail Page with Multi-Image Gallery Zoom, Size Selector, Size Guide & Related Products
 */

require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

$productModel = new Product();
$slug = sanitize_input($_GET['slug'] ?? '');
$id = sanitize_input($_GET['id'] ?? '');

$product = null;
if (!empty($slug)) {
    $product = $productModel->getBySlug($slug);
} elseif (!empty($id)) {
    $product = $productModel->getById($id);
}

if (!$product) {
    header("Location: shop.php");
    exit();
}

$pageTitle = $product['name'];

// Handle Add to Cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid CSRF token.');
    } else {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $size = sanitize_input($_POST['size'] ?? 'M');
        $color = sanitize_input($_POST['color'] ?? 'Default');

        Cart::add($product['id'], $qty, $size, $color);
        set_flash_message('success', 'Added "' . $product['name'] . '" (' . $size . ', ' . $color . ') to your shopping bag.');

        if (isset($_POST['buy_now'])) {
            header("Location: cart.php");
            exit();
        }
    }
}

// Manage Recently Viewed Products in Session
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
if (!in_array($product['id'], $_SESSION['recently_viewed'])) {
    array_unshift($_SESSION['recently_viewed'], $product['id']);
    $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5); // keep max 5
}

// Fetch Related Products
$relatedProducts = $productModel->getRelatedProducts($product['category_id'], $product['id'], 4);

// Prepare image list
$images = $product['images'];
if (empty($images)) {
    $images = [['image_path' => 'assets/images/placeholder.jpg', 'is_primary' => 1]];
}

$availableSizes = array_map('trim', explode(',', $product['sizes'] ?? 'S,M,L,XL,XXL'));
$availableColors = array_map('trim', explode(',', $product['colors'] ?? 'Gold,Emerald Green,Black,White'));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Product Page Container -->
<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex text-xs text-stone-500 mb-8 space-x-2">
            <a href="index.php" class="hover:text-gold-500">Home</a>
            <span>/</span>
            <a href="shop.php" class="hover:text-gold-500">Shop</a>
            <span>/</span>
            <a href="shop.php?category=<?= escape_output($product['category_slug']) ?>" class="hover:text-gold-500"><?= escape_output($product['category_name']) ?></a>
            <span>/</span>
            <span class="text-stone-900 dark:text-stone-200 font-semibold line-clamp-1"><?= escape_output($product['name']) ?></span>
        </nav>

        <!-- Main Product Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 bg-white dark:bg-stone-900 p-6 sm:p-10 rounded-3xl shadow-lg border border-stone-200 dark:border-stone-800">

            <!-- Product Gallery (Primary + Thumbnails) -->
            <div class="space-y-4">
                <div class="relative overflow-hidden rounded-2xl bg-stone-100 dark:bg-stone-800 aspect-w-3 aspect-h-4 h-[420px] sm:h-[520px] border border-stone-200 dark:border-stone-700 img-zoom-container">
                    <img id="mainProductImage"
                         src="<?= escape_output($images[0]['image_path']) ?>"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=1000&q=80';"
                         alt="<?= escape_output($product['name']) ?>"
                         class="w-full h-full object-cover object-top transition-all duration-300">
                </div>

                <!-- Thumbnail Navigation -->
                <?php if (count($images) > 1): ?>
                    <div class="flex space-x-3 overflow-x-auto pb-2">
                        <?php foreach ($images as $img): ?>
                            <button type="button"
                                    onclick="document.getElementById('mainProductImage').src='<?= escape_output($img['image_path']) ?>'"
                                    class="w-20 h-24 rounded-xl overflow-hidden border-2 border-transparent focus:border-gold-500 flex-shrink-0 bg-stone-100 dark:bg-stone-800">
                                <img src="<?= escape_output($img['image_path']) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=300&q=80';" class="w-full h-full object-cover">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Form & Info -->
            <div class="flex flex-col justify-between space-y-6">
                <div>
                    <!-- Category & Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-extrabold text-gold-600 dark:text-gold-400 tracking-widest">
                            <?= escape_output($product['category_name']) ?>
                        </span>
                        <span class="text-xs text-stone-400">SKU: <?= escape_output($product['sku']) ?></span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mt-2">
                        <?= escape_output($product['name']) ?>
                    </h1>

                    <!-- Price -->
                    <div class="mt-4 flex items-baseline space-x-3">
                        <?php if ($product['sale_price']): ?>
                            <span class="font-serif text-3xl font-bold text-emerald-950 dark:text-gold-400">
                                <?= CURRENCY_SYMBOL ?><?= number_format($product['sale_price'], 2) ?>
                            </span>
                            <span class="text-base text-stone-400 line-through">
                                <?= CURRENCY_SYMBOL ?><?= number_format($product['price'], 2) ?>
                            </span>
                            <span class="bg-red-800 text-white text-xs font-bold px-2.5 py-1 rounded-md">Save <?= CURRENCY_SYMBOL ?><?= number_format($product['price'] - $product['sale_price'], 2) ?></span>
                        <?php else: ?>
                            <span class="font-serif text-3xl font-bold text-emerald-950 dark:text-gold-400">
                                <?= CURRENCY_SYMBOL ?><?= number_format($product['price'], 2) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 mt-4 leading-relaxed font-light">
                        <?= escape_output($product['short_description']) ?>
                    </p>

                    <!-- Form Selection -->
                    <form action="product.php?slug=<?= escape_output($product['slug']) ?>" method="POST" class="mt-6 space-y-6">
                        <?= csrf_field() ?>

                        <!-- Size Selector + Size Guide Modal Trigger -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white">Select Tailored Size</label>
                                <button type="button" onclick="document.getElementById('sizeGuideModal').classList.remove('hidden')" class="text-xs text-gold-600 dark:text-gold-400 hover:underline flex items-center font-semibold">
                                    <i class="fa-solid fa-ruler-horizontal mr-1"></i> Size Guide
                                </button>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <?php foreach ($availableSizes as $index => $sz): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size" value="<?= escape_output($sz) ?>" <?= $index === 0 ? 'checked' : '' ?> class="peer sr-only">
                                        <span class="w-12 h-12 rounded-xl border border-stone-300 dark:border-stone-700 flex items-center justify-center text-xs font-bold text-stone-800 dark:text-stone-200 peer-checked:bg-gold-500 peer-checked:border-gold-600 peer-checked:text-emerald-950 transition-all hover:border-gold-500 shadow-sm">
                                            <?= escape_output($sz) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Color Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white mb-2">Select Color</label>
                            <div class="flex flex-wrap gap-3">
                                <?php foreach ($availableColors as $index => $col): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="<?= escape_output($col) ?>" <?= $index === 0 ? 'checked' : '' ?> class="peer sr-only">
                                        <span class="px-4 py-2 rounded-xl border border-stone-300 dark:border-stone-700 flex items-center justify-center text-xs font-semibold text-stone-800 dark:text-stone-200 peer-checked:bg-emerald-950 peer-checked:text-gold-400 peer-checked:border-gold-500 transition-all shadow-sm">
                                            <?= escape_output($col) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-white mb-2">Quantity</label>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center border border-stone-300 dark:border-stone-700 rounded-xl overflow-hidden bg-stone-50 dark:bg-stone-800">
                                    <button type="button" onclick="let q = document.getElementById('qty'); if(q.value > 1) q.value--;" class="px-3 py-2 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700">-</button>
                                    <input type="number" id="qty" name="quantity" value="1" min="1" max="50" class="w-12 text-center bg-transparent border-none text-xs font-bold text-stone-900 dark:text-white focus:outline-none">
                                    <button type="button" onclick="let q = document.getElementById('qty'); q.value++;" class="px-3 py-2 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700">+</button>
                                </div>
                                <span class="text-xs text-stone-500">In Stock (<?= (int)$product['stock_quantity'] ?> available)</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="submit" name="add_to_cart" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs flex items-center justify-center">
                                <i class="fa-solid fa-bag-shopping mr-2"></i> Add To Bag
                            </button>
                            <button type="submit" name="add_to_cart" value="1" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs flex items-center justify-center">
                                <i class="fa-brands fa-whatsapp mr-2 text-sm"></i> Order via WhatsApp
                            </button>
                            <input type="hidden" name="buy_now" value="1">
                        </div>

                    </form>
                </div>

                <!-- Guarantee Badges -->
                <div class="pt-6 border-t border-stone-100 dark:border-stone-800 grid grid-cols-3 gap-2 text-center text-[10px] text-stone-500">
                    <div><i class="fa-solid fa-scissors text-gold-500 text-sm mb-1 block"></i> Bespoke Tailored Fit</div>
                    <div><i class="fa-solid fa-truck-fast text-gold-500 text-sm mb-1 block"></i> Fast Delivery</div>
                    <div><i class="fa-brands fa-whatsapp text-emerald-500 text-sm mb-1 block"></i> Direct Tailor Chat</div>
                </div>

            </div>
        </div>

        <!-- Full Description & Material Care -->
        <div class="mt-12 bg-white dark:bg-stone-900 p-8 rounded-3xl border border-stone-200 dark:border-stone-800">
            <h3 class="font-serif text-2xl font-bold text-stone-900 dark:text-white mb-4">Garment Craftsmanship & Description</h3>
            <div class="prose dark:prose-invert max-w-none text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                <?= nl2br(escape_output($product['description'])) ?>
            </div>
        </div>

        <!-- Related Products Section -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="mt-16">
                <h3 class="font-serif text-2xl font-bold text-stone-900 dark:text-white mb-6">You May Also Like</h3>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($relatedProducts as $rel): ?>
                        <div class="bg-white dark:bg-stone-900 rounded-2xl overflow-hidden shadow-sm border border-stone-200 dark:border-stone-800">
                            <a href="product.php?slug=<?= escape_output($rel['slug']) ?>" class="block h-52 overflow-hidden img-zoom-container">
                                <img src="<?= escape_output($rel['primary_image'] ?? 'assets/images/placeholder.jpg') ?>"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover">
                            </a>
                            <div class="p-4">
                                <h4 class="font-serif font-bold text-xs text-stone-900 dark:text-white line-clamp-1"><?= escape_output($rel['name']) ?></h4>
                                <span class="text-xs text-gold-600 dark:text-gold-400 font-bold block mt-1"><?= CURRENCY_SYMBOL ?><?= number_format($rel['price'], 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Size Guide Modal -->
<div id="sizeGuideModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-stone-900 text-white rounded-3xl max-w-lg w-full p-6 border border-gold-600/30 shadow-2xl relative">
        <button onclick="document.getElementById('sizeGuideModal').classList.add('hidden')" class="absolute top-4 right-4 text-stone-400 hover:text-white text-xl">&times;</button>
        <h3 class="font-serif text-xl font-bold text-gold-400 mb-2">Bespoke Size Measurement Guide</h3>
        <p class="text-xs text-stone-300 mb-4">Standard size measurements for Omoja African Male Kaftans and Agbada robes (Inches):</p>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gold-600/30 text-gold-400">
                        <th class="py-2">Size</th>
                        <th class="py-2">Chest</th>
                        <th class="py-2">Shoulder</th>
                        <th class="py-2">Length</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800 text-stone-300">
                    <tr><td class="py-2 font-bold">Small (S)</td><td>36" - 38"</td><td>17"</td><td>38"</td></tr>
                    <tr><td class="py-2 font-bold">Medium (M)</td><td>39" - 41"</td><td>18"</td><td>40"</td></tr>
                    <tr><td class="py-2 font-bold">Large (L)</td><td>42" - 44"</td><td>19"</td><td>42"</td></tr>
                    <tr><td class="py-2 font-bold">X-Large (XL)</td><td>45" - 47"</td><td>20"</td><td>44"</td></tr>
                    <tr><td class="py-2 font-bold">XX-Large (XXL)</td><td>48" - 50"</td><td>21"</td><td>45"</td></tr>
                </tbody>
            </table>
        </div>
        <p class="text-[10px] text-stone-400 mt-4 italic">* Need custom tailoring? Leave a note during WhatsApp checkout!</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
