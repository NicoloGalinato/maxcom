<?php
// Common header for admin pages

// Get unread contact submissions count for all admin pages
if (!isset($unread_count)) {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $unread_count_stmt = $conn->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
    $unread_count = $unread_count_stmt->fetchColumn();
}

// Format the badge count
$badge_count = $unread_count > 99 ? '99+' : $unread_count;
$show_badge = $unread_count > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Management CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../assets/images/default/ph-cup-new-eagle.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mobile-menu {
            display: none;
        }
        
        @media (max-width: 1024px) {
            .nav-full {
                display: none;
            }
            
            .mobile-menu-open {
                display: block !important;
            }
        }

        @media (min-width: 1025px) {
            .mobile-menu {
                display: none !important;
            }
        }
        .file-item:hover {
            background-color: #f9fafb;
        }
        .image-preview {
            max-height: 200px;
            max-width: 200px;
        }
        
        /* Notification badge animation */
        .notification-badge {
            animation: pulse 2s infinite;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        
        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
<!-- Top Header -->
<header class="bg-white shadow-sm relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <i class="fas fa-trophy text-blue-600 text-2xl mr-3"></i>
                <span class="text-xl font-semibold text-gray-800 hidden sm:inline">Sports Management CMS</span>
                <span class="text-xl font-semibold text-gray-800 sm:hidden">Sports CMS</span>
            </div>
            
            <!-- Desktop User Info -->
            <div class="hidden lg:flex items-center space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Dashboard</a>
                <span class="text-gray-500">|</span>
                <span class="text-gray-700">Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="text-gray-500 hover:text-gray-700 flex items-center transition-colors duration-300">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    <span>Logout</span>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="lg:hidden flex items-center">
                <button id="mobileMenuButton" class="text-gray-600 hover:text-gray-900 p-2 transition-colors duration-300 relative">
                    <i class="fas fa-bars text-xl"></i>
                    <?php if ($show_badge): ?>
                        <span class="absolute top-1 right-1 bg-red-500 notification-dot"></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Desktop Navigation -->
<nav class="bg-blue-600 text-white nav-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8">
            <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">
                Dashboard
            </a>
            <a href="manage-content.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Content</a>
            <a href="manage-services.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Services</a>
            <a href="manage-testimonials.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Testimonials</a>
            <a href="manage-galleries.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Galleries</a>
            <a href="manage-blog.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Blog</a>
            <a href="manage-contact-submissions.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap relative flex items-center">
                Contact Submissions
                <?php if ($show_badge): ?>
                    <span class="ml-2 bg-red-500 text-white notification-badge"><?php echo $badge_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="file-manager.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap">Files</a>
            
            <div class="relative group">
                <button class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap flex items-center">
                    More <i class="fas fa-chevron-down ml-1 text-xs"></i>
                </button>
                <div class="absolute left-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-gray-200">
                    <a href="manage-users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap transition-colors duration-300">Users</a>
                    <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap transition-colors duration-300">Settings</a>
                    <a href="backup.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap transition-colors duration-300">Backup</a>
                    <a href="analytics.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap transition-colors duration-300">Analytics</a>
                </div>
            </div>
            
            <a href="../index.php" target="_blank" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition whitespace-nowrap flex items-center">
                <i class="fas fa-external-link-alt mr-1"></i>
                View Site
            </a>
        </div>
    </div>
</nav>

<!-- Mobile Navigation -->
<nav id="mobileNav" class="mobile-menu bg-blue-600 text-white shadow-lg">
    <div class="px-4 py-4 space-y-3">
        <!-- Mobile User Info -->
        <div class="pb-3 border-b border-blue-500">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="text-sm hover:text-blue-200 flex items-center transition-colors duration-300">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Mobile Navigation Links -->
        <div class="space-y-2">
            <a href="dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">
                Dashboard
            </a>
            <a href="manage-content.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Content</a>
            <a href="manage-services.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Services</a>
            <a href="manage-testimonials.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Testimonials</a>
            <a href="manage-galleries.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Galleries</a>
            <a href="manage-blog.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Blog</a>
            <a href="manage-contact-submissions.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition relative flex items-center justify-between">
                <span>Contact Submissions</span>
                <?php if ($show_badge): ?>
                    <span class="bg-red-500 text-white notification-badge"><?php echo $badge_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="file-manager.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Files</a>
            
            <!-- More Options for Mobile -->
            <div class="pt-2 border-t border-blue-500">
                <div class="px-3 py-2 text-sm font-medium text-blue-200">More Options</div>
                <a href="manage-users.php" class="block px-6 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Users</a>
                <a href="settings.php" class="block px-6 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Settings</a>
                <a href="backup.php" class="block px-6 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Backup</a>
                <a href="analytics.php" class="block px-6 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition">Analytics</a>
            </div>
            
            <a href="../index.php" target="_blank" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition border-t border-blue-500 pt-2">
                View Site
            </a>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileNav = document.getElementById('mobileNav');
    
    console.log('Mobile menu button:', mobileMenuButton);
    console.log('Mobile nav:', mobileNav);
    
    if (mobileMenuButton && mobileNav) {
        mobileMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Menu button clicked');
            
            if (mobileNav.classList.contains('mobile-menu-open')) {
                mobileNav.classList.remove('mobile-menu-open');
                console.log('Menu closed');
            } else {
                mobileNav.classList.add('mobile-menu-open');
                console.log('Menu opened');
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileNav.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                mobileNav.classList.remove('mobile-menu-open');
                console.log('Menu closed (outside click)');
            }
        });
        
        // Close mobile menu when clicking on a link
        mobileNav.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                mobileNav.classList.remove('mobile-menu-open');
                console.log('Menu closed (link click)');
            }
        });
        
        // Close mobile menu on window resize (if resizing to desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                mobileNav.classList.remove('mobile-menu-open');
            }
        });
    } else {
        console.error('Mobile menu elements not found');
    }
});
</script>