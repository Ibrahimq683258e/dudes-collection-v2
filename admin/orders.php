<?php
/**
 * Admin Orders Management Module
 */

$pageTitle = "Manage Customer WhatsApp Orders";

require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$orderModel = new Order();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? 'pending');

        if ($orderId) {
            $orderModel->updateStatus($orderId, $status);
            AuditLog::log('Update Order Status', "Updated order #{$orderId} status to {$status}");
            set_flash_message('success', "Order status updated to '{$status}'.");
        }
        header("Location: orders.php");
        exit();
    }
}

$orders = $orderModel->getAllOrders();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">WhatsApp Orders Management</h1>
        <p class="text-xs text-stone-400 mt-1">Review customer delivery requests, tailoring notes, and update order status.</p>
    </div>
</div>

<div class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-stone-300">
            <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                <tr>
                    <th class="p-4">Order Ref</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Phone / City</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Update Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800">
                <?php foreach ($orders as $ord): ?>
                    <tr class="hover:bg-stone-800/40">
                        <td class="p-4 font-bold text-white">#<?= escape_output($ord['order_number']) ?></td>
                        <td class="p-4">
                            <span class="font-bold text-white block"><?= escape_output($ord['customer_name']) ?></span>
                            <span class="text-stone-400 text-[10px]"><?= escape_output($ord['customer_email']) ?></span>
                        </td>
                        <td class="p-4">
                            <span class="block font-bold text-emerald-400"><i class="fa-brands fa-whatsapp mr-1"></i><?= escape_output($ord['customer_phone']) ?></span>
                            <span class="text-stone-400 text-[10px]"><?= escape_output($ord['delivery_city']) ?></span>
                        </td>
                        <td class="p-4 font-serif font-bold text-gold-400"><?= CURRENCY_SYMBOL ?><?= number_format($ord['total_amount'], 2) ?></td>
                        <td class="p-4 text-stone-400 text-[10px]"><?= date('M d, Y H:i', strtotime($ord['created_at'])) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold uppercase <?= $ord['status'] === 'completed' ? 'bg-emerald-950 text-emerald-400' : ($ord['status'] === 'cancelled' ? 'bg-red-950 text-red-400' : 'bg-amber-950 text-amber-300') ?>">
                                <?= escape_output($ord['status']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <form action="orders.php" method="POST" class="inline-flex items-center space-x-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="bg-stone-800 border border-stone-700 text-white rounded-lg py-1 px-2 text-xs focus:outline-none">
                                    <option value="pending" <?= $ord['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $ord['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="completed" <?= $ord['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $ord['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
