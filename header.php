<?php
// header.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : htmlspecialchars($site_settings['site_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/default/ph-cup-new-eagle.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .mobile-menu {
            transition: all 0.3s ease-in-out;
        }
        
        /* Navigation Link Styles */
        .nav-link {
            position: relative;
            padding: 8px 0;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #2563eb;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .nav-link:hover {
            color: #2563eb;
        }
        
        .nav-link.active {
            color: #2563eb;
            font-weight: 500;
        }
        
        /* Mobile Navigation Styles */
        .mobile-nav-link {
            position: relative;
            padding: 12px 0;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .mobile-nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #2563eb;
            transition: all 0.3s ease;
        }
        
        .mobile-nav-link:hover::after,
        .mobile-nav-link.active::after {
            width: 100%;
        }
        
        .mobile-nav-link:hover {
            color: #2563eb;
            padding-left: 8px;
        }
        
        .mobile-nav-link.active {
            color: #2563eb;
            font-weight: 500;
            background-color: #f8fafc;
            padding-left: 12px;
        }
        
        .gallery-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }
        
        .blog-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .blog-card:hover {
            transform: translateY(-5px);
        }
        .read-more {
            position: relative;
        }
        .read-more::after {
            content: '→';
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        .blog-card:hover .read-more::after {
            transform: translateX(3px);
        }
        
        /* Header shadow on scroll */
        .header-scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-md transition-all duration-300" 
             x-data="{ mobileMenuOpen: false, scrolled: false }" 
             @scroll.window="scrolled = window.pageYOffset > 10"
             :class="scrolled ? 'header-scrolled' : ''">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <?php if (!empty($site_settings['site_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($site_settings['site_logo']); ?>" 
                        alt="<?php echo htmlspecialchars($site_settings['site_name']); ?>" 
                        class="h-10 w-auto">
                <?php else: ?>
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo substr($site_settings['site_name'], 0, 1); ?>
                    </div>
                <?php endif; ?>
                <span class="text-xl font-bold text-gray-800">
                    <?php echo htmlspecialchars($site_settings['site_name']); ?>
                </span>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex space-x-8">
                <a href="index.php" 
                   class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                   Home
                </a>
                <a href="services.php" 
                   class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>">
                   Services
                </a>
                <a href="about.php" 
                   class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
                   About
                </a>
                <a href="gallery.php" 
                   class="nav-link <?php echo $current_page == 'gallery.php' ? 'active' : ''; ?>">
                   Gallery
                </a>
                <a href="blog.php" 
                   class="nav-link <?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">
                   Blog
                </a>
                <a href="contact.php" 
                   class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">
                   Contact
                </a>
            </nav>
            
            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 hover:text-blue-600 transition-colors duration-300">
                <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                <i class="fas fa-times text-xl" x-show="mobileMenuOpen"></i>
            </button>
            
            <div class="hidden md:block">
                <a href="contact.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                    Get Started
                </a>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-menu md:hidden bg-white border-t" x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2">
            <div class="container mx-auto px-4 py-2">
                <a href="index.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                   <i class="fas fa-home w-5 mr-3 text-center"></i> Home
                </a>
                <a href="services.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'services.php' ? 'active' : ''; ?>">
                   <i class="fas fa-concierge-bell w-5 mr-3 text-center"></i> Services
                </a>
                <a href="about.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
                   <i class="fas fa-info-circle w-5 mr-3 text-center"></i> About
                </a>
                <a href="gallery.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'gallery.php' ? 'active' : ''; ?>">
                   <i class="fas fa-images w-5 mr-3 text-center"></i> Gallery
                </a>
                <a href="blog.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">
                   <i class="fas fa-blog w-5 mr-3 text-center"></i> Blog
                </a>
                <a href="contact.php" 
                   class="mobile-nav-link block <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">
                   <i class="fas fa-envelope w-5 mr-3 text-center"></i> Contact
                </a>
                <a href="contact.php" class="block bg-blue-600 text-white px-4 py-3 rounded-lg text-center hover:bg-blue-700 transition duration-300 mt-4 transform hover:scale-105">
                    <i class="fas fa-rocket mr-2"></i> Get Started
                </a>
            </div>
        </div>
    </header>