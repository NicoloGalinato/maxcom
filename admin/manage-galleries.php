<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle gallery operations
if (isset($_POST['add_gallery'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $sort_order = intval($_POST['sort_order']);
    
    $cover_image = '';
    if (!empty($_FILES['cover_image']['name'])) {
        $upload = uploadImage($_FILES['cover_image'], 'uploads/galleries/');
        if ($upload['success']) {
            $cover_image = $upload['filename'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO galleries (title, description, cover_image, category, sort_order, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $cover_image, $category, $sort_order, $_SESSION['admin_id']])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Gallery created successfully!</div>';
    }
}

if (isset($_GET['delete_gallery'])) {
    $id = intval($_GET['delete_gallery']);
    $stmt = $conn->prepare("DELETE FROM galleries WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Gallery deleted successfully!</div>';
    }
}

// Get all galleries
$stmt = $conn->prepare("SELECT * FROM galleries ORDER BY sort_order ASC, created_at DESC");
$stmt->execute();
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Image Galleries</h1>
                <p class="text-gray-600">Manage your sports event galleries and athlete portfolios</p>
            </div>
            <!-- Add Gallery Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create New Gallery</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Gallery Title</label>
                                <input type="text" id="title" name="title" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select id="category" name="category" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="events">Sports Events</option>
                                    <option value="athletes">Athletes</option>
                                    <option value="training">Training</option>
                                    <option value="partners">Partners</option>
                                    <option value="general">General</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="cover_image" class="block text-sm font-medium text-gray-700">Cover Image</label>
                                <input type="file" id="cover_image" name="cover_image" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" value="0" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="add_gallery" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Gallery
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Galleries List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Galleries</h3>
                    
                    <?php if (empty($galleries)): ?>
                        <p class="text-gray-500 text-center py-8">No galleries found. Create your first gallery above.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($galleries as $gallery): ?>
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                    <?php if ($gallery['cover_image']): ?>
                                        <img src="../uploads/galleries/<?php echo htmlspecialchars($gallery['cover_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($gallery['title']); ?>" 
                                             class="w-full h-48 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-images text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg text-gray-800"><?php echo htmlspecialchars($gallery['title']); ?></h4>
                                        <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($gallery['description']); ?></p>
                                        <div class="flex justify-between items-center mt-3">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?php echo ucfirst($gallery['category']); ?></span>
                                            <div class="space-x-2">
                                                <a href="manage-gallery-images.php?gallery_id=<?php echo $gallery['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900 text-sm">Manage Images</a>
                                                <a href="?delete_gallery=<?php echo $gallery['id']; ?>" 
                                                   class="text-red-600 hover:text-red-900 text-sm" 
                                                   onclick="return confirm('Delete this gallery?')">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>