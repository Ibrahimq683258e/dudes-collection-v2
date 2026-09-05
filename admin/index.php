<?php
/**
 * Admin Dashboard & Sales Analytics
 */

$pageTitle = "Dashboard Analytics Overview";

require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$productModel = new Product();
$orderModel = new Order();
$userModel = new User();

// Fetch Core Statistics
$totalProducts = $productModel->getTotalCount();
$totalOrders = $orderModel->getTotalOrderCount();
$totalRevenue = $orderModel->getTotalRevenue();
$totalCustomers = count($userModel->getAllCustomers());

// Fetch Recent 5 Orders
$recentOrders = array_slice($orderModel->getAllOrders(), 0, 5);

require_once __DIR__ . '/header.php';
?>

<!-- Title Bar -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Dashboard Overview</h1>
        <p class="text-xs text-stone-400 mt-1">Real-time statistics for Omoja Male Boutique Store.</p>
    </div>
    <a href="../index.php" target="_blank" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-4 py-2 rounded-xl text-xs flex items-center transition-colors">
        <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i> Preview Customer Storefront
    </a>
</div>

<!-- Stat Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-gold-500/10 text-gold-400 flex items-center justify-center text-2xl border border-gold-500/20">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Total Sales Revenue</span>
            <div class="font-serif text-2xl font-bold text-gold-400 mt-0.5">
                <?= CURRENCY_SYMBOL ?><?= number_format($totalRevenue, 2) ?>
            </div>
        </div>
    </div>

    <div class="bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-2xl border border-emerald-500/20">
            <i class="fa-solid fa-bag-shopping"></i>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Total WhatsApp Orders</span>
            <div class="font-serif text-2xl font-bold text-white mt-0.5">
                <?= $totalOrders ?>
            </div>
        </div>
    </div>

    <div class="bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-2xl border border-blue-500/20">
            <i class="fa-solid fa-shirt"></i>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Total Products</span>
            <div class="font-serif text-2xl font-bold text-white mt-0.5">
                <?= $totalProducts ?>
            </div>
        </div>
    </div>

    <div class="bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-2xl border border-purple-500/20">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Registered Customers</span>
            <div class="font-serif text-2xl font-bold text-white mt-0.5">
                <?= $totalCustomers ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Recent Orders Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Sales Analytics Chart -->
    <div class="lg:col-span-2 bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl space-y-4">
        <div class="flex justify-between items-center pb-4 border-b border-stone-800">
            <h3 class="font-serif font-bold text-lg text-white">Monthly Sales Analytics</h3>
            <span class="text-xs text-gold-400 font-semibold bg-emerald-950 px-3 py-1 rounded-full border border-gold-600/30">2026 Financial Year</span>
        </div>
        <div class="h-64 relative">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Recent Orders Table Preview -->
    <div class="bg-stone-900 border border-stone-800 p-6 rounded-3xl shadow-xl space-y-4">
        <div class="flex justify-between items-center pb-4 border-b border-stone-800">
            <h3 class="font-serif font-bold text-lg text-white">Recent WhatsApp Orders</h3>
            <a href="orders.php" class="text-xs text-gold-400 hover:underline">View All</a>
        </div>

        <div class="space-y-3">
            <?php if (!empty($recentOrders)): ?>
                <?php foreach ($recentOrders as $ro): ?>
                    <div class="p-3 bg-stone-800/60 rounded-xl border border-stone-700/50 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-white block">#<?= escape_output($ro['order_number']) ?></span>
                            <span class="text-stone-400 text-[10px]"><?= escape_output($ro['customer_name']) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="font-serif font-bold text-gold-400 block"><?= CURRENCY_SYMBOL ?><?= number_format($ro['total_amount'], 2) ?></span>
                            <span class="text-[9px] uppercase px-2 py-0.5 rounded font-extrabold <?= $ro['status'] === 'completed' ? 'bg-emerald-950 text-emerald-400' : 'bg-amber-900/60 text-amber-300' ?>"><?= escape_output($ro['status']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-xs text-stone-500 text-center py-6">No recent orders recorded.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Chart.js Setup Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            datasets: [{
                label: 'Monthly Sales Revenue (<?= CURRENCY_SYMBOL ?>)',
                data: [1200, 1900, 2400, 1800, 3200, 4100, 3800, 5200, 6400],
                borderColor: '#d4af37',
                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#ffffff', font: { family: 'Plus Jakarta Sans' } } }
            },
            scales: {
                x: { ticks: { color: '#a8a29e' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                y: { ticks: { color: '#a8a29e' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
