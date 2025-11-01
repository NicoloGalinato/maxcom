<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle backup creation
if (isset($_POST['create_backup'])) {
    $backup_type = sanitize($_POST['backup_type']);
    $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = '../backups/' . $backup_name;
    
    // Create backups directory if it doesn't exist
    if (!file_exists('../backups')) {
        mkdir('../backups', 0755, true);
    }
    
    if ($backup_type === 'database') {
        if (createDatabaseBackup($conn, $backup_path)) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Database backup created successfully!</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error creating database backup.</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Full backup feature coming soon.</div>';
    }
}

// Handle backup download
if (isset($_GET['download_backup'])) {
    $backup_file = sanitize($_GET['download_backup']);
    $file_path = '../backups/' . $backup_file;
    
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}

// Handle backup deletion
if (isset($_GET['delete_backup'])) {
    $backup_file = sanitize($_GET['delete_backup']);
    $file_path = '../backups/' . $backup_file;
    
    if (file_exists($file_path) && unlink($file_path)) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Backup deleted successfully!</div>';
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error deleting backup.</div>';
    }
}

// Get existing backups
$backups = [];
if (file_exists('../backups')) {
    $files = scandir('../backups');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $file_path = '../backups/' . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'modified' => filemtime($file_path)
            ];
        }
    }
    
    // Sort by modification time (newest first)
    usort($backups, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

/**
 * Create database backup
 */
function createDatabaseBackup($conn, $backup_path) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $output = "-- Sports Management CMS Database Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- PHP Version: " . phpversion() . "\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Drop table if exists
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Create table structure
        $result = $conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        $output .= "\n" . $row[1] . ";\n\n";
        
        // Insert data
        $result = $conn->query("SELECT * FROM `$table`");
        $row_count = $result->rowCount();
        
        if ($row_count > 0) {
            $output .= "-- Dumping data for table `$table`\n";
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $output .= "INSERT INTO `$table` VALUES(";
                $values = [];
                
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                
                $output .= implode(', ', $values) . ");\n";
            }
            
            $output .= "\n";
        }
    }
    
    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    return file_put_contents($backup_path, $output) !== false;
}
?>

    <?php include 'header.php'; ?>

    <main class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Backup & Restore</h1>
                <p class="text-gray-600">Create and manage database backups</p>
            </div>

            <?php echo $message; ?>

            <!-- Create Backup -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create New Backup</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Backup Type</label>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" id="database" name="backup_type" value="database" checked 
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="database" class="ml-3 block text-sm font-medium text-gray-700">
                                        Database Only
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="full" name="backup_type" value="full" 
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="full" class="ml-3 block text-sm font-medium text-gray-700">
                                        Full Backup (Database + Files)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Important</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>• Regular backups help protect your data</p>
                                        <p>• Store backups in a secure location</p>
                                        <p>• Test backup restoration periodically</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="create_backup" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-download mr-2"></i>Create Backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Backups -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Existing Backups</h3>
                    
                    <?php if (empty($backups)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-database text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-600 mb-2">No Backups Found</h3>
                            <p class="text-gray-500">Create your first backup to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Backup File</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-database text-blue-500 text-xl mr-3"></i>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($backup['name']); ?></div>
                                                        <div class="text-sm text-gray-500">Database Backup</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo formatFileSize($backup['size']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y g:i A', $backup['modified']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="?download_backup=<?php echo $backup['name']; ?>" 
                                                   class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-download mr-1"></i>Download
                                                </a>
                                                <a href="?delete_backup=<?php echo $backup['name']; ?>" 
                                                   class="text-red-600 hover:text-red-900"
                                                   onclick="return confirm('Delete this backup?')">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Restore Section -->
            <div class="bg-white shadow rounded-lg mt-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Restore Backup</h3>
                    
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Warning</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>Restoring a backup will overwrite all current data. This action cannot be undone.</p>
                                    <p class="mt-1"><strong>Always backup your current data before restoring.</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" name="backup_file" accept=".sql" 
                                           class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                    <p class="mt-1 text-sm text-gray-500">Select a .sql backup file to restore</p>
                                </div>
                                <button type="submit" name="restore_backup" 
                                        class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        onclick="return confirm('WARNING: This will overwrite all current data. Are you sure?')">
                                    <i class="fas fa-upload mr-2"></i>Restore
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
