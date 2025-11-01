<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle settings update
if (isset($_POST['update_settings'])) {
    $site_name = sanitize($_POST['site_name']);
    $site_email = sanitize($_POST['site_email']);
    $contact_phone = sanitize($_POST['contact_phone']);
    $contact_address = sanitize($_POST['contact_address']);
    $facebook_url = sanitize($_POST['facebook_url']);
    $twitter_url = sanitize($_POST['twitter_url']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $linkedin_url = sanitize($_POST['linkedin_url']);
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/logo/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['site_logo']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo-' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_path)) {
                $logo_path = 'uploads/logo/' . $filename;
                
                // Delete old logo if exists
                $stmt = $conn->prepare("SELECT image_path FROM general_content WHERE section_name = 'site_settings'");
                $stmt->execute();
                $old_logo = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($old_logo && $old_logo['image_path'] && file_exists('../' . $old_logo['image_path'])) {
                    unlink('../' . $old_logo['image_path']);
                }
            }
        }
    }
    
    // Update general settings
    $settings = [
        'site_name' => $site_name,
        'site_email' => $site_email,
        'contact_phone' => $contact_phone,
        'contact_address' => $contact_address,
        'facebook_url' => $facebook_url,
        'twitter_url' => $twitter_url,
        'instagram_url' => $instagram_url,
        'linkedin_url' => $linkedin_url
    ];
    
    $settings_json = json_encode($settings);
    
    if ($logo_path) {
        $stmt = $conn->prepare("
            INSERT INTO general_content (section_name, content_title, extra_data, image_path) 
            VALUES ('site_settings', 'Site Settings', ?, ?) 
            ON DUPLICATE KEY UPDATE extra_data = ?, image_path = ?
        ");
        $result = $stmt->execute([$settings_json, $logo_path, $settings_json, $logo_path]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO general_content (section_name, content_title, extra_data) 
            VALUES ('site_settings', 'Site Settings', ?) 
            ON DUPLICATE KEY UPDATE extra_data = ?
        ");
        $result = $stmt->execute([$settings_json, $settings_json]);
    }
    
    if ($result) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Settings updated successfully!</div>';
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error updating settings.</div>';
    }
}

// Get current settings
$stmt = $conn->prepare("SELECT extra_data, image_path FROM general_content WHERE section_name = 'site_settings'");
$stmt->execute();
$settings_data = $stmt->fetch(PDO::FETCH_ASSOC);

$settings = [
    'site_name' => 'Elite Sports Management',
    'site_email' => 'info@elitesportsmanagement.com',
    'contact_phone' => '+1 (555) 123-4567',
    'contact_address' => '123 Sports Avenue, Athletic City, AC 12345',
    'facebook_url' => '',
    'twitter_url' => '',
    'instagram_url' => '',
    'linkedin_url' => ''
];

$current_logo = null;

if ($settings_data) {
    if ($settings_data['extra_data']) {
        $saved_settings = json_decode($settings_data['extra_data'], true);
        if ($saved_settings) {
            $settings = array_merge($settings, $saved_settings);
        }
    }
    $current_logo = $settings_data['image_path'];
}
?>
    <?php include 'header.php'; ?>

    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Site Settings</h1>
                <p class="text-gray-600">Manage your website configuration and contact information</p>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <!-- Logo Upload -->
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Site Logo</h3>
                            <div class="flex items-center space-x-6">
                                <div class="flex-shrink-0">
                                    <?php if ($current_logo): ?>
                                        <img id="logo-preview" src="../<?php echo htmlspecialchars($current_logo); ?>" 
                                             alt="Current Logo" class="h-16 w-auto">
                                    <?php else: ?>
                                        <div id="logo-preview" class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <label for="site_logo" class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                    <input type="file" id="site_logo" name="site_logo" 
                                           accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                           onchange="previewLogo(this)">
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF, or SVG. Max 2MB.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="site_email" class="block text-sm font-medium text-gray-700">Site Email</label>
                                    <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Rest of your existing form remains the same -->
                        <!-- Contact Information -->
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Contact Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="contact_address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea id="contact_address" name="contact_address" rows="3" 
                                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Social Media Links</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="facebook_url" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook
                                    </label>
                                    <input type="url" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url']); ?>" 
                                           placeholder="https://facebook.com/yourpage" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="twitter_url" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter
                                    </label>
                                    <input type="url" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url']); ?>" 
                                           placeholder="https://twitter.com/yourprofile" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="instagram_url" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram
                                    </label>
                                    <input type="url" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url']); ?>" 
                                           placeholder="https://instagram.com/yourprofile" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
                                        <i class="fab fa-linkedin text-blue-700 mr-2"></i>LinkedIn
                                    </label>
                                    <input type="url" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>" 
                                           placeholder="https://linkedin.com/company/yourcompany" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- System Information -->
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">System Information</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">PHP Version:</span>
                                        <span class="text-gray-600"><?php echo phpversion(); ?></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Database:</span>
                                        <span class="text-gray-600">MySQL</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Server Software:</span>
                                        <span class="text-gray-600"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">CMS Version:</span>
                                        <span class="text-gray-600">1.0.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_settings" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
    function previewLogo(input) {
        const preview = document.getElementById('logo-preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    // Replace div with img
                    const img = document.createElement('img');
                    img.id = 'logo-preview';
                    img.src = e.target.result;
                    img.alt = 'Logo Preview';
                    img.className = 'h-16 w-auto';
                    preview.parentNode.replaceChild(img, preview);
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>