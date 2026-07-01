<?php
/**
 * KejaConnect - Forgot Password Flow
 * Handles creating a temporary crypted reset token in database
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$email = '';
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf)) {
        $error_msg = 'Security token invalid. Please reload.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Please specify a valid registered email address.';
    } else {
        $db = get_db_connection();
        
        // Ensure user exists first
        $stmt_user = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_user->execute([$email]);
        
        if ($stmt_user->fetch()) {
            // Generate standard crypted token
            $token_raw = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token_raw);
            
            // Set expire in 1 hour
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store in password_resets table
            $stmt_insert = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt_insert->execute([$email, $token_hash, $expires]);

            // Create recovery URL
            $reset_link = BASE_URL . '/reset_password.php?token=' . $token_raw . '&email=' . urlencode($email);

            // Mock email sending
            log_audit('forgot_password_request', "Password recovery initiated for $email", null);

            // Store simulated email content to display in UI for preview testing purposes
            $success_msg = "A password recovery email would have been dispatched. Since you are in a preview sandbox, click the direct link below to simulate the email reset process:";
            $_SESSION['mock_reset_url'] = $reset_link;
        } else {
            // Standard security practice: Don't leak registered addresses, say check inbox
            $success_msg = "If that email address is configured with us, an instructional recovery link has been shared to it.";
        }
    }
}

$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Password | KejaConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <div class="text-center">
            <span class="text-3xl font-extrabold tracking-tight text-brand-600">Keja<span class="text-brand-accent">Connect</span></span>
            <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-901">Password Recovery</h2>
            <p class="mt-2 text-sm text-gray-500">Recover your locked dashboard portal</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-xl sm:px-10 border border-gray-100">
            
            <?php if (!empty($error_msg)): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3.5 rounded-lg text-sm font-medium">
                    <?php echo sanitize($error_msg); ?>
                </div>
            <?php elseif (!empty($success_msg)): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg text-sm">
                    <p class="font-semibold"><?php echo sanitize($success_msg); ?></p>
                    <?php if (isset($_SESSION['mock_reset_url'])): ?>
                        <div class="mt-3 p-3 bg-white border border-green-200 rounded-md">
                            <a href="<?php echo $_SESSION['mock_reset_url']; ?>" class="font-bold text-brand-600 hover:text-brand-700 underline break-all">
                                [Simulation Reset Link] Click here to reset password directly
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Your Registered Email Address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo sanitize($email); ?>" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">Send Recovery Link</button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm">
                <a href="<?php echo BASE_URL; ?>/login.php" class="font-semibold text-brand-600 hover:text-brand-750">&larr; Return to Sign In Screen</a>
            </div>

        </div>
    </div>

</body>
</html>
