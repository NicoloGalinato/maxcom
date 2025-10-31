<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

// Get counts for dashboard
$services_count = $conn->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
$testimonials_count = $conn->query("SELECT COUNT(*) FROM testimonials WHERE is_active = 1")->fetchColumn();
$hero_content = getHeroContent($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sports Management CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-sliders-h text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Hero Section</dt>
                                <dd class="text-lg font-medium text-gray-900">Active</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-concierge-bell text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Services</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $services_count; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-comments text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Testimonials</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $testimonials_count; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="manage-content.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-edit text-blue-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Manage Content</h3>
                    <p class="mt-1 text-sm text-gray-500">Update hero section and general content</p>
                </div>
            </a>

            <a href="manage-services.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-concierge-bell text-green-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Manage Services</h3>
                    <p class="mt-1 text-sm text-gray-500">Add, edit, or remove services</p>
                </div>
            </a>

            <a href="manage-testimonials.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-comments text-purple-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Testimonials</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage client testimonials</p>
                </div>
            </a>

            <a href="../index.php" target="_blank" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-eye text-orange-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">View Site</h3>
                    <p class="mt-1 text-sm text-gray-500">Preview your landing page</p>
                </div>
            </a>
            <a href="manage-galleries.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-images text-purple-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Galleries</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage photo galleries</p>
                </div>
            </a>

            <a href="manage-blog.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-newspaper text-green-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Blog</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage blog posts</p>
                </div>
            </a>

            <a href="file-manager.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-folder-open text-orange-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">File Manager</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage uploaded files</p>
                </div>
            </a>

            <a href="analytics.php" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                <div class="px-4 py-5 sm:p-6 text-center">
                    <i class="fas fa-chart-bar text-indigo-600 text-3xl mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-900">Analytics</h3>
                    <p class="mt-1 text-sm text-gray-500">View site statistics</p>
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Latest updates to your content</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-history text-4xl mb-3"></i>
                        <p>Activity log will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>