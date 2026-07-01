<?php
/**
 * KejaConnect - Core Reusable Header Layout
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notifications.php';

// Assert permission rules exist
require_login();

$user_role = $_SESSION['user_role'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'System User';
$user_email = $_SESSION['user_email'] ?? '';
$unread_notices = get_unread_notices_count($_SESSION['user_id'] ?? 0);
$profile_avatar = $_SESSION['user_profile_photo'] ?? '';

// Basic page highlighting
$current_uri = $_SERVER['REQUEST_URI'];
function is_active_menu($segment) {
    global $current_uri;
    return strpos($current_uri, $segment) !== false ? 'text-white bg-green-800' : 'text-green-100 hover:text-white hover:bg-green-700';
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . " | " . APP_NAME : APP_NAME; ?></title>
    <!-- Google Fonts Inter & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind v3 / v4 Play CDN for quick client styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9f3',
                            100: '#dcf0e2',
                            600: '#1a6b3c', // Deep Green
                            700: '#155630',
                            accent: '#f0a500' // Gold Accent
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-brand { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col md:flex-row overflow-hidden">

<!-- MOBILE NAV TOPBAR -->
<div class="md:hidden bg-brand-600 text-white flex items-center justify-between px-4 py-3 border-b border-green-700 shadow-sm shrink-0">
    <div class="flex items-center space-x-2">
        <span class="text-2xl font-bold font-brand text-brand-accent">Keja<span class="text-white">Connect</span></span>
    </div>
    <div class="flex items-center space-x-4">
        <button id="mobileMenuBtn" class="p-1 rounded-md text-green-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
    </div>
</div>

<!-- SIDEBAR NAVIGATION (Desktop and responsive mobile container toggled via JS) -->
<aside id="sidebar" class="bg-brand-600 text-white w-64 flex flex-col justify-between shadow-xl transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out fixed inset-y-0 left-0 z-40 md:static shrink-0 md:flex">
    
    <div>
        <!-- Sidebar Brand Title logo -->
        <div class="p-6 bg-brand-700 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-extrabold font-brand tracking-tight text-brand-accent">Keja<span class="text-white">Connect</span></span>
            </div>
            <!-- Close mobile drawer -->
            <button id="closeSidebarBtn" class="md:hidden text-green-100 hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Role Badge and welcome metadata -->
        <div class="px-6 py-4 border-b border-green-700 bg-brand-700/40">
            <p class="text-xs text-green-200 font-medium tracking-wide uppercase">Workspace</p>
            <h4 class="text-sm font-bold mt-0.5 truncate"><?php echo sanitize($user_name); ?></h4>
            <span class="inline-flex mt-1 items-center rounded-full bg-brand-accent px-2 py-0.5 text-xs font-semibold text-brand-700 uppercase">
                <?php echo sanitize($user_role); ?>
            </span>
        </div>

        <!-- NAVIGATION LINKS ACCORDING TO USER ROLES -->
        <nav class="p-4 space-y-1 overflow-y-auto">
            
            <?php if ($user_role === 'admin'): ?>
                <!-- ADMIN ROUTES -->
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo is_active_menu('admin/dashboard.php'); ?>">
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/users/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('admin/users/'); ?>">
                    <span>User Management</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/properties/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('admin/properties/'); ?>">
                    <span>Properties & Units</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/reports/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('admin/reports/'); ?>">
                    <span>Financial Reports</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/notices/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('admin/notices/'); ?>">
                    <span>Global Broadcasts</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/settings/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('admin/settings/'); ?>">
                    <span>Settings & Logs</span>
                </a>

            <?php elseif ($user_role === 'landlord'): ?>
                <!-- LANDLORD ROUTES -->
                <a href="<?php echo BASE_URL; ?>/landlord/dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo is_active_menu('landlord/dashboard.php'); ?>">
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/properties/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/properties/'); ?>">
                    <span>My Properties & Units</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/tenants/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/tenants/'); ?>">
                    <span>Tenant Management</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/payments/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/payments/'); ?>">
                    <span>Rent & Payments</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/maintenance/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/maintenance/'); ?>">
                    <span>Maintenance Requests</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/notices/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/notices/'); ?>">
                    <span>Communication Desk</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/documents/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('landlord/documents/'); ?>">
                    <span>Document Vault</span>
                </a>

            <?php elseif ($user_role === 'tenant'): ?>
                <!-- TENANT ROUTES -->
                <a href="<?php echo BASE_URL; ?>/tenant/dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo is_active_menu('tenant/dashboard.php'); ?>">
                    <span>Dashboard Portal</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/tenant/tenancy.php" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('tenant/tenancy.php'); ?>">
                    <span>My Tenancy Details</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/tenant/payments/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('tenant/payments/'); ?>">
                    <span>Settle Rent / Receipts</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/tenant/maintenance/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('tenant/maintenance/'); ?>">
                    <span>Fix Maintenance</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/tenant/notices/" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('tenant/notices/'); ?>">
                    <span>Announcements</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/tenant/profile.php" class="flex items-center space-x-3 text-sm font-medium px-3 py-2.5 rounded-lg transition-colors <?php echo is_active_menu('tenant/profile.php'); ?>">
                    <span>My User Profile</span>
                </a>
            <?php endif; ?>

        </nav>
    </div>

    <!-- SIDEBAR FOOTER & SYSTEM SIGNOUT -->
    <div class="p-4 border-t border-green-700 bg-brand-700/30 flex flex-col space-y-3">
        <a href="<?php echo BASE_URL; ?>/tenant/notices/" class="relative flex items-center justify-between px-3 py-1.5 rounded-md hover:bg-green-700 text-sm font-medium text-green-100 hover:text-white">
            <span>Alerts Box</span>
            <?php if ($unread_notices > 0): ?>
                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white px-1">
                    <?php echo $unread_notices; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center space-x-3 text-sm font-bold text-red-200 hover:text-white hover:bg-red-800 px-3 py-2.5 rounded-lg transition-colors">
            <span>Logout Account</span>
        </a>
    </div>
</aside>

<!-- MAIN VIEWPORT CONTAINER -->
<div class="flex-1 flex flex-col overflow-hidden min-w-0">
    
    <!-- TOP BAR (Desktop navigation & notification quick panel) -->
    <header class="bg-white border-b border-gray-200 py-4 px-6 justify-between flex items-center shrink-0">
        <div>
            <h1 class="text-xl font-bold font-brand text-gray-900"><?php echo isset($page_heading) ? sanitize($page_heading) : "Platform Portal"; ?></h1>
            <p class="text-xs text-gray-500 mt-0.5"><?php echo date('l, F j, Y'); ?> (UTC)</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Global Helpdesk Ticket Button -->
            <span class="hidden sm:inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/10">Kenya Real-Estate Cloud</span>
            
            <div class="relative">
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">View notifications</span>
                    <?php if ($unread_notices > 0): ?>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-brand-accent ring-2 ring-white"></span>
                    <?php endif; ?>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </a>
            </div>

            <!-- Profile Widget -->
            <div class="flex items-center space-x-2">
                <div class="h-8 w-8 rounded-full overflow-hidden bg-brand-100 border border-brand-600 flex items-center justify-center text-brand-700 font-bold text-xs">
                    <?php if (!empty($profile_avatar)): ?>
                        <img class="h-full w-full object-cover" src="<?php echo BASE_URL . '/' . sanitize($profile_avatar); ?>" alt="avatar">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user_name, 0, 2)); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- VIEWPOT MAIN CONTENT PANEL -->
    <main class="flex-1 overflow-y-auto p-6">
        
        <!-- ALERT FLASH NOTIFICATIONS -->
        <?php $flash = get_flash_message(); ?>
        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg border flex items-center justify-between <?php
                switch($flash['type']) {
                    case 'success': echo 'bg-green-50 text-green-800 border-green-200'; break;
                    case 'danger': echo 'bg-red-50 text-red-800 border-red-200'; break;
                    case 'warning': echo 'bg-yellow-50 text-yellow-800 border-yellow-200'; break;
                    default: echo 'bg-blue-50 text-blue-800 border-blue-200'; break;
                }
            ?>">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-semibold"><?php echo sanitize($flash['text']); ?></span>
                </div>
                <button onclick="this.parentElement.remove();" class="text-gray-500 hover:text-gray-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
