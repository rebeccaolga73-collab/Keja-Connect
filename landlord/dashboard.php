<?php
/**
 * KejaConnect - Landlord Management Dashboard
 */
$page_title = "Landlord Dashboard";
$page_heading = "My Landlord Portal";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Count owned properties
$stmt_prop = $db->prepare("SELECT COUNT(*) FROM properties WHERE landlord_id = ?");
$stmt_prop->execute([$landlord_id]);
$my_properties = (int) $stmt_prop->fetchColumn();

// Count owned units & occupied numbers
$stmt_units_all = $db->prepare("
    SELECT COUNT(u.id) 
    FROM units u 
    INNER JOIN properties p ON u.property_id = p.id 
    WHERE p.landlord_id = ?
");
$stmt_units_all->execute([$landlord_id]);
$my_units_total = (int) $stmt_units_all->fetchColumn();

$stmt_occ = $db->prepare("
    SELECT COUNT(u.id) 
    FROM units u 
    INNER JOIN properties p ON u.property_id = p.id 
    WHERE p.landlord_id = ? AND u.status = 'occupied'
");
$stmt_occ->execute([$landlord_id]);
$my_units_occupied = (int) $stmt_occ->fetchColumn();

$my_units_vacant = $my_units_total - $my_units_occupied;

// Sum Collected This Month
$current_month = date('Y-m');
$stmt_coll = $db->prepare("SELECT SUM(amount) FROM payments WHERE landlord_id = ? AND month_paid_for = ? AND status = 'confirmed'");
$stmt_coll->execute([$landlord_id, $current_month]);
$collected_this_month = (float) $stmt_coll->fetchColumn();

// Sum Pending Payments
$stmt_pend = $db->prepare("SELECT SUM(amount) FROM payments WHERE landlord_id = ? AND month_paid_for = ? AND status = 'pending'");
$stmt_pend->execute([$landlord_id, $current_month]);
$pending_this_month = (float) $stmt_pend->fetchColumn();

// Fetch Recent Maintenance claims
$stmt_maint = $db->prepare("
    SELECT m.*, u.unit_number, p.name as prop_name, t.full_name as tenant_name 
    FROM maintenance_requests m 
    INNER JOIN units u ON m.unit_id = u.id 
    INNER JOIN properties p ON u.property_id = p.id 
    INNER JOIN users t ON m.tenant_id = t.id 
    WHERE m.landlord_id = ? 
    ORDER BY m.created_at DESC 
    LIMIT 4
");
$stmt_maint->execute([$landlord_id]);
$recent_maint = $stmt_maint->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- PRIMARY KPI STATS -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    
    <!-- Statistic 1 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">My Properties</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $my_properties; ?></h3>
                <p class="text-xs text-brand-600 mt-0.5"><?php echo $my_units_total; ?> total listed units</p>
            </div>
        </div>
    </div>

    <!-- Statistic 2 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Occupied Slots</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $my_units_occupied; ?> <span class="text-sm font-normal text-gray-400">/ <?php echo $my_units_total; ?></span></h3>
                <p class="text-xs text-yellow-600 mt-0.5"><?php echo $my_units_vacant; ?> vacant units to fill</p>
            </div>
        </div>
    </div>

    <!-- Statistic 3 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg text-brand-accent">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Collected (KES)</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo format_money($collected_this_month); ?></h3>
                <p class="text-xs text-gray-500 mt-0.5">Approved so far this month</p>
            </div>
        </div>
    </div>

    <!-- Statistic 4 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg text-brand-accent">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Unprocessed / Pending</p>
                <h3 class="text-2xl font-bold text-red-600 mt-1"><?php echo format_money($pending_this_month); ?></h3>
                <p class="text-xs text-gray-500 mt-0.5">Requires approval check</p>
            </div>
        </div>
    </div>

</div>

<!-- CHART AND SHORTCUTS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <!-- Left: Earnings Chart -->
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 lg:col-span-2">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">My Dashboard Analytics: Monthly Revenue</h3>
        <div class="h-64 relative">
            <canvas id="landlordRevenueChart"></canvas>
        </div>
    </div>

    <!-- Right: Quick actions and metrics -->
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Quick Shortcuts</h3>
            <p class="text-xs text-gray-400 mb-6 font-medium">Add homes, recruit renters, upload files, and resolve queries securely on a single dashboard.</p>
            
            <div class="space-y-3">
                <a href="<?php echo BASE_URL; ?>/landlord/properties/" class="flex justify-center bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 text-xs rounded-lg shadow-sm transition-all">
                    + Add New Property
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/tenants/" class="flex justify-center bg-green-50 hover:bg-green-100 text-brand-600 font-bold py-3 text-xs rounded-lg border border-brand-100 transition-all">
                    Invite/Onboard Reneter Email
                </a>
                <a href="<?php echo BASE_URL; ?>/landlord/payments/" class="flex justify-center bg-yellow-50 hover:bg-yellow-101 text-brand-accent font-bold py-3 text-xs rounded-lg border border-yellow-150 transition-all">
                    Process Unconfirmed Payments
                </a>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-5 mt-5">
            <h5 class="text-xs font-bold text-gray-650">Onboarding Rules</h5>
            <p class="text-[10px] text-gray-400 leading-relaxed mt-1">Renters require a valid active email configuration in the database. Ensure documents are in standard PDF format.</p>
        </div>
    </div>

</div>

<!-- RECENT MAINTENANCE QUESTIONS -->
<div class="bg-white shadow-sm border border-gray-150 rounded-xl p-6 overflow-hidden">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Active Tenant Maintenance Claims</h3>
        <a href="<?php echo BASE_URL; ?>/landlord/maintenance/" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline">View Full Maintenance Inbox &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit & Residene</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Claim Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Raised By</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">State</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-150 bg-white">
                <?php if (count($recent_maint) === 0): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No active reports received. Great job maintainining infrastructure!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_maint as $m): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900 font-bold"><?php echo sanitize($m['prop_name']) . " — Unit " . sanitize($m['unit_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500"><?php echo sanitize($m['title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500"><?php echo sanitize($m['tenant_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo get_status_badge_class($m['status']); ?>">
                                    <?php echo sanitize($m['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo get_status_badge_class($m['priority']); ?>">
                                    <?php echo sanitize($m['priority']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <a href="<?php echo BASE_URL; ?>/landlord/maintenance/view.php?id=<?php echo $m['id']; ?>" class="text-brand-600 hover:text-brand-700 font-bold hover:underline">Inspect Case &rarr;</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('landlordRevenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Gross Collections (KES)',
                data: [45000, 90000, 90000, 135000, 135000, <?php echo $collected_this_month; ?>],
                backgroundColor: '#1a6b3c',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
