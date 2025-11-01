<?php
// Replace the existing database connection code with this:
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get site settings
$site_settings = getSiteSettings($conn);

$slug = $_GET['slug'] ?? '';

// Get blog post
$stmt = $conn->prepare("
    SELECT bp.*, au.username as author_name, au.full_name 
    FROM blog_posts bp 
    LEFT JOIN admin_users au ON bp.author_id = au.id 
    WHERE bp.slug = ? AND bp.status = 'published' AND bp.is_active = 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: blog.php");
    exit();
}

// Update view count
$stmt = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
$stmt->execute([$post['id']]);

// Get related posts
$stmt = $conn->prepare("
    SELECT id, title, slug, excerpt, featured_image, published_at 
    FROM blog_posts 
    WHERE category = ? AND id != ? AND status = 'published' AND is_active = 1 
    ORDER BY published_at DESC 
    LIMIT 3
");
$stmt->execute([$post['category'], $post['id']]);
$related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent posts for sidebar
$recent_posts = $conn->query("
    SELECT id, title, slug, published_at 
    FROM blog_posts 
    WHERE status = 'published' AND is_active = 1 
    ORDER BY published_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
$page_title = htmlspecialchars($post['title']) .' | '. htmlspecialchars($site_settings['site_name']);
require_once 'header.php';
?>
    <!-- Blog Post Header -->
    <section class="bg-blue-600 text-white py-12">
        <div class="container mx-auto px-4">
            <nav class="flex items-center space-x-2 text-sm mb-4">
                <a href="index.php" class="hover:text-blue-200">Home</a>
                <span>></span>
                <a href="blog.php" class="hover:text-blue-200">Blog</a>
                <span>></span>
                <span class="text-blue-200"><?php echo htmlspecialchars($post['title']); ?></span>
            </nav>
            
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <div class="flex flex-wrap items-center gap-4 text-blue-100">
                <div class="flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    <span><?php echo htmlspecialchars($post['full_name'] ?: $post['author_name']); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar mr-2"></i>
                    <span><?php echo date('F j, Y', strtotime($post['published_at'])); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    <span><?php echo $post['views'] + 1; ?> views</span>
                </div>
                <span class="bg-blue-500 px-3 py-1 rounded-full text-sm capitalize">
                    <?php echo str_replace('-', ' ', $post['category']); ?>
                </span>
            </div>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Content -->
                <div class="lg:w-2/3">
                    <article class="prose max-w-none">
                        <?php if ($post['featured_image']): ?>
                            <img src="uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                 class="w-full h-96 object-cover rounded-lg mb-8">
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <?php echo $post['content']; ?>
                        </div>
                        
                        <!-- Tags -->
                        <?php if ($post['tags']): ?>
                            <?php $tags = json_decode($post['tags'], true); ?>
                            <?php if (is_array($tags) && !empty($tags)): ?>
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Tags:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                                                <?php echo htmlspecialchars(trim($tag)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Share Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Share this post:</h4>
                            <div class="flex space-x-4">
                                <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" 
                                   target="_blank" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                    <i class="fab fa-facebook-f mr-2"></i>Share
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&text=<?php echo urlencode($post['title']); ?>" 
                                   target="_blank" 
                                   class="bg-blue-400 text-white px-4 py-2 rounded-lg hover:bg-blue-500 transition duration-300">
                                    <i class="fab fa-twitter mr-2"></i>Tweet
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" 
                                   target="_blank" 
                                   class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition duration-300">
                                    <i class="fab fa-linkedin-in mr-2"></i>Share
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- Related Posts -->
                    <?php if (!empty($related_posts)): ?>
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Related Articles</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php foreach ($related_posts as $related): ?>
                                <article class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                                    <?php if ($related['featured_image']): ?>
                                        <img src="uploads/blog/<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                             class="w-full h-32 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-gray-400 text-2xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-4">
                                        <h4 class="font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($related['title']); ?></h4>
                                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars(substr($related['excerpt'], 0, 100)); ?>...</p>
                                        <a href="blog-post.php?slug=<?php echo $related['slug']; ?>" 
                                           class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                                            Read More →
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3">
                    <!-- About Author -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">About the Author</h3>
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($post['full_name'] ?: $post['author_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($post['full_name'] ?: $post['author_name']); ?></h4>
                                <p class="text-sm text-gray-600">Sports Management Expert</p>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm">Professional sports manager with years of experience in athlete representation and sports business development.</p>
                    </div>

                    <!-- Recent Posts -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Posts</h3>
                        <ul class="space-y-4">
                            <?php foreach ($recent_posts as $recent): ?>
                                <li class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                                    <div>
                                        <a href="blog-post.php?slug=<?php echo $recent['slug']; ?>" 
                                           class="text-gray-700 hover:text-blue-600 font-medium block">
                                            <?php echo htmlspecialchars($recent['title']); ?>
                                        </a>
                                        <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($recent['published_at'])); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

 <?php include 'footer.php'; ?>