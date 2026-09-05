<?php
/**
 * Admin Promotional Popups Management Module
 */

$pageTitle = "Manage Promotional Popups";

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $action = $_POST['post_action'] ?? '';

        if ($action === 'save_popup') {
            $title = sanitize_input($_POST['title'] ?? '');
            $subtitle = sanitize_input($_POST['subtitle'] ?? '');
            $buttonText = sanitize_input($_POST['button_text'] ?? 'Shop Collection');
            $buttonLink = sanitize_input($_POST['button_link'] ?? 'shop.php');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $id = (int)($_POST['id'] ?? 0);

            $imagePath = null;
            if (isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] === UPLOAD_ERR_OK) {
                $upload = FileUploader::uploadImage('popup_image', 'uploads/popups/');
                if ($upload['status']) {
                    $imagePath = $upload['file_path'];
                }
            }

            if ($id) {
                if ($imagePath) {
                    $stmt = $db->prepare("UPDATE popups SET title = :t, subtitle = :s, image_path = :img, button_text = :bt, button_link = :bl, is_active = :a WHERE id = :id");
                    $stmt->execute([':t' => $title, ':s' => $subtitle, ':img' => $imagePath, ':bt' => $buttonText, ':bl' => $buttonLink, ':a' => $isActive, ':id' => $id]);
                } else {
                    $stmt = $db->prepare("UPDATE popups SET title = :t, subtitle = :s, button_text = :bt, button_link = :bl, is_active = :a WHERE id = :id");
                    $stmt->execute([':t' => $title, ':s' => $subtitle, ':bt' => $buttonText, ':bl' => $buttonLink, ':a' => $isActive, ':id' => $id]);
                }
            } else {
                $stmt = $db->prepare("INSERT INTO popups (title, subtitle, image_path, button_text, button_link, is_active) VALUES (:t, :s, :img, :bt, :bl, :a)");
                $stmt->execute([':t' => $title, ':s' => $subtitle, ':img' => $imagePath, ':bt' => $buttonText, ':bl' => $buttonLink, ':a' => $isActive]);
            }

            AuditLog::log('Manage Popup Ad', "Saved promotional popup: {$title}");
            set_flash_message('success', 'Promotional popup ad saved.');
        } elseif ($action === 'delete_popup') {
            $pid = (int)($_POST['id'] ?? 0);
            if ($pid) {
                $stmt = $db->prepare("DELETE FROM popups WHERE id = :id");
                $stmt->execute([':id' => $pid]);
                AuditLog::log('Delete Popup Ad', "Deleted popup #{$pid}");
                set_flash_message('success', 'Promotional popup deleted.');
            }
        }
        header("Location: popups.php");
        exit();
    }
}

$popups = $db->query("SELECT * FROM popups ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Promotional Ads & Popups System</h1>
        <p class="text-xs text-stone-400 mt-1">Configure modal popup promotional ads displayed to site visitors.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Add Popup Form -->
    <div class="bg-stone-900 border border-stone-800 rounded-3xl p-6 shadow-xl h-fit space-y-4">
        <h3 class="font-serif font-bold text-lg text-gold-400 pb-3 border-b border-stone-800">Add Promotional Popup</h3>

        <form action="popups.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save_popup">

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Ad Title *</label>
                <input type="text" name="title" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Subtitle / Offer Text</label>
                <input type="text" name="subtitle" placeholder="e.g. Get 15% off your first order!" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Button Text</label>
                    <input type="text" name="button_text" value="Shop Now" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Button Link</label>
                    <input type="text" name="button_link" value="shop.php" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-gold-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Popup Banner Image Upload</label>
                <input type="file" name="popup_image" accept="image/*" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-300">
            </div>

            <label class="flex items-center space-x-2 cursor-pointer text-xs text-stone-300">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-gold-500 focus:ring-0">
                <span>Active Banner Popup</span>
            </label>

            <button type="submit" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-colors">
                Save Promotional Ad
            </button>
        </form>
    </div>

    <!-- Popups List -->
    <div class="lg:col-span-2 bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-300">
                <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="p-4">Title</th>
                        <th class="p-4">Subtitle</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800">
                    <?php foreach ($popups as $p): ?>
                        <tr class="hover:bg-stone-800/40">
                            <td class="p-4 font-bold text-white"><?= escape_output($p['title']) ?></td>
                            <td class="p-4 text-stone-400"><?= escape_output($p['subtitle']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?= $p['is_active'] ? 'bg-emerald-950 text-emerald-400' : 'bg-stone-800 text-stone-500' ?>">
                                    <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <form action="popups.php" method="POST" class="inline" onsubmit="return confirm('Delete this popup ad?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="post_action" value="delete_popup">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="text-red-400 hover:underline"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
