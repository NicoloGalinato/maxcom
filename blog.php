<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get current page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Get total posts count
$total_posts = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND is_active = 1")->fetchColumn();
$total_pages = ceil($total_posts / $limit);

// Get published blog posts
$stmt = $conn->prepare("
    SELECT bp.*, au.username as author_name, au.full_name 
    FROM blog_posts bp 
    LEFT JOIN admin_users au ON bp.author_id = au.id 
    WHERE bp.status = 'published' AND bp.is_active = 1 
    ORDER BY bp.published_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $conn->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get recent posts
$recent_posts = $conn->query("
    SELECT id, title, slug, published_at 
    FROM blog_posts 
    WHERE status = 'published' AND is_active = 1 
    ORDER BY published_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Sports News & Updates - Elite Sports Management";
require_once 'header.php';
?>

    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Sports News & Updates</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Stay updated with the latest in sports management, athlete news, and industry insights</p>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Content -->
                <div class="lg:w-2/3">
                    <?php if (empty($posts)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-newspaper text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-600 mb-2">No Blog Posts Yet</h3>
                            <p class="text-gray-500">Check back soon for exciting sports news and updates!</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <?php foreach ($posts as $post): ?>
                                <article class="blog-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                                    <?php if ($post['featured_image']): ?>
                                        <img src="uploads/blog/<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                             class="w-full h-48 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-newspaper text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-6">
                                        <div class="flex items-center text-sm text-gray-500 mb-3">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs capitalize">
                                                <?php echo str_replace('-', ' ', $post['category']); ?>
                                            </span>
                                            <span class="mx-2">•</span>
                                            <span><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                                        </div>
                                        
                                        <h2 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($post['title']); ?></h2>
                                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500">
                                                By <?php echo htmlspecialchars($post['full_name'] ?: $post['author_name']); ?>
                                            </span>
                                            <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" 
                                               class="read-more text-blue-600 hover:text-blue-700 font-semibold">
                                                Read More
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="flex justify-center mt-12">
                            <nav class="flex items-center space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="blog.php?page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="blog.php?page=<?php echo $i; ?>" 
                                       class="px-3 py-2 rounded <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="blog.php?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3">
                    <!-- Search -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Search Blog</h3>
                        <form class="flex">
                            <input type="text" placeholder="Search articles..." class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Categories</h3>
                        <ul class="space-y-2">
                            <?php foreach ($categories as $category): ?>
                                <li>
                                    <a href="blog.php?category=<?php echo $category['slug']; ?>" 
                                       class="flex justify-between text-gray-600 hover:text-blue-600">
                                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            <?php
                                            $count_stmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts WHERE category = ? AND status = 'published' AND is_active = 1");
                                            $count_stmt->execute([$category['slug']]);
                                            echo $count_stmt->fetchColumn();
                                            ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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

    <!-- CTA Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Stay Updated with Sports News</h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">Subscribe to our newsletter for the latest updates in sports management and athlete news</p>
            <form class="max-w-md mx-auto flex">
                <input type="email" placeholder="Enter your email" class="flex-1 border border-gray-300 rounded-l-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700 transition duration-300">
                    Subscribe
                </button>
            </form>
        </div>
    </section>

 <?php include 'footer.php'; ?>