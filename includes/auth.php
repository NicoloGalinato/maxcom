<?php
function checkAuth() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        header("Location: login.php");
        exit();
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function hasPermission($permission) {
    // Add permission logic here if needed
    return true;
}
?>