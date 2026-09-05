<?php
/**
 * Admin Dedicated Login Page
 */

$pageTitle = "Admin Portal Login";

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Models.php';
require_once __DIR__ . '/../includes/security.php';

if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    header("Location: index.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token.";
    } elseif (!check_rate_limit('admin_login', 5, 60)) {
        $errors[] = "Too many login attempts. Please wait 60 seconds.";
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $res = $userModel->login($email, $password);

        if ($res['status']) {
            if ($res['user']['role'] !== 'admin') {
                session_unset();
                session_destroy();
                $errors[] = "Access Denied. You do not have administrator privileges.";
            } else {
                AuditLog::log('Admin Login', 'Administrator signed into admin dashboard.');
                header("Location: index.php");
                exit();
            }
        } else {
            $errors[] = $res['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login | Omoja Male Boutique</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-stone-950 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-stone-900 rounded-3xl p-8 border border-gold-600/30 shadow-2xl space-y-6">

            <div class="text-center space-y-2">
                <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-yellow-600 to-yellow-400 flex items-center justify-center text-emerald-950 font-bold text-2xl mx-auto shadow-lg">
                    O
                </div>
                <h1 class="text-2xl font-bold tracking-wider text-white">ADMIN PORTAL</h1>
                <p class="text-xs text-stone-400">Omoja Male Boutique Store Management</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-900/80 text-white p-4 rounded-xl text-xs space-y-1 border border-red-500/30">
                    <?php foreach ($errors as $e): ?>
                        <div>• <?= escape_output($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Admin Email</label>
                    <input type="email" name="email" value="<?= escape_output($_POST['email'] ?? 'admin@omoja.shop') ?>" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-yellow-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full bg-stone-800 border border-stone-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-yellow-500">
                </div>

                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider">
                    Authenticate Admin
                </button>
            </form>

            <div class="text-center pt-2">
                <a href="../index.php" class="text-xs text-stone-500 hover:text-stone-300">&larr; Return to Customer Storefront</a>
            </div>

        </div>
    </div>
</body>
</html>
