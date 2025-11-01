<?php
// Replace the existing database connection code with this:
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

$about_content = getGeneralContent($conn, 'about_content');
$page_title = "About Us" . " | " . htmlspecialchars($site_settings['site_name']);
require_once 'header.php';
?>

    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">About Us</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Leading sports management company dedicated to athlete success</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8 text-center"><?php echo htmlspecialchars($about_content['content_title']); ?></h2>
                <div class="prose prose-lg max-w-none text-gray-600 mb-12">
                    <?php echo nl2br(htmlspecialchars($about_content['content_text'])); ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">500+</h3>
                        <p class="text-gray-600">Athletes Represented</p>
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-medal text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">150+</h3>
                        <p class="text-gray-600">Championship Wins</p>
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-handshake text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">50+</h3>
                        <p class="text-gray-600">Partner Organizations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    <p class="text-gray-600">To empower athletes and sports organizations with strategic management solutions that drive success, growth, and sustainability in the competitive world of sports.</p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600">To be the leading sports management company globally, recognized for excellence in athlete representation, innovation in sports business, and commitment to ethical practices.</p>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>