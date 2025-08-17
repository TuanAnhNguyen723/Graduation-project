<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Product.php';
require_once '../../models/Category.php';

$product = new Product();
$category = new Category();

// Lấy danh sách danh mục
$categories_result = $category->getAll();
$categories = [];
while($row = $categories_result->fetch()) {
    $categories[] = $row;
}

$message = '';
$message_type = '';

// Xử lý form submit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate dữ liệu
        if(empty($_POST['name'])) {
            throw new Exception('Tên sản phẩm không được để trống');
        }
        if(empty($_POST['sku'])) {
            throw new Exception('SKU không được để trống');
        }
        if(empty($_POST['price']) || !is_numeric($_POST['price'])) {
            throw new Exception('Giá sản phẩm không hợp lệ');
        }
        if(empty($_POST['stock_quantity']) || !is_numeric($_POST['stock_quantity'])) {
            throw new Exception('Số lượng tồn kho không hợp lệ');
        }

        // Kiểm tra SKU đã tồn tại chưa
        if($product->skuExists($_POST['sku'])) {
            throw new Exception('SKU đã tồn tại, vui lòng chọn SKU khác');
        }

        // Tạo slug từ tên sản phẩm
        $slug = $product->createSlug($_POST['name']);

        // Gán dữ liệu cho object
        $product->name = $_POST['name'];
        $product->slug = $slug;
        $product->description = $_POST['description'] ?? '';
        $product->sku = $_POST['sku'];
        $product->price = (float)$_POST['price'];
        $product->sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $product->stock_quantity = (int)$_POST['stock_quantity'];
        $product->category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $product->brand = $_POST['brand'] ?? '';
        $product->images = $_POST['images'] ?? '';
        $product->is_active = isset($_POST['is_active']) ? 1 : 0;

        // Tạo sản phẩm
        if($product->create()) {
            $message = 'Tạo sản phẩm thành công!';
            $message_type = 'success';
            
            // Reset form
            $_POST = array();
        } else {
            throw new Exception('Có lỗi xảy ra khi tạo sản phẩm');
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
    <title>Thêm sản phẩm mới | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Thêm sản phẩm mới" name="description" />
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
            transition: all 0.3s ease;
        }
        
        .page-wrapper {
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
        page-title-box
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

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            color: #6c757d;
        }

        .input-group .form-control {
            border-right: none;
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
                                        <a class="nav-link active" href="index.php">Products</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../categories/index.php">Categories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="iconoir-shopping-bag"></i> 
                                <span>Products</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../categories/index.php">
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
                        <h5 class="mb-0 fw-semibold text-truncate">Thêm sản phẩm mới</h5>
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

                    <!-- Form thêm sản phẩm -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Thông tin sản phẩm</h4>
                                    <p class="text-muted font-14">Điền đầy đủ thông tin để tạo sản phẩm mới</p>
                                    
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="row">
                                            <!-- Thông tin cơ bản -->
                                            <div class="col-lg-8">
                                                <div class="form-section">
                                                    <h5 class="mb-3">Thông tin cơ bản</h5>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="name" name="name" 
                                                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="sku" name="sku" 
                                                                   value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="description" class="form-label">Mô tả sản phẩm</label>
                                                        <textarea class="form-control" id="description" name="description" rows="4"
                                                                  placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="brand" class="form-label">Thương hiệu</label>
                                                            <input type="text" class="form-control" id="brand" name="brand" 
                                                                   value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="category_id" class="form-label">Danh mục</label>
                                                            <select class="form-select" id="category_id" name="category_id">
                                                                <option value="">Chọn danh mục</option>
                                                                <?php foreach($categories as $cat): ?>
                                                                    <option value="<?php echo $cat['id']; ?>" 
                                                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Thông tin giá và tồn kho -->
                                                <div class="form-section">
                                                    <h5 class="mb-3">Giá và tồn kho</h5>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-4 mb-3">
                                                            <label for="price" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" id="price" name="price" 
                                                                       step="0.01" min="0" 
                                                                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                                                                <span class="input-group-text">₫</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" id="sale_price" name="sale_price" 
                                                                       step="0.01" min="0" 
                                                                       value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>">
                                                                <span class="input-group-text">₫</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="stock_quantity" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                                                   min="0" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '0'); ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sidebar phải -->
                                            <div class="col-lg-4">
                                                <!-- Hình ảnh sản phẩm -->
                                                <div class="form-section">
                                                    <h5 class="mb-3">Hình ảnh sản phẩm</h5>
                                                    
                                                    <div class="mb-3">
                                                        <label for="images" class="form-label">URL hình ảnh</label>
                                                        <input type="url" class="form-control" id="images" name="images" 
                                                               value="<?php echo htmlspecialchars($_POST['images'] ?? ''); ?>"
                                                               placeholder="https://example.com/image.jpg">
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
                                                            Sản phẩm hoạt động
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Nút submit -->
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="iconoir-save"></i> Tạo sản phẩm
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
        document.getElementById('images').addEventListener('input', function() {
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
            document.getElementById('images').value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }

        // Auto-generate SKU từ tên sản phẩm
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value.trim();
            const skuInput = document.getElementById('sku');
            
            if(name && !skuInput.value) {
                const sku = name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 8);
                skuInput.value = sku + Math.random().toString(36).substring(2, 6).toUpperCase();
            }
        });
    </script>
</body>
</html>
