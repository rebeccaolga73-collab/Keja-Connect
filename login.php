<?php
/**
 * KejaConnect - Secure Entry Login Portal
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already authenticated
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            break;
        case 'landlord':
            header('Location: ' . BASE_URL . '/landlord/dashboard.php');
            break;
        case 'tenant':
            header('Location: ' . BASE_URL . '/tenant/dashboard.php');
            break;
    }
    exit;
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!validate_csrf_token($csrf)) {
        $error = 'Security check failed. Please refresh and try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Both email and password fields are required.';
    } else {
        // Authenticate user
        if (login_user($email, $password)) {
            // Authentication succeeded, redirect based on role
            $role = $_SESSION['user_role'] ?? '';
            switch ($role) {
                case 'admin':
                    header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    break;
                case 'landlord':
                    header('Location: ' . BASE_URL . '/landlord/dashboard.php');
                    break;
                case 'tenant':
                    header('Location: ' . BASE_URL . '/tenant/dashboard.php');
                    break;
                default:
                    // Fallback mismatch
                    logout_user();
                    $error = 'Invalid workspace role configuration.';
                    break;
            }
            exit;
        } else {
            $error = 'Invalid email or password credentials.';
        }
    }
}

// Generate secure CSRF token
$csrf_token = get_csrf_token();
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal Login | KejaConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            600: '#1a6b3c',
                            accent: '#f0a500'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>/" class="inline-flex items-center space-x-2">
                <span class="text-3xl font-extrabold font-brand tracking-tight text-brand-600">Keja<span class="text-brand-accent">Connect</span></span>
            </a>
            <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-905">Sign in to your account</h2>
            <p class="mt-2 text-sm text-gray-500">Authenticating database gateway</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-xl sm:px-10 border border-gray-100">
            
            <!-- ALERT FLASH / GENERAL ERROR MESSAGES -->
            <?php if (!empty($error)): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-850 p-3.5 rounded-lg text-sm font-medium flex items-center space-x-2">
                    <svg class="h-5 w-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span><?php echo sanitize($error); ?></span>
                </div>
            <?php elseif ($flash): ?>
                <div class="mb-4 p-3.5 rounded-lg border text-sm font-medium flex items-center space-x-2 <?php
                    echo ($flash['type'] === 'success') ? 'bg-green-50 text-green-800 border-green-200' : 'bg-yellow-50 text-yellow-850 border-yellow-200';
                ?>">
                    <span><?php echo sanitize($flash['text']); ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="<?php echo BASE_URL; ?>/login.php" method="POST">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-750">Registered Email Address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo sanitize($email); ?>" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-750">Password</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                    </div>

                    <div class="text-sm">
                        <a href="<?php echo BASE_URL; ?>/forgot_password.php" class="font-medium text-brand-600 hover:text-brand-700">Forgot your password?</a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition-colors">Sign in to Dashboard</button>
                </div>
            </form>

            <!-- Registration Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="<?php echo BASE_URL; ?>/register.php" class="font-medium text-brand-600 hover:text-brand-700">Register here</a>
                </p>
            </div>

        </div>
    </div>

</body>
</html>
