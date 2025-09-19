<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_once 'config/database.php';
require_once 'models/Product.php';
require_once 'models/Category.php';

$product = new Product();
$category = new Category();

// Lấy thống kê
$total_products = $product->count();
$total_categories = $category->count();
$recent_products = $product->getAll(5);
$recent_categories = $category->getAll(5);
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard | Graduation Project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Admin Dashboard" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Font Consistency CSS -->
    <link href="assets/css/font-consistency.css" rel="stylesheet" type="text/css" />
    
    <!-- Common Layout CSS -->
    <link href="admin/partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        /* Enhanced Stat Cards */
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card .card-body {
            position: relative;
            z-index: 2;
        }
        
        /* Normalize Bootstrap heading sizes inside stat cards */
        .stat-card .card-title {
            font-size: 0.7rem;
            margin-bottom: 0.3rem;
        }
        .stat-card h2 {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
        }
        
        .stat-card .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        /* Enhanced Quick Action Buttons */
        .quick-action {
            transition: all 0.3s ease;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
        
        .quick-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .quick-action:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .quick-action:hover::before {
            left: 100%;
        }
        
        /* Enhanced Product Cards */
        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            position: relative;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .product-card:hover::before {
            transform: scaleX(1);
        }
        
        .product-card .card-body {
            padding: 0.75rem;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .product-card:hover .product-image {
            border-color: #007bff;
            transform: scale(1.1);
        }
        
        .product-card .card-title {
            color: #2c3e50;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        
        .product-card .card-text {
            font-size: 0.8rem;
            line-height: 1.4;
            color: #6c757d;
            margin-bottom: 0.6rem;
        }
        
        .product-card .badge {
            font-size: 0.65rem;
            padding: 0.3em 0.6em;
            border-radius: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }
        
        .product-card .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            font-size: 0.7rem;
        }
        
        .product-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .product-card .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
        }
        
        .product-card .btn-outline-primary:hover {
            background: #007bff;
            color: white;
        }
        
        .product-card .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .product-card .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }
        
        /* Enhanced Search Section */
        .search-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .search-section .form-control,
        .search-section .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-section .form-control:focus,
        .search-section .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            transform: translateY(-2px);
        }
        
        .search-section .btn {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Enhanced Table Styling */
        .table {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            padding: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .table tbody tr:hover {
            background: rgba(0,123,255,0.05);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        
        /* Enhanced Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            border: 2px dashed #dee2e6;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .empty-state h5 {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 2rem;
        }
        
        /* Enhanced Progress Bars */
        .progress {
            height: 8px;
            border-radius: 10px;
            background: rgba(255,255,255,0.3);
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 10px;
            transition: width 1s ease;
        }
        
        /* Fix overflow issues */
        body {
            overflow-x: hidden;
        }
        
        .page-wrapper {
            overflow-x: hidden;
        }
        
        .container-fluid {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        .col-md-6, .col-lg-3, .col-12, .col-md-3 {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .startbar .startbar-menu .navbar-nav .nav-item.active .nav-link.active {
            color: white !important;
        }
        
        .content-page {
            overflow: visible !important;
        }
        
        .content {
            overflow: visible !important;
        }
        
        .container-fluid {
            overflow: visible !important;
        }
        
        .navbar-custom {
            overflow: visible !important;
        }
        
        .navbar-actions {
            overflow: visible !important;
        }
        
        .navbar-actions .dropdown {
            overflow: visible !important;
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'admin/partials/sidebar.php'; ?>

    <!-- Include Header -->
    <?php include 'admin/partials/header.php'; ?>

    <!-- Main Content Area -->
    <div class="content-page">
        <div class="content">
            
    <!-- Start Content-->
    <div class="container-fluid">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="iconoir-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Thống kê tổng quan -->
                <div class="row mb-1">
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Tổng sản phẩm</h6>
                                        <h2 class="mb-0 fw-bold"><?php echo $total_products; ?></h2>
                                        <div class="progress mt-3">
                                            <div class="progress-bar bg-white" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="icon-wrapper ms-3">
                                        <i class="iconoir-shopping-bag" style="font-size: 28px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Tổng danh mục</h6>
                                        <h2 class="mb-0 fw-bold"><?php echo $total_categories; ?></h2>
                                        <div class="progress mt-3">
                                            <div class="progress-bar bg-white" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="icon-wrapper ms-3">
                                        <i class="iconoir-folder" style="font-size: 28px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Hôm nay</h6>
                                        <h2 class="mb-0 fw-bold"><?php echo date('d/m'); ?></h2>
                                        <div class="progress mt-3">
                                            <div class="progress-bar bg-white" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="icon-wrapper ms-3">
                                        <i class="iconoir-calendar" style="font-size: 28px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Trạng thái</h6>
                                        <h2 class="mb-0 fw-bold">Online</h2>
                                        <div class="progress mt-3">
                                            <div class="progress-bar bg-white" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="icon-wrapper ms-3">
                                        <i class="iconoir-wifi" style="font-size: 28px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Thao tác nhanh</h4>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/products/" class="btn btn-primary w-100 quick-action">
                                            <i class="iconoir-plus me-2"></i>
                                            Thêm sản phẩm mới
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/categories/" class="btn btn-success w-100 quick-action">
                                            <i class="iconoir-folder me-2"></i>
                                            Thêm danh mục mới
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/products/" class="btn btn-info w-100 quick-action">
                                            <i class="iconoir-shopping-bag me-2"></i>
                                            Quản lý sản phẩm
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/categories/" class="btn btn-warning w-100 quick-action">
                                            <i class="iconoir-folder me-2"></i>
                                            Quản lý danh mục
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Data -->
                <div class="row">
                    <!-- Recent Products -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title">Sản phẩm gần đây</h4>
                                    <a href="admin/products/" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                                </div>
                                <?php if($recent_products->rowCount() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Tên sản phẩm</th>
                                                    <th>Danh mục</th>
                                                    <th>Giá</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($prod = $recent_products->fetch()): ?>
                                                    <tr>
                                                        <td>
                                                            <h6 class="font-14 mb-1 fw-normal"><?php echo htmlspecialchars($prod['name']); ?></h6>
                                                            <span class="text-muted font-13"><?php echo htmlspecialchars($prod['sku']); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info-subtle text-info">
                                                                <?php echo htmlspecialchars($prod['category_name'] ?? 'Chưa phân loại'); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <h6 class="font-14 mb-1 fw-normal">
                                                                <?php echo number_format($prod['price'], 0, ',', '.'); ?> ₫
                                                            </h6>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $prod['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                <?php echo $prod['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="iconoir-shopping-bag" style="font-size: 48px; color: #dee2e6;"></i>
                                        <h6 class="mt-3">Chưa có sản phẩm nào</h6>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Categories -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title">Danh mục gần đây</h4>
                                    <a href="admin/categories/" class="btn btn-sm btn-outline-success">Xem tất cả</a>
                                </div>
                                <?php if($recent_categories->rowCount() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Tên danh mục</th>
                                                    <th>Mô tả</th>
                                                    <th>Thứ tự</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($cat = $recent_categories->fetch()): ?>
                                                    <tr>
                                                        <td>
                                                            <h6 class="font-14 mb-1 fw-normal"><?php echo htmlspecialchars($cat['name']); ?></h6>
                                                            <span class="text-muted font-13"><?php echo htmlspecialchars($cat['slug']); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted">
                                                                <?php echo htmlspecialchars(substr($cat['description'], 0, 50)) . (strlen($cat['description']) > 50 ? '...' : ''); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark"><?php echo $cat['sort_order']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $cat['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                <?php echo $cat['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="iconoir-folder" style="font-size: 48px; color: #dee2e6;"></i>
                                        <h6 class="mt-3">Chưa có danh mục nào</h6>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Simplebar -->
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <!-- Common Layout JS -->
    <script src="admin/partials/layout.js"></script>
</body>
</html>
