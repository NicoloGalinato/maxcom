<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$conn = $database->getConnection();

$message = '';
$hero_content = getHeroContent($conn);
$about_content = getGeneralContent($conn, 'about_content');
$contact_info = getGeneralContent($conn, 'contact_info');

// Handle hero content update
if (isset($_POST['update_hero'])) {
    $title = sanitize($_POST['title']);
    $subtitle = sanitize($_POST['subtitle']);
    $button1_text = sanitize($_POST['button1_text']);
    $button2_text = sanitize($_POST['button2_text']);
    
    $background_image = $hero_content['background_image'];
    if (!empty($_FILES['background_image']['name'])) {
        $upload = uploadImage($_FILES['background_image']);
        if ($upload['success']) {
            $background_image = $upload['filename'];
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error: ' . $upload['message'] . '</div>';
        }
    }
    
    $stmt = $conn->prepare("UPDATE hero_content SET title = ?, subtitle = ?, button1_text = ?, button2_text = ?, background_image = ? WHERE id = ?");
    if ($stmt->execute([$title, $subtitle, $button1_text, $button2_text, $background_image, $hero_content['id']])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Hero content updated successfully!</div>';
        $hero_content = getHeroContent($conn);
    }
}

// Handle about content update
if (isset($_POST['update_about'])) {
    $about_title = sanitize($_POST['about_title']);
    $about_text = sanitize($_POST['about_text']);
    
    $stmt = $conn->prepare("UPDATE general_content SET content_title = ?, content_text = ? WHERE section_name = 'about_content'");
    if ($stmt->execute([$about_title, $about_text])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">About content updated successfully!</div>';
        $about_content = getGeneralContent($conn, 'about_content');
    }
}

// Handle contact info update
if (isset($_POST['update_contact'])) {
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    
    $contact_data = json_encode([
        'address' => $address,
        'phone' => $phone,
        'email' => $email
    ]);
    
    $stmt = $conn->prepare("UPDATE general_content SET extra_data = ? WHERE section_name = 'contact_info'");
    if ($stmt->execute([$contact_data])) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Contact information updated successfully!</div>';
        $contact_info = getGeneralContent($conn, 'contact_info');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content - Sports Management CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Content</h1>
                <p class="text-gray-600">Update your landing page content</p>
            </div>
            <!-- Hero Section Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Hero Section</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($hero_content['title']); ?>" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtitle</label>
                            <textarea id="subtitle" name="subtitle" rows="3" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($hero_content['subtitle']); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="button1_text" class="block text-sm font-medium text-gray-700">Primary Button Text</label>
                                <input type="text" id="button1_text" name="button1_text" value="<?php echo htmlspecialchars($hero_content['button1_text']); ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="button2_text" class="block text-sm font-medium text-gray-700">Secondary Button Text</label>
                                <input type="text" id="button2_text" name="button2_text" value="<?php echo htmlspecialchars($hero_content['button2_text']); ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="background_image" class="block text-sm font-medium text-gray-700">Background Image</label>
                            <input type="file" id="background_image" name="background_image" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <?php if ($hero_content['background_image']): ?>
                                <p class="mt-2 text-sm text-gray-500">Current: <?php echo $hero_content['background_image']; ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_hero" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Hero Section
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- About Section Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">About Section</h3>
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="about_title" class="block text-sm font-medium text-gray-700">About Title</label>
                            <input type="text" id="about_title" name="about_title" value="<?php echo htmlspecialchars($about_content['content_title']); ?>" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="about_text" class="block text-sm font-medium text-gray-700">About Text</label>
                            <textarea id="about_text" name="about_text" rows="6" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($about_content['content_text']); ?></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_about" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update About Section
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contact Information Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Contact Information</h3>
                    <form method="POST" class="space-y-6">
                        <?php
                        $contact_data = json_decode($contact_info['extra_data'], true);
                        ?>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($contact_data['address']); ?>" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($contact_data['phone']); ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($contact_data['email']); ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_contact" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Contact Info
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Hero Images Management -->
            <div class="bg-white shadow rounded-lg mt-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Manage Hero Images</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="hero_images" class="block text-sm font-medium text-gray-700">Upload New Hero Images</label>
                            <input type="file" id="hero_images" name="hero_images[]" multiple 
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">You can select multiple images. Recommended size: 1920x1080px</p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="upload_hero_images" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Upload Images
                            </button>
                        </div>
                    </form>
                    
                    <!-- Display existing hero images -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-4">Current Hero Images</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- PHP code to display existing hero images -->
                            <?php
                            $hero_images = glob('../uploads/hero/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                            foreach ($hero_images as $image): 
                                $filename = basename($image);
                            ?>
                                <div class="relative">
                                    <img src="../uploads/hero/<?php echo $filename; ?>" alt="Hero Image" class="w-full h-24 object-cover rounded">
                                    <button onclick="setAsHeroBackground('<?php echo $filename; ?>')" 
                                            class="absolute top-1 right-1 bg-blue-600 text-white p-1 rounded text-xs">
                                        Set Active
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>