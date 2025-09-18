<?php
/**
 * File kiểm tra đăng nhập cho các trang admin
 * Include file này vào đầu các trang admin để bảo vệ
 */

session_start();

// Cấu hình
$login_required = true; // Có thể override trong từng trang
$redirect_on_fail = true; // Có thể override trong từng trang

// Override từ các trang include
if (isset($require_login)) {
    $login_required = $require_login;
}
if (isset($redirect_on_login_fail)) {
    $redirect_on_fail = $redirect_on_login_fail;
}

// Kiểm tra đăng nhập
function checkAdminLogin($redirect = true) {
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    
    if (!$isLoggedIn && $redirect) {
        // Lưu URL hiện tại để redirect sau khi đăng nhập
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        header('Location: ../login.php?error=' . urlencode('Vui lòng đăng nhập để tiếp tục'));
        exit();
    }
    
    return $isLoggedIn;
}

// Lấy thông tin admin hiện tại
function getCurrentAdmin() {
    if (!checkAdminLogin(false)) {
        return null;
    }
    
    return [
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'login_time' => $_SESSION['admin_login_time'] ?? time(),
        'session_duration' => time() - ($_SESSION['admin_login_time'] ?? time())
    ];
}

// Kiểm tra và redirect nếu cần
if ($login_required) {
    checkAdminLogin($redirect_on_fail);
}

// Helper function để logout
function adminLogout() {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    header('Location: ../login.php?success=' . urlencode('Đăng xuất thành công!'));
    exit();
}
?>
