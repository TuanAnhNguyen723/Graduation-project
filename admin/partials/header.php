<?php
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
?>
<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid" style="display: flex !important; align-items: center !important; justify-content: space-between !important; flex-direction: row !important;">
        <!-- Page Title -->
        <div class="page-title" style="flex-shrink: 0; min-width: 200px; margin-left: 1rem;">
            <h5 class="mb-0 fw-semibold text-dark">Admin Dashboard</h5>
            <small class="text-muted">Quản lý hệ thống E-commerce & IoT</small>
        </div>

        <!-- Search Bar -->
        <div class="search-container" style="flex: 1; max-width: 400px; margin: 0 1rem; flex-shrink: 0;">
            <form class="search-form">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="iconoir-search text-muted"></i>
                    </span>
                    <input type="search" class="form-control border-start-0 ps-0" 
                           placeholder="Tìm kiếm sản phẩm, danh mục, cảm biến...">
                    <button class="btn btn-primary" type="submit">
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Actions -->
        <div class="navbar-actions" style="display: flex; align-items: center; gap: 0.5rem; min-width: 300px; justify-content: flex-end; flex-shrink: 0;">
            <!-- Language Selector -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                        type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo $root_path; ?>assets/images/flags/us_flag.jpg" 
                         alt="VN" class="rounded-circle me-2" width="20" height="20">
                    <span>VN</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">
                        <img src="<?php echo $root_path; ?>assets/images/flags/us_flag.jpg" 
                             alt="VN" width="16" height="16" class="me-2">Tiếng Việt
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <img src="<?php echo $root_path; ?>assets/images/flags/us_flag.jpg" 
                             alt="EN" width="16" height="16" class="me-2">English
                    </a></li>
                </ul>
            </div>

            <!-- Theme Toggle -->
            <button class="btn btn-outline-secondary me-2" id="light-dark-mode" 
                    title="Chuyển đổi giao diện">
                <i class="iconoir-half-moon dark-mode"></i>
                <i class="iconoir-sun-light light-mode d-none"></i>
            </button>

            <!-- Notifications -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-secondary position-relative" 
                        type="button" data-bs-toggle="dropdown">
                    <i class="iconoir-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Thông báo</h6>
                        <a href="#" class="text-decoration-none text-muted small">Đánh dấu đã đọc</a>
                    </div>
                    <div class="dropdown-body p-0" style="max-height: 300px; overflow-y: auto;">
                        <!-- Notification Item 1 -->
                        <div class="dropdown-item p-3 border-bottom">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="iconoir-shopping-bag"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-semibold">Sản phẩm mới được thêm</h6>
                                    <p class="text-muted mb-1 small">Đã thêm sản phẩm mới vào hệ thống.</p>
                                    <small class="text-muted">2 phút trước</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 2 -->
                        <div class="dropdown-item p-3 border-bottom">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="iconoir-folder"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-semibold">Danh mục đã được cập nhật</h6>
                                    <p class="text-muted mb-1 small">Danh mục sản phẩm đã được cập nhật thành công.</p>
                                    <small class="text-muted">10 phút trước</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 3 -->
                        <div class="dropdown-item p-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="iconoir-thermometer"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 fw-semibold">Cảm biến nhiệt độ báo động</h6>
                                    <p class="text-muted mb-1 small">Nhiệt độ vượt quá ngưỡng cho phép.</p>
                                    <small class="text-muted">15 phút trước</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-footer text-center p-3 border-top">
                        <a href="#" class="text-decoration-none text-primary fw-semibold">Xem tất cả thông báo</a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                        type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo $root_path; ?>assets/images/users/avatar-1.jpg" 
                         alt="Admin" class="rounded-circle me-2" width="32" height="32">
                    <div class="text-start">
                        <div class="fw-semibold text-dark">Admin</div>
                        <small class="text-muted">Quản trị viên</small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Xin chào, Admin!</h6>
                        <small class="text-muted">Quản trị viên hệ thống</small>
                    </li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-user me-2"></i>Hồ sơ cá nhân
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-settings me-2"></i>Cài đặt
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-lock me-2"></i>Khóa màn hình
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#">
                        <i class="iconoir-log-out me-2"></i>Đăng xuất
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="btn btn-link d-lg-none p-0" id="toggle-sidebar" style="flex-shrink: 0;">
            <i class="iconoir-menu fs-4"></i>
        </button>
    </div>
</div>
<!-- end Topbar -->
