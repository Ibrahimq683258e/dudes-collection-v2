<?php
/**
 * Modern Male Boutique Homepage
 */

$pageTitle = "Authentic African Male Apparel & Bespoke Kaftans";

require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

// Handle Newsletter Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $email = filter_var($_POST['newsletter_email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            set_flash_message('success', 'Thank you for subscribing to Omoja VIP Boutique list!');
        } else {
            set_flash_message('danger', 'Please enter a valid email address.');
        }
    }
}

$productModel = new Product();
$categoryModel = new Category();
$bannerModel = new Banner();

$banners = $bannerModel->getActiveBanners();
$categories = $categoryModel->getAllActive();
$featuredProducts = $productModel->getFeatured(6);
$bestsellerProducts = $productModel->getBestSellers(6);
$newArrivals = $productModel->getNewArrivals(6);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Slider (Attached directly to header without top gap) -->
<section class="relative bg-emerald-950 text-white overflow-hidden border-b border-gold-600/30 -mt-0">
    <div class="swiper hero-swiper w-full h-[520px] md:h-[620px]">
        <div class="swiper-wrapper">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $banner): ?>
                    <div class="swiper-slide relative flex items-center justify-center">
                        <!-- Background Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950 via-emerald-950/80 to-transparent z-10"></div>
                        <!-- Background Image -->
                        <img src="<?= escape_output($banner['image_path']) ?>"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=1600&q=80';"
                             alt="<?= escape_output($banner['title']) ?>"
                             class="absolute inset-0 w-full h-full object-cover object-center">

                        <!-- Content -->
                        <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 w-full text-left" data-aos="fade-up">
                            <h1 class="font-serif text-3xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight mb-4 max-w-2xl">
                                <?= escape_output($banner['title']) ?>
                            </h1>
                            <p class="text-stone-300 text-sm md:text-base mb-8 max-w-xl font-light leading-relaxed">
                                <?= escape_output($banner['subtitle']) ?>
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <a href="<?= escape_output($banner['button_link']) ?>" class="bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 text-emerald-950 font-bold px-8 py-3.5 rounded-full shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center text-sm">
                                    <?= escape_output($banner['button_text']) ?> <i class="fa-solid fa-arrow-right-long ml-2"></i>
                                </a>
                                <a href="shop.php" class="border border-white/40 hover:border-gold-400 text-white hover:text-gold-400 px-8 py-3.5 rounded-full font-semibold transition-all text-sm backdrop-blur-sm">
                                    View Full Lookbook
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="swiper-slide relative flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-950 via-emerald-950/90 to-transparent z-10"></div>
                    <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=1600&q=80" alt="African Male Fashion" class="absolute inset-0 w-full h-full object-cover">
                    <div class="relative z-20 max-w-7xl mx-auto px-6 sm:px-8 w-full text-left">
                        <span class="px-3 py-1 bg-gold-600/30 border border-gold-500/50 text-gold-400 text-xs font-semibold tracking-widest uppercase rounded-full">
                            Bespoke African Style
                        </span>
                        <h1 class="font-serif text-4xl sm:text-6xl font-bold text-white mt-4 mb-4">
                            Authentic African Male Luxury
                        </h1>
                        <p class="text-stone-300 text-sm sm:text-base max-w-xl mb-8">
                            Discover handcrafted Kaftans, Agbadas & bespoke shirts designed for the modern gentleman.
                        </p>
                        <a href="shop.php" class="bg-gold-500 text-emerald-950 font-bold px-8 py-3.5 rounded-full shadow-lg hover:bg-gold-400 transition-all inline-block">
                            Shop Now &rarr;
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Shop By Category Section (Omoja Style) -->
<section class="py-16 bg-stone-100 dark:bg-stone-900 border-b border-stone-200 dark:border-stone-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10" data-aos="fade-up">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Curated Collections</span>
                <h2 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">Shop by Category</h2>
            </div>
            <a href="shop.php" class="mt-4 md:mt-0 text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-gold-400 hover:underline flex items-center">
                Explore All Categories <i class="fa-solid fa-chevron-right ml-2 text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-6">
            <?php foreach ($categories as $cat): ?>
                <a href="shop.php?category=<?= escape_output($cat['slug']) ?>" class="group block relative rounded-2xl overflow-hidden shadow-md bg-stone-900 text-white transform transition-all hover:-translate-y-1.5 hover:shadow-xl" data-aos="fade-up">
                    <div class="aspect-w-1 aspect-h-1 h-44 sm:h-52 w-full img-zoom-container relative">
                        <img src="<?= escape_output($cat['image']) ?>"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=600&q=80';"
                             alt="<?= escape_output($cat['name']) ?>"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/90 via-emerald-950/30 to-transparent"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-center">
                        <h3 class="font-serif font-bold text-sm sm:text-base text-white group-hover:text-gold-400 transition-colors">
                            <?= escape_output($cat['name']) ?>
                        </h3>
                        <span class="text-[10px] text-stone-300 font-medium block mt-0.5">
                            <?= $cat['product_count'] ?> Products
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Best Sellers & Featured Products Section -->
<section class="py-16 bg-stone-50 dark:bg-stone-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up">
            <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Handcrafted Excellence</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mt-1">Best Sellers & Featured</h2>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-2">
                Tailored for distinction. Explore our most sought-after royal Kaftans and 3-piece ceremonial Agbadas.
            </p>
        </div>

        <!-- Product Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($bestsellerProducts as $prod): ?>
                <div class="group bg-white dark:bg-stone-900 rounded-2xl overflow-hidden shadow-md border border-stone-200 dark:border-stone-800 flex flex-col justify-between transition-all hover:shadow-2xl hover:border-gold-500/50" data-aos="fade-up">

                    <div class="relative img-zoom-container bg-stone-100 dark:bg-stone-800">
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                            <?php if ($prod['sale_price']): ?>
                                <span class="bg-red-800 text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-md shadow">
                                    Sale
                                </span>
                            <?php endif; ?>
                            <?php if ($prod['is_bestseller']): ?>
                                <span class="bg-gold-600 text-emerald-950 text-[10px] font-bold uppercase px-2.5 py-1 rounded-md shadow">
                                    Bestseller
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Wishlist Button -->
                        <a href="wishlist.php?action=toggle&id=<?= $prod['id'] ?>" class="absolute top-3 right-3 z-10 w-9 h-9 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md rounded-full flex items-center justify-center text-stone-700 dark:text-stone-300 hover:text-red-500 shadow transition-colors">
                            <i class="fa-regular fa-heart"></i>
                        </a>

                        <!-- Primary Product Image -->
                        <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="block aspect-w-3 aspect-h-4 h-72 sm:h-80 w-full overflow-hidden">
                            <img src="<?= escape_output($prod['primary_image'] ?? 'assets/images/placeholder.jpg') ?>"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80';"
                                 alt="<?= escape_output($prod['name']) ?>"
                                 class="w-full h-full object-cover object-top">
                        </a>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex flex-col flex-grow justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gold-600 dark:text-gold-400 tracking-wider">
                                <?= escape_output($prod['category_name']) ?>
                            </span>
                            <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="block">
                                <h3 class="font-serif font-bold text-base text-stone-900 dark:text-white group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors line-clamp-1 mt-1">
                                    <?= escape_output($prod['name']) ?>
                                </h3>
                            </a>
                            <p class="text-xs text-stone-500 dark:text-stone-400 line-clamp-2 mt-1.5 font-light">
                                <?= escape_output($prod['short_description']) ?>
                            </p>
                        </div>

                        <!-- Price & Direct Action -->
                        <div class="mt-4 pt-3 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                            <div>
                                <?php if ($prod['sale_price']): ?>
                                    <span class="font-serif font-bold text-lg text-emerald-900 dark:text-gold-400">
                                        <?= CURRENCY_SYMBOL ?><?= number_format($prod['sale_price'], 2) ?>
                                    </span>
                                    <span class="text-xs text-stone-400 line-through ml-1">
                                        <?= CURRENCY_SYMBOL ?><?= number_format($prod['price'], 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="font-serif font-bold text-lg text-emerald-900 dark:text-gold-400">
                                        <?= CURRENCY_SYMBOL ?><?= number_format($prod['price'], 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <a href="product.php?slug=<?= escape_output($prod['slug']) ?>" class="bg-emerald-950 hover:bg-gold-600 hover:text-emerald-950 text-gold-400 w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-md" title="Select Options">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Direct WhatsApp Checkout Promotion Banner (Omoja Concept) -->
<section class="py-16 bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-950 text-white border-y border-gold-600/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div data-aos="fade-right">
                <span class="inline-block px-3 py-1 bg-emerald-800 text-gold-400 text-xs font-bold rounded-full uppercase tracking-widest mb-4">
                    Fast & Convenient Ordering
                </span>
                <h2 class="font-serif text-3xl sm:text-5xl font-extrabold text-white leading-tight mb-4">
                    Order Directly via WhatsApp Chat
                </h2>
                <p class="text-stone-300 text-sm sm:text-base leading-relaxed mb-6 font-light">
                    No complicated payment gateways or registrations needed! Select your preferred sizes and colors, add to bag, and click <strong class="text-gold-400">Order via WhatsApp</strong>. You will be redirected straight to our boutique master tailor with your exact order breakdown.
                </p>
                <div class="space-y-3 mb-8 text-xs sm:text-sm text-stone-200">
                    <div class="flex items-center"><i class="fa-solid fa-check-circle text-gold-400 mr-3 text-base"></i> Real-time custom measurement confirmation with tailor</div>
                    <div class="flex items-center"><i class="fa-solid fa-check-circle text-gold-400 mr-3 text-base"></i> Fast local delivery & doorstep dispatch</div>
                    <div class="flex items-center"><i class="fa-solid fa-check-circle text-gold-400 mr-3 text-base"></i> Secure payment on delivery / Mobile Money</div>
                </div>
                <a href="shop.php" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3.5 rounded-full shadow-xl transition-all inline-flex items-center text-sm">
                    <i class="fa-brands fa-whatsapp text-xl mr-2"></i> Start Shopping Now
                </a>
            </div>

            <!-- Features Showcase Cards -->
            <div class="grid grid-cols-2 gap-4" data-aos="fade-left">
                <div class="bg-emerald-900/60 backdrop-blur-md p-6 rounded-2xl border border-gold-600/30">
                    <i class="fa-solid fa-mobile-screen-button text-3xl text-gold-400 mb-3 block"></i>
                    <h4 class="font-serif font-bold text-white text-base">Mobile First</h4>
                    <p class="text-xs text-stone-300 mt-1">Built specifically for effortless smartphone browsing.</p>
                </div>
                <div class="bg-emerald-900/60 backdrop-blur-md p-6 rounded-2xl border border-gold-600/30">
                    <i class="fa-brands fa-whatsapp text-3xl text-gold-400 mb-3 block"></i>
                    <h4 class="font-serif font-bold text-white text-base">Direct Chat</h4>
                    <p class="text-xs text-stone-300 mt-1">Instant communication with store owner & designer.</p>
                </div>
                <div class="bg-emerald-900/60 backdrop-blur-md p-6 rounded-2xl border border-gold-600/30">
                    <i class="fa-solid fa-gem text-3xl text-gold-400 mb-3 block"></i>
                    <h4 class="font-serif font-bold text-white text-base">Royal Fabrics</h4>
                    <p class="text-xs text-stone-300 mt-1">Premium polished cottons, damasks, and wool blends.</p>
                </div>
                <div class="bg-emerald-900/60 backdrop-blur-md p-6 rounded-2xl border border-gold-600/30">
                    <i class="fa-solid fa-truck-fast text-3xl text-gold-400 mb-3 block"></i>
                    <h4 class="font-serif font-bold text-white text-base">Rapid Dispatch</h4>
                    <p class="text-xs text-stone-300 mt-1">Express courier packaging to your doorstep.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Instagram Style Showcase Grid -->
<section class="py-16 bg-stone-100 dark:bg-stone-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
        <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">@Omoja.Shop Lookbook</span>
        <h2 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1 mb-8">Follow Our Gentlemans Gallery</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
            <div class="relative group aspect-w-1 aspect-h-1 overflow-hidden rounded-xl img-zoom-container">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=600&q=80" alt="Instagram Look" class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-emerald-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-gold-400 text-xl">
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
