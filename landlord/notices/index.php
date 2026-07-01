<?php
/**
 * KejaConnect - Communication Desk (Notices)
 */
$page_title = "Communication Desk";
$page_heading = "Communication Desk";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch notices sent by this landlord
$stmt = $db->prepare("
    SELECT n.*, 
           u.full_name as sender_name
    FROM notices n 
    LEFT JOIN users u ON n.sender_id = u.id
    WHERE n.sender_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$landlord_id]);
$notices = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Notices & Communications</h2>
        <a href="<?php echo BASE_URL; ?>/landlord/notices/send.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
            <span>+ Send Notice</span>
        </a>
    </div>

    <?php if (!empty($notices)): ?>
        <div class="space-y-4">
            <?php foreach ($notices as $notice): ?>
                <div class="bg-white p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900"><?php echo sanitize($notice['title'] ?? 'Notice'); ?></h3>
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?php echo sanitize($notice['message'] ?? ''); ?></p>
                            <div class="flex items-center space-x-4 mt-4 text-xs text-gray-500">
                                <span><?php echo date('M d, Y', strtotime($notice['created_at'])); ?></span>
                                <span class="px-2 py-1 bg-gray-100 rounded"><?php echo $notice['recipient_count'] ?? 0; ?> Recipients</span>
                            </div>
                        </div>
                        <a href="#" class="text-blue-600 hover:text-blue-900 font-medium text-sm">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900">No notices sent yet</h3>
            <p class="mt-2 text-sm text-gray-600">Send your first notice to tenants.</p>
            <a href="<?php echo BASE_URL; ?>/landlord/notices/send.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
                Send First Notice
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
