<?php
/**
 * KejaConnect - My User Profile (Tenant)
 */
$page_title = "My User Profile";
$page_heading = "My User Profile";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Role check
require_role('tenant');

$user_id = $_SESSION['user_id'];
$db = get_db_connection();
$error = '';
$success = '';

// Fetch user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'tenant'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf)) {
        $error = 'Security check failed.';
    } else {
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        if (empty($full_name) || empty($email) || empty($phone)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check->execute([$email, $user_id]);
            
            if ($stmt_check->fetch()) {
                $error = 'This email is already in use by another account.';
            } else {
                $stmt_update = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                if ($stmt_update->execute([$full_name, $email, $phone, $user_id])) {
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['user_email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        }
    }
}

$csrf_token = get_csrf_token();

include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6 max-w-2xl">
    <!-- Profile Card -->
    <div class="bg-white p-8 rounded-lg border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Profile</h2>

        <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                <?php echo sanitize($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <!-- Full Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                <input type="text" name="full_name" required 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-600"
                    value="<?php echo sanitize($user['full_name'] ?? ''); ?>">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-600"
                    value="<?php echo sanitize($user['email'] ?? ''); ?>">
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                <input type="tel" name="phone" required 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-600"
                    value="<?php echo sanitize($user['phone'] ?? ''); ?>">
            </div>

            <!-- Role (Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                <input type="text" readonly 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50"
                    value="<?php echo ucfirst($user['role']); ?>">
            </div>

            <!-- Status (Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Account Status</label>
                <input type="text" readonly 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50"
                    value="<?php echo ucfirst($user['status']); ?>">
            </div>

            <!-- Joined Date (Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Member Since</label>
                <input type="text" readonly 
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50"
                    value="<?php echo date('M d, Y', strtotime($user['created_at'])); ?>">
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" class="w-full px-6 py-3 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="bg-white p-8 rounded-lg border border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Change Password</h3>
        <a href="<?php echo BASE_URL; ?>/forgot_password.php" class="text-brand-600 hover:text-brand-700 font-medium">
            Click here to reset your password
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
