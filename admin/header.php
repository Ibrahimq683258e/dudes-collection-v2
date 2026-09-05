<?php
/**
 * Admin Panel Common Header & Navigation Wrapper (RBAC Protected)
 */

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../classes/Models.php';

// Role-Based Access Control (RBAC) Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    set_flash_message('danger', 'Unauthorized Access. Please login with an administrator account.');
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape_output($pageTitle) . ' | Admin Portal' : 'Omoja Admin Portal' ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: { 950: '#002210', 900: '#003318' },
                        gold: { 400: '#e5c158', 500: '#d4af37', 600: '#c5a059' }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome & Chart.js CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen flex flex-col md:flex-row font-sans">

    <!-- Admin Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-stone-900 border-r border-stone-800 flex-shrink-0 flex flex-col justify-between">
        <div>
            <!-- Admin Brand -->
            <div class="h-20 flex items-center px-6 bg-emerald-950 border-b border-gold-600/30">
                <div class="w-8 h-8 rounded-full bg-gold-500 text-emerald-950 font-bold flex items-center justify-center mr-3">O</div>
                <div>
                    <span class="font-serif font-bold text-white tracking-wider block leading-none">OMOJA<span class="text-gold-400">.</span></span>
                    <span class="text-[9px] uppercase tracking-widest text-gold-400">Admin Control</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="index.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-gauge text-base mr-3 w-5"></i> Dashboard Overview
                </a>
                <a href="products.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-shirt text-base mr-3 w-5"></i> Products Catalog
                </a>
                <a href="categories.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-layer-group text-base mr-3 w-5"></i> Categories
                </a>
                <a href="orders.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-bag-shopping text-base mr-3 w-5"></i> WhatsApp Orders
                </a>
                <a href="customers.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-users text-base mr-3 w-5"></i> Customers
                </a>
                <a href="banners.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'banners.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-images text-base mr-3 w-5"></i> Hero Banners
                </a>
                <a href="popups.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'popups.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-bullhorn text-base mr-3 w-5"></i> Promo Popups
                </a>
                <a href="logs.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-shield-halved text-base mr-3 w-5"></i> Audit Logs
                </a>
                <a href="settings.php" class="flex items-center px-4 py-3 rounded-xl hover:bg-emerald-900/50 hover:text-gold-400 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-emerald-950 text-gold-400 font-bold border border-gold-600/30' : 'text-stone-400' ?>">
                    <i class="fa-solid fa-gear text-base mr-3 w-5"></i> Store Settings
                </a>
            </nav>
        </div>

        <!-- Admin Profile Footer Bar -->
        <div class="p-4 border-t border-stone-800 bg-stone-900/50 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-user-shield text-xl text-gold-400"></i>
                <div class="text-xs">
                    <span class="font-bold block text-white"><?= escape_output($adminName) ?></span>
                    <span class="text-[10px] text-emerald-400">Master Admin</span>
                </div>
            </div>
            <a href="../logout.php" class="text-stone-400 hover:text-red-400 text-sm" title="Log Out"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <main class="flex-grow p-6 md:p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            <?= display_flash_message() ?>
