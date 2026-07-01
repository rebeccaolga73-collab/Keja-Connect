<?php
/**
 * KejaConnect - Notifications & Alert Managers
 */
require_once __DIR__ . '/../config/db.php';

/**
 * Send a notification/notice to a specific system user or role broadcast
 * @param int $sender_id Uploader/User ID sending
 * @param int|null $recipient_id Target User ID (NULL for global or broad recipient)
 * @param int|null $tenancy_id Associated tenancy or unit contract
 * @param string $subject Subject header
 * @param string $message Extensive text body of warning/reminder
 * @param string $type Notice category: 'rent_reminder', 'eviction', 'maintenance', 'general'
 * @return bool
 */
function send_notice($sender_id, $recipient_id, $tenancy_id, $subject, $message, $type = 'general') {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO notices (sender_id, recipient_id, tenancy_id, subject, message, type, is_read) VALUES (?, ?, ?, ?, ?, ?, 0)");
        return $stmt->execute([$sender_id, $recipient_id, $tenancy_id, $subject, $message, $type]);
    } catch (Exception $e) {
        error_log("Notification dispatch failures: " . $e->getMessage());
        return false;
    }
}

/**
 * Retrieve current unread notifications count for a specific target user
 * @param int $user_id Logged in active subscriber
 * @return int
 */
function get_unread_notices_count($user_id) {
    try {
        $db = get_db_connection();
        
        // Count directed notices + global broadcasts where sender isn't themselves
        $stmt = $db->prepare("SELECT COUNT(*) FROM notices WHERE (recipient_id = ? OR (recipient_id IS NULL AND sender_id != ?)) AND is_read = 0");
        $stmt->execute([$user_id, $user_id]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Get unread count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mark all notifications as read for a specific user
 * @param int $user_id Logged in active subscriber
 * @return bool
 */
function mark_all_notices_as_read($user_id) {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("UPDATE notices SET is_read = 1 WHERE recipient_id = ? AND is_read = 0");
        return $stmt->execute([$user_id]);
    } catch (Exception $e) {
        error_log("Mark notifications read error: " . $e->getMessage());
        return false;
    }
}
