<?php
/**
 * KejaConnect - Reset Password Controller
 * Updates secure database user pass crypts
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$token_raw = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$error = '';
$success = false;

if (empty($token_raw) || empty($email)) {
    $error = 'Invalid or expired recovery parameters.';
}

$db = get_db_connection();

// Verify token in DB
$token_hash = hash('sha256', $token_raw);
$stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() LIMIT 1");
$stmt->execute([$email, $token_hash]);
$reset_record = $stmt->fetch();

if (!$reset_record) {
    $error = 'The password reset token is invalid, has been used, or has expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $new_password = $_POST['password'] ?? '';
    $match_password = $_POST['confirm_password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf)) {
        $error = 'Security check code expired. Try again.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Passwords must contain at least 8 characters.';
    } elseif ($new_password !== $match_password) {
        $error = 'Passwords do not match.';
    } else {
        // Build encrypted password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update User Password
        $stmt_update = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt_update->execute([$hashed_password, $email]);

        // Clear used recovery tokens for this email
        $stmt_clear = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt_clear->execute([$email]);

        // Audit log action
        log_audit('password_reset', "Password updated successfully via token-reset for $email", null);
        
        $success = true;
        unset($_SESSION['mock_reset_url']);
    }
}

$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Password Reset | KejaConnect</title>
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
            <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-901">Reset Password Profile</h2>
            <p class="mt-2 text-sm text-gray-400">Apply a new secure dashboard credential</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-xl sm:px-10 border border-gray-100">
            
            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg text-sm text-center">
                    <p class="font-bold mb-2">Password Updated!</p>
                    <p class="mb-4 text-xs text-green-700">Your portal credentials have been successfully updated. You may now continue to login.</p>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="inline-block bg-brand-600 text-white rounded-md px-4 py-2 text-xs font-semibold hover:bg-brand-700 transition-colors">Sign In Here</a>
                </div>
            <?php else: ?>

                <!-- Error notice banner -->
                <?php if (!empty($error)): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3.5 rounded-lg text-sm font-medium">
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($error)): ?>
                    <form class="space-y-6" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-750">New Secure Password</label>
                            <span class="text-xs text-gray-400 block mb-1">Minimum 8 characters containing mixed digits</span>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-750">Repeat Password</label>
                            <div class="mt-1">
                                <input id="confirm_password" name="confirm_password" type="password" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition-colors">Save New Secure Password</button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="mt-6 text-center text-sm">
                    <a href="<?php echo BASE_URL; ?>/login.php" class="font-semibold text-brand-600 hover:text-brand-750">&larr; Cancel and return</a>
                </div>

            <?php endif; ?>

        </div>
    </div>

</body>
</html>
