<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle blog post operations
if (isset($_POST['add_post'])) {
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $excerpt = sanitize($_POST['excerpt']);
    $content = $_POST['content'];
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    $tags = json_encode(explode(',', sanitize($_POST['tags'])));
    
    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadImage($_FILES['featured_image'], 'uploads/blog/');
        if ($upload['success']) {
            $featured_image = $upload['filename'];
        }
    }
    
    $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
    
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $category, $tags, $_SESSION['admin_id'], $status, $published_at])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Blog post created successfully!</div>';
    }
}

if (isset($_GET['delete_post'])) {
    $id = intval($_GET['delete_post']);
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Blog post deleted successfully!</div>';
    }
}

if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $stmt = $conn->prepare("UPDATE blog_posts SET status = IF(status = 'published', 'draft', 'published'), published_at = IF(status = 'draft', NOW(), published_at) WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Post status updated!</div>';
    }
}

// Get all blog posts
$stmt = $conn->prepare("SELECT bp.*, au.username as author_name FROM blog_posts bp LEFT JOIN admin_users au ON bp.author_id = au.id ORDER BY bp.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $conn->query("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Blog Management</h1>
                <p class="text-gray-600">Create and manage your sports news and updates</p>
            </div>
            <!-- Add Blog Post Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create New Blog Post</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Post Title</label>
                            <input type="text" id="title" name="title" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select id="category" name="category" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description of the post"></textarea>
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                            <textarea id="content" name="content" rows="12" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Write your blog post content here..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                                <input type="file" id="featured_image" name="featured_image" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                                <input type="text" id="tags" name="tags" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="sports, news, athletes (comma separated)">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="add_post" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Blog Posts List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Blog Posts</h3>
                    
                    <?php if (empty($posts)): ?>
                        <p class="text-gray-500 text-center py-8">No blog posts found. Create your first post above.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($post['title']); ?></div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($post['excerpt']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo ucfirst(str_replace('-', ' ', $post['category'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                    <?php echo ucfirst($post['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($post['author_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="?toggle_status=<?php echo $post['id']; ?>" 
                                                   class="text-<?php echo $post['status'] === 'published' ? 'yellow' : 'green'; ?>-600 hover:text-<?php echo $post['status'] === 'published' ? 'yellow' : 'green'; ?>-900">
                                                    <?php echo $post['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                                </a>
                                                <a href="edit-blog-post.php?id=<?php echo $post['id']; ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                <a href="?delete_post=<?php echo $post['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this post?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>