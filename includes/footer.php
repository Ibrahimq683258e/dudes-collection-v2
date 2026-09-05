<?php
/**
 * Global Footer Component & Mobile Navigation Bar
 */
?>
    <!-- Floating WhatsApp Quick Order Button -->
    <a href="https://wa.me/233500000000?text=<?= urlencode("Hello Dude's Collection! I would like to inquire about your male fashion collection.") ?>"
       target="_blank"
       rel="noopener"
       class="fixed bottom-20 right-5 lg:bottom-8 lg:right-8 z-50 bg-emerald-600 hover:bg-emerald-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-2xl transition-all transform hover:scale-110 group"
       title="Direct WhatsApp Order Chat">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span class="absolute right-16 bg-emerald-950 text-gold-400 text-xs px-3 py-1 rounded-md shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none font-semibold border border-gold-600/30">
            Order via WhatsApp
        </span>
    </a>

    <!-- Footer -->
    <footer class="mt-auto bg-stone-900 text-stone-300 border-t border-gold-600/20 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-stone-800">

                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gold-500 flex items-center justify-center text-emerald-950 font-sans font-black text-xl">
                            D
                        </div>
                        <span class="font-sans text-xl font-black tracking-tight text-white uppercase">DUDE'S <span class="text-gold-400">COLLECTION</span></span>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed">
                        Modern Male Boutique Store. Designed for the modern gentleman who commands respect and clean elegance.
                    </p>
                    <div class="flex space-x-4 pt-2 text-gold-400">
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-instagram text-lg"></i></a>
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-tiktok text-lg"></i></a>
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-whatsapp text-lg"></i></a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h4 class="font-serif text-white font-bold mb-4 tracking-wider uppercase text-sm border-b border-gold-600/30 pb-2">Collections</h4>
                    <ul class="space-y-2.5 text-xs text-stone-400">
                        <li><a href="shop.php?category=traditional-kaftans" class="hover:text-gold-400 transition-colors">Traditional Royal Kaftans</a></li>
                        <li><a href="shop.php?category=agbadas-ceremonial" class="hover:text-gold-400 transition-colors">Agbada 3-Piece Sets</a></li>
                        <li><a href="shop.php?category=modern-dashikis" class="hover:text-gold-400 transition-colors">Fitted Modern Dashikis</a></li>
                        <li><a href="shop.php?category=bespoke-suits-shirts" class="hover:text-gold-400 transition-colors">Bespoke Native Blazers</a></li>
                        <li><a href="shop.php?category=footwear-caps" class="hover:text-gold-400 transition-colors">Handmade Caps & Cufflinks</a></li>
                    </ul>
                </div>

                <!-- Customer Care -->
                <div>
                    <h4 class="font-serif text-white font-bold mb-4 tracking-wider uppercase text-sm border-b border-gold-600/30 pb-2">Customer Care</h4>
                    <ul class="space-y-2.5 text-xs text-stone-400">
                        <li><a href="about.php" class="hover:text-gold-400 transition-colors">About Our Atelier</a></li>
                        <li><a href="contact.php" class="hover:text-gold-400 transition-colors">Bespoke Tailoring Inquiries</a></li>
                        <li><a href="cart.php" class="hover:text-gold-400 transition-colors">Shopping Bag & Checkout</a></li>
                        <li><a href="login.php" class="hover:text-gold-400 transition-colors">My Account Portal</a></li>
                        <li><a href="#" class="hover:text-gold-400 transition-colors">Measurement Size Guide</a></li>
                    </ul>
                </div>

                <!-- Newsletter Subscription -->
                <div class="space-y-4">
                    <h4 class="font-serif text-white font-bold mb-4 tracking-wider uppercase text-sm border-b border-gold-600/30 pb-2">VIP Boutique Newsletter</h4>
                    <p class="text-xs text-stone-400">
                        Subscribe to receive exclusive preview access to new traditional collections and VIP discounts.
                    </p>
                    <form action="index.php" method="POST" class="space-y-2">
                        <?= csrf_field() ?>
                        <div class="flex">
                            <input type="email" name="newsletter_email" placeholder="Your email address" required class="bg-stone-800 text-white placeholder-stone-500 text-xs px-3 py-2.5 rounded-l-lg border border-stone-700 focus:outline-none focus:border-gold-500 w-full">
                            <button type="submit" class="bg-gold-600 hover:bg-gold-500 text-emerald-950 font-bold px-4 rounded-r-lg text-xs transition-colors whitespace-nowrap">
                                Join
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- Bottom Copyright & Security Badge -->
            <div class="pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-stone-500 space-y-4 md:space-y-0">
                <p>&copy; <?= date('Y') ?> Dude's Collection. All rights reserved. Premium Male Apparel.</p>
                <div class="flex items-center space-x-6 text-stone-400">
                    <span class="flex items-center"><i class="fa-solid fa-shield-halved text-emerald-500 mr-1"></i> Secure 256-Bit SSL</span>
                    <span class="flex items-center"><i class="fa-brands fa-whatsapp text-emerald-500 mr-1"></i> Direct WhatsApp Order Verification</span>
                </div>
            </div>
        </div>
    </footer>

    <?php
    // Promotional Popup Integration
    $db = Database::getInstance()->getConnection();
    $popupStmt = $db->query("SELECT * FROM popups WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $activePopup = $popupStmt ? $popupStmt->fetch() : null;
    ?>
    <?php if ($activePopup): ?>
        <div id="promoAdModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
            <div class="bg-stone-900 border border-gold-600/40 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl relative text-center">
                <button onclick="document.getElementById('promoAdModal').classList.add('hidden'); sessionStorage.setItem('promo_dismissed', '1');" class="absolute top-3 right-3 text-stone-400 hover:text-white bg-stone-800/80 rounded-full w-8 h-8 flex items-center justify-center text-lg z-10">&times;</button>
                <?php if (!empty($activePopup['image_path'])): ?>
                    <img src="<?= escape_output($activePopup['image_path']) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80';" class="w-full h-48 object-cover">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=800&q=80" class="w-full h-48 object-cover">
                <?php endif; ?>
                <div class="p-6 space-y-3">
                    <span class="inline-block px-3 py-1 bg-gold-600/30 text-gold-400 text-[10px] font-bold rounded-full uppercase tracking-wider">Exclusive Offer</span>
                    <h3 class="font-serif text-2xl font-bold text-white"><?= escape_output($activePopup['title']) ?></h3>
                    <p class="text-xs text-stone-300 font-light leading-relaxed"><?= escape_output($activePopup['subtitle']) ?></p>
                    <a href="<?= escape_output($activePopup['button_link'] ?? 'shop.php') ?>" class="inline-block w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3 rounded-2xl text-xs transition-colors uppercase tracking-wider shadow-lg">
                        <?= escape_output($activePopup['button_text'] ?? 'Shop Collection') ?> &rarr;
                    </a>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (!sessionStorage.getItem('promo_dismissed')) {
                    setTimeout(function() {
                        const modal = document.getElementById('promoAdModal');
                        if (modal) modal.classList.remove('hidden');
                    }, 1000);
                }
            });
        </script>
    <?php endif; ?>

    <!-- Mobile Bottom Navigation Bar (Omoja Mobile First Feel) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-emerald-950/95 backdrop-blur-md border-t border-gold-600/30 text-white z-50 flex items-center justify-around py-2.5 shadow-2xl">
        <a href="index.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-solid fa-house text-lg"></i>
            <span class="text-[10px] font-medium mt-1">Home</span>
        </a>
        <a href="shop.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-solid fa-border-all text-lg"></i>
            <span class="text-[10px] font-medium mt-1">Categories</span>
        </a>
        <a href="cart.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors relative">
            <i class="fa-solid fa-bag-shopping text-lg"></i>
            <?php if (Cart::getItemCount() > 0): ?>
                <span class="absolute -top-1 right-2 bg-gold-500 text-emerald-950 text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                    <?= Cart::getItemCount() ?>
                </span>
            <?php endif; ?>
            <span class="text-[10px] font-medium mt-1">Cart</span>
        </a>
        <a href="wishlist.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-regular fa-heart text-lg"></i>
            <span class="text-[10px] font-medium mt-1">Saved</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-solid fa-user text-lg"></i>
            <span class="text-[10px] font-medium mt-1">Account</span>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
