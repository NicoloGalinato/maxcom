<?php
// services.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

$services = getServices($conn);
$page_title = "Our Services - Elite Sports Management";
require_once 'header.php';
?>

    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Our Services</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Comprehensive sports management solutions tailored to your needs</p>
        </div>
    </section>

    <!-- Services Details -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <?php foreach ($services as $index => $service): ?>
                <div class="bg-gray-50 rounded-xl p-8 card-hover <?php echo $index % 2 === 0 ? 'lg:order-1' : 'lg:order-2'; ?>">
                    <div class="w-16 h-16 bg-<?php echo $service['color']; ?>-100 rounded-full flex items-center justify-center mb-6">
                        <i class="<?php echo $service['icon']; ?> text-<?php echo $service['color']; ?>-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="text-gray-600 mb-6 text-lg"><?php echo htmlspecialchars($service['description']); ?></p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span class="text-gray-700">Professional representation and negotiation</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span class="text-gray-700">Strategic career planning</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span class="text-gray-700">Comprehensive support services</span>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <a href="contact.php?service=<?php echo urlencode($service['title']); ?>" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300 inline-flex items-center">
                            Get Started with <?php echo htmlspecialchars($service['title']); ?>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
                
                <div class="<?php echo $index % 2 === 0 ? 'lg:order-2' : 'lg:order-1'; ?> flex items-center justify-center">
                    <div class="bg-<?php echo $service['color']; ?>-100 rounded-2xl p-8 w-full h-64 flex items-center justify-center">
                        <i class="<?php echo $service['icon']; ?> text-<?php echo $service['color']; ?>-600 text-6xl"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Ready to Get Started?</h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">Contact us today to discuss how our services can help you achieve your sports career goals</p>
            <a href="contact.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-300">
                Contact Us Now
            </a>
        </div>
    </section>

<?php include 'footer.php'; ?>