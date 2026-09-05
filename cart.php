<?php
/**
 * Shopping Cart Page
 */

$pageTitle = "Your Shopping Bag";

require_once __DIR__ . '/classes/Models.php';
require_once __DIR__ . '/includes/security.php';

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $action = $_POST['action'] ?? '';
        $itemKey = sanitize_input($_POST['item_key'] ?? '');

        if ($action === 'update') {
            $qty = (int)($_POST['quantity'] ?? 1);
            Cart::update($itemKey, $qty);
            set_flash_message('success', 'Cart updated successfully.');
        } elseif ($action === 'remove') {
            Cart::remove($itemKey);
            set_flash_message('success', 'Item removed from cart.');
        } elseif ($action === 'clear') {
            Cart::clear();
            set_flash_message('info', 'Shopping cart cleared.');
        }
        header("Location: cart.php");
        exit();
    }
}

$cartItems = Cart::getCart();
$cartTotal = Cart::getTotal();

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-12 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mb-8">
            Your Shopping Bag <span class="text-xs font-normal text-stone-500">(<?= count($cartItems) ?> items)</span>
        </h1>

        <?php if (!empty($cartItems)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Cart Items List -->
                <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 space-y-6">
                    <div class="flex justify-between items-center pb-4 border-b border-stone-200 dark:border-stone-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Garment Item</span>
                        <form action="cart.php" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="text-xs text-red-500 hover:underline">Clear All</button>
                        </form>
                    </div>

                    <div class="divide-y divide-stone-100 dark:divide-stone-800">
                        <?php foreach ($cartItems as $itemKey => $item): ?>
                            <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-24 rounded-xl overflow-hidden bg-stone-100 dark:bg-stone-800 flex-shrink-0">
                                        <img src="<?= escape_output($item['image']) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=300&q=80';" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="font-serif font-bold text-sm text-stone-900 dark:text-white"><?= escape_output($item['name']) ?></h3>
                                        <div class="text-xs text-stone-500 mt-1 space-x-2">
                                            <span>Size: <strong class="text-stone-800 dark:text-stone-200"><?= escape_output($item['size']) ?></strong></span>
                                            <span>Color: <strong class="text-stone-800 dark:text-stone-200"><?= escape_output($item['color']) ?></strong></span>
                                        </div>
                                        <div class="text-xs font-bold text-gold-600 dark:text-gold-400 mt-2">
                                            <?= CURRENCY_SYMBOL ?><?= number_format($item['price'], 2) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity & Actions -->
                                <div class="flex items-center space-x-4 w-full sm:w-auto justify-between sm:justify-end">
                                    <form action="cart.php" method="POST" class="flex items-center border border-stone-300 dark:border-stone-700 rounded-lg overflow-hidden">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="item_key" value="<?= escape_output($itemKey) ?>">
                                        <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="50" onchange="this.form.submit()" class="w-14 text-center bg-transparent text-xs font-bold text-stone-900 dark:text-white py-1.5 focus:outline-none">
                                    </form>

                                    <div class="text-xs font-serif font-bold text-emerald-950 dark:text-white w-20 text-right">
                                        <?= CURRENCY_SYMBOL ?><?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </div>

                                    <form action="cart.php" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_key" value="<?= escape_output($itemKey) ?>">
                                        <button type="submit" class="text-stone-400 hover:text-red-500 text-sm"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary Side Panel -->
                <div class="bg-emerald-950 text-white rounded-3xl p-6 shadow-xl border border-gold-600/30 h-fit space-y-6">
                    <h3 class="font-serif font-bold text-lg text-gold-400 border-b border-gold-600/30 pb-3">Order Summary</h3>

                    <div class="space-y-3 text-xs text-stone-300">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-bold text-white"><?= CURRENCY_SYMBOL ?><?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tailoring & Delivery</span>
                            <span class="text-gold-400 font-bold">Free Direct WhatsApp Dispatch</span>
                        </div>
                        <div class="flex justify-between border-t border-gold-600/30 pt-3 text-sm font-bold text-white">
                            <span>Estimated Total</span>
                            <span class="font-serif text-lg text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($cartTotal, 2) ?></span>
                        </div>
                    </div>

                    <a href="checkout.php" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs flex items-center justify-center">
                        <i class="fa-brands fa-whatsapp mr-2 text-base"></i> Proceed to WhatsApp Checkout
                    </a>

                    <p class="text-[10px] text-stone-400 text-center italic">
                        Clicking proceed will collect your delivery address and redirect your order directly to the boutique manager on WhatsApp.
                    </p>
                </div>

            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-12 text-center border border-stone-200 dark:border-stone-800">
                <i class="fa-solid fa-bag-shopping text-5xl text-stone-300 dark:text-stone-700 mb-4"></i>
                <h2 class="font-serif text-2xl font-bold text-stone-800 dark:text-white">Your Shopping Bag is Empty</h2>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-2 mb-6">Explore our royal Kaftans and native blazers to add items to your cart.</p>
                <a href="shop.php" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3 rounded-full text-xs shadow-lg transition-colors">
                    Explore Shop Catalog &rarr;
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
