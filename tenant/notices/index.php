<?php
/**
 * KejaConnect - Announcements (Tenant Notices)
 */
$page_title = "Announcements";
$page_heading = "Announcements";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/notifications.php';

// Role check
require_role('tenant');

$tenant_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch current tenancy to get property/unit
$stmt_tn = $db->prepare("SELECT id, unit_id FROM tenancies WHERE tenant_id = ? AND status = 'active' LIMIT 1");
$stmt_tn->execute([$tenant_id]);
$tenancy = $stmt_tn->fetch();

// Fetch announcements/notices for this tenant
$stmt = $db->prepare("
    SELECT n.* 
    FROM notices n 
    WHERE n.unit_id = ? OR n.id IN (
        SELECT notice_id FROM tenancies WHERE tenant_id = ?
    )
    ORDER BY n.created_at DESC
");
$stmt->execute([$tenancy['unit_id'] ?? 0, $tenant_id]);
$notices = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <h2 class="text-2xl font-bold text-gray-900">Announcements</h2>

    <?php if (!empty($notices)): ?>
        <div class="space-y-4">
            <?php foreach ($notices as $notice): ?>
                <div class="bg-white p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900"><?php echo sanitize($notice['title'] ?? 'Announcement'); ?></h3>
                            <p class="text-sm text-gray-600 mt-2"><?php echo sanitize($notice['message'] ?? ''); ?></p>
                            <p class="text-xs text-gray-500 mt-4"><?php echo date('M d, Y \a\t g:i A', strtotime($notice['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No announcements yet</h3>
            <p class="mt-2 text-sm text-gray-600">Check back later for updates from your landlord.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
