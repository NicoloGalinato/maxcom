<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$gallery_id = intval($_GET['gallery_id'] ?? 0);
if (!$gallery_id) {
    redirect('manage-galleries.php');
}

// Get gallery info
$stmt = $conn->prepare("SELECT * FROM galleries WHERE id = ?");
$stmt->execute([$gallery_id]);
$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    redirect('manage-galleries.php');
}

$message = '';

// Handle image upload
if (isset($_POST['upload_images'])) {
    if (!empty($_FILES['images']['name'][0])) {
        $uploaded_count = 0;
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];
                
                $upload = uploadImage($file, 'uploads/galleries/');
                if ($upload['success']) {
                    $title = pathinfo($file['name'], PATHINFO_FILENAME);
                    $stmt = $conn->prepare("INSERT INTO gallery_images (gallery_id, image_path, title, uploaded_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$gallery_id, $upload['filename'], $title, $_SESSION['admin_id']]);
                    $uploaded_count++;
                }
            }
        }
        
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $uploaded_count . ' images uploaded successfully!</div>';
    }
}

// Handle image operations
if (isset($_POST['update_images'])) {
    foreach ($_POST['titles'] as $image_id => $title) {
        $sort_order = intval($_POST['sort_orders'][$image_id]);
        $stmt = $conn->prepare("UPDATE gallery_images SET title = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([sanitize($title), $sort_order, $image_id]);
    }
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Images updated successfully!</div>';
}

if (isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Image deleted successfully!</div>';
}

// Get gallery images
$stmt = $conn->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC, created_at DESC");
$stmt->execute([$gallery_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($gallery['title']); ?></h1>
                    <p class="text-gray-600">Manage images for this gallery</p>
                </div>
                <a href="manage-galleries.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Back to Galleries
                </a>
            </div>

            <!-- Upload Images Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upload Images</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="images" class="block text-sm font-medium text-gray-700">Select Images</label>
                            <input type="file" id="images" name="images[]" multiple 
                                   accept="image/*" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">You can select multiple images (JPEG, PNG, GIF)</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="upload_images" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Upload Images
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Images List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Gallery Images (<?php echo count($images); ?>)</h3>
                    
                    <?php if (empty($images)): ?>
                        <p class="text-gray-500 text-center py-8">No images in this gallery yet. Upload some images above.</p>
                    <?php else: ?>
                        <form method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                <?php foreach ($images as $image): ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <img src="../uploads/galleries/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                             class="w-full h-48 object-cover">
                                        
                                        <div class="p-4">
                                            <input type="text" 
                                                   name="titles[<?php echo $image['id']; ?>]" 
                                                   value="<?php echo htmlspecialchars($image['title']); ?>" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 mb-2"
                                                   placeholder="Image title">
                                            
                                            <input type="number" 
                                                   name="sort_orders[<?php echo $image['id']; ?>]" 
                                                   value="<?php echo $image['sort_order']; ?>" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Sort order">
                                            
                                            <div class="mt-2 flex justify-between">
                                                <span class="text-xs text-gray-500">
                                                    <?php echo date('M j, Y', strtotime($image['created_at'])); ?>
                                                </span>
                                                <a href="?gallery_id=<?php echo $gallery_id; ?>&delete_image=<?php echo $image['id']; ?>" 
                                                   class="text-red-600 hover:text-red-900 text-sm" 
                                                   onclick="return confirm('Delete this image?')">
                                                    Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_images" 
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Update All Images
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>