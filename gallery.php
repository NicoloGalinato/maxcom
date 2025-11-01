<?php
// Replace the existing database connection code with this:
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

// Get all active galleries
$stmt = $conn->prepare("SELECT * FROM galleries WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
$stmt->execute();
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get gallery counts
$gallery_counts = [];
foreach ($galleries as $gallery) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM gallery_images WHERE gallery_id = ? AND is_active = 1");
    $stmt->execute([$gallery['id']]);
    $gallery_counts[$gallery['id']] = $stmt->fetchColumn();
}
$page_title = "Photo Galleries" .' | '. htmlspecialchars($site_settings['site_name']);
require_once 'header.php';
?>


    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Photo Galleries</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Explore our collection of sports events, athlete portfolios, and behind-the-scenes moments</p>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <!-- Category Filters -->
            <div class="text-center mb-12">
                <div class="flex flex-wrap justify-center gap-2 mb-8" id="filter-buttons">
                    <button class="filter-btn active px-4 py-2 rounded-full bg-blue-600 text-white transition-all duration-300 hover:bg-blue-700" data-filter="all">All Galleries</button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 transition-all duration-300 hover:bg-gray-300 hover:text-gray-900" data-filter="events">Sports Events</button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 transition-all duration-300 hover:bg-gray-300 hover:text-gray-900" data-filter="athletes">Athletes</button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 transition-all duration-300 hover:bg-gray-300 hover:text-gray-900" data-filter="training">Training</button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 transition-all duration-300 hover:bg-gray-300 hover:text-gray-900" data-filter="partners">Partners</button>
                </div>
            </div>

            <!-- Galleries Grid -->
            <?php if (empty($galleries)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-images text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-600 mb-2">No Galleries Yet</h3>
                    <p class="text-gray-500">Check back soon for exciting sports galleries!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="galleries-grid">
                    <?php foreach ($galleries as $gallery): ?>
                        <div class="gallery-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 transition-all duration-500 ease-in-out transform hover:scale-105 hover:shadow-lg" data-category="<?php echo $gallery['category']; ?>">
                            <div class="relative">
                                <?php if ($gallery['cover_image']): ?>
                                    <img src="uploads/galleries/<?php echo htmlspecialchars($gallery['cover_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($gallery['title']); ?>" 
                                         class="w-full h-64 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-images text-gray-400 text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <span class="category-badge bg-blue-600 text-white px-3 py-1 rounded-full text-sm capitalize">
                                    <?php echo $gallery['category']; ?>
                                </span>
                                
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                                    <div class="text-white text-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                        <i class="fas fa-eye text-3xl mb-2"></i>
                                        <p class="font-semibold">View Gallery</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($gallery['title']); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($gallery['description']); ?></p>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        <i class="fas fa-images mr-1"></i>
                                        <?php echo $gallery_counts[$gallery['id']]; ?> photos
                                    </span>
                                    <a href="gallery-view.php?id=<?php echo $gallery['id']; ?>" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                                        View Gallery
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Want to See Your Photos Here?</h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">Join our sports management family and get featured in our exclusive galleries</p>
            <a href="index.php#contact" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                Get In Touch
            </a>
        </div>
    </section>

    <script>
    // Category Filter Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const galleryCards = document.querySelectorAll('.gallery-card');
        
        // Filter function
        function filterGalleries(category) {
            galleryCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        }
        
        // Add click event to filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                });
                
                // Add active class to clicked button
                this.classList.remove('bg-gray-200', 'text-gray-700');
                this.classList.add('active', 'bg-blue-600', 'text-white');
                
                // Filter galleries
                const filterValue = this.getAttribute('data-filter');
                filterGalleries(filterValue);
            });
        });
        
        // Initialize with all galleries showing
        filterGalleries('all');
    });

    // Add smooth animations for gallery cards
    document.addEventListener('DOMContentLoaded', function() {
        const galleryCards = document.querySelectorAll('.gallery-card');
        
        galleryCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>

 <?php include 'footer.php'; ?>