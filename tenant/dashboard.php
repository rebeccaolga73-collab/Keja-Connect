<?php
/**
 * KejaConnect - Tenant Dashboard Portal
 */
$page_title = "Tenant Dashboard";
$page_heading = "My Tenant Portal";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Role Guard for tenants
require_role('tenant');

$tenant_id = $_SESSION['user_id'];
$db = get_db_connection();

// 1. Fetch current active tenancy of this tenant
$stmt_tenancy = $db->prepare("
    SELECT t.*, u.unit_number, u.bedrooms, u.bathrooms, u.floor, p.name as prop_name, p.address as prop_address, p.county as prop_county, p.amenities as prop_amenities
    FROM tenancies t
    INNER JOIN units u ON t.unit_id = u.id
    INNER JOIN properties p ON u.property_id = p.id
    WHERE t.tenant_id = ? AND t.status = 'active'
    LIMIT 1
");
$stmt_tenancy->execute([$tenant_id]);
$tenancy = $stmt_tenancy->fetch();

$has_tenancy = ($tenancy !== false);

// 2. Count Open Maintenance Requests
$open_claims = 0;
if ($has_tenancy) {
    $stmt_maint = $db->prepare("SELECT COUNT(*) FROM maintenance_requests WHERE tenant_id = ? AND status IN ('open', 'in_progress')");
    $stmt_maint->execute([$tenant_id]);
    $open_claims = (int) $stmt_maint->fetchColumn();
}

// 3. Last Payment Details
$last_payment_amount = 0;
$last_payment_date = 'None Recorded';
$last_payment_status = '';
if ($has_tenancy) {
    $stmt_pay = $db->prepare("SELECT amount, payment_date, status FROM payments WHERE tenancy_id = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt_pay->execute([$tenancy['id']]);
    $last_pay = $stmt_pay->fetch();
    if ($last_pay) {
        $last_payment_amount = (float) $last_pay['amount'];
        $last_payment_date = date('d M Y', strtotime($last_pay['payment_date']));
        $last_payment_status = $last_pay['status'];
    }
}

// 4. Fetch last 6 months payment history
$payments_history = [];
if ($has_tenancy) {
    $stmt_history = $db->prepare("SELECT * FROM payments WHERE tenancy_id = ? ORDER BY payment_date DESC LIMIT 6");
    $stmt_history->execute([$tenancy['id']]);
    $payments_history = $stmt_history->fetchAll();
}

// 5. Fetch recent directed notices
$stmt_notices = $db->prepare("
    SELECT * FROM notices 
    WHERE recipient_id = ? OR recipient_id IS NULL AND is_read = 0
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt_notices->execute([$tenant_id]);
$unread_broadcasts = $stmt_notices->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- PRIMARY GRID OVERVIEW -->
<?php if (!$has_tenancy): ?>
    <!-- Notice of No Tenant Agreement setup -->
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded-2xl mb-8">
        <h3 class="text-xl font-bold font-brand mb-2">Awaiting Lease Placement</h3>
        <p class="text-sm">Your tenant account has been successfully provisioned. However, there is no active lease agreement currently mapping your account to a property. Please share your account email (<code><?php echo sanitize($user_email); ?></code>) with your respective landlord to initiate onboarding setup.</p>
    </div>
<?php else: ?>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        
        <!-- Tab 1: Apartment Info -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">My Unit Number</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">Unit <?php echo sanitize($tenancy['unit_number']); ?></h3>
                    <p class="text-xs text-brand-600 mt-0.5"><?php echo sanitize($tenancy['prop_name']); ?></p>
                </div>
            </div>
        </div>

        <!-- Tab 2: Monthly Due Rent -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-50 p-3 rounded-lg text-brand-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Monthly Rent</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo format_money($tenancy['rent_amount']); ?></h3>
                    <p class="text-xs text-gray-500 mt-0.5">Due date: 5th of every month</p>
                </div>
            </div>
        </div>

        <!-- Tab 3: Last payment details -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg text-brand-accent">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Last Settle Date</p>
                    <h3 class="text-sm font-bold text-gray-900 mt-1 truncate"><?php echo $last_payment_date; ?></h3>
                    <p class="text-xs font-medium text-green-600 mt-0.5"><?php echo $last_payment_amount > 0 ? format_money($last_payment_amount) : '0'; ?> (<?php echo sanitize($last_payment_status); ?>)</p>
                </div>
            </div>
        </div>

        <!-- Tab 4: Pending maintenance claims -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-150 p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-50 p-3 rounded-lg text-brand-accent">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">Active Claims</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $open_claims; ?> open tickets</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Reported plumbing or electrics</p>
                </div>
            </div>
        </div>

    </div>

    <!-- MAIN GRID SYSTEM DISPLAY (Payments history Vs Announcements) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Left: Payments Table -->
        <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 lg:col-span-2 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">My Recent Payments (Last 6 Months)</h3>
                <a href="<?php echo BASE_URL; ?>/tenant/payments/" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline">Add M-Pesa Record &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Paid For</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">M-Pesa Code</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Receipt No</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Verification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        <?php if (count($payments_history) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-xs text-gray-500">No payment logs exist. Create your initial record.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments_history as $p): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900 font-semibold"><?php echo date('F Y', strtotime($p['month_paid_for'] . '-01')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500"><?php echo format_money($p['amount']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono"><?php echo sanitize($p['mpesa_code'] ?? 'None'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono"><?php echo sanitize($p['receipt_number']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border <?php echo get_status_badge_class($p['status']); ?>">
                                            <?php echo sanitize($p['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Notices -->
        <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-150 overflow-hidden flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider mb-4">Latest System Announcements</h3>
                
                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-150">
                        <?php if (count($unread_broadcasts) === 0): ?>
                            <li class="py-5 text-center text-xs text-gray-550 list-none">No active reminders found. Up to date!</li>
                        <?php else: ?>
                            <?php foreach ($unread_broadcasts as $n): ?>
                                <li class="py-4 font-normal">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-gray-900"><?php echo sanitize($n['subject']); ?></p>
                                        <p class="text-[11px] text-gray-500 mt-1 leading-normal"><?php echo sanitize($n['message']); ?></p>
                                        <p class="text-[9px] text-gray-400 mt-1"><?php echo date('M d, H:i Y', strtotime($n['created_at'])); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-5 mt-5">
                <a href="<?php echo BASE_URL; ?>/tenant/notices/" class="text-xs text-brand-600 hover:text-brand-700 font-bold underline flex items-center justify-center">
                    Enter Announcements Screen &rarr;
                </a>
            </div>
        </div>

    </div>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
