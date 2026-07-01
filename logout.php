<?php
/**
 * KejaConnect - Logout Controller
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();

// Flash logout notification
set_flash_message('success', 'You have logged out successfully. Have a nice day!');
header('Location: ' . BASE_URL . '/login.php');
exit;
