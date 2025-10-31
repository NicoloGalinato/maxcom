<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

// Get basic stats
$total_services = $conn->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
$total_testimonials = $conn->query("SELECT COUNT(*) FROM testimonials WHERE is_active = 1")->fetchColumn();
$total_galleries = $conn->query("SELECT COUNT(*) FROM galleries WHERE is_active = 1")->fetchColumn();
$total_blog_posts = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND is_active = 1")->fetchColumn();
$total_contacts = $conn->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
$unread_contacts = $conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();

// Get recent activity
$recent_contacts = $conn->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recent_posts = $conn->query("SELECT title, published_at, views FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get popular posts
$popular_posts = $conn->query("SELECT title, slug, views FROM blog_posts WHERE status = 'published' ORDER BY views DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get storage usage (approximate)
$storage_used = 0;
$upload_dirs = ['uploads/hero', 'uploads/services', 'uploads/testimonials', 'uploads/galleries', 'uploads/blog'];

foreach ($upload_dirs as $dir) {
    if (file_exists('../' . $dir)) {
        $storage_used += folderSize('../' . $dir);
    }
}

/**
 * Calculate folder size recursively
 */
function folderSize($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Sports Management CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
                <p class="text-gray-600">Overview of your website performance and content</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Services -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fas fa-concierge-bell text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Services</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $total_services; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonials -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <i class="fas fa-comments text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Testimonials</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $total_testimonials; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blog Posts -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <i class="fas fa-newspaper text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Blog Posts</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $total_blog_posts; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Messages -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Contact Messages</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        <?php echo $total_contacts; ?>
                                        <?php if ($unread_contacts > 0): ?>
                                            <span class="text-sm text-red-600 ml-1">(<?php echo $unread_contacts; ?> new)</span>
                                        <?php endif; ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Contact Messages -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Contact Messages</h3>
                        
                        <?php if (empty($recent_contacts)): ?>
                            <p class="text-gray-500 text-center py-4">No contact messages yet.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_contacts as $contact): ?>
                                    <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($contact['name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($contact['email']); ?></p>
                                            <p class="text-sm text-gray-500 mt-1"><?php echo substr($contact['message'], 0, 100); ?>...</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-500 block">
                                                <?php echo date('M j, g:i A', strtotime($contact['submitted_at'])); ?>
                                            </span>
                                            <?php if (!$contact['is_read']): ?>
                                                <span class="inline-block mt-1 bg-red-100 text-red-800 text-xs px-2 py-1 rounded">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="manage-contact-submissions.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    View All Messages →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Popular Blog Posts -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Popular Blog Posts</h3>
                        
                        <?php if (empty($popular_posts)): ?>
                            <p class="text-gray-500 text-center py-4">No blog posts yet.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($popular_posts as $post): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($post['title']); ?></h4>
                                            <div class="flex items-center mt-1 text-sm text-gray-500">
                                                <i class="fas fa-eye mr-1"></i>
                                                <span><?php echo $post['views']; ?> views</span>
                                            </div>
                                        </div>
                                        <a href="../blog-post.php?slug=<?php echo $post['slug']; ?>" target="_blank" 
                                           class="text-blue-600 hover:text-blue-700 ml-4">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="manage-blog.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Manage Blog Posts →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">System Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Storage Usage -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Storage Usage</h4>
                            <div class="bg-gray-200 rounded-full h-4">
                                <div class="bg-blue-600 h-4 rounded-full" style="width: <?php echo min(($storage_used / (100 * 1024 * 1024)) * 100, 100); ?>%"></div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600 mt-2">
                                <span><?php echo formatBytes($storage_used); ?> used</span>
                                <span>100 MB limit</span>
                            </div>
                        </div>

                        <!-- Server Info -->
                        <div>
                            <h4 class="text-md font-medium text-gray-700 mb-3">Server Information</h4>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>PHP Version:</span>
                                    <span class="font-medium"><?php echo phpversion(); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Database:</span>
                                    <span class="font-medium">MySQL</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Server Software:</span>
                                    <span class="font-medium"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Quick Actions</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="manage-content.php" class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg text-center hover:bg-blue-100 transition duration-300">
                                <i class="fas fa-edit block text-xl mb-1"></i>
                                <span class="text-sm">Edit Content</span>
                            </a>
                            <a href="manage-blog.php" class="bg-green-50 text-green-700 px-4 py-2 rounded-lg text-center hover:bg-green-100 transition duration-300">
                                <i class="fas fa-newspaper block text-xl mb-1"></i>
                                <span class="text-sm">Manage Blog</span>
                            </a>
                            <a href="manage-galleries.php" class="bg-purple-50 text-purple-700 px-4 py-2 rounded-lg text-center hover:bg-purple-100 transition duration-300">
                                <i class="fas fa-images block text-xl mb-1"></i>
                                <span class="text-sm">Galleries</span>
                            </a>
                            <a href="backup.php" class="bg-orange-50 text-orange-700 px-4 py-2 rounded-lg text-center hover:bg-orange-100 transition duration-300">
                                <i class="fas fa-download block text-xl mb-1"></i>
                                <span class="text-sm">Backup</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>