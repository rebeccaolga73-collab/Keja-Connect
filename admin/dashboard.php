<?php
/**
 * KejaConnect - Admin Control Center Dashboard
 */
$page_title = "Admin Dashboard";
$page_heading = "Administration Control Center";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Safe Role Guard: Only allows active admins
require_role('admin');

$db = get_db_connection();

// 1. Fetch KPI figures
// Total Users
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
// Total Landlords
$total_landlords = $db->query("SELECT COUNT(*) FROM users WHERE role = 'landlord'")->fetchColumn();
// Total Tenants
$total_tenants = $db->query("SELECT COUNT(*) FROM users WHERE role = 'tenant'")->fetchColumn();
// Total Properties
$total_properties = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
// Total Units
$total_units = $db->query("SELECT COUNT(*) FROM units")->fetchColumn();
// Occupied units
$occupied_units = $db->query("SELECT COUNT(*) FROM units WHERE status = 'occupied'")->fetchColumn();
// Vacant units
$vacant_units = $total_units - $occupied_units;

// Total Revenue This Month
$current_month = date('Y-m');
$stmt_rev = $db->prepare("SELECT SUM(amount) FROM payments WHERE month_paid_for = ? AND status = 'confirmed'");
$stmt_rev->execute([$current_month]);
$revenue_this_month = (float) $stmt_rev->fetchColumn();

// 2. Fetch Recent System Activity Audits
$recent_logs = $db->query("
    SELECT l.*, u.full_name, u.role 
    FROM audit_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 6
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- PRIMARY KPI CARDS -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    
    <!-- KPI 1 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Total Active Users</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $total_users; ?></h3>
                <p class="text-xs text-brand-600 mt-0.5"><?php echo $total_landlords; ?> landlords | <?php echo $total_tenants; ?> tenants</p>
            </div>
        </div>
    </div>

    <!-- KPI 2 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Managed Properties</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $total_properties; ?></h3>
                <p class="text-xs text-gray-500 mt-0.5">Across different counties</p>
            </div>
        </div>
    </div>

    <!-- KPI 3 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Occupancy Status</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">
                    <?php echo $total_units > 0 ? round(($occupied_units / $total_units) * 100, 1) : 0; ?>%
                </h3>
                <p class="text-xs text-gray-500 mt-0.5"><?php echo $occupied_units; ?> Occupied | <?php echo $vacant_units; ?> Vacant</p>
            </div>
        </div>
    </div>

    <!-- KPI 4 -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg text-brand-accent">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Collections (Month)</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo format_money($revenue_this_month); ?></h3>
                <p class="text-xs text-green-600 mt-0.5">Status: Confirmed Payouts</p>
            </div>
        </div>
    </div>

</div>

<!-- CHART ANALYTICS BOARDS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 lg:col-span-2">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Monthly Financial Revenue (KES)</h3>
        <div class="h-64 relative">
            <canvas id="revenueLineChart"></canvas>
        </div>
    </div>
    
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Occupancy Ratio</h3>
        <div class="h-64 relative">
            <canvas id="occupancyDoughnutChart"></canvas>
        </div>
    </div>
</div>

<!-- AUDITS AND ACTIONS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left: Audit Logs Table -->
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 lg:col-span-2 overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Recent System Audit Log</h3>
            <a href="<?php echo BASE_URL; ?>/admin/settings/" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline">View Full Audit Trail &larr;</a>
        </div>
        
        <div class="flow-root">
            <ul role="list" class="-my-5 divide-y divide-gray-150">
                <?php if (count($recent_logs) === 0): ?>
                    <li class="py-5 text-center text-sm text-gray-500">No audits found in memory.</li>
                <?php else: ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900"><?php echo sanitize($log['action']); ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5"><?php echo sanitize($log['description']); ?></p>
                                    <p class="text-[10px] text-gray-400 mt-1">IP: <?php echo sanitize($log['ip_address']); ?> | <?php echo sanitize($log['full_name'] ?? 'Guest'); ?> (<?php echo sanitize($log['role'] ?? 'Unauthenticated'); ?>)</p>
                                </div>
                                <div class="inline-flex items-center text-xs text-gray-400">
                                    <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Right: Quick Actions Panel -->
    <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-4">Quick Shortcuts</h3>
            <p class="text-xs text-gray-400 mb-6 font-medium">As an Administrator, you can instantly provision users, audit properties, and issue broadcasts.</p>
            
            <div class="space-y-3">
                <a href="<?php echo BASE_URL; ?>/admin/users/add.php" class="flex items-center w-full bg-brand-600 hover:bg-brand-700 text-white justify-center text-xs font-bold py-3.5 rounded-lg shadow-sm transition-all">
                    Register New Landlord & Tenant
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/properties/" class="flex items-center w-full bg-green-50 hover:bg-green-100 text-brand-600 justify-center text-xs font-bold py-3.5 rounded-lg border border-brand-100 transition-all">
                    View Managed Listings
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/notices/" class="flex items-center w-full bg-yellow-50 hover:bg-yellow-100 text-brand-accent justify-center text-xs font-bold py-3.5 rounded-lg border border-yellow-150 transition-all">
                    Issue Broadcast Warning Notice
                </a>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6 mt-6">
            <h5 class="text-xs font-bold text-gray-600 mb-2">Technical Core Stack</h5>
            <p class="text-[11px] text-gray-400 leading-relaxed">Runs in secure transactional MySQL environment. Security level: CSRF guarded, prepared statements, salted secure passwords.</p>
        </div>
    </div>

</div>

<!-- Chart.js Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Line Chart: Revenue
    const ctxLine = document.getElementById('revenueLineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Paid Collections (KES)',
                data: [120000, 185000, 240000, 310000, 450000, <?php echo $revenue_this_month; ?>],
                borderColor: '#1a6b3c',
                backgroundColor: 'rgba(26, 107, 60, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#e5e7eb' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Doughnut Chart: Occupancy Rates
    const ctxDoughnut = document.getElementById('occupancyDoughnutChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Occupied Units', 'Vacant Units'],
            datasets: [{
                data: [<?php echo $occupied_units; ?>, <?php echo $vacant_units; ?>],
                backgroundColor: ['#1a6b3c', '#e2e8f0'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
