<?php
// Lấy đường dẫn hiện tại để xác định trang active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Tính toán đường dẫn tương đối đến thư mục gốc
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);

// Tìm vị trí của 'admin' trong đường dẫn
$admin_index = array_search('admin', $path_parts);

if ($admin_index !== false) {
    // Tính số cấp cần lùi lại để đến thư mục gốc
    $depth = count($path_parts) - $admin_index - 1;
    $root_path = str_repeat('../', $depth);
} else {
    // Nếu không tìm thấy 'admin', sử dụng đường dẫn mặc định
    $root_path = '';
}

// Xác định trang active - CHỈ CÓ 1 TRANG ACTIVE TẠI MỘT THỜI ĐIỂM
$active_page = '';

// Kiểm tra theo thứ tự ưu tiên - CHÍNH XÁC HƠN
if (strpos($current_path, '/admin/notifications/') !== false) {
    $active_page = 'notifications';
} elseif (strpos($current_path, '/admin/iot/locations/') !== false) {
    $active_page = 'iot_locations';
} elseif (strpos($current_path, '/admin/iot/sensors/') !== false) {
    $active_page = 'iot_sensors';
} elseif (strpos($current_path, '/admin/iot/') !== false && $current_page === 'index.php') {
    $active_page = 'iot_dashboard';
} elseif (strpos($current_path, '/admin/categories/') !== false) {
    $active_page = 'categories';
} elseif (strpos($current_path, '/admin/products/') !== false) {
    $active_page = 'products';
} elseif ($current_dir === '' && $current_page === 'index.php') {
    $active_page = 'dashboard';
}

// Xác định menu Ecommerce có active không
$is_ecommerce_active = ($active_page === 'products' || $active_page === 'categories');

// Debug: In ra để kiểm tra
// echo "<!-- Debug: Current path: $current_path, Active page: $active_page -->";
?>
<!-- Left Sidebar Start -->
<div class="startbar d-print-none">
    <!-- Brand -->
    <div class="brand">
        <a href="<?php echo $root_path; ?>index.php" class="logo">
            <span><img src="<?php echo $root_path; ?>assets/images/logo-sm.png" alt="logo-small" class="logo-sm"></span>
            <span class=""><img src="<?php echo $root_path; ?>assets/images/logo-light.png" alt="logo-large" class="logo-lg logo-light"><img src="<?php echo $root_path; ?>assets/images/logo-dark.png" alt="logo-large" class="logo-lg logo-dark"></span>
        </a>
    </div>
    <!-- Sidebar Menu -->
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label mt-2"><span>Navigation</span></li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir === '' && $current_page === 'index.php') ? 'active' : ''; ?>" href="<?php echo $root_path; ?>index.php">
                            <i class="iconoir-report-columns"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_ecommerce_active ? 'active' : ''; ?>" href="#sidebarEcommerce" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo $is_ecommerce_active ? 'true' : 'false'; ?>" aria-controls="sidebarEcommerce">
                            <i class="iconoir-folder me-2"></i><span>Ecommerce</span>
                        </a>
                        <div class="collapse <?php echo $is_ecommerce_active ? 'show' : ''; ?>" id="sidebarEcommerce">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_page === 'products' ? 'active' : ''; ?>" href="<?php echo $root_path; ?>admin/products/index.php">
                                        <i class="iconoir-shopping-bag me-2"></i>Products
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_page === 'categories' ? 'active' : ''; ?>" href="<?php echo $root_path; ?>admin/categories/index.php">
                                        <i class="iconoir-folder me-2"></i>Categories
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="menu-label mt-2"><span>IoT System</span></li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'iot_dashboard' ? 'active' : ''; ?>" href="<?php echo $root_path; ?>admin/iot/index.php">
                            <i class="iconoir-dashboard-dots"></i><span>IoT Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'iot_sensors' ? 'active' : ''; ?>" href="<?php echo $root_path; ?>admin/iot/sensors/">
                            <i class="iconoir-tv me-2"></i><span>Quản lý cảm biến</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'iot_locations' ? 'active' : ''; ?>" href="<?php echo $root_path; ?>admin/iot/locations/">
                            <i class="iconoir-map-pin"></i><span>Quản lý vị trí</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->
