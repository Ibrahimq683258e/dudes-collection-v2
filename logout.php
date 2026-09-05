<?php
/**
 * User Logout Script
 */

require_once __DIR__ . '/includes/security.php';

// Clear session
session_unset();
session_destroy();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

set_flash_message('info', 'You have been logged out.');
header("Location: login.php");
exit();
