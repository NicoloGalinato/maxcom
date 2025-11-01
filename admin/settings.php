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
    
    $stmt = $conn->prepare("
        INSERT INTO general_content (section_name, content_title, extra_data) 
        VALUES ('site_settings', 'Site Settings', ?) 
        ON DUPLICATE KEY UPDATE extra_data = ?
    ");
    
    if ($stmt->execute([$settings_json, $settings_json])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Settings updated successfully!</div>';
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error updating settings.</div>';
    }
}

// Get current settings
$stmt = $conn->prepare("SELECT extra_data FROM general_content WHERE section_name = 'site_settings'");
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

if ($settings_data && $settings_data['extra_data']) {
    $saved_settings = json_decode($settings_data['extra_data'], true);
    if ($saved_settings) {
        $settings = array_merge($settings, $saved_settings);
    }
}
?>
    <?php include 'header.php'; ?>

    <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Site Settings</h1>
                <p class="text-gray-600">Manage your website configuration and contact information</p>
            </div>

            <?php echo $message; ?>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" class="space-y-6">
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
</body>
</html>