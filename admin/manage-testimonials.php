<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';
$testimonials = getTestimonials($conn);

// Handle add/edit testimonial
if (isset($_POST['save_testimonial'])) {
    $id = $_POST['id'] ?? 0;
    $client_name = sanitize($_POST['client_name']);
    $client_role = sanitize($_POST['client_role']);
    $testimonial_text = sanitize($_POST['testimonial_text']);
    $sort_order = intval($_POST['sort_order']);
    
    $client_image = $_POST['current_image'] ?? '';
    if (!empty($_FILES['client_image']['name'])) {
        $upload = uploadImage($_FILES['client_image']);
        if ($upload['success']) {
            $client_image = $upload['filename'];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ' . $upload['message'] . '</div>';
        }
    }
    
    if ($id > 0) {
        // Update existing testimonial
        $stmt = $conn->prepare("UPDATE testimonials SET client_name = ?, client_role = ?, testimonial_text = ?, client_image = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$client_name, $client_role, $testimonial_text, $client_image, $sort_order, $id])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Testimonial updated successfully!</div>';
        }
    } else {
        // Add new testimonial
        $stmt = $conn->prepare("INSERT INTO testimonials (client_name, client_role, testimonial_text, client_image, sort_order) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$client_name, $client_role, $testimonial_text, $client_image, $sort_order])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Testimonial added successfully!</div>';
        }
    }
    $testimonials = getTestimonials($conn);
}

// Handle delete testimonial
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("UPDATE testimonials SET is_active = 0 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Testimonial deleted successfully!</div>';
        $testimonials = getTestimonials($conn);
    }
}

// Get testimonial for editing
$edit_testimonial = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $edit_testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Testimonials</h1>
                <p class="text-gray-600">Add, edit, or remove client testimonials</p>
            </div>

            <!-- Testimonial Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <?php echo $edit_testimonial ? 'Edit Testimonial' : 'Add New Testimonial'; ?>
                    </h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="id" value="<?php echo $edit_testimonial ? $edit_testimonial['id'] : 0; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $edit_testimonial ? $edit_testimonial['client_image'] : ''; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                <input type="text" id="client_name" name="client_name" value="<?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['client_name']) : ''; ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="client_role" class="block text-sm font-medium text-gray-700">Client Role/Title</label>
                                <input type="text" id="client_role" name="client_role" value="<?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['client_role']) : ''; ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="testimonial_text" class="block text-sm font-medium text-gray-700">Testimonial Text</label>
                            <textarea id="testimonial_text" name="testimonial_text" rows="4" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['testimonial_text']) : ''; ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="client_image" class="block text-sm font-medium text-gray-700">Client Photo</label>
                                <input type="file" id="client_image" name="client_image" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <?php if ($edit_testimonial && $edit_testimonial['client_image']): ?>
                                    <p class="mt-2 text-sm text-gray-500">Current: <?php echo $edit_testimonial['client_image']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="<?php echo $edit_testimonial ? $edit_testimonial['sort_order'] : 0; ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <?php if ($edit_testimonial): ?>
                                <a href="manage-testimonials.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Cancel
                                </a>
                            <?php endif; ?>
                            <button type="submit" name="save_testimonial" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <?php echo $edit_testimonial ? 'Update Testimonial' : 'Add Testimonial'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Testimonials List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Current Testimonials</h3>
                    
                    <?php if (empty($testimonials)): ?>
                        <p class="text-gray-500 text-center py-4">No testimonials found. Add your first testimonial above.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Testimonial</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <?php if ($testimonial['client_image']): ?>
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" src="../uploads/<?php echo htmlspecialchars($testimonial['client_image']); ?>" alt="">
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($testimonial['client_name']); ?></div>
                                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($testimonial['client_role']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 max-w-xs truncate"><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $testimonial['sort_order']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="?edit=<?php echo $testimonial['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                                <a href="?delete=<?php echo $testimonial['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this testimonial?')">Delete</a>
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
</body>
</html>