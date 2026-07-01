<?php
/**
 * KejaConnect - User Registration Portal
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

$error = '';
$success = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'tenant'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF
    if (!validate_csrf_token($csrf)) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        // Collect form data
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? 'tenant';
        
        // Store form data for re-display
        $formData = [
            'full_name' => sanitize($full_name),
            'email' => sanitize($email),
            'phone' => sanitize($phone),
            'role' => $role
        ];
        
        // Validate inputs
        if (empty($full_name)) {
            $error = 'Full name is required.';
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'A valid email address is required.';
        } elseif (empty($phone)) {
            $error = 'Phone number is required.';
        } elseif (empty($password) || strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $password_confirm) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['landlord', 'tenant'])) {
            $error = 'Invalid role selection.';
        } else {
            // Attempt registration
            $result = register_user($full_name, $email, $phone, $password, $role);
            if ($result === true) {
                $success = 'Registration successful! Please login with your credentials.';
                // Clear form after successful registration
                $formData = [
                    'full_name' => '',
                    'email' => '',
                    'phone' => '',
                    'role' => 'tenant'
                ];
            } else {
                $error = $result; // Error message from register_user function
            }
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
    <title>Register | KejaConnect</title>
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
            <h2 class="mt-6 text-3xl font-extrabold text-slate-900">Create your account</h2>
            <p class="mt-2 text-sm text-slate-600">Join as a landlord or tenant</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
            
            <!-- Flash Message from Session -->
            <?php if ($flash): ?>
                <div class="mb-4 p-4 rounded-lg bg-<?php echo $flash['type']; ?>-50 border border-<?php echo $flash['type']; ?>-200">
                    <p class="text-sm text-<?php echo $flash['type']; ?>-800"><?php echo sanitize($flash['text']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 flex items-start">
                    <svg class="h-5 w-5 text-red-400 mt-0.5 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800"><?php echo sanitize($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 flex items-start">
                    <svg class="h-5 w-5 text-green-400 mt-0.5 mr-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800"><?php echo sanitize($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm"
                        value="<?php echo htmlspecialchars($formData['full_name']); ?>" 
                        placeholder="John Doe">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input type="email" name="email" id="email" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm"
                        value="<?php echo htmlspecialchars($formData['email']); ?>" 
                        placeholder="you@example.com">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm"
                        value="<?php echo htmlspecialchars($formData['phone']); ?>" 
                        placeholder="+254712345678">
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700">I want to register as</label>
                    <select name="role" id="role" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm">
                        <option value="tenant" <?php echo $formData['role'] === 'tenant' ? 'selected' : ''; ?>>Tenant</option>
                        <option value="landlord" <?php echo $formData['role'] === 'landlord' ? 'selected' : ''; ?>>Landlord</option>
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" id="password" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm"
                        placeholder="••••••••"
                        minlength="8">
                    <p class="mt-1 text-xs text-slate-500">At least 8 characters</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" required 
                        class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-brand-600 focus:border-brand-600 sm:text-sm"
                        placeholder="••••••••"
                        minlength="8">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600">
                    Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600">
                    Already have an account? 
                    <a href="<?php echo BASE_URL; ?>/login.php" class="font-medium text-brand-600 hover:text-brand-700">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
