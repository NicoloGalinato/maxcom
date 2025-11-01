<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';
$services = getServices($conn);

// Handle add/edit service
if (isset($_POST['save_service'])) {
    $id = $_POST['id'] ?? 0;
    $icon = sanitize($_POST['icon']);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $link_text = sanitize($_POST['link_text']);
    $color = sanitize($_POST['color']);
    $sort_order = intval($_POST['sort_order']);
    
    if ($id > 0) {
        // Update existing service
        $stmt = $conn->prepare("UPDATE services SET icon = ?, title = ?, description = ?, link_text = ?, color = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$icon, $title, $description, $link_text, $color, $sort_order, $id])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Service updated successfully!</div>';
        }
    } else {
        // Add new service
        $stmt = $conn->prepare("INSERT INTO services (icon, title, description, link_text, color, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$icon, $title, $description, $link_text, $color, $sort_order])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Service added successfully!</div>';
        }
    }
    $services = getServices($conn);
}

// Handle delete service
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Service deleted successfully!</div>';
        $services = getServices($conn);
    }
}

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $edit_service = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Services</h1>
                <p class="text-gray-600">Add, edit, or remove services from your landing page</p>
            </div>

            <!-- Service Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?>
                    </h3>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="id" value="<?php echo $edit_service ? $edit_service['id'] : 0; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Font Awesome Icon</label>
                                <input type="text" id="icon" name="icon" value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : ''; ?>" 
                                       placeholder="fa-user-tie" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Enter Font Awesome icon class (e.g., fa-user-tie)</p>
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700">Color Theme</label>
                                <select id="color" name="color" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="blue" <?php echo ($edit_service && $edit_service['color'] == 'blue') ? 'selected' : ''; ?>>Blue</option>
                                    <option value="green" <?php echo ($edit_service && $edit_service['color'] == 'green') ? 'selected' : ''; ?>>Green</option>
                                    <option value="purple" <?php echo ($edit_service && $edit_service['color'] == 'purple') ? 'selected' : ''; ?>>Purple</option>
                                    <option value="red" <?php echo ($edit_service && $edit_service['color'] == 'red') ? 'selected' : ''; ?>>Red</option>
                                    <option value="yellow" <?php echo ($edit_service && $edit_service['color'] == 'yellow') ? 'selected' : ''; ?>>Yellow</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Service Title</label>
                            <input type="text" id="title" name="title" value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="link_text" class="block text-sm font-medium text-gray-700">Link Text</label>
                                <input type="text" id="link_text" name="link_text" value="<?php echo $edit_service ? htmlspecialchars($edit_service['link_text']) : 'Learn More'; ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="<?php echo $edit_service ? $edit_service['sort_order'] : 0; ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <?php if ($edit_service): ?>
                                <a href="manage-services.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Cancel
                                </a>
                            <?php endif; ?>
                            <button type="submit" name="save_service" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <?php echo $edit_service ? 'Update Service' : 'Add Service'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Services List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Current Services</h3>
                    
                    <?php if (empty($services)): ?>
                        <p class="text-gray-500 text-center py-4">No services found. Add your first service above.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($service['title']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo substr(htmlspecialchars($service['description']), 0, 100); ?>...</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <i class="<?php echo htmlspecialchars($service['icon']); ?> text-<?php echo $service['color']; ?>-600"></i>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $service['sort_order']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="?edit=<?php echo $service['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                                <a href="?delete=<?php echo $service['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
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