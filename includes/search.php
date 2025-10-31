<?php
require_once 'config/database.php';

class Search {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Search across multiple content types
     */
    public function globalSearch($query, $limit = 10) {
        $search_term = '%' . $query . '%';
        $results = [];
        
        // Search blog posts
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                title,
                excerpt as description,
                'blog' as type,
                slug,
                published_at as date,
                featured_image as image
            FROM blog_posts 
            WHERE (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) 
            AND status = 'published' 
            AND is_active = 1
            ORDER BY published_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        $blog_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($blog_results as $result) {
            $result['url'] = "blog-post.php?slug=" . $result['slug'];
            $results[] = $result;
        }
        
        // Search services
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                title,
                description,
                'service' as type,
                '' as slug,
                created_at as date,
                '' as image
            FROM services 
            WHERE (title LIKE ? OR description LIKE ?) 
            AND is_active = 1
            ORDER BY sort_order ASC 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $search_term, $limit]);
        $service_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($service_results as $result) {
            $result['url'] = "index.php#services";
            $results[] = $result;
        }
        
        // Search galleries
        $stmt = $this->conn->prepare("
            SELECT 
                id,
                title,
                description,
                'gallery' as type,
                '' as slug,
                created_at as date,
                cover_image as image
            FROM galleries 
            WHERE (title LIKE ? OR description LIKE ?) 
            AND is_active = 1
            ORDER BY sort_order ASC 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $search_term, $limit]);
        $gallery_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($gallery_results as $result) {
            $result['url'] = "gallery-view.php?id=" . $result['id'];
            $results[] = $result;
        }
        
        // Sort results by date (newest first)
        usort($results, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Search only blog posts
     */
    public function searchBlog($query, $category = null, $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $search_term = '%' . $query . '%';
        
        $sql = "
            SELECT 
                bp.*,
                au.username as author_name,
                au.full_name
            FROM blog_posts bp 
            LEFT JOIN admin_users au ON bp.author_id = au.id 
            WHERE (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?) 
            AND bp.status = 'published' 
            AND bp.is_active = 1
        ";
        
        $params = [$search_term, $search_term, $search_term];
        
        if ($category) {
            $sql .= " AND bp.category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY bp.published_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $limit = 5) {
        $search_term = '%' . $query . '%';
        $suggestions = [];
        
        // Blog post titles
        $stmt = $this->conn->prepare("
            SELECT title, 'blog' as type 
            FROM blog_posts 
            WHERE title LIKE ? AND status = 'published' AND is_active = 1 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $limit]);
        $blog_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Service titles
        $stmt = $this->conn->prepare("
            SELECT title, 'service' as type 
            FROM services 
            WHERE title LIKE ? AND is_active = 1 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $limit]);
        $service_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Gallery titles
        $stmt = $this->conn->prepare("
            SELECT title, 'gallery' as type 
            FROM galleries 
            WHERE title LIKE ? AND is_active = 1 
            LIMIT ?
        ");
        $stmt->execute([$search_term, $limit]);
        $gallery_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $suggestions = array_merge($blog_suggestions, $service_suggestions, $gallery_suggestions);
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Get popular search terms
     */
    public function getPopularSearches($limit = 10) {
        // In a real application, you'd store search queries in a table
        // For now, we'll return some default suggestions
        
        return [
            ['term' => 'sports management', 'count' => 15],
            ['term' => 'athlete representation', 'count' => 12],
            ['term' => 'event planning', 'count' => 10],
            ['term' => 'training programs', 'count' => 8],
            ['term' => 'career development', 'count' => 7],
        ];
    }
    
    /**
     * Log search query for analytics
     */
    public function logSearch($query, $results_count = 0) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $stmt = $this->conn->prepare("
            INSERT INTO search_logs (query, results_count, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([$query, $results_count, $ip_address, $user_agent]);
    }
}

// Create search logs table if not exists
function createSearchLogsTable($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS search_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            query VARCHAR(255) NOT NULL,
            results_count INT DEFAULT 0,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_query (query),
            INDEX idx_created_at (created_at)
        )
    ";
    
    $conn->exec($sql);
}
?>