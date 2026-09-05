<?php
/**
 * Admin Categories Management Module
 */

$pageTitle = "Manage Boutique Categories";

require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

$categoryModel = new Category();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid security token.');
    } else {
        $action = $_POST['post_action'] ?? '';
        if ($action === 'save_category') {
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = sanitize_input($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            $description = sanitize_input($_POST['description'] ?? '');
            $id = (int)($_POST['id'] ?? 0);

            if (!empty($name)) {
                $data = [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];

                if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
                    $upload = FileUploader::uploadImage('category_image', 'uploads/categories/');
                    if ($upload['status']) {
                        $data['image'] = $upload['file_path'];
                    }
                }

                $categoryModel->save($data);
                AuditLog::log('Manage Category', "Saved category: {$name}");
                set_flash_message('success', "Category '{$name}' saved successfully.");
            }
        } elseif ($action === 'delete_category') {
            $cid = (int)($_POST['id'] ?? 0);
            if ($cid) {
                $categoryModel->delete($cid);
                AuditLog::log('Delete Category', "Deleted category #{$cid}");
                set_flash_message('success', "Category deleted.");
            }
        }
        header("Location: categories.php");
        exit();
    }
}

$categories = $categoryModel->getAll();

require_once __DIR__ . '/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="font-serif text-3xl font-bold text-white">Categories Management</h1>
        <p class="text-xs text-stone-400 mt-1">Organize Kaftans, Agbada sets, and native blazers into boutique collections.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Add Category Form -->
    <div class="bg-stone-900 border border-stone-800 rounded-3xl p-6 shadow-xl h-fit space-y-4">
        <h3 class="font-serif font-bold text-lg text-gold-400 pb-3 border-b border-stone-800">Add New Category</h3>

        <form action="categories.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="post_action" value="save_category">

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Category Name *</label>
                <input type="text" name="name" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Slug (Optional)</label>
                <input type="text" name="slug" placeholder="e.g. traditional-kaftans" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-gold-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full bg-stone-800 border border-stone-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-gold-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Category Image</label>
                <input type="file" name="category_image" accept="image/*" class="w-full bg-stone-800 border border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-300">
            </div>

            <label class="flex items-center space-x-2 cursor-pointer text-xs text-stone-300">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-gold-500 focus:ring-0">
                <span>Active</span>
            </label>

            <button type="submit" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-colors">
                Save Category
            </button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="lg:col-span-2 bg-stone-900 border border-stone-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-300">
                <thead class="bg-emerald-950 text-gold-400 uppercase text-[10px] tracking-wider font-bold">
                    <tr>
                        <th class="p-4">Category Name</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Products</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-stone-800/40">
                            <td class="p-4 font-bold text-white"><?= escape_output($cat['name']) ?></td>
                            <td class="p-4 font-mono text-stone-400"><?= escape_output($cat['slug']) ?></td>
                            <td class="p-4 font-bold text-gold-400"><?= (int)$cat['product_count'] ?> Garments</td>
                            <td class="p-4 text-right">
                                <form action="categories.php" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="post_action" value="delete_category">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
