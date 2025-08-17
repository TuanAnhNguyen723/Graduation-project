<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Category.php';

$category = new Category();

// Lấy danh sách danh mục cha
$parent_categories = $category->getParentCategories();
$parents = [];
while($row = $parent_categories->fetch()) {
    $parents[] = $row;
}

$message = '';
$message_type = '';

// Xử lý form submit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate dữ liệu
        if(empty($_POST['name'])) {
            throw new Exception('Tên danh mục không được để trống');
        }

        // Tạo slug từ tên danh mục
        $slug = $category->createSlug($_POST['name']);

        // Gán dữ liệu cho object
        $category->name = $_POST['name'];
        $category->slug = $slug;
        $category->description = $_POST['description'] ?? '';
        $category->parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $category->image = $_POST['image'] ?? '';
        $category->sort_order = (int)($_POST['sort_order'] ?? 0);
        $category->is_active = isset($_POST['is_active']) ? 1 : 0;

        // Tạo danh mục
        if($category->create()) {
            $message = 'Tạo danh mục thành công!';
            $message_type = 'success';
            
            // Reset form
            $_POST = array();
        } else {
            throw new Exception('Có lỗi xảy ra khi tạo danh mục');
        }
    } catch(Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Thêm danh mục mới | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Thêm danh mục mới" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/custom-override.css" rel="stylesheet" type="text/css" />
    
    <style>
        /* Sidebar Styles */
        .startbar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: #fff;
            border-right: 1px solid #e9ecef;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .startbar .brand {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .startbar .brand .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .startbar .brand .logo-sm {
            height: 32px;
            width: auto;
        }
        
        .startbar .brand .logo-lg {
            height: 24px;
            width: auto;
            margin-left: 0.5rem;
        }
        
        .startbar-menu {
            padding: 1rem 0;
        }
        
        .startbar-menu .nav-link {
            color: #6c757d;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .startbar-menu .nav-link:hover,
        .startbar-menu .nav-link.active {
            color: #495057;
            background-color: rgba(0, 123, 255, 0.1);
        }
        
        .startbar-menu .nav-link i {
            width: 20px;
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .startbar-menu .menu-label {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .startbar-menu .collapse .nav-link {
            padding-left: 3rem;
            font-size: 0.9rem;
        }
        
        /* Main Content Adjustment */
        .topbar {
            margin-left: 260px;
            transition: all 0.3s ease;
        }
        
        .page-wrapper-img,
        .page-wrapper {
            margin-left: 260px;
            transition: all 0.3s ease;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .startbar {
                transform: translateX(-100%);
            }
            
            .startbar.show {
                transform: translateX(0);
            }
            
            .topbar,
            .page-wrapper-img,
            .page-wrapper {
                margin-left: 0;
            }
        }
        
        /* Topbar Enhancements */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.5rem 0;
        }
        
        .welcome-text h6 {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .top-search {
            border-radius: 20px;
            border: 1px solid #e9ecef;
            padding: 0.5rem 1rem;
        }
        
        .top-search:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .thumb-sm {
            width: 32px;
            height: 32px;
            object-fit: cover;
        }
        
        .thumb-md {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        
        /* Page specific styles */
        .page-title {
            margin-bottom: 1.5rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }

        /* Form Styles */
        .form-section {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-section h5 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .btn {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .parent-category-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 0.875rem;
            color: #1976d2;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .card-body {
            padding: 30px;
        }

        .header-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Left Sidebar Start -->
    <div class="startbar d-print-none">
        <!-- Brand -->
        <div class="brand">
            <a href="../../index.php" class="logo">
                <span>
                    <img src="../../assets/images/logo-sm.png" alt="logo-small" class="logo-sm">
                </span>
                <span class="">
                    <img src="../../assets/images/logo-light.png" alt="logo-large" class="logo-lg logo-light">
                    <img src="../../assets/images/logo-dark.png" alt="logo-large" class="logo-lg logo-dark">
                </span>
            </a>
        </div>
        
        <!-- Sidebar Menu -->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label mt-2">
                            <span>Navigation</span>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../../index.php">
                                <i class="iconoir-report-columns"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#sidebarEcommerce" data-bs-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="sidebarEcommerce"> 
                                <i class="iconoir-cart-alt"></i>                                        
                                <span>Ecommerce</span>
                            </a>
                            <div class="collapse" id="sidebarEcommerce">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link" href="../products/index.php">Products</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="index.php">Categories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../products/index.php">
                                <i class="iconoir-shopping-bag"></i> 
                                <span>Products</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="iconoir-folder"></i> 
                                <span>Categories</span>
                            </a>
                        </li>
                        
                        <li class="menu-label mt-2">
                            <span>IoT System</span>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../iot/index.php">
                                <i class="iconoir-thermometer"></i>
                                <span>IoT Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../iot/sensors/">
                                <i class="iconoir-sensor"></i>
                                <span>Quản lý cảm biến</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../iot/locations/">
                                <i class="iconoir-map-pin"></i>
                                <span>Quản lý vị trí</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../iot/dashboard/test_iot_api.html">
                                <i class="iconoir-test-tube"></i>
                                <span>Test API</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-fluid">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">    
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">                        
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu"></i>
                        </button>
                    </li> 
                    <li class="mx-2 welcome-text">
                        <h5 class="mb-0 fw-semibold text-truncate">Thêm danh mục mới</h5>
                        <h6 class="mb-0 fw-normal text-muted text-truncate fs-14">Quản lý hệ thống E-commerce</h6>
                    </li>                   
                </ul>
                
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li class="hide-phone app-search">
                        <form role="search" class="d-flex">
                            <input type="search" class="form-control top-search mb-0" placeholder="Tìm kiếm...">
                            <button type="submit"><i class="iconoir-search"></i></button>
                        </form>
                    </li>
                    
                    <li class="dropdown">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                        aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <img src="../../assets/images/flags/us_flag.jpg" alt="" class="thumb-sm rounded-circle">
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#"><img src="../../assets/images/flags/us_flag.jpg" alt="" height="15" class="me-2">Tiếng Việt</a>
                            <a class="dropdown-item" href="#"><img src="../../assets/images/flags/us_flag.jpg" alt="" height="15" class="me-2">English</a>
                        </div>
                    </li>
                    
                    <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                            <i class="iconoir-half-moon dark-mode"></i>
                            <i class="iconoir-sun-light light-mode"></i>
                        </a>                    
                    </li>

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                            <i class="iconoir-bell"></i>
                            <span class="alert-badge"></span>
                        </a>
                        <div class="dropdown-menu stop dropdown-menu-end dropdown-lg py-0">
                            <h5 class="dropdown-item-text m-0 py-3 d-flex justify-content-between align-items-center">
                                Thông báo <a href="#" class="badge text-body-tertiary badge-pill">
                                    <i class="iconoir-plus-circle fs-4"></i>
                                </a>
                            </h5>
                            <div class="dropdown-body">
                                <div class="dropdown-item">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="../../assets/images/users/avatar-1.jpg" alt="" class="thumb-sm rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 text-truncate">Cập nhật hệ thống thành công</p>
                                            <small class="text-muted">2 giờ trước</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="page-wrapper-img">
    </div>

    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Thông báo -->
                    <?php if(!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form thêm danh mục -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Thông tin danh mục</h4>
                                    <p class="text-muted font-14">Điền đầy đủ thông tin để tạo danh mục mới</p>
                                    
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="row">
                                            <!-- Thông tin cơ bản -->
                                            <div class="col-lg-8">
                                                <div class="form-section">
                                                    <h5 class="mb-3">Thông tin cơ bản</h5>
                                                    
                                                    <div class="mb-3">
                                                        <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="name" name="name" 
                                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                                        <div class="form-text">Tên hiển thị của danh mục</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="description" class="form-label">Mô tả</label>
                                                        <textarea class="form-control" id="description" name="description" rows="4"
                                                                  placeholder="Mô tả chi tiết về danh mục..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                                        <div class="form-text">Mô tả ngắn gọn về danh mục này</div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="parent_id" class="form-label">Danh mục cha</label>
                                                            <select class="form-select" id="parent_id" name="parent_id">
                                                                <option value="">Không có danh mục cha</option>
                                                                <?php foreach($parents as $parent): ?>
                                                                    <option value="<?php echo $parent['id']; ?>" 
                                                                            <?php echo (isset($_POST['parent_id']) && $_POST['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($parent['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="form-text">Chọn danh mục cha nếu muốn tạo danh mục con</div>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="sort_order" class="form-label">Thứ tự sắp xếp</label>
                                                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                                                   value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>" min="0">
                                                            <div class="form-text">Số càng nhỏ càng hiển thị trước</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sidebar phải -->
                                            <div class="col-lg-4">
                                                <!-- Hình ảnh danh mục -->
                                                <div class="form-section">
                                                    <h5 class="mb-3">Hình ảnh danh mục</h5>
                                                    
                                                    <div class="mb-3">
                                                        <label for="image" class="form-label">URL hình ảnh</label>
                                                        <input type="url" class="form-control" id="image" name="image" 
                                                               value="<?php echo htmlspecialchars($_POST['image'] ?? ''); ?>"
                                                               placeholder="https://example.com/image.jpg">
                                                        <div class="form-text">URL hình ảnh đại diện cho danh mục</div>
                                                    </div>

                                                    <div id="imagePreview" class="text-center" style="display: none;">
                                                        <img src="" alt="Preview" class="preview-image mb-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearImagePreview()">
                                                            Xóa ảnh
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Trạng thái -->
                                                <div class="form-section">
                                                    <h5 class="mb-3">Trạng thái</h5>
                                                    
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                               <?php echo (isset($_POST['is_active']) || !isset($_POST['is_active'])) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="is_active">
                                                            Danh mục hoạt động
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Nút submit -->
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="iconoir-save"></i> Tạo danh mục
                                                    </button>
                                                    <a href="index.php" class="btn btn-outline-secondary">
                                                        <i class="iconoir-arrow-left"></i> Hủy bỏ
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/js/app.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('togglemenu').addEventListener('click', function() {
            const sidebar = document.querySelector('.startbar');
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.startbar');
            const toggleBtn = document.getElementById('togglemenu');
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Preview hình ảnh
        document.getElementById('image').addEventListener('input', function() {
            const url = this.value.trim();
            const preview = document.getElementById('imagePreview');
            const img = preview.querySelector('img');
            
            if(url) {
                img.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });

        // Xóa preview hình ảnh
        function clearImagePreview() {
            document.getElementById('image').value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }

        // Hiển thị thông tin danh mục cha khi chọn
        document.getElementById('parent_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const parentInfo = document.getElementById('parentInfo');
            
            if(this.value && parentInfo) {
                parentInfo.remove();
            } else if(this.value) {
                const info = document.createElement('div');
                info.id = 'parentInfo';
                info.className = 'parent-category-info';
                info.innerHTML = `<strong>Danh mục cha:</strong> ${selectedOption.text}`;
                this.parentNode.appendChild(info);
            }
        });
    </script>
</body>
</html>
