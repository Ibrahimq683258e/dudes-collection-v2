<?php
/**
 * Contact Page with Security CAPTCHA Protection
 */

$pageTitle = "Contact Boutique Atelier";

require_once __DIR__ . '/includes/captcha.php';
require_once __DIR__ . '/includes/security.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF security token.";
    } elseif (!Captcha::verify($_POST['captcha'] ?? '')) {
        $errors[] = "Incorrect security CAPTCHA code.";
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');

        if (!$email) $errors[] = "Please provide a valid email address.";
        if (empty($name)) $errors[] = "Name is required.";
        if (empty($message)) $errors[] = "Message cannot be empty.";

        if (empty($errors)) {
            $successMessage = "Thank you for contacting Omoja Male Boutique atelier! Our tailoring representative will reach out shortly.";
        }
    }
}

$captchaSvg = Captcha::generate();

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-16 bg-stone-50 dark:bg-stone-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up">
            <span class="text-xs font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Bespoke Concierge</span>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 dark:text-white mt-1">Get In Touch</h1>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-2">Have a question about custom fittings, group wedding attire, or measurements? Reach our atelier team.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Contact Info -->
            <div class="bg-emerald-950 text-white rounded-3xl p-8 border border-gold-600/30 space-y-6 shadow-xl h-fit">
                <h3 class="font-serif text-2xl font-bold text-gold-400 border-b border-gold-600/30 pb-4">Atelier Location</h3>

                <div class="space-y-4 text-xs text-stone-300">
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-location-dot text-gold-400 text-base mt-0.5"></i>
                        <span>12 Oxford Street, Osu, Accra, Ghana</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-phone text-gold-400 text-base"></i>
                        <span>+233 50 000 0000</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-envelope text-gold-400 text-base"></i>
                        <span>sales@omoja.shop</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fa-brands fa-whatsapp text-emerald-400 text-base"></i>
                        <span>Direct WhatsApp Orders: Active 24/7</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gold-600/30">
                    <a href="https://wa.me/233500000000" target="_blank" class="w-full bg-gold-500 hover:bg-gold-400 text-emerald-950 font-bold py-3 rounded-2xl transition-colors text-xs flex items-center justify-center">
                        <i class="fa-brands fa-whatsapp mr-2 text-base"></i> Chat Live on WhatsApp
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-3xl p-8 shadow-sm border border-stone-200 dark:border-stone-800">
                <?php if ($successMessage): ?>
                    <div class="bg-emerald-950 text-gold-400 p-6 rounded-2xl text-xs font-bold mb-6 border border-gold-600/30 text-center">
                        <i class="fa-solid fa-circle-check text-2xl block mb-2"></i>
                        <?= escape_output($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-800 text-white p-4 rounded-xl mb-6 text-xs space-y-1">
                        <?php foreach ($errors as $err): ?>
                            <div>• <?= escape_output($err) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" class="space-y-4">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Your Name *</label>
                            <input type="text" name="name" value="<?= escape_output($_POST['name'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Email Address *</label>
                            <input type="email" name="email" value="<?= escape_output($_POST['email'] ?? '') ?>" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Subject</label>
                        <input type="text" name="subject" value="<?= escape_output($_POST['subject'] ?? '') ?>" placeholder="e.g. Bespoke Wedding Kaftans Inquiry" class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1">Your Message *</label>
                        <textarea name="message" rows="4" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl p-3 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500"><?= escape_output($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <!-- Security CAPTCHA -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-2">Security Verification *</label>
                        <div class="flex items-center space-x-4">
                            <div><?= $captchaSvg ?></div>
                            <input type="text" name="captcha" placeholder="Enter code" required class="w-full bg-stone-50 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:border-gold-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-emerald-950 hover:bg-emerald-900 text-gold-400 font-bold py-3.5 rounded-2xl shadow-xl transition-all text-xs uppercase tracking-wider mt-2">
                        Send Message
                    </button>
                </form>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
