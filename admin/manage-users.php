<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if user has permission (only superadmin can manage users)
$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($current_user['role'] !== 'superadmin') {
    $_SESSION['error'] = "You don't have permission to manage users.";
    redirect('dashboard.php');
}

$message = '';

// Handle add user
if (isset($_POST['add_user'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $role = sanitize($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Username already exists!</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $password, $email, $full_name, $role])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">User created successfully!</div>';
        }
    }
}

// Handle delete user
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['admin_id']) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">You cannot delete your own account!</div>';
    } else {
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">User deleted successfully!</div>';
        }
    }
}

// Handle toggle active status
if (isset($_GET['toggle_active'])) {
    $user_id = intval($_GET['toggle_active']);
    
    // Prevent deactivating yourself
    if ($user_id == $_SESSION['admin_id']) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">You cannot deactivate your own account!</div>';
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET is_active = NOT is_active WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">User status updated!</div>';
        }
    }
}

// Get all users
$stmt = $conn->prepare("SELECT * FROM admin_users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Sports Management CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <main class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php echo $message; ?>
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
                <p class="text-gray-600">Create and manage admin users for the CMS</p>
            </div>

            <!-- Add User Form -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New User</h3>
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" id="username" name="username" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="full_name" name="full_name" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                <select id="role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="admin">Admin</option>
                                    <option value="editor">Editor</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Minimum 6 characters</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="add_user" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">System Users</h3>
                    
                    <?php if (empty($users)): ?>
                        <p class="text-gray-500 text-center py-8">No users found.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></div>
                                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['username']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $user['role'] === 'superadmin' ? 'bg-purple-100 text-purple-800' : 
                                                          ($user['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                    <a href="?toggle_active=<?php echo $user['id']; ?>" 
                                                       class="text-<?php echo $user['is_active'] ? 'yellow' : 'green'; ?>-600 hover:text-<?php echo $user['is_active'] ? 'yellow' : 'green'; ?>-900">
                                                        <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </a>
                                                    <a href="?delete_user=<?php echo $user['id']; ?>" 
                                                       class="text-red-600 hover:text-red-900" 
                                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                                        Delete
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Current User</span>
                                                <?php endif; ?>
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