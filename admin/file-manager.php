<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';
$current_dir = $_GET['dir'] ?? 'uploads';
$base_path = realpath('../') . '/';

// Security: Prevent directory traversal
if (strpos($current_dir, '..') !== false) {
    $current_dir = 'uploads';
}

$full_path = $base_path . $current_dir;

// Ensure the path is within our allowed directories
$allowed_dirs = ['uploads', 'uploads/hero', 'uploads/services', 'uploads/testimonials', 'uploads/galleries', 'uploads/blog'];
if (!in_array($current_dir, $allowed_dirs) && !preg_match('/^uploads\/(hero|services|testimonials|galleries|blog)/', $current_dir)) {
    $current_dir = 'uploads';
    $full_path = $base_path . $current_dir;
}

// Handle file upload
if (isset($_POST['upload_files']) && !empty($_FILES['files']['name'][0])) {
    $uploaded_count = 0;
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = basename($_FILES['files']['name'][$key]);
            $target_file = $full_path . '/' . $file_name;
            
            // Check if file already exists
            if (file_exists($target_file)) {
                $file_name = time() . '_' . $file_name;
                $target_file = $full_path . '/' . $file_name;
            }
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                $uploaded_count++;
            }
        }
    }
    
    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . $uploaded_count . ' files uploaded successfully!</div>';
}

// Handle file deletion
if (isset($_GET['delete_file'])) {
    $file_to_delete = sanitize($_GET['delete_file']);
    $file_path = $full_path . '/' . $file_to_delete;
    
    // Security: Ensure we're only deleting files within allowed directories
    if (file_exists($file_path) && is_file($file_path) && strpos(realpath($file_path), $base_path) === 0) {
        if (unlink($file_path)) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">File deleted successfully!</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error deleting file.</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Invalid file path.</div>';
    }
}

// Handle folder creation
if (isset($_POST['create_folder'])) {
    $folder_name = sanitize($_POST['folder_name']);
    $new_folder_path = $full_path . '/' . $folder_name;
    
    if (!file_exists($new_folder_path)) {
        if (mkdir($new_folder_path, 0755, true)) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Folder created successfully!</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error creating folder.</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Folder already exists.</div>';
    }
}

// Get files and folders
$items = [];
if (is_dir($full_path)) {
    $scan = scandir($full_path);
    foreach ($scan as $item) {
        if ($item != '.' && $item != '..') {
            $item_path = $full_path . '/' . $item;
            $items[] = [
                'name' => $item,
                'path' => $current_dir . '/' . $item,
                'is_dir' => is_dir($item_path),
                'size' => is_file($item_path) ? filesize($item_path) : 0,
                'modified' => filemtime($item_path),
                'type' => mime_content_type($item_path)
            ];
        }
    }
}

// Sort: folders first, then files
usort($items, function($a, $b) {
    if ($a['is_dir'] && !$b['is_dir']) return -1;
    if (!$a['is_dir'] && $b['is_dir']) return 1;
    return strcmp($a['name'], $b['name']);
});

// Get breadcrumb
$breadcrumbs = [];
$parts = explode('/', $current_dir);
$current_path = '';
foreach ($parts as $part) {
    $current_path .= $part . '/';
    $breadcrumbs[] = [
        'name' => $part ?: 'Root',
        'path' => rtrim($current_path, '/')
    ];
}
?>
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">File Manager</h1>
                <p class="text-gray-600">Manage your website files and images</p>
            </div>

            <?php echo $message; ?>

            <!-- Breadcrumb -->
            <div class="bg-white shadow rounded-lg mb-4">
                <div class="px-4 py-3">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                <li>
                                    <?php if ($index < count($breadcrumbs) - 1): ?>
                                        <a href="?dir=<?php echo $crumb['path']; ?>" class="text-blue-600 hover:text-blue-700">
                                            <?php echo htmlspecialchars($crumb['name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500"><?php echo htmlspecialchars($crumb['name']); ?></span>
                                    <?php endif; ?>
                                </li>
                                <?php if ($index < count($breadcrumbs) - 1): ?>
                                    <li><span class="text-gray-400">/</span></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-4 flex flex-wrap gap-4">
                    <!-- Upload Files -->
                    <div>
                        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-upload mr-2"></i>Upload Files
                        </button>
                    </div>

                    <!-- Create Folder -->
                    <div>
                        <button onclick="document.getElementById('folderModal').classList.remove('hidden')" 
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fas fa-folder-plus mr-2"></i>Create Folder
                        </button>
                    </div>

                    <!-- Storage Info -->
                    <div class="ml-auto">
                        <?php
                        $total_size = 0;
                        $file_count = 0;
                        foreach ($items as $item) {
                            if (!$item['is_dir']) {
                                $total_size += $item['size'];
                                $file_count++;
                            }
                        }
                        ?>
                        <span class="text-sm text-gray-600">
                            <?php echo $file_count; ?> files, 
                            <?php echo formatFileSize($total_size); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Files and Folders List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <?php if (empty($items)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-folder-open text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-600 mb-2">Empty Folder</h3>
                            <p class="text-gray-500">Upload files or create folders to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modified</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($items as $item): ?>
                                        <tr class="file-item">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <?php if ($item['is_dir']): ?>
                                                        <i class="fas fa-folder text-yellow-500 text-xl mr-3"></i>
                                                        <a href="?dir=<?php echo $item['path']; ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?php if (strpos($item['type'], 'image/') === 0): ?>
                                                            <i class="fas fa-image text-green-500 text-xl mr-3"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-file text-gray-400 text-xl mr-3"></i>
                                                        <?php endif; ?>
                                                        <span class="text-gray-900"><?php echo htmlspecialchars($item['name']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $item['is_dir'] ? '-' : formatFileSize($item['size']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y g:i A', $item['modified']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <?php if ($item['is_dir']): ?>
                                                    <a href="?dir=<?php echo $item['path']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Open</a>
                                                <?php else: ?>
                                                    <?php if (strpos($item['type'], 'image/') === 0): ?>
                                                        <button onclick="previewImage('<?php echo $item['path']; ?>')" class="text-green-600 hover:text-green-900 mr-3">Preview</button>
                                                    <?php endif; ?>
                                                    <a href="../<?php echo $item['path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                    <a href="?dir=<?php echo $current_dir; ?>&delete_file=<?php echo $item['name']; ?>" 
                                                       class="text-red-600 hover:text-red-900"
                                                       onclick="return confirm('Delete this file?')">Delete</a>
                                                <?php endif; ?>
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

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Files</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <input type="file" name="files[]" multiple 
                               class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <p class="mt-1 text-sm text-gray-500">You can select multiple files</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" name="upload_files" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="folderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Folder</h3>
                <form method="POST">
                    <div class="mb-4">
                        <label for="folder_name" class="block text-sm font-medium text-gray-700">Folder Name</label>
                        <input type="text" id="folder_name" name="folder_name" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('folderModal').classList.add('hidden')" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" name="create_folder" 
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Image Preview</h3>
                <img id="previewImage" src="" alt="Preview" class="image-preview mx-auto mb-4">
                <div class="flex justify-end">
                    <button onclick="document.getElementById('previewModal').classList.add('hidden')" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function previewImage(imagePath) {
            document.getElementById('previewImage').src = '../' + imagePath;
            document.getElementById('previewModal').classList.remove('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const uploadModal = document.getElementById('uploadModal');
            const folderModal = document.getElementById('folderModal');
            const previewModal = document.getElementById('previewModal');
            
            if (event.target === uploadModal) uploadModal.classList.add('hidden');
            if (event.target === folderModal) folderModal.classList.add('hidden');
            if (event.target === previewModal) previewModal.classList.add('hidden');
        }
    </script>
</body>
</html>
