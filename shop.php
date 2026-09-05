<?php
/**
 * Shop / Catalog Page with Category, Size, Price, Color Filters & Search
 */

$pageTitle = "Boutique Catalog & Collections";

require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

$productModel = new Product();
$categoryModel = new Category();

// Filter inputs
$categorySlug = sanitize_input($_GET['category'] ?? '');
$searchQuery = sanitize_input($_GET['search'] ?? '');
$selectedSize = sanitize_input($_GET['size'] ?? '');
$selectedColor = sanitize_input($_GET['color'] ?? '');
$sortOption = sanitize_input($_GET['sort'] ?? 'newest');

$selectedCategoryId = null;
$currentCategory = null;
if (!empty($categorySlug)) {
    $currentCategory = $categoryModel->getBySlug($categorySlug);
    if ($currentCategory) {
        $selectedCategoryId = $currentCategory['id'];
    }
}

$categories = $categoryModel->getAllActive();
$products = $productModel->getAllActive(null, null, $selectedCategoryId, $searchQuery, $sortOption);

// Filter products array locally for size & color if selected
if (!empty($selectedSize)) {
    $products = array_filter($products, function($p) use ($selectedSize) {
        $sizes = array_map('trim', explode(',', $p['sizes'] ?? ''));
        return in_array($selectedSize, $sizes);
    });
}

if (!empty($selectedColor)) {
    $products = array_filter($products, function($p) use ($selectedColor) {
        $colors = array_map('trim', explode(',', strtolower($p['colors'] ?? '')));
        return strpos(strtolower($p['colors'] ?? ''), strtolower($selectedColor)) !== false;
    });
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Shop Header Banner -->
<section class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-950 text-white py-12 border-b border-gold-600/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="text-xs font-bold uppercase tracking-widest text-gold-400">Master Tailoring</span>
        <h1 class="font-serif text-3xl sm:text-5xl font-bold mt-1">
            <?= $currentCategory ? escape_output($currentCategory['name']) : 'Boutique Catalog' ?>
        </h1>
        <p class="text-xs sm:text-sm text-stone-300 mt-2 max-w-xl mx-auto font-light">
            <?= $currentCategory ? escape_output($currentCategory['description']) : 'Explore our full range of handcrafted African Kaftans, Agbada robes, and tailored native shirts.' ?>
        </p>
    </div>
</section>

<!-- Shop Body -->
<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Sidebar Filters -->
            <aside class="bg-white dark:bg-stone-900 p-6 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 h-fit space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800">
                    <h3 class="font-serif font-bold text-lg text-stone-900 dark:text-white flex items-center">
                        <i class="fa-solid fa-filter text-gold-500 mr-2 text-sm"></i> Filters
                    </h3>
                    <a href="shop.php" class="text-xs text-gold-600 dark:text-gold-400 hover:underline">Reset All</a>
                </div>

                <form action="shop.php" method="GET" class="space-y-6">

                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="<?= escape_output($searchQuery) ?>" placeholder="Keywords..." class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg pl-3 pr-8 py-2 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                            <button type="submit" class="absolute right-3 top-2.5 text-stone-400 hover:text-gold-500"><i class="fa-solid fa-search text-xs"></i></button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Categories</label>
                        <div class="space-y-1.5 text-xs">
                            <a href="shop.php<?= $searchQuery ? '?search='.urlencode($searchQuery) : '' ?>" class="block py-1 px-2.5 rounded-lg font-medium transition-colors <?= empty($categorySlug) ? 'bg-emerald-950 text-gold-400 font-bold' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' ?>">
                                All Categories
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="shop.php?category=<?= escape_output($cat['slug']) ?>" class="block py-1 px-2.5 rounded-lg font-medium transition-colors <?= $categorySlug === $cat['slug'] ? 'bg-emerald-950 text-gold-400 font-bold' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' ?>">
                                    <?= escape_output($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Select Size</label>
                        <div class="grid grid-cols-4 gap-2 text-center text-xs">
                            <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
                                <a href="shop.php?<?= http_build_query(array_merge($_GET, ['size' => $selectedSize === $sz ? '' : $sz])) ?>"
                                   class="py-1.5 rounded-md border font-medium transition-all <?= $selectedSize === $sz ? 'bg-gold-500 border-gold-600 text-emerald-950 font-bold' : 'border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:border-gold-500' ?>">
                                    <?= $sz ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Color Filter -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Primary Color</label>
                        <select name="color" onchange="this.form.submit()" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg p-2 text-xs text-stone-900 dark:text-white">
                            <option value="">All Colors</option>
                            <option value="Gold" <?= $selectedColor === 'Gold' ? 'selected' : '' ?>>Gold</option>
                            <option value="Emerald" <?= $selectedColor === 'Emerald' ? 'selected' : '' ?>>Emerald Green</option>
                            <option value="Black" <?= $selectedColor === 'Black' ? 'selected' : '' ?>>Black</option>
                            <option value="White" <?= $selectedColor === 'White' ? 'selected' : '' ?>>White</option>
                            <option value="Navy" <?= $selectedColor === 'Navy' ? 'selected' : '' ?>>Navy Blue</option>
                            <option value="Burgundy" <?= $selectedColor === 'Burgundy' ? 'selected' : '' ?>>Burgundy</option>
                        </select>
                    </div>

                    <?php if ($categorySlug): ?>
                        <input type="hidden" name="category" value="<?= escape_output($categorySlug) ?>">
                    <?php endif; ?>

                    <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-2.5 rounded-xl text-xs shadow-md transition-colors">
                        Apply Filters
                    </button>
                </form>
            </aside>

            <!-- Product Grid Content -->
            <main class="lg:col-span-3">

                <!-- Sorting & Stats Bar -->
                <div class="bg-white dark:bg-stone-900 p-4 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                    <div class="text-xs text-stone-600 dark:text-stone-400">
                        Showing <strong class="text-stone-900 dark:text-white"><?= count($products) ?></strong> boutique garments
                    </div>

                    <form action="shop.php" method="GET" class="flex items-center space-x-2 text-xs">
                        <?php foreach ($_GET as $k => $v): if ($k !== 'sort'): ?>
                            <input type="hidden" name="<?= escape_output($k) ?>" value="<?= escape_output($v) ?>">
                        <?php endif; endforeach; ?>

                        <label class="font-medium text-stone-600 dark:text-stone-400">Sort By:</label>
                        <select name="sort" onchange="this.form.submit()" class="bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 text-stone-900 dark:text-white rounded-lg py-1.5 px-3 focus:outline-none focus:border-gold-500">
                            <option value="newest" <?= $sortOption === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="price_low" <?= $sortOption === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sortOption === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="popular" <?= $sortOption === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                        </select>
                    </form>
                </div>

                <!-- Products Display -->
                <?php if (!empty($products)): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <?php foreach ($products as $prod): ?>
                            <div class="group bg-white dark:bg-stone-900 rounded-2xl overflow-hidden shadow-sm border border-stone-200 dark:border-stone-800 flex flex-col justify-between transition-all hover:shadow-xl hover:border-gold-500/50">

                                <div class="relative img-zoom-container bg-stone-100 dark:bg-stone-800">
                                    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1">
                                        <?php if ($prod['sale_price']): ?>
                                            <span class="bg-red-800 text-white text-[9px] font-bold uppercase px-2 py-0.5 rounded shadow">Sale</span>
                                        <?php endif; ?>
                                        <?php if ($prod['is_new']): ?>
                                            <span class="bg-emerald-800 text-gold-400 text-[9px] font-bold uppercase px-2 py-0.5 rounded shadow">New</span>
                                        <?php endif; ?>
                                    </div>

                                    <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="block aspect-w-3 aspect-h-4 h-64 sm:h-72 w-full overflow-hidden">
                                        <img src="<?= escape_output($prod['primary_image'] ?? 'assets/images/placeholder.jpg') ?>"
                                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80';"
                                             alt="<?= escape_output($prod['name']) ?>"
                                             class="w-full h-full object-cover object-top">
                                    </a>
                                </div>

                                <div class="p-4 flex flex-col flex-grow justify-between">
                                    <div>
                                        <span class="text-[9px] uppercase font-bold text-gold-600 dark:text-gold-400 tracking-wider">
                                            <?= escape_output($prod['category_name']) ?>
                                        </span>
                                        <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="block">
                                            <h3 class="font-serif font-bold text-sm text-stone-900 dark:text-white group-hover:text-gold-600 transition-colors line-clamp-1 mt-0.5">
                                                <?= escape_output($prod['name']) ?>
                                            </h3>
                                        </a>
                                    </div>

                                    <div class="mt-3 pt-2 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                                        <div>
                                            <?php if ($prod['sale_price']): ?>
                                                <span class="font-serif font-bold text-base text-emerald-950 dark:text-gold-400">
                                                    <?= CURRENCY_SYMBOL ?><?= number_format($prod['sale_price'], 2) ?>
                                                </span>
                                                <span class="text-[10px] text-stone-400 line-through ml-1">
                                                    <?= CURRENCY_SYMBOL ?><?= number_format($prod['price'], 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="font-serif font-bold text-base text-emerald-950 dark:text-gold-400">
                                                    <?= CURRENCY_SYMBOL ?><?= number_format($prod['price'], 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold text-xs px-3 py-1.5 rounded-lg shadow transition-colors">
                                            View
                                        </a>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-stone-900 rounded-2xl p-12 text-center border border-stone-200 dark:border-stone-800">
                        <i class="fa-solid fa-shirt text-4xl text-stone-300 dark:text-stone-700 mb-3"></i>
                        <h3 class="font-serif text-xl font-bold text-stone-800 dark:text-white">No Products Found</h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 mb-4">We couldn't find any garments matching your current filter choices.</p>
                        <a href="shop.php" class="bg-emerald-950 text-gold-400 px-6 py-2 rounded-full text-xs font-bold inline-block">Clear Filters</a>
                    </div>
                <?php endif; ?>

            </main>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
