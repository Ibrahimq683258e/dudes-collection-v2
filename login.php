<?php
/**
 * User Login Page with Account Lockout & Session Security
 */

$pageTitle = "Customer Login";

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/security.php';

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } elseif (!check_rate_limit('login', 5, 60)) {
        $errors[] = "Too many login attempts. Please wait 60 seconds before trying again.";
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = "Please fill in all required fields.";
        } else {
            $userModel = new User();
            $loginResult = $userModel->login($email, $password);

            if ($loginResult['status']) {
                set_flash_message('success', 'Welcome back, ' . escape_output($loginResult['user']['name']) . '!');

                if ($loginResult['user']['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: profile.php");
                }
                exit();
            } else {
                $errors[] = $loginResult['message'];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-16 bg-stone-50 dark:bg-stone-950 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full px-4">
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-8 shadow-xl border border-stone-200 dark:border-stone-800">

            <div class="text-center mb-8">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Welcome Back</span>
                <h1 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">Sign In</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-800 text-white p-4 rounded-xl mb-6 text-xs space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <div>• <?= escape_output($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?= escape_output($_POST['email'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">Password</label>
                        <a href="forgot_password.php" class="text-xs text-gold-600 dark:text-gold-400 hover:underline font-medium">Forgot Password?</a>
                    </div>
                    <input type="password" name="password" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                </div>

                <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider mt-4">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-stone-500">
                Don't have an account? <a href="register.php" class="text-gold-600 dark:text-gold-400 font-bold hover:underline">Create one here</a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
