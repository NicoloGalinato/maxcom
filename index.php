<?php
// Replace the existing database connection code with this:
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

// Get all content from database
$hero_content = getHeroContent($conn);
$services = getServices($conn);
$testimonials = getTestimonials($conn);
$about_content = getGeneralContent($conn, 'about_content');
$contact_info = getGeneralContent($conn, 'contact_info');
$contact_data = json_decode($contact_info['extra_data'], true);

$page_title = htmlspecialchars($site_settings['site_name']);
require_once 'header.php';
?>

    <!-- Hero Section -->
    <section id="home" class="hero-bg text-white py-20 md:py-32 fade-in bg-gray-700">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 slide-in-left"><?php echo htmlspecialchars($hero_content['title']); ?></h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto slide-in-right"><?php echo htmlspecialchars($hero_content['subtitle']); ?></p>
            <div class="flex flex-col md:flex-row justify-center gap-4">
                <a href="services.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-300 bounce inline-flex items-center justify-center">
                    <?php echo htmlspecialchars($hero_content['button1_text']); ?> <i class="fas fa-arrow-right ml-2"></i>
                </a>
                <a href="contact.php" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-white hover:text-gray-800 transition duration-300 inline-flex items-center justify-center">
                    <?php echo htmlspecialchars($hero_content['button2_text']); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Services Preview Section -->
    <section id="services" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Comprehensive sports management solutions tailored to your needs</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach (array_slice($services, 0, 3) as $service): ?>
                <div class="bg-gray-50 rounded-xl p-6 card-hover">
                    <div class="w-14 h-14 bg-<?php echo $service['color']; ?>-100 rounded-full flex items-center justify-center mb-4">
                        <i class="<?php echo $service['icon']; ?> text-<?php echo $service['color']; ?>-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($service['description']); ?></p>
                    <a href="services.php" class="text-blue-600 font-medium flex items-center">
                        Learn More <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="services.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-300 inline-flex items-center">
                    View All Services <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- About Preview Section -->
    <section id="about" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($about_content['content_title']); ?></h2>
                    <p class="text-gray-600 mb-6"><?php echo htmlspecialchars(substr($about_content['content_text'], 0, 200)); ?>...</p>
                    
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Professional Representation</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Strategic Planning</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Financial Management</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="text-gray-700">Brand Development</span>
                        </div>
                    </div>
                    
                    <a href="about.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300 inline-flex items-center">
                        Learn More About Us <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                
                <div class="lg:w-1/2 relative">
                    <div class="bg-blue-600 rounded-2xl p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">Our Mission</h3>
                        <p class="mb-6">To empower athletes and sports organizations with strategic management solutions that drive success, growth, and sustainability in the competitive world of sports.</p>
                        
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-trophy text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold">500+</h4>
                                <p class="text-blue-200">Athletes Represented</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-medal text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold">150+</h4>
                                <p class="text-blue-200">Championship Wins</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Carousel -->
    <section id="testimonials" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Client Testimonials</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">What our clients say about our services</p>
            </div>
            
            <!-- Carousel -->
            <div x-data="{ activeSlide: 0, slides: [
                <?php foreach ($testimonials as $index => $testimonial): ?>
                { 
                    name: '<?php echo addslashes($testimonial['client_name']); ?>', 
                    role: '<?php echo addslashes($testimonial['client_role']); ?>', 
                    text: '<?php echo addslashes($testimonial['testimonial_text']); ?>', 
                    image: '<?php echo $testimonial['client_image'] ? 'uploads/' . $testimonial['client_image'] : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80'; ?>' 
                }<?php echo ($index < count($testimonials) - 1) ? ',' : ''; ?>
                <?php endforeach; ?>
            ] }" class="relative max-w-4xl mx-auto">
                <div class="overflow-hidden rounded-2xl bg-gray-100">
                    <div class="flex transition-transform duration-500 ease-in-out" 
                         :style="`transform: translateX(-${activeSlide * 100}%)`">
                        <template x-for="(slide, index) in slides" :key="index">
                            <div class="w-full flex-shrink-0 px-8 py-12 md:py-16">
                                <div class="flex flex-col md:flex-row items-center gap-8">
                                    <div class="md:w-1/3 flex justify-center">
                                        <img :src="slide.image" :alt="slide.name" class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover shadow-lg">
                                    </div>
                                    <div class="md:w-2/3 text-center md:text-left">
                                        <p class="text-gray-600 text-lg mb-6" x-text="slide.text"></p>
                                        <h4 class="text-xl font-bold text-gray-800" x-text="slide.name"></h4>
                                        <p class="text-gray-500" x-text="slide.role"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Carousel Controls -->
                <div class="flex justify-center mt-6 space-x-3">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button 
                            class="w-3 h-3 rounded-full transition-colors duration-300" 
                            :class="activeSlide === index ? 'bg-blue-600' : 'bg-gray-300'"
                            @click="activeSlide = index"
                        ></button>
                    </template>
                </div>
                
                <!-- Navigation Arrows -->
                <button 
                    class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-gray-100 transition-colors duration-300"
                    @click="activeSlide = activeSlide === 0 ? slides.length - 1 : activeSlide - 1"
                >
                    <i class="fas fa-chevron-left text-gray-700"></i>
                </button>
                <button 
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-gray-100 transition-colors duration-300"
                    @click="activeSlide = activeSlide === slides.length - 1 ? 0 : activeSlide + 1"
                >
                    <i class="fas fa-chevron-right text-gray-700"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/2 bg-blue-600 text-white p-8 md:p-12">
                        <h2 class="text-3xl font-bold mb-6">Get In Touch</h2>
                        <p class="mb-8">Ready to take your sports career or organization to the next level? Contact us today for a consultation.</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-4"></i>
                                <div>
                                    <h4 class="font-bold">Our Office</h4>
                                    <p><?php echo htmlspecialchars($contact_data['address']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-phone mt-1 mr-4"></i>
                                <div>
                                    <h4 class="font-bold">Phone</h4>
                                    <p><?php echo htmlspecialchars($contact_data['phone']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-envelope mt-1 mr-4"></i>
                                <div>
                                    <h4 class="font-bold">Email</h4>
                                    <p><?php echo htmlspecialchars($contact_data['email']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:w-1/2 p-8 md:p-12">
                        <form class="space-y-6" method="POST" action="process-contact.php">
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
                                    <option value="<?php echo $service['title']; ?>"><?php echo htmlspecialchars($service['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="message" class="block text-gray-700 font-medium mb-2">Message *</label>
                                <textarea id="message" name="message" rows="4" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                        placeholder="Your message..."></textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-300">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

 <?php include 'footer.php'; ?>