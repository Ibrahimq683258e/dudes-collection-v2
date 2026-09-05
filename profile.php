<?php
/**
 * User Profile & Order History Page
 */

$pageTitle = "My Account & Orders";

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userModel = new User();
$orderModel = new Order();

$user = $userModel->findById($_SESSION['user_id']);
$orders = $orderModel->getOrdersByUserId($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid CSRF security token.');
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');

        if (!empty($name)) {
            $userModel->updateProfile($_SESSION['user_id'], $name, $phone, $address, $city);
            $_SESSION['user_name'] = $name;
            set_flash_message('success', 'Profile information updated successfully.');
            header("Location: profile.php");
            exit();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-4 border-b border-stone-200 dark:border-stone-800">
            <div>
                <h1 class="font-serif text-3xl font-bold text-stone-900 dark:text-white">
                    Welcome, <?= escape_output($user['name']) ?>
                </h1>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Manage your order history and saved delivery addresses.</p>
            </div>
            <a href="logout.php" class="mt-4 md:mt-0 text-xs bg-red-800 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-xl transition-colors">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> Log Out
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Order History Panel -->
            <div class="lg:col-span-2 space-y-6">
                <h2 class="font-serif text-xl font-bold text-stone-900 dark:text-white flex items-center">
                    <i class="fa-solid fa-bag-shopping text-gold-500 mr-2"></i> Your Order History
                </h2>

                <?php if (!empty($orders)): ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $ord): ?>
                            <?php $items = $orderModel->getOrderItems($ord['id']); ?>
                            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm space-y-4">
                                <div class="flex flex-wrap items-center justify-between pb-3 border-b border-stone-100 dark:border-stone-800 text-xs gap-2">
                                    <div>
                                        <span class="font-bold text-stone-900 dark:text-white">Order #<?= escape_output($ord['order_number']) ?></span>
                                        <span class="text-stone-400 ml-2"><?= date('M d, Y', strtotime($ord['created_at'])) ?></span>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider <?= $ord['status'] === 'completed' ? 'bg-emerald-950 text-gold-400' : ($ord['status'] === 'cancelled' ? 'bg-red-900 text-white' : 'bg-amber-900/60 text-amber-300') ?>">
                                        <?= escape_output($ord['status']) ?>
                                    </span>
                                </div>

                                <div class="divide-y divide-stone-100 dark:divide-stone-800">
                                    <?php foreach ($items as $it): ?>
                                        <div class="py-2 flex justify-between items-center text-xs">
                                            <div>
                                                <h4 class="font-bold text-stone-800 dark:text-stone-200"><?= escape_output($it['product_name']) ?></h4>
                                                <span class="text-[10px] text-stone-400">Size: <?= escape_output($it['size']) ?> | Color: <?= escape_output($it['color']) ?> | Qty: <?= (int)$it['quantity'] ?></span>
                                            </div>
                                            <span class="font-serif font-bold text-stone-900 dark:text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($it['subtotal'], 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-t border-stone-100 dark:border-stone-800 flex justify-between items-center text-xs font-bold text-stone-900 dark:text-white">
                                    <span>Total Paid: <strong class="font-serif text-sm text-gold-600 dark:text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($ord['total_amount'], 2) ?></strong></span>
                                    <span class="text-[10px] text-emerald-500"><i class="fa-brands fa-whatsapp mr-1"></i> WhatsApp Order Verified</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-stone-900 rounded-2xl p-8 text-center border border-stone-200 dark:border-stone-800">
                        <p class="text-xs text-stone-500">You haven't placed any WhatsApp boutique orders yet.</p>
                        <a href="shop.php" class="mt-3 inline-block bg-gold-500 text-emerald-950 px-6 py-2 rounded-full text-xs font-bold">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Settings Panel -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm h-fit space-y-4">
                <h2 class="font-serif text-xl font-bold text-stone-900 dark:text-white pb-3 border-b border-stone-100 dark:border-stone-800">
                    Profile Information
                </h2>

                <form action="profile.php" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_profile" value="1">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Full Name</label>
                        <input type="text" name="name" value="<?= escape_output($user['name']) ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Email (Read Only)</label>
                        <input type="email" value="<?= escape_output($user['email']) ?>" readonly class="w-full bg-stone-200 dark:bg-stone-800/50 border border-stone-300 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?= escape_output($user['phone']) ?>" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Street Address</label>
                        <input type="text" name="address" value="<?= escape_output($user['address']) ?>" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">City</label>
                        <input type="text" name="city" value="<?= escape_output($user['city']) ?>" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3 rounded-xl shadow transition-colors text-xs uppercase tracking-wider">
                        Save Profile Details
                    </button>
                </form>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
