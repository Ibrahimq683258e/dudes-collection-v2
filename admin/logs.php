<?php
/**
 * Admin Security Audit Logs Module
 */

$pageTitle = "Security Audit Logs";

require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$auditModel = new AuditLog();
$logs = $auditModel->getAllLogs(100);

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">System Security & Audit Logs</h1>
        <p class="text-xs text-stone-400 mt-1">Audit trail of all administrative actions, product modifications, and user status changes.</p>
    </div>
</div>

<div class="bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-stone-300">
            <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                <tr>
                    <th class="p-4">Timestamp</th>
                    <th class="p-4">Action</th>
                    <th class="p-4">Admin / User</th>
                    <th class="p-4">Details</th>
                    <th class="p-4">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-stone-800/40">
                        <td class="p-4 text-stone-400 font-mono text-[10px]"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        <td class="p-4 font-bold text-gold-400"><?= escape_output($log['action']) ?></td>
                        <td class="p-4 font-bold text-white"><?= escape_output($log['user_name'] ?? 'System / Anonymous') ?></td>
                        <td class="p-4 text-stone-300 max-w-xs truncate"><?= escape_output($log['details']) ?></td>
                        <td class="p-4 font-mono text-[10px] text-emerald-400"><?= escape_output($log['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
