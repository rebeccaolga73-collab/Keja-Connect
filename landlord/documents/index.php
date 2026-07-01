<?php
/**
 * KejaConnect - Document Vault
 */
$page_title = "Document Vault";
$page_heading = "Document Vault";

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Role check
require_role('landlord');

$landlord_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch documents uploaded by this landlord
$stmt = $db->prepare("
    SELECT d.*, 
           u.full_name as uploaded_by
    FROM documents d 
    INNER JOIN users u ON d.uploader_id = u.id
    WHERE d.uploader_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$landlord_id]);
$documents = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="grid grid-cols-1 gap-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Document Vault</h2>
        <a href="<?php echo BASE_URL; ?>/landlord/documents/upload.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
            <span>+ Upload Document</span>
        </a>
    </div>

    <?php if (!empty($documents)): ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Document Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Size</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($documents as $doc): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo sanitize($doc['original_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($doc['file_type']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($doc['uploaded_by']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?></td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="#" class="text-blue-600 hover:text-blue-900 font-medium">Download</a>
                                <a href="#" class="text-red-600 hover:text-red-900 font-medium">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900">No documents uploaded</h3>
            <p class="mt-2 text-sm text-gray-600">Upload important documents for easy access and sharing.</p>
            <a href="<?php echo BASE_URL; ?>/landlord/documents/upload.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">
                Upload Your First Document
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
