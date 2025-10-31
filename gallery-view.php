<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

$gallery_id = intval($_GET['id'] ?? 0);

// Get gallery info
$stmt = $conn->prepare("SELECT * FROM galleries WHERE id = ? AND is_active = 1");
$stmt->execute([$gallery_id]);
$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    header("Location: gallery.php");
    exit();
}

// Get gallery images
$stmt = $conn->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? AND is_active = 1 ORDER BY sort_order ASC, created_at DESC");
$stmt->execute([$gallery_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related galleries
$stmt = $conn->prepare("SELECT * FROM galleries WHERE id != ? AND is_active = 1 ORDER BY RAND() LIMIT 3");
$stmt->execute([$gallery_id]);
$related_galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$page_title = htmlspecialchars($gallery['title']) . " - Elite Sports Management";
require_once 'header.php';
?>

    <!-- Gallery Header -->
    <section class="bg-blue-600 text-white py-12">
        <div class="container mx-auto px-4">
            <nav class="flex items-center space-x-2 text-sm mb-4">
                <a href="index.php" class="hover:text-blue-200">Home</a>
                <span>></span>
                <a href="gallery.php" class="hover:text-blue-200">Gallery</a>
                <span>></span>
                <span class="text-blue-200"><?php echo htmlspecialchars($gallery['title']); ?></span>
            </nav>
            
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($gallery['title']); ?></h1>
            <p class="text-xl text-blue-100 mb-4"><?php echo htmlspecialchars($gallery['description']); ?></p>
            <div class="flex items-center space-x-4 text-blue-200">
                <span class="bg-blue-500 px-3 py-1 rounded-full text-sm">
                    <i class="fas fa-images mr-1"></i>
                    <?php echo count($images); ?> photos
                </span>
                <span class="bg-blue-500 px-3 py-1 rounded-full text-sm capitalize">
                    <?php echo $gallery['category']; ?>
                </span>
            </div>
        </div>
    </section>

    <!-- Gallery Images -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <?php if (empty($images)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-images text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-600 mb-2">No Photos Yet</h3>
                    <p class="text-gray-500">Photos will be added to this gallery soon!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="gallery-grid">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="image-card bg-white rounded-lg shadow-md overflow-hidden cursor-pointer" 
                             onclick="openLightbox(<?php echo $index; ?>)">
                            <img src="uploads/galleries/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                 class="w-full h-64 object-cover">
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($image['title']); ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Related Galleries -->
    <?php if (!empty($related_galleries)): ?>
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">More Galleries</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($related_galleries as $related): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                        <?php if ($related['cover_image']): ?>
                            <img src="uploads/galleries/<?php echo htmlspecialchars($related['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-images text-gray-400 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($related['description']); ?></p>
                            <a href="gallery-view.php?id=<?php echo $related['id']; ?>" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                View Gallery
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <button class="lightbox-nav lightbox-prev" onclick="changeImage(-1)">&#10094;</button>
        <button class="lightbox-nav lightbox-next" onclick="changeImage(1)">&#10095;</button>
        <img id="lightbox-image" src="" alt="">
        <!-- <div id="lightbox-caption" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-4 text-center"></div> -->
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <?php include 'footer-content.php'; ?>
        </div>
    </footer>

    <script>
        let currentImageIndex = 0;
        const images = <?php echo json_encode($images); ?>;

        function openLightbox(index) {
            currentImageIndex = index;
            updateLightbox();
            document.getElementById('lightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex >= images.length) currentImageIndex = 0;
            if (currentImageIndex < 0) currentImageIndex = images.length - 1;
            updateLightbox();
        }

        function updateLightbox() {
            const image = images[currentImageIndex];
            document.getElementById('lightbox-image').src = 'uploads/galleries/' + image.image_path;
            document.getElementById('lightbox-caption').textContent = image.title;
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').style.display === 'flex') {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') changeImage(-1);
                if (e.key === 'ArrowRight') changeImage(1);
            }
        });

        // Close lightbox when clicking outside image
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    </script>
</body>
</html>