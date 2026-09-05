<?php
/**
 * Admin Hero Banners Management Module
 */

$pageTitle = "Manage Hero Banners";

require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$bannerModel = new Banner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $action = $_POST['post_action'] ?? '';

        if ($action === 'save_banner') {
            $title = sanitize_input($_POST['title'] ?? '');
            $subtitle = sanitize_input($_POST['subtitle'] ?? '');
            $buttonText = sanitize_input($_POST['button_text'] ?? 'Shop Now');
            $buttonLink = sanitize_input($_POST['button_link'] ?? 'shop.php');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $id = (int)($_POST['id'] ?? 0);

            $imagePath = 'assets/images/banners/hero-1.jpg'; // fallback
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $upload = FileUploader::uploadImage('banner_image', 'uploads/banners/');
                if ($upload['status']) {
                    $imagePath = $upload['file_path'];
                }
            }

            $data = [
                'id' => $id,
                'title' => $title,
                'subtitle' => $subtitle,
                'button_text' => $buttonText,
                'button_link' => $buttonLink,
                'image_path' => $imagePath,
                'sort_order' => $sortOrder,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $bannerModel->save($data);
            AuditLog::log('Manage Banner', "Saved banner: {$title}");
            set_flash_message('success', 'Hero banner saved successfully.');
        } elseif ($action === 'delete_banner') {
            $bid = (int)($_POST['id'] ?? 0);
            if ($bid) {
                $bannerModel->delete($bid);
                AuditLog::log('Delete Banner', "Deleted banner #{$bid}");
                set_flash_message('success', 'Banner deleted.');
            }
        }
        header("Location: banners.php");
        exit();
    }
}

$banners = $bannerModel->getAll();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Hero Banners Management</h1>
        <p class="text-xs text-stone-400 mt-1">Configure front page hero slider slides and promotional text.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Add Banner Form -->
    <div class="bg-stone-900 border border-stone-800 rounded-3xl p-6 shadow-xl h-fit space-y-4">
        <h3 class="font-serif font-bold text-lg text-gold-400 pb-3 border-b border-stone-800">Add New Banner</h3>

        <form action="banners.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save_banner">

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Banner Title *</label>
                <input type="text" name="title" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Subtitle</label>
                <input type="text" name="subtitle" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
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
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Banner Image Upload *</label>
                <input type="file" name="banner_image" accept="image/*" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-300">
            </div>

            <label class="flex items-center space-x-2 cursor-pointer text-xs text-stone-300">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-gold-500 focus:ring-0">
                <span>Active</span>
            </label>

            <button type="submit" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-colors">
                Save Hero Banner
            </button>
        </form>
    </div>

    <!-- Banners List -->
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
                    <?php foreach ($banners as $b): ?>
                        <tr class="hover:bg-stone-800/40">
                            <td class="p-4 font-bold text-white"><?= escape_output($b['title']) ?></td>
                            <td class="p-4 text-stone-400"><?= escape_output($b['subtitle']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?= $b['is_active'] ? 'bg-emerald-950 text-emerald-400' : 'bg-stone-800 text-stone-500' ?>">
                                    <?= $b['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <form action="banners.php" method="POST" class="inline" onsubmit="return confirm('Delete this banner?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="post_action" value="delete_banner">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
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
