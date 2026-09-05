<?php
/**
 * Admin Customer Management Module
 */

$pageTitle = "Manage Customers";

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$userModel = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newStatus = sanitize_input($_POST['status'] ?? 'active');

        if ($userId) {
            $userModel->toggleUserStatus($userId, $newStatus);
            AuditLog::log('Toggle Customer Status', "Updated customer #{$userId} status to {$newStatus}");
            set_flash_message('success', "Customer account status updated to '{$newStatus}'.");
        }
        header("Location: customers.php");
        exit();
    }
}

$customers = $userModel->getAllCustomers();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Registered Customer Accounts</h1>
        <p class="text-xs text-stone-400 mt-1">View registered customers, lock or unlock account status.</p>
    </div>
</div>

<div class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-stone-300">
            <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                <tr>
                    <th class="p-4">Customer Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Joined Date</th>
                    <th class="p-4">Account Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800">
                <?php foreach ($customers as $c): ?>
                    <tr class="hover:bg-stone-800/40">
                        <td class="p-4 font-bold text-white"><?= escape_output($c['name']) ?></td>
                        <td class="p-4 font-mono text-stone-400"><?= escape_output($c['email']) ?></td>
                        <td class="p-4"><?= escape_output($c['phone'] ?? 'N/A') ?></td>
                        <td class="p-4 text-stone-400 text-[10px]"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold uppercase <?= $c['status'] === 'active' ? 'bg-emerald-950 text-emerald-400' : 'bg-red-950 text-red-400' ?>">
                                <?= escape_output($c['status']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <form action="customers.php" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="toggle_status" value="1">
                                <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                                <?php if ($c['status'] === 'active'): ?>
                                    <input type="hidden" name="status" value="locked">
                                    <button type="submit" class="bg-red-900 hover:bg-red-800 text-white font-bold py-1 px-3 rounded-lg text-[10px]">Lock Account</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="bg-emerald-900 hover:bg-emerald-800 text-gold-400 font-bold py-1 px-3 rounded-lg text-[10px]">Unlock Account</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
