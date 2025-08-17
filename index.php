<?php
session_start();
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
    
    <!-- Common Layout CSS -->
    <link href="admin/partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .quick-action {
            transition: all 0.3s ease;
        }
        .quick-action:hover {
            transform: scale(1.05);
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
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    <?php include 'admin/partials/sidebar.php'; ?>

    <!-- Include Header -->
    <?php include 'admin/partials/header.php'; ?>

    <!-- Main Content Area -->
    <div class="content-page">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Thống kê tổng quan -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Tổng sản phẩm</h5>
                                        <h3 class="mb-0"><?php echo $total_products; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-shopping-bag" style="font-size: 48px; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Tổng danh mục</h5>
                                        <h3 class="mb-0"><?php echo $total_categories; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-folder" style="font-size: 48px; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Hôm nay</h5>
                                        <h3 class="mb-0"><?php echo date('d/m'); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-calendar" style="font-size: 48px; opacity: 0.7;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Trạng thái</h5>
                                        <h3 class="mb-0">Online</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-wifi" style="font-size: 48px; opacity: 0.7;"></i>
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
                                        <a href="admin/products/index.php" class="btn btn-primary w-100 quick-action">
                                            <i class="iconoir-plus me-2"></i>
                                            Thêm sản phẩm mới
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/categories/index.php" class="btn btn-success w-100 quick-action">
                                            <i class="iconoir-folder me-2"></i>
                                            Thêm danh mục mới
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/products/index.php" class="btn btn-info w-100 quick-action">
                                            <i class="iconoir-shopping-bag me-2"></i>
                                            Quản lý sản phẩm
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin/categories/index.php" class="btn btn-warning w-100 quick-action">
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
                                    <a href="admin/products/index.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
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
                                    <a href="admin/categories/index.php" class="btn btn-sm btn-outline-success">Xem tất cả</a>
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
    <!-- App JS -->
    <script src="assets/js/app.js"></script>
    <!-- Common Layout JS -->
    <script src="admin/partials/layout.js"></script>
</body>
</html>
