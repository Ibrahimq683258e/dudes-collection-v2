<?php
/**
 * Global Header Component
 */

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../classes/User.php';

$cartCount = Cart::getItemCount();
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $userModel = new User();
    $currentUser = $userModel->findById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape_output($pageTitle) . ' | ' . SITE_NAME : SITE_NAME . ' | Authentic African Male Style' ?></title>
    <meta name="description" content="Modern African-inspired luxury male clothing boutique store. Handcrafted Kaftans, Agbada, Dashiki and Bespoke Native Shirts.">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            800: '#004d25',
                            900: '#003318',
                            950: '#002210',
                        },
                        gold: {
                            400: '#e5c158',
                            500: '#d4af37',
                            600: '#c5a059',
                            700: '#997a38',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Swiper JS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-stone-50 text-stone-800 dark:bg-stone-950 dark:text-stone-100 flex flex-col min-h-screen font-sans pb-16 lg:pb-0">

    <!-- Announcement Top Bar -->
    <div class="bg-emerald-950 text-gold-400 text-xs py-2 px-4 text-center tracking-widest uppercase font-medium border-b border-gold-600/30 flex justify-between items-center">
        <div class="hidden sm:block">Worldwide Express Shipping Available</div>
        <div class="mx-auto sm:mx-0 font-bold">✨ Grand Opening: Free Tailoring Consultation on All Kaftans</div>
        <div class="hidden sm:block"><i class="fa-brands fa-whatsapp text-emerald-400 mr-1">check</i> Direct WhatsApp Ordering Enabled</div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-emerald-900/95 backdrop-blur-md text-white border-b border-gold-600/20 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo -->
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-gold-600 to-gold-400 flex items-center justify-center text-emerald-950 font-sans font-extrabold text-xl shadow-lg group-hover:scale-105 transition-transform">
                    D
                </div>
                <div>
                    <span class="font-sans text-xl font-black tracking-tight text-white block leading-none uppercase">DUDE'S <span class="text-gold-400">COLLECTION</span></span>
                    <span class="text-[9px] uppercase tracking-widest text-gold-400 font-semibold">Men's Premium Store</span>
                </div>
            </a>

            <!-- Desktop Menu Navigation -->
            <nav class="hidden lg:flex items-center space-x-8 font-medium text-sm tracking-wide">
                <a href="index.php" class="hover:text-gold-400 transition-colors py-2 border-b-2 border-transparent hover:border-gold-400">Home</a>
                <a href="shop.php" class="hover:text-gold-400 transition-colors py-2 border-b-2 border-transparent hover:border-gold-400">Shop Catalog</a>
                <a href="shop.php?category=traditional-kaftans" class="hover:text-gold-400 transition-colors py-2">Kaftans</a>
                <a href="shop.php?category=agbadas-ceremonial" class="hover:text-gold-400 transition-colors py-2">Agbadas</a>
                <a href="about.php" class="hover:text-gold-400 transition-colors py-2">Our Story</a>
                <a href="contact.php" class="hover:text-gold-400 transition-colors py-2">Contact</a>
            </nav>

            <!-- Action Buttons & Icons -->
            <div class="flex items-center space-x-5">

                <!-- Search Trigger Form -->
                <form action="shop.php" method="GET" class="hidden sm:flex items-center relative">
                    <input type="text" name="search" placeholder="Search Kaftans..." class="bg-emerald-950/60 border border-gold-600/30 text-white placeholder-stone-400 rounded-full pl-4 pr-9 py-1.5 text-xs focus:outline-none focus:border-gold-400 transition-all w-36 focus:w-48">
                    <button type="submit" class="absolute right-3 text-gold-400 hover:text-white"><i class="fa-solid fa-magnifying-glass text-xs"></i></button>
                </form>

                <!-- Wishlist Icon -->
                <a href="wishlist.php" class="hover:text-gold-400 transition-colors relative p-1" title="Wishlist">
                    <i class="fa-regular fa-heart text-xl"></i>
                </a>

                <!-- Shopping Cart Icon -->
                <a href="cart.php" class="hover:text-gold-400 transition-colors relative p-1" title="Shopping Cart">
                    <i class="fa-solid fa-bag-shopping text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-gold-500 text-emerald-950 text-[10px] font-extrabold w-5 h-5 rounded-full flex items-center justify-center animate-pulse">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Account / Profile -->
                <?php if ($currentUser): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-sm text-stone-200 hover:text-gold-400 focus:outline-none">
                            <i class="fa-solid fa-user-circle text-2xl text-gold-400"></i>
                            <span class="hidden md:inline font-medium"><?= escape_output(explode(' ', $currentUser['name'])[0]) ?></span>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-2 w-48 bg-stone-900 border border-gold-600/30 rounded-xl shadow-2xl py-2 hidden group-hover:block z-50">
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="admin/index.php" class="block px-4 py-2 text-xs text-gold-400 hover:bg-emerald-900 font-bold"><i class="fa-solid fa-gauge mr-2"></i> Admin Panel</a>
                            <?php endif; ?>
                            <a href="profile.php" class="block px-4 py-2 text-xs text-stone-200 hover:bg-emerald-900"><i class="fa-solid fa-user mr-2"></i> My Account & Orders</a>
                            <a href="logout.php" class="block px-4 py-2 text-xs text-red-400 hover:bg-emerald-900"><i class="fa-solid fa-right-from-bracket mr-2"></i> Log Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-gold-600 hover:bg-gold-500 text-emerald-950 px-4 py-1.5 rounded-full text-xs font-bold transition-all shadow-md">
                        Login
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </header>

    <!-- Flash Notifications Container -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <?= display_flash_message() ?>
        </div>
    <?php endif; ?>
