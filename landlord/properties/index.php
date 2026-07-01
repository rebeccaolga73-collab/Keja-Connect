<?php
/**
 * KejaConnect - Landlord Properties & Units Management
 */
$page_title = "My Properties & Units";
$page_heading = "My Properties & Units";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch all properties for this landlord
$stmt = $db->prepare("
    SELECT p.*, 
           COUNT(u.id) as total_units,
           SUM(CASE WHEN u.status = 'occupied' THEN 1 ELSE 0 END) as occupied_units
    FROM properties p 
    LEFT JOIN units u ON p.id = u.property_id
    WHERE p.landlord_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$landlord_id]);
$properties = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <!-- Properties Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Property Listings</h2>
        <a href="<?php echo BASE_URL; ?>/landlord/properties/add.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
            <span>+ Add New Property</span>
        </a>
    </div>

    <?php if (!empty($properties)): ?>
        <!-- Properties Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($properties as $prop): ?>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo sanitize($prop['name']); ?></h3>
                        <p class="text-sm text-gray-600 mb-4"><?php echo sanitize($prop['address']); ?></p>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4 py-4 border-t border-b">
                            <div>
                                <p class="text-2xl font-bold text-gray-900"><?php echo (int)$prop['total_units'] ?? 0; ?></p>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Units</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-green-600"><?php echo (int)$prop['occupied_units'] ?? 0; ?></p>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Occupied</p>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <a href="<?php echo BASE_URL; ?>/landlord/properties/view.php?id=<?php echo $prop['id']; ?>" class="flex-1 px-3 py-2 rounded-md bg-blue-50 text-blue-700 text-sm font-medium hover:bg-blue-100">
                                View Details
                            </a>
                            <a href="<?php echo BASE_URL; ?>/landlord/properties/edit.php?id=<?php echo $prop['id']; ?>" class="flex-1 px-3 py-2 rounded-md bg-gray-50 text-gray-700 text-sm font-medium hover:bg-gray-100">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No properties yet</h3>
            <p class="mt-2 text-sm text-gray-600">Get started by adding your first property to manage.</p>
            <a href="<?php echo BASE_URL; ?>/landlord/properties/add.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
                Add Your First Property
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
