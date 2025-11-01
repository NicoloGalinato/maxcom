<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $target_dir = "uploads/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "File is too large."];
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        return ["success" => false, "message" => "Only JPG, JPEG, PNG & GIF files are allowed."];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "message" => "Error uploading file."];
    }
}

function getHeroContent($conn) {
    $stmt = $conn->prepare("SELECT * FROM hero_content WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getServices($conn) {
    $stmt = $conn->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTestimonials($conn) {
    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGeneralContent($conn, $section_name) {
    $stmt = $conn->prepare("SELECT * FROM general_content WHERE section_name = ?");
    $stmt->execute([$section_name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get gallery by ID
 */
function getGallery($conn, $gallery_id) {
    $stmt = $conn->prepare("SELECT * FROM galleries WHERE id = ? AND is_active = 1");
    $stmt->execute([$gallery_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all active galleries
 */
function getAllGalleries($conn) {
    $stmt = $conn->prepare("SELECT * FROM galleries WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get images for a gallery
 */
function getGalleryImages($conn, $gallery_id) {
    $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? AND is_active = 1 ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute([$gallery_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get blog post by slug
 */
function getBlogPost($conn, $slug) {
    $stmt = $conn->prepare("
        SELECT bp.*, au.username as author_name, au.full_name 
        FROM blog_posts bp 
        LEFT JOIN admin_users au ON bp.author_id = au.id 
        WHERE bp.slug = ? AND bp.status = 'published' AND bp.is_active = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all published blog posts
 */
function getPublishedBlogPosts($conn, $limit = null, $offset = 0) {
    $sql = "
        SELECT bp.*, au.username as author_name, au.full_name 
        FROM blog_posts bp 
        LEFT JOIN admin_users au ON bp.author_id = au.id 
        WHERE bp.status = 'published' AND bp.is_active = 1 
        ORDER BY bp.published_at DESC
    ";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $conn->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get blog categories
 */
function getBlogCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Format file size
 */
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}

/**
 * Create slug from text
 */
if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
}

/**
 * Get gallery image counts
 */
function getGalleryImageCounts($conn, $galleries) {
    $counts = [];
    foreach ($galleries as $gallery) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM gallery_images WHERE gallery_id = ? AND is_active = 1");
        $stmt->execute([$gallery['id']]);
        $counts[$gallery['id']] = $stmt->fetchColumn();
    }
    return $counts;
}

/**
 * Get total blog posts count
 */
function getTotalBlogPosts($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND is_active = 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Get recent blog posts
 */
function getRecentBlogPosts($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT id, title, slug, published_at 
        FROM blog_posts 
        WHERE status = 'published' AND is_active = 1 
        ORDER BY published_at DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get popular blog posts
 */
function getPopularBlogPosts($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT title, slug, views 
        FROM blog_posts 
        WHERE status = 'published' AND is_active = 1 
        ORDER BY views DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get related blog posts
 */
function getRelatedBlogPosts($conn, $current_post_id, $category, $limit = 3) {
    $stmt = $conn->prepare("
        SELECT id, title, slug, excerpt, featured_image, published_at 
        FROM blog_posts 
        WHERE category = ? AND id != ? AND status = 'published' AND is_active = 1 
        ORDER BY published_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$category, $current_post_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update blog post views
 */
function updateBlogPostViews($conn, $post_id) {
    $stmt = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    return $stmt->execute([$post_id]);
}

/**
 * Get contact submissions
 */
function getContactSubmissions($conn, $limit = null) {
    $sql = "SELECT * FROM contact_submissions ORDER BY submitted_at DESC";
    if ($limit) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $conn->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get unread contact count
 */
function getUnreadContactCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0");
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Calculate folder size recursively
 */
if (!function_exists('folderSize')) {
    function folderSize($dir) {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : folderSize($each);
        }
        return $size;
    }
}

/**
 * Format bytes to human readable
 */
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

/**
 * Get site settings
 */
// Add this to your includes/functions.php file
function getSiteSettings($conn) {
    $stmt = $conn->prepare("SELECT extra_data FROM general_content WHERE section_name = 'site_settings'");
    $stmt->execute();
    $settings_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $default_settings = [
        'site_name' => 'Elite Sports Management',
        'site_email' => 'info@elitesportsmanagement.com',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_address' => '123 Sports Avenue, Athletic City, AC 12345',
        'facebook_url' => '',
        'twitter_url' => '',
        'instagram_url' => '',
        'linkedin_url' => ''
    ];
    
    if ($settings_data && $settings_data['extra_data']) {
        $saved_settings = json_decode($settings_data['extra_data'], true);
        if ($saved_settings) {
            return array_merge($default_settings, $saved_settings);
        }
    }
    
    return $default_settings;
}
?>