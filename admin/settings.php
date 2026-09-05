<?php
/**
 * Admin Store Settings Module
 */

$pageTitle = "Store & WhatsApp Settings";

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $settingsToUpdate = [
            'store_name' => sanitize_input($_POST['store_name'] ?? 'Omoja Male Boutique'),
            'store_tagline' => sanitize_input($_POST['store_tagline'] ?? ''),
            'whatsapp_number' => sanitize_input($_POST['whatsapp_number'] ?? '233500000000'),
            'contact_email' => sanitize_input($_POST['contact_email'] ?? ''),
            'contact_phone' => sanitize_input($_POST['contact_phone'] ?? ''),
            'currency_symbol' => sanitize_input($_POST['currency_symbol'] ?? 'GH¢')
        ];

        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val");
        foreach ($settingsToUpdate as $k => $v) {
            $stmt->execute([':key' => $k, ':val' => $v]);
        }

        AuditLog::log('Update Store Settings', 'Updated WhatsApp phone number and store settings.');
        set_flash_message('success', 'Store settings updated successfully.');
        header("Location: settings.php");
        exit();
    }
}

// Fetch current settings
$settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Store & WhatsApp Configurations</h1>
        <p class="text-xs text-stone-400 mt-1">Configure your boutique store phone numbers, currency symbols, and details.</p>
    </div>
</div>

<div class="bg-stone-900 border border-stone-800 rounded-3xl p-8 shadow-xl max-w-2xl">
    <form action="settings.php" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Store Name</label>
            <input type="text" name="store_name" value="<?= escape_output($settings['store_name'] ?? 'Omoja Male Boutique') ?>" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Store Tagline / Slogan</label>
            <input type="text" name="store_tagline" value="<?= escape_output($settings['store_tagline'] ?? '') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase text-gold-400 mb-1"><i class="fa-brands fa-whatsapp mr-1"></i> Destination WhatsApp Phone Number (With Country Code)</label>
            <input type="text" name="whatsapp_number" value="<?= escape_output($settings['whatsapp_number'] ?? '233500000000') ?>" required placeholder="e.g. 233500000000" class="w-full bg-stone-800 border border-gold-600/40 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            <span class="text-[10px] text-stone-400 mt-1 block">Customer WhatsApp checkout orders will automatically redirect to this phone number.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Contact Email</label>
                <input type="email" name="contact_email" value="<?= escape_output($settings['contact_email'] ?? 'sales@omoja.shop') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Currency Symbol</label>
                <input type="text" name="currency_symbol" value="<?= escape_output($settings['currency_symbol'] ?? 'GH¢') ?>" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>
        </div>

        <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold px-8 py-3.5 rounded-2xl text-xs uppercase tracking-wider shadow-lg transition-colors">
            Save Store Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
