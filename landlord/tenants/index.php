<?php
/**
 * KejaConnect - Tenant Management
 */
$page_title = "Tenant Management";
$page_heading = "Tenant Management";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch all tenants for this landlord's properties
$stmt = $db->prepare("
    SELECT DISTINCT u.*, 
           t.unit_number, 
           p.name as property_name,
           tn.start_date, 
           tn.end_date,
           tn.status as tenancy_status
    FROM users u 
    INNER JOIN tenancies tn ON u.id = tn.tenant_id 
    INNER JOIN units t ON tn.unit_id = t.id 
    INNER JOIN properties p ON t.property_id = p.id 
    WHERE p.landlord_id = ? AND u.role = 'tenant'
    ORDER BY tn.start_date DESC
");
$stmt->execute([$landlord_id]);
$tenants = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <h2 class="text-2xl font-bold text-gray-900">Your Tenants</h2>

    <?php if (!empty($tenants)): ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phone</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Property</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Unit</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($tenants as $tenant): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo sanitize($tenant['full_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($tenant['email']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($tenant['phone']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($tenant['property_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($tenant['unit_number']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    <?php echo ucfirst($tenant['tenancy_status']); ?>
                                </span>
                            </td>
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
            <h3 class="text-lg font-medium text-gray-900">No tenants yet</h3>
            <p class="mt-2 text-sm text-gray-600">Add properties and units to start managing tenants.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
