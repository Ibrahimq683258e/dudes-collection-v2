<?php
/**
 * Reset Password with Token Page
 */

$pageTitle = "Reset Account Password";

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/security.php';

$token = sanitize_input($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$userModel = new User();

$resetRecord = $token ? $userModel->verifyResetToken($token) : null;

if (!$resetRecord) {
    $errors[] = "Invalid, used or expired password reset token.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetRecord) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        } else {
            $result = $userModel->resetPasswordWithToken($token, $password);
            if ($result['status']) {
                set_flash_message('success', $result['message']);
                header("Location: login.php");
                exit();
            } else {
                $errors[] = $result['message'];
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
                <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Security Reset</span>
                <h1 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">Set New Password</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-800 text-white p-4 rounded-xl mb-6 text-xs space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <div>• <?= escape_output($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($resetRecord): ?>
                <form action="reset_password.php?token=<?= escape_output($token) ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= escape_output($token) ?>">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">New Password</label>
                        <input type="password" name="password" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider mt-4">
                        Update Password & Log In
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="forgot_password.php" class="bg-gold-500 text-emerald-950 font-bold px-6 py-2.5 rounded-full text-xs">Request New Reset Link</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
