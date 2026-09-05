<?php
/**
 * Forgot Password Request Page
 */

$pageTitle = "Forgot Password";

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/security.php';

$message = '';
$messageType = '';
$resetTokenLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Invalid CSRF security token.";
        $messageType = "danger";
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if ($email) {
            $userModel = new User();
            $token = $userModel->createPasswordResetToken($email);

            if ($token) {
                $message = "Password reset instructions have been generated. (In production, an email with a secure link is sent to the user).";
                $messageType = "success";
                $resetTokenLink = "reset_password.php?token=" . urlencode($token);
            } else {
                // Do not reveal email non-existence for security
                $message = "If an account exists with this email address, password reset instructions have been sent.";
                $messageType = "info";
            }
        } else {
            $message = "Please enter a valid email address.";
            $messageType = "danger";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-16 bg-stone-50 dark:bg-stone-950 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full px-4">
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-8 shadow-xl border border-stone-200 dark:border-stone-800">

            <div class="text-center mb-8">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Account Recovery</span>
                <h1 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">Forgot Password</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="p-4 rounded-xl mb-6 text-xs <?= $messageType === 'success' ? 'bg-emerald-950 text-gold-400 border border-gold-600/30' : ($messageType === 'danger' ? 'bg-red-800 text-white' : 'bg-stone-800 text-stone-200') ?>">
                    <div><?= escape_output($message) ?></div>
                    <?php if ($resetTokenLink): ?>
                        <div class="mt-3 pt-2 border-t border-gold-600/30 font-bold">
                            <a href="<?= escape_output($resetTokenLink) ?>" class="text-gold-400 underline">Click here to Reset Password Now &rarr;</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Registered Email Address</label>
                    <input type="email" name="email" value="<?= escape_output($_POST['email'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                </div>

                <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider mt-4">
                    Send Password Reset Link
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-stone-500">
                Remembered your password? <a href="login.php" class="text-gold-600 dark:text-gold-400 font-bold hover:underline">Log in here</a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
