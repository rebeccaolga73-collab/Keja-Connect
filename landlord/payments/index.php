<?php
/**
 * KejaConnect - Rent & Payments
 */
$page_title = "Rent & Payments";
$page_heading = "Rent & Payments";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch recent payments
$stmt = $db->prepare("
    SELECT p.*, 
           u.full_name as tenant_name,
           t.unit_number,
           pr.name as property_name
    FROM payments p 
    INNER JOIN tenancies tn ON p.tenancy_id = tn.id 
    INNER JOIN users u ON tn.tenant_id = u.id 
    INNER JOIN units t ON tn.unit_id = t.id 
    INNER JOIN properties pr ON t.property_id = pr.id 
    WHERE p.landlord_id = ?
    ORDER BY p.created_at DESC
    LIMIT 50
");
$stmt->execute([$landlord_id]);
$payments = $stmt->fetchAll();

// Summary stats
$stmt_total = $db->prepare("SELECT SUM(amount) FROM payments WHERE landlord_id = ? AND status = 'confirmed'");
$stmt_total->execute([$landlord_id]);
$total_collected = (float) $stmt_total->fetchColumn();

$stmt_pending = $db->prepare("SELECT SUM(amount) FROM payments WHERE landlord_id = ? AND status = 'pending'");
$stmt_pending->execute([$landlord_id]);
$total_pending = (float) $stmt_pending->fetchColumn();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Total Collected</p>
            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo format_money($total_collected); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Pending Payments</p>
            <p class="text-3xl font-bold text-orange-600 mt-2"><?php echo format_money($total_pending); ?></p>
        </div>
    </div>

    <!-- Payments Table -->
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Payment History</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tenant</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Property</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Month</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo sanitize($payment['tenant_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($payment['property_name']); ?></td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?php echo format_money($payment['amount']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('M Y', strtotime($payment['month_paid_for'])); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php 
                                    echo ($payment['status'] === 'confirmed') ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700'; 
                                ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
