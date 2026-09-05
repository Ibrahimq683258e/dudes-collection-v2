<?php
/**
 * Modern Online Male Boutique Store Configuration
 */

// Define application base paths
define('APP_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', APP_ROOT . '/uploads');
define('UPLOADS_URL', 'uploads/');

// Site info settings
if (!defined('SITE_NAME')) define('SITE_NAME', "Dude's Collection");
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/omoja'); // Adjust as necessary for deployment
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'GH¢');

// Security Configurations
define('SESSION_LIFETIME', 1800); // 30 minutes idle timeout
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION_MINUTES', 15);
define('PASSWORD_MIN_LENGTH', 8);

// Error Reporting Configuration
// In production, display_errors should be 0, but log errors securely
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/logs/error.log');
error_reporting(E_ALL);

// Ensure logs directory exists
if (!file_exists(APP_ROOT . '/logs')) {
    @mkdir(APP_ROOT . '/logs', 0755, true);
}
