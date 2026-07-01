<?php
/**
 * KejaConnect - Fix Maintenance (Tenant Maintenance Requests)
 */
$page_title = "Fix Maintenance";
$page_heading = "Fix Maintenance";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('tenant');

$tenant_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch maintenance requests submitted by this tenant
$stmt = $db->prepare("
    SELECT m.*, 
           t.unit_number,
           p.name as property_name,
           l.full_name as landlord_name
    FROM maintenance_requests m 
    INNER JOIN units t ON m.unit_id = t.id 
    INNER JOIN properties p ON t.property_id = p.id 
    INNER JOIN users l ON p.landlord_id = l.id 
    WHERE m.tenant_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$tenant_id]);
$requests = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Maintenance Requests</h2>
        <a href="<?php echo BASE_URL; ?>/tenant/maintenance/submit.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
            <span>+ Submit Request</span>
        </a>
    </div>

    <?php if (!empty($requests)): ?>
        <div class="space-y-4">
            <?php foreach ($requests as $request): ?>
                <div class="bg-white p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900"><?php echo sanitize($request['issue_description']); ?></h3>
                            <p class="text-sm text-gray-600 mt-2"><?php echo sanitize($request['property_name']); ?> - Unit <?php echo sanitize($request['unit_number']); ?></p>
                            
                            <div class="flex items-center space-x-4 mt-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php 
                                    echo ($request['priority'] === 'urgent') ? 'bg-red-50 text-red-700' : (($request['priority'] === 'high') ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700');
                                ?>">
                                    Priority: <?php echo ucfirst($request['priority']); ?>
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php 
                                    echo ($request['status'] === 'open') ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700'; 
                                ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                                <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/tenant/maintenance/view.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-900 font-medium text-sm">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900">No maintenance requests</h3>
            <p class="mt-2 text-sm text-gray-600">Submit a request if you need maintenance assistance.</p>
            <a href="<?php echo BASE_URL; ?>/tenant/maintenance/submit.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
                Submit Your First Request
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
