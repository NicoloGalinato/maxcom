<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

// Get all contact submissions
$stmt = $conn->prepare("SELECT * FROM contact_submissions ORDER BY submitted_at DESC");
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unread count for header
$unread_count_stmt = $conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
$unread_count = $unread_count_stmt->fetchColumn();

// Mark as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    redirect('manage-contact-submissions.php');
}

// Delete submission
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->execute([$id]);
    redirect('manage-contact-submissions.php');
}
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Contact Form Submissions</h1>
                <p class="text-gray-600">Manage inquiries from your website visitors</p>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <?php if (empty($submissions)): ?>
                        <p class="text-gray-500 text-center py-8">No contact submissions yet.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr class="<?php echo $submission['is_read'] ? '' : 'bg-blue-50'; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($submission['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($submission['phone']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($submission['email']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($submission['service_interested']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $submission['is_read'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo $submission['is_read'] ? 'Read' : 'New'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="viewMessage(<?php echo $submission['id']; ?>, '<?php echo addslashes($submission['name']); ?>', '<?php echo addslashes($submission['message']); ?>')" 
                                                        class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                                <?php if (!$submission['is_read']): ?>
                                                    <a href="?mark_read=<?php echo $submission['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">Mark Read</a>
                                                <?php endif; ?>
                                                <a href="?delete=<?php echo $submission['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this submission?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900"></h3>
                <div class="mt-2">
                    <p id="modalMessage" class="text-sm text-gray-500"></p>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewMessage(id, name, message) {
        document.getElementById('modalTitle').textContent = 'Message from ' + name;
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('messageModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('messageModal').classList.add('hidden');
    }
    </script>
</body>
</html>