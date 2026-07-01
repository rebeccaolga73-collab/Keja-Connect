<?php
/**
 * KejaConnect - Maintenance Requests
 */
$page_title = "Maintenance Requests";
$page_heading = "Maintenance Requests";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch maintenance requests
$stmt = $db->prepare("
    SELECT m.*, 
           u.full_name as tenant_name,
           t.unit_number,
           p.name as property_name
    FROM maintenance_requests m 
    INNER JOIN units t ON m.unit_id = t.id 
    INNER JOIN properties p ON t.property_id = p.id 
    INNER JOIN users u ON m.tenant_id = u.id 
    WHERE m.landlord_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$landlord_id]);
$requests = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <h2 class="text-2xl font-bold text-gray-900">Maintenance Requests</h2>

    <?php if (!empty($requests)): ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tenant</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Property</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Unit</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Issue</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Priority</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($requests as $request): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo sanitize($request['tenant_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($request['property_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($request['unit_number']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs"><?php echo sanitize($request['issue_description']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php 
                                    echo ($request['priority'] === 'urgent') ? 'bg-red-50 text-red-700' : (($request['priority'] === 'high') ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700');
                                ?>">
                                    <?php echo ucfirst($request['priority']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php 
                                    echo ($request['status'] === 'open') ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700'; 
                                ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <a href="#" class="text-blue-600 hover:text-blue-900 font-medium">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900">No maintenance requests</h3>
            <p class="mt-2 text-sm text-gray-600">All systems operational!</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
