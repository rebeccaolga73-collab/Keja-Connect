<?php
/**
 * KejaConnect - Settle Rent / Receipts (Tenant Payments)
 */
$page_title = "Settle Rent / Receipts";
$page_heading = "Settle Rent / Receipts";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('tenant');

$tenant_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch current tenancy
$stmt_tn = $db->prepare("SELECT id FROM tenancies WHERE tenant_id = ? AND status = 'active' LIMIT 1");
$stmt_tn->execute([$tenant_id]);
$tenancy = $stmt_tn->fetch();

if (!$tenancy) {
    include __DIR__ . '/../../includes/header.php';
    ?>
    <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
        <h3 class="text-lg font-medium text-gray-900">No active tenancy</h3>
        <p class="mt-2 text-sm text-gray-600">You need an active lease to make payments.</p>
    </div>
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <?php exit;
}

$tenancy_id = $tenancy['id'];

// Fetch payment history
$stmt = $db->prepare("
    SELECT p.*, 
           t.unit_number,
           pr.name as property_name
    FROM payments p 
    INNER JOIN tenancies tn ON p.tenancy_id = tn.id 
    INNER JOIN units t ON tn.unit_id = t.id 
    INNER JOIN properties pr ON t.property_id = pr.id 
    WHERE p.tenancy_id = ?
    ORDER BY p.month_paid_for DESC
");
$stmt->execute([$tenancy_id]);
$payments = $stmt->fetchAll();

// Calculate summary
$stmt_total = $db->prepare("SELECT SUM(amount) FROM payments WHERE tenancy_id = ? AND status = 'confirmed'");
$stmt_total->execute([$tenancy_id]);
$total_paid = (float) $stmt_total->fetchColumn();

$stmt_pending = $db->prepare("SELECT SUM(amount) FROM payments WHERE tenancy_id = ? AND status = 'pending'");
$stmt_pending->execute([$tenancy_id]);
$total_pending = (float) $stmt_pending->fetchColumn();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Total Paid</p>
            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo format_money($total_paid); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Pending Payments</p>
            <p class="text-3xl font-bold text-orange-600 mt-2"><?php echo format_money($total_pending); ?></p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <a href="#" class="inline-flex items-center px-6 py-3 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
            <span>Make a Payment</span>
        </a>
        <a href="#" class="inline-flex items-center px-6 py-3 rounded-lg bg-gray-100 text-gray-900 font-medium hover:bg-gray-200">
            <span>View Receipt</span>
        </a>
    </div>

    <!-- Payment History -->
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Payment History</h2>
        <?php if (!empty($payments)): ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Month</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($payments as $payment): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo date('M Y', strtotime($payment['month_paid_for'])); ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?php echo format_money($payment['amount']); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium <?php 
                                        echo ($payment['status'] === 'confirmed') ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700'; 
                                    ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 font-medium">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                <p class="text-sm text-gray-600">No payment history yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
