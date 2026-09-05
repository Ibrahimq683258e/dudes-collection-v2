<?php
/**
 * WhatsApp Direct Checkout Process
 * Collects delivery details, stores order in database, clears cart, and redirects to WhatsApp.
 */

$pageTitle = "Checkout & WhatsApp Direct Order";

require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/security.php';

$cartItems = Cart::getCart();
$cartTotal = Cart::getTotal();

if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $userModel = new User();
    $currentUser = $userModel->findById($_SESSION['user_id']);
}

$errors = [];

// Handle Checkout Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } else {
        $customerName = sanitize_input($_POST['customer_name'] ?? '');
        $customerEmail = sanitize_input($_POST['customer_email'] ?? '');
        $customerPhone = sanitize_input($_POST['customer_phone'] ?? '');
        $deliveryAddress = sanitize_input($_POST['delivery_address'] ?? '');
        $deliveryCity = sanitize_input($_POST['delivery_city'] ?? '');
        $orderNotes = sanitize_input($_POST['order_notes'] ?? '');

        if (empty($customerName)) $errors[] = "Please provide your full name.";
        if (empty($customerPhone)) $errors[] = "Please provide your phone number for delivery.";
        if (empty($deliveryAddress)) $errors[] = "Please enter your delivery street address.";
        if (empty($deliveryCity)) $errors[] = "Please enter your city/location.";

        if (empty($errors)) {
            $orderModel = new Order();
            $customerData = [
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'address' => $deliveryAddress,
                'city' => $deliveryCity,
                'notes' => $orderNotes
            ];

            $userId = $_SESSION['user_id'] ?? null;
            $orderResult = $orderModel->createOrder($customerData, $cartItems, $cartTotal, $userId);

            if ($orderResult['status']) {
                $orderNumber = $orderResult['order_number'];

                // Clear cart after successfully storing order
                Cart::clear();

                // Fetch Store Settings
                $db = Database::getInstance()->getConnection();
                $settingStmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_number' LIMIT 1");
                $settingStmt->execute();
                $whatsappNumber = $settingStmt->fetchColumn() ?: '233500000000';
                $message  = "👑 *NEW ORDER - OMOJA MALE BOUTIQUE* 👑\n";
                $message .= "----------------------------------------\n";
                $message .= "*Order Reference:* #" . $orderNumber . "\n";
                $message .= "*Customer Name:* " . $customerName . "\n";
                $message .= "*Phone:* " . $customerPhone . "\n";
                $message .= "*Delivery Address:* " . $deliveryAddress . ", " . $deliveryCity . "\n";
                if (!empty($orderNotes)) {
                    $message .= "*Tailoring Notes:* " . $orderNotes . "\n";
                }
                $message .= "----------------------------------------\n";
                $message .= "*ORDERED GARMENTS:*\n";

                foreach ($cartItems as $item) {
                    $message .= "• " . $item['name'] . " (Size: " . $item['size'] . ", Color: " . $item['color'] . ") x" . $item['quantity'] . " = " . CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2) . "\n";
                }

                $message .= "----------------------------------------\n";
                $message .= "*TOTAL AMOUNT:* " . CURRENCY_SYMBOL . number_format($cartTotal, 2) . "\n\n";
                $message .= "Hello! I have placed my order on your store. Please confirm my order and send payment/delivery details.";

                $whatsappUrl = "https://wa.me/" . $whatsappNumber . "?text=" . urlencode($message);

                // Render Redirect Page
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Redirecting to WhatsApp...</title>
                    <script src="https://cdn.tailwindcss.com"></script>
                </head>
                <body class="bg-stone-950 text-white min-h-screen flex items-center justify-center p-4">
                    <div class="bg-emerald-950 border border-gold-600/40 p-8 rounded-3xl max-w-md w-full text-center shadow-2xl space-y-6">
                        <div class="w-20 h-20 bg-gold-500 rounded-full flex items-center justify-center text-emerald-950 text-4xl mx-auto animate-bounce">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <h2 class="font-serif text-2xl font-bold text-gold-400">Order Placed Successfully!</h2>
                        <p class="text-xs text-stone-300">Order Ref: <strong class="text-white">#<?= escape_output($orderNumber) ?></strong></p>
                        <p class="text-xs text-stone-300 leading-relaxed">
                            Redirecting you to WhatsApp to chat directly with our store manager and finalize delivery...
                        </p>
                        <a href="<?= escape_output($whatsappUrl) ?>" class="inline-block bg-gold-500 text-emerald-950 font-bold px-8 py-3.5 rounded-full text-xs shadow-xl hover:bg-gold-400 transition-all">
                            Open WhatsApp Now &rarr;
                        </a>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = "<?= $whatsappUrl ?>";
                        }, 1200);
                    </script>
                </body>
                </html>
                <?php
                exit();
            } else {
                $errors[] = "Failed to place order: " . $orderResult['message'];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mb-2">
            Delivery Details & Checkout
        </h1>
        <p class="text-xs text-stone-500 dark:text-stone-400 mb-8">
            Complete your delivery contact information below. When you click <strong class="text-gold-600">Place Order via WhatsApp</strong>, your order will be recorded and sent directly to the store manager.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-800 text-white p-4 rounded-xl mb-6 text-xs space-y-1">
                <?php foreach ($errors as $err): ?>
                    <div>• <?= escape_output($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Delivery Contact Form -->
            <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-stone-200 dark:border-stone-800 space-y-6">
                <form action="checkout.php" method="POST" class="space-y-6">
                    <?= csrf_field() ?>

                    <h3 class="font-serif font-bold text-lg text-stone-900 dark:text-white pb-3 border-b border-stone-200 dark:border-stone-800">
                        Customer Contact Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Full Name *</label>
                            <input type="text" name="customer_name" value="<?= escape_output($_POST['customer_name'] ?? $currentUser['name'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Phone / WhatsApp Number *</label>
                            <input type="tel" name="customer_phone" value="<?= escape_output($_POST['customer_phone'] ?? $currentUser['phone'] ?? '') ?>" placeholder="+233..." required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Email Address</label>
                        <input type="email" name="customer_email" value="<?= escape_output($_POST['customer_email'] ?? $currentUser['email'] ?? '') ?>" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <h3 class="font-serif font-bold text-lg text-stone-900 dark:text-white pt-4 pb-3 border-b border-stone-200 dark:border-stone-800">
                        Delivery Address
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Street Address *</label>
                            <input type="text" name="delivery_address" value="<?= escape_output($_POST['delivery_address'] ?? $currentUser['address'] ?? '') ?>" placeholder="e.g. House 14, Oxford Street" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">City / Location *</label>
                            <input type="text" name="delivery_city" value="<?= escape_output($_POST['delivery_city'] ?? $currentUser['city'] ?? '') ?>" placeholder="e.g. Accra" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Tailoring Notes / Special Requests (Optional)</label>
                        <textarea name="order_notes" rows="3" placeholder="Specify any custom chest measurements, sleeve adjustments or preferred delivery times..." class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl p-3 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500"><?= escape_output($_POST['order_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-4 rounded-2xl shadow-xl transition-all text-sm flex items-center justify-center">
                        <i class="fa-brands fa-whatsapp text-xl mr-2 text-gold-400"></i> Place Order via WhatsApp
                    </button>
                </form>
            </div>

            <!-- Order Review Panel -->
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 h-fit space-y-4">
                <h3 class="font-serif font-bold text-lg text-stone-900 dark:text-white pb-3 border-b border-stone-200 dark:border-stone-800">
                    Order Summary
                </h3>

                <div class="divide-y divide-stone-100 dark:divide-stone-800 max-h-80 overflow-y-auto">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <h4 class="font-bold text-stone-800 dark:text-stone-200"><?= escape_output($item['name']) ?></h4>
                                <span class="text-[10px] text-stone-400">Size: <?= escape_output($item['size']) ?> | Qty: <?= (int)$item['quantity'] ?></span>
                            </div>
                            <span class="font-serif font-bold text-stone-900 dark:text-gold-400">
                                <?= CURRENCY_SYMBOL ?><?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex justify-between text-base font-bold text-stone-900 dark:text-white">
                    <span>Total Amount:</span>
                    <span class="font-serif text-emerald-950 dark:text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($cartTotal, 2) ?></span>
                </div>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
