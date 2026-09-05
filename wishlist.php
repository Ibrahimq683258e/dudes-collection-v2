<?php
/**
 * Saved Wishlist Page
 */

$pageTitle = "My Saved Wishlist";

require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

$wishlistModel = new Wishlist();

// Handle toggle request via GET URL
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && !empty($_GET['id'])) {
    if (!isset($_SESSION['user_id'])) {
        set_flash_message('warning', 'Please log in to save items to your wishlist.');
        header("Location: login.php");
        exit();
    }

    $productId = (int)$_GET['id'];
    $result = $wishlistModel->toggle($_SESSION['user_id'], $productId);

    if ($result === 'added') {
        set_flash_message('success', 'Garment saved to your wishlist.');
    } else {
        set_flash_message('info', 'Item removed from wishlist.');
    }
    header("Location: wishlist.php");
    exit();
}

$wishlistItems = [];
if (isset($_SESSION['user_id'])) {
    $wishlistItems = $wishlistModel->getUserWishlist($_SESSION['user_id']);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mb-8">
            Saved Garments Wishlist <span class="text-xs font-normal text-stone-500">(<?= count($wishlistItems) ?> items)</span>
        </h1>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200 dark:border-stone-800">
                <i class="fa-regular fa-heart text-5xl text-stone-300 dark:text-stone-700 mb-4"></i>
                <h2 class="font-serif text-2xl font-bold text-stone-800 dark:text-white">Save Your Favorite Tailored Fits</h2>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-2 mb-6">Log in to save bespoke kaftans and agbada sets across devices.</p>
                <a href="login.php" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3 rounded-full text-xs shadow-lg transition-colors">
                    Login to Account &rarr;
                </a>
            </div>
        <?php elseif (!empty($wishlistItems)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="bg-white dark:bg-stone-900 rounded-2xl overflow-hidden shadow-sm border border-stone-200 dark:border-stone-800 flex flex-col justify-between">
                        <div class="relative img-zoom-container bg-stone-100 dark:bg-stone-800">
                            <a href="wishlist.php?action=toggle&id=<?= $item['id'] ?>" class="absolute top-3 right-3 z-10 w-8 h-8 bg-white/80 dark:bg-stone-900/80 rounded-full flex items-center justify-center text-red-500 shadow">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </a>
                            <a href="product.php?slug=<?= escape_output($item['slug']) ?>" class="block h-64 overflow-hidden">
                                <img src="<?= escape_output($item['primary_image'] ?? 'assets/images/placeholder.jpg') ?>"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover">
                            </a>
                        </div>
                        <div class="p-4 flex flex-col justify-between flex-grow">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-gold-600 dark:text-gold-400"><?= escape_output($item['category_name']) ?></span>
                                <h3 class="font-serif font-bold text-sm text-stone-900 dark:text-white line-clamp-1 mt-0.5"><?= escape_output($item['name']) ?></h3>
                            </div>
                            <div class="mt-3 pt-2 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                                <span class="font-serif font-bold text-sm text-emerald-950 dark:text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($item['price'], 2) ?></span>
                                <a href="product.php?slug=<?= escape_output($item['slug']) ?>" class="bg-gold-500 text-emerald-950 font-bold text-xs px-3 py-1 rounded-lg">View Garment</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200 dark:border-stone-800">
                <i class="fa-regular fa-heart text-5xl text-stone-300 dark:text-stone-700 mb-4"></i>
                <h2 class="font-serif text-2xl font-bold text-stone-800 dark:text-white">Your Wishlist is Empty</h2>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-2 mb-6">Browse our catalog and click the heart icon on any Kaftan or Agbada set to save it here.</p>
                <a href="shop.php" class="bg-gold-500 text-emerald-950 font-bold px-8 py-3 rounded-full text-xs">Browse Shop Catalog</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
