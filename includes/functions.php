<?php
/**
 * KejaConnect - Helper & Utility Functions
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Sanitize variables for safe HTML display (Prevent XSS)
 * @param string $data
 * @return string
 */
function sanitize($data) {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency to Kenyan Shilling standard (KES)
 * @param float $amount
 * @return string
 */
function format_money($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Log a user action to the audit logs table
 * @param string $action
 * @param string $description
 * @param int|null $user_id
 * @return bool
 */
function log_audit($action, $description, $user_id = null) {
    try {
        $db = get_db_connection();
        $uid = $user_id ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$uid, $action, $description, $ip]);
    } catch (Exception $e) {
        error_log("Audit logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle document or photo file uploads securely
 * @param array $file_array $_FILES['element_name']
 * @param string $target_subfolder 'photos' or 'documents' or 'receipts'
 * @param array $allowed_extensions
 * @return string|false Saved filename on success, or false on rejection
 */
function upload_file($file_array, $target_subfolder, $allowed_extensions) {
    if (!isset($file_array) || $file_array['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Verify upload size boundaries
    if ($file_array['size'] > UPLOAD_MAX_SIZE) {
        set_flash_message('danger', 'File exceeds the maximum limit of ' . (UPLOAD_MAX_SIZE / (1024 * 1024)) . 'MB');
        return false;
    }

    $filename = $file_array['name'];
    $file_tmp = $file_array['tmp_name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_extensions)) {
        set_flash_message('danger', 'Invalid file extension. Allowed list: ' . implode(', ', $allowed_extensions));
        return false;
    }

    // Create target directory structures safely
    $upload_dir = __DIR__ . '/../uploads/' . $target_subfolder . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Use safe collision-free unique name
    $new_filename = uniqid('keja_', true) . '.' . $ext;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file_tmp, $destination)) {
        return 'uploads/' . $target_subfolder . '/' . $new_filename;
    }

    set_flash_message('danger', 'Failed to move uploaded file. Check server permissions.');
    return false;
}

/**
 * Create a simple, modular HTML Pagination Navbar
 * @param int $total_records
 * @param int $records_per_page
 * @param int $current_page
 * @param string $page_param name of GET parameter
 * @return string
 */
function render_pagination($total_records, $records_per_page, $current_page, $page_param = 'page') {
    $total_pages = ceil($total_records / $records_per_page);
    if ($total_pages <= 1) {
        return '';
    }

    $html = '<nav class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6" aria-label="Pagination">';
    
    // Summary info
    $start_item = (($current_page - 1) * $records_per_page) + 1;
    $end_item = min($current_page * $records_per_page, $total_records);
    $html .= '<div class="hidden sm:block"><p class="text-sm text-gray-700">Showing <span class="font-medium">' . $start_item . '</span> to <span class="font-medium">' . $end_item . '</span> of <span class="font-medium">' . $total_records . '</span> records</p></div>';

    $html .= '<div class="flex flex-1 justify-between sm:justify-end gap-x-2">';
    
    // Back URL query params preservation
    $query = $_GET;
    
    // Previous button
    if ($current_page > 1) {
        $query[$page_param] = $current_page - 1;
        $prev_url = '?' . http_build_query($query);
        $html .= '<a href="' . $prev_url . '" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">Previous</a>';
    } else {
        $html .= '<span class="relative inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-400 ring-1 ring-inset ring-gray-200 cursor-not-allowed">Previous</span>';
    }

    // Next button
    if ($current_page < $total_pages) {
        $query[$page_param] = $current_page + 1;
        $next_url = '?' . http_build_query($query);
        $html .= '<a href="' . $next_url . '" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">Next</a>';
    } else {
        $html .= '<span class="relative inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-400 ring-1 ring-inset ring-gray-200 cursor-not-allowed">Next</span>';
    }

    $html .= '</div></nav>';
    return $html;
}

/**
 * Returns color code or css class list corresponding to status values
 * @param string $status
 * @return string CSS class list
 */
function get_status_badge_class($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'active':
        case 'confirmed':
        case 'occupied':
        case 'resolved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'suspended':
        case 'rejected':
        case 'terminated':
        case 'urgent':
        case 'high':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'pending':
        case 'in_progress':
        case 'medium':
        case 'maintenance':
            return 'bg-yellow-105 text-yellow-800 border-yellow-200';
        case 'open':
        case 'vacant':
        case 'low':
        default:
            return 'bg-blue-100 text-blue-800 border-blue-200';
    }
}
