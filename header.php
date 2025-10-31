<?php
// header.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Elite Sports Management'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .mobile-menu {
            transition: all 0.3s ease-in-out;
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
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-md" x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-trophy text-blue-600 text-2xl mr-2"></i>
                <span class="text-xl font-bold text-gray-800">Elite Sports Management</span>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex space-x-8">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a>
                <a href="services.php" class="text-gray-700 hover:text-blue-600 font-medium">Services</a>
                <a href="about.php" class="text-gray-700 hover:text-blue-600 font-medium">About</a>
                <a href="gallery.php" class="text-gray-700 hover:text-blue-600 font-medium">Gallery</a>
                <a href="blog.php" class="text-gray-700 hover:text-blue-600 font-medium">Blog</a>
                <a href="contact.php" class="text-gray-700 hover:text-blue-600 font-medium">Contact</a>
            </nav>
            
            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700">
                <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                <i class="fas fa-times text-xl" x-show="mobileMenuOpen"></i>
            </button>
            
            <div class="hidden md:block">
                <a href="contact.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    Get Started
                </a>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-menu md:hidden bg-white border-t" x-show="mobileMenuOpen" x-transition>
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="index.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Home</a>
                <a href="services.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Services</a>
                <a href="about.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">About</a>
                <a href="gallery.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Gallery</a>
                <a href="blog.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Blog</a>
                <a href="contact.php" class="block text-gray-700 hover:text-blue-600 font-medium py-2">Contact</a>
                <a href="contact.php" class="block bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition duration-300">
                    Get Started
                </a>
            </div>
        </div>
    </header>