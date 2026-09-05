<?php
/**
 * User Registration Page with Security CAPTCHA & Password Policy Validation
 */

$pageTitle = "Create Customer Account";

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/includes/captcha.php';
require_once __DIR__ . '/includes/security.php';

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } elseif (!Captcha::verify($_POST['captcha'] ?? '')) {
        $errors[] = "Incorrect security CAPTCHA code.";
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = sanitize_input($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $address = sanitize_input($_POST['address'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');

        if (!$email) $errors[] = "Please provide a valid email address.";
        if (empty($name)) $errors[] = "Full name is required.";
        if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            $userModel = new User();
            $regResult = $userModel->register($name, $email, $phone, $password, $address, $city);

            if ($regResult['status']) {
                set_flash_message('success', $regResult['message']);
                header("Location: login.php");
                exit();
            } else {
                $errors[] = $regResult['message'];
            }
        }
    }
}

$captchaSvg = Captcha::generate();

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-16 bg-stone-50 dark:bg-stone-950 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full px-4">
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-8 shadow-xl border border-stone-200 dark:border-stone-800">

            <div class="text-center mb-8">
                <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Join Dude's Collection</span>
                <h1 class="font-serif text-3xl font-bold text-stone-900 dark:text-white mt-1">Create Account</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-800 text-white p-4 rounded-xl mb-6 text-xs space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <div>• <?= escape_output($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Full Name *</label>
                    <input type="text" name="name" value="<?= escape_output($_POST['name'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Email *</label>
                        <input type="email" name="email" value="<?= escape_output($_POST['email'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?= escape_output($_POST['phone'] ?? '') ?>" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Password *</label>
                        <input type="password" name="password" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Confirm Password *</label>
                        <input type="password" name="confirm_password" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>
                </div>

                <!-- CAPTCHA Verification -->
                <div class="pt-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Security Verification *</label>
                    <div class="flex items-center space-x-4">
                        <div><?= $captchaSvg ?></div>
                        <input type="text" name="captcha" placeholder="Enter code" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider mt-4">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-stone-500">
                Already have an account? <a href="login.php" class="text-gold-600 dark:text-gold-400 font-bold hover:underline">Log in here</a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
