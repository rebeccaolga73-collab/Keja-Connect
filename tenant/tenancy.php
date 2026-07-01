<?php
/**
 * KejaConnect - My Tenancy Details
 */
$page_title = "My Tenancy Details";
$page_heading = "My Tenancy Details";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Role check
require_role('tenant');

$tenant_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch current tenancy
$stmt = $db->prepare("
    SELECT t.*, 
           u.unit_number,
           p.name as property_name,
           p.address as property_address,
           l.full_name as landlord_name,
           l.phone as landlord_phone,
           l.email as landlord_email
    FROM tenancies t 
    INNER JOIN units u ON t.unit_id = u.id 
    INNER JOIN properties p ON u.property_id = p.id 
    INNER JOIN users l ON p.landlord_id = l.id 
    WHERE t.tenant_id = ? AND t.status = 'active'
    ORDER BY t.start_date DESC
    LIMIT 1
");
$stmt->execute([$tenant_id]);
$tenancy = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <?php if ($tenancy): ?>
        <!-- Tenancy Details Card -->
        <div class="bg-white p-8 rounded-lg border border-gray-200 shadow-sm">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Current Tenancy</h2>
            
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Property Information -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Property Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Property Name</p>
                            <p class="text-lg font-semibold text-gray-900"><?php echo sanitize($tenancy['property_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm text-gray-600"><?php echo sanitize($tenancy['property_address']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Unit Number</p>
                            <p class="text-sm font-semibold text-gray-900"><?php echo sanitize($tenancy['unit_number']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Lease Information -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Lease Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Start Date</p>
                            <p class="text-sm font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($tenancy['start_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">End Date</p>
                            <p class="text-sm font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($tenancy['end_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">
                                <?php echo ucfirst($tenancy['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Landlord Contact -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Landlord Contact Information</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="text-sm font-semibold text-gray-900"><?php echo sanitize($tenancy['landlord_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-sm text-gray-600"><?php echo sanitize($tenancy['landlord_phone']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm text-gray-600"><?php echo sanitize($tenancy['landlord_email']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900">No active tenancy</h3>
            <p class="mt-2 text-sm text-gray-600">You don't currently have an active lease agreement. Contact your landlord to get started.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
