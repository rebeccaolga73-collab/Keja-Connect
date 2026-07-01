<?php
/**
 * KejaConnect - Session & Authentication Helper
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Ensure user is logged in. If not, redirect to login page.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        set_flash_message('warning', 'Please login to access this area.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    // Check if account is active
    if (isset($_SESSION['user_status']) && $_SESSION['user_status'] !== 'active') {
        logout_user();
        set_flash_message('danger', 'Your account has been suspended. Please contact the administrator.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Ensure user belongs to a specific role. Rejects if unauthorized.
 * @param array|string $allowed_roles
 */
function require_role($allowed_roles) {
    require_login();
    
    $user_role = $_SESSION['user_role'] ?? '';
    $allowed = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];

    if (!in_array($user_role, $allowed)) {
        // Log access violation attempt
        $db = get_db_connection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_id = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'access_violation', ?, ?)");
        $stmt->execute([$user_id, "Attempted unauthorized access to a page requiring role(s): " . implode(', ', $allowed), $ip]);

        set_flash_message('danger', 'Access denied: You do not have permissions to view that resource.');
        
        // Redirect to their respective dashboards
        switch ($user_role) {
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
                header('Location: ' . BASE_URL . '/login.php');
                break;
        }
        exit;
    }
}

/**
 * Authenticates login credentials and creates user session
 * @param string $email
 * @param string $password
 * @return bool
 */
function login_user($email, $password) {
    $db = get_db_connection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if ($user && $user['status'] !== 'active') {
        // Log attempt on suspended account
        $stmt_log = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'login_suspended', 'Attempted login on suspended account', ?)");
        $stmt_log->execute([$user['id'], $ip]);
        set_flash_message('danger', 'This account has been suspended by Admin.');
        return false;
    }

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent session fixation issues
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['user_profile_photo'] = $user['profile_photo'];

        // Audit Log
        $stmt_log = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'login_success', 'User logged in successfully', ?)");
        $stmt_log->execute([$user['id'], $ip]);

        return true;
    }

    // Log failed login
    $failed_user_id = $user ? $user['id'] : null;
    $stmt_log = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'login_failed', ?, ?)");
    $stmt_log->execute([$failed_user_id, "Failed login attempt for email: $email", $ip]);

    return false;
}

/**
 * Logs out user and destroys session
 */
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        // Audit log before clearing
        try {
            $db = get_db_connection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $stmt_log = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'logout', 'User logged out', ?)");
            $stmt_log->execute([$_SESSION['user_id'], $ip]);
        } catch (Exception $e) {
            // Silently ignore DB errors during logout
        }
    }

    // Clear session variables
    $_SESSION = [];
    
    // Destroy cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally destroy the session
    @session_destroy();
}

/**
 * Register a new user (tenant or landlord)
 * @param string $full_name
 * @param string $email
 * @param string $phone
 * @param string $password
 * @param string $role 'tenant' or 'landlord'
 * @return bool|string True on success, error message on failure
 */
function register_user($full_name, $email, $phone, $password, $role) {
    try {
        $db = get_db_connection();
        
        // Validate role
        if (!in_array($role, ['tenant', 'landlord'])) {
            return 'Invalid role specified.';
        }
        
        // Check if email already exists
        $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            return 'Email address is already registered.';
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Insert new user
        $stmt_insert = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $result = $stmt_insert->execute([$full_name, $email, $phone, $hashed_password, $role]);
        
        if ($result) {
            // Log registration
            $new_user_id = $db->lastInsertId();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $stmt_log = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, 'registration', ?, ?)");
            $stmt_log->execute([$new_user_id, "New $role account registered", $ip]);
            
            return true;
        } else {
            return 'An error occurred during registration. Please try again.';
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return 'A database error occurred. Please try again later.';
    }
}
