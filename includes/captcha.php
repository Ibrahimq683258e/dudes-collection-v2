<?php
/**
 * CAPTCHA Generator & Validator
 * Uses SVG inline image output without requiring external GD library.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Captcha {
    public static function generate() {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }

        $_SESSION['captcha_code'] = strtolower($code);

        // Generate SVG graphic
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="130" height="42" viewBox="0 0 130 42" class="rounded border border-amber-600/30 select-none bg-emerald-950">';

        // Random background noise lines
        for ($i = 0; $i < 6; $i++) {
            $x1 = rand(0, 130);
            $y1 = rand(0, 42);
            $x2 = rand(0, 130);
            $y2 = rand(0, 42);
            $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#c5a059" stroke-opacity="0.3" stroke-width="1.5"/>', $x1, $y1, $x2, $y2);
        }

        // Render text letters with random rotation
        for ($i = 0; $i < strlen($code); $i++) {
            $x = 18 + ($i * 22);
            $y = rand(26, 32);
            $angle = rand(-20, 20);
            $svg .= sprintf('<text x="%d" y="%d" transform="rotate(%d %d %d)" fill="#d4af37" font-family="monospace" font-weight="bold" font-size="22">%s</text>', $x, $y, $angle, $x, $y, $code[$i]);
        }

        $svg .= '</svg>';
        return $svg;
    }

    public static function verify($userInput) {
        if (!isset($_SESSION['captcha_code']) || empty($userInput)) {
            return false;
        }
        $isValid = (strtolower(trim($userInput)) === $_SESSION['captcha_code']);
        unset($_SESSION['captcha_code']); // One-time use captcha
        return $isValid;
    }
}
