<?php
/**
 * Global Footer Component & Mobile Navigation Bar
 */
?>
    <!-- Floating WhatsApp Quick Order Button -->
    <a href="https://wa.me/233500000000?text=<?= urlencode('Hello Omoja Male Boutique! I would like to inquire about your bespoke male fashion collection.') ?>"
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
                        <div class="w-10 h-10 rounded-full bg-gold-500 flex items-center justify-center text-emerald-950 font-serif font-extrabold text-xl">
                            O
                        </div>
                        <span class="font-serif text-2xl font-bold tracking-wider text-white">OMOJA<span class="text-gold-400">.</span></span>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed">
                        Authentic African Male Luxury & Bespoke Apparel. Designed for the modern gentleman who commands respect and timeless elegance.
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
                <p>&copy; <?= date('Y') ?> Omoja Male Boutique. All rights reserved. Premium African Elegance.</p>
                <div class="flex items-center space-x-6 text-stone-400">
                    <span class="flex items-center"><i class="fa-solid fa-shield-halved text-emerald-500 mr-1"></i> Secure 256-Bit SSL</span>
                    <span class="flex items-center"><i class="fa-brands fa-whatsapp text-emerald-500 mr-1"></i> Direct WhatsApp Order Verification</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar (Omoja Mobile First Feel) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-emerald-950/95 backdrop-blur-md border-t border-gold-600/30 text-white z-50 flex items-center justify-around py-2.5 shadow-2xl">
        <a href="index.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-solid fa-house text-lg"></i>
            <span class="text-[10px] font-medium mt-1">Home</span>
        </a>
        <a href="shop.php" class="flex flex-col items-center text-center text-stone-300 hover:text-gold-400 transition-colors">
            <i class="fa-solid fa-grid-2 text-lg"></i>
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
