<?php
// Replace the existing database connection code with this:
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

$contact_info = getGeneralContent($conn, 'contact_info');
$contact_data = json_decode($contact_info['extra_data'], true);
$services = getServices($conn);

$selected_service = $_GET['service'] ?? '';
$page_title = "Contact Us" .' | '. htmlspecialchars($site_settings['site_name']);
require_once 'header.php';
?>

    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Get in touch with our sports management experts</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Contact Info -->
                    <div class="lg:col-span-1">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Get In Touch</h2>
                        <p class="text-gray-600 mb-8">Ready to take your sports career or organization to the next level? Contact us today for a consultation.</p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-600 mt-1 mr-4 text-xl"></i>
                                <div>
                                    <h4 class="font-bold text-gray-800">Our Office</h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($contact_data['address']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-phone text-blue-600 mt-1 mr-4 text-xl"></i>
                                <div>
                                    <h4 class="font-bold text-gray-800">Phone</h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($contact_data['phone']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-envelope text-blue-600 mt-1 mr-4 text-xl"></i>
                                <div>
                                    <h4 class="font-bold text-gray-800">Email</h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($contact_data['email']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-clock text-blue-600 mt-1 mr-4 text-xl"></i>
                                <div>
                                    <h4 class="font-bold text-gray-800">Business Hours</h4>
                                    <p class="text-gray-600">Monday - Friday: 9:00 AM - 6:00 PM</p>
                                    <p class="text-gray-600">Saturday: 10:00 AM - 2:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-100 rounded-2xl p-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h3>
                            <form class="space-y-6" method="POST" action="process-contact.php">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="name" class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                        <input type="text" id="name" name="name" required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="Your Name">
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-gray-700 font-medium mb-2">Email Address *</label>
                                        <input type="email" id="email" name="email" required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="your.email@example.com">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="+1 (555) 123-4567">
                                    </div>
                                    
                                    <div>
                                        <label for="service" class="block text-gray-700 font-medium mb-2">Service Interested In</label>
                                        <select id="service" name="service" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select a service</option>
                                            <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['title']; ?>" <?php echo $selected_service === $service['title'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($service['title']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="message" class="block text-gray-700 font-medium mb-2">Message *</label>
                                    <textarea id="message" name="message" rows="6" required 
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                              placeholder="Tell us about your sports management needs..."></textarea>
                                </div>
                                
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-300">
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Visit Our Office</h2>
                <div class="bg-gray-200 rounded-lg h-96 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-gray-400 text-6xl mb-4"></i>
                        <p class="text-gray-600">Interactive map would be embedded here</p>
                        <p class="text-gray-500 text-sm mt-2"><?php echo htmlspecialchars($contact_data['address']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>