<?php
/**
 * About Page
 */

$pageTitle = "Our Atelier Story";

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-16 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Authentic Heritage</span>
            <h1 class="font-serif text-4xl sm:text-5xl font-bold text-stone-900 dark:text-white mt-2">
                Crafting Timeless African Male Luxury
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-4 leading-relaxed font-light">
                Dude's Collection was born out of a passion to redefine contemporary male elegance. We blend traditional craftsmanship with sharp modern tailoring.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
            <div class="rounded-3xl overflow-hidden shadow-2xl border border-gold-600/30" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1000&q=80" alt="Master Tailor at Work" class="w-full h-96 object-cover">
            </div>

            <div class="space-y-6" data-aos="fade-left">
                <h2 class="font-serif text-3xl font-bold text-stone-900 dark:text-white">
                    Every Stitch Tells a Story of Royalty
                </h2>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed font-light">
                    In our boutique atelier, master artisans work with high-density metallic embroidery threads, polished cottons, damasks, and wool blends to craft garments fit for kings and executives.
                </p>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed font-light">
                    From our signature Senate Kaftans to 3-Piece Grand Agbadas, wearing Dude's Collection is an assertion of status, heritage, and pride.
                </p>
                <div class="pt-2">
                    <a href="shop.php" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3.5 rounded-full shadow-lg transition-all inline-block text-xs">
                        Explore Our Tailored Collection &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
