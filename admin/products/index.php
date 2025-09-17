<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Product.php';
require_once '../../models/Category.php';

$product = new Product();
$category = new Category();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get products with filters
$products_result = $product->getAll($search, $category_filter);
$products = [];
if ($products_result) {
    while ($row = $products_result->fetch()) {
        $products[] = $row;
    }
}

$categories_result = $category->getAll();
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý Sản phẩm | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý sản phẩm" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/custom-override.css" rel="stylesheet" type="text/css" />
    
    <!-- Common Admin Layout CSS -->
    <link href="../partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        /* Enhanced Product Cards */
        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
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
            transform: scale(1.05);
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
        
        .product-description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            max-height: 3em;
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 500;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            word-wrap: break-word;
            word-break: break-word;
        }

        .badge-red {
            width: 100px;
            justify-content: center;
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
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
        }
        
        .search-section .form-control,
        .search-section .form-select {
            border-radius: 8px;
            border: 1.5px solid #e9ecef;
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .search-section .form-control:focus,
        .search-section .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.15);
            transform: translateY(-1px);
            background: #ffffff;
        }
        
        .search-section .form-control::placeholder {
            color: #adb5bd;
            font-size: 0.8rem;
        }
        
        .search-section .btn {
            border-radius: 8px;
            padding: 0.35rem 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 0.75rem;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            box-shadow: 0 2px 6px rgba(0,123,255,0.2);
            transition: all 0.3s ease;
            white-space: nowrap;
            line-height: 1.2;
        }
        
        .search-section .btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        
        .search-section .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0,123,255,0.2);
        }
        
        .search-section .btn i {
            margin-right: 0.3rem;
            font-size: 0.8rem;
        }
        
        .search-section .btn-outline-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            border: none;
            color: white;
            box-shadow: 0 2px 6px rgba(108,117,125,0.2);
        }
        
        .search-section .btn-outline-secondary:hover {
            background: linear-gradient(135deg, #545b62 0%, #3d4449 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(108,117,125,0.3);
        }
        
        /* Enhanced Stat Cards */
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card .card-body {
            padding: 0.75rem;
        }
        
        .stat-card .icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .stat-card .card-title {
            font-size: 0.7rem;
            margin-bottom: 0.3rem;
        }
        
        .stat-card h2 {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
        }
        
        /* Enhanced Progress Bars */
        .progress {
            height: 4px;
            border-radius: 4px;
            background: rgba(255,255,255,0.3);
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 4px;
            transition: width 1s ease;
        }
        
        /* Enhanced Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 14px;
            border: 2px dashed #dee2e6;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 0.8rem;
            display: block;
        }
        
        .empty-state h5 {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 1rem;
            font-size: 0.8rem;
        }
        
        /* Quick Action Buttons */
        .quick-action {
            transition: all 0.3s ease;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.25px;
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
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        
        .quick-action:hover::before {
            left: 100%;
        }
        
        /* Loading Animation for Search */
        .loading {
            position: relative;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading .btn-text {
            opacity: 0;
        }
        
        .loading .btn-icon {
            opacity: 0;
        }
        
        /* Fade animation for content reload */
        .content-fade {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Enhanced search input focus */
        #searchInput:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            transform: translateY(-1px);
        }
        
        /* Category select enhancement */
        #categorySelect:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            transform: translateY(-1px);
        }
        
        /* Enhanced Search Highlighting */
        mark {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            color: #856404 !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
            border: 1px solid rgba(255,193,7,0.3) !important;
            animation: highlightPulse 0.6s ease-in-out !important;
        }
        
        @keyframes highlightPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Search Results Counter */
        .search-results-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #1565c0;
            font-weight: 500;
        }
        
        .search-results-info i {
            margin-right: 0.5rem;
            color: #1976d2;
        }
        
        /* Enhanced Empty State for Search */
        .search-empty-state {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border: 2px dashed #ffb74d;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
        }
        
        .search-empty-state i {
            font-size: 4rem;
            color: #ff9800;
            margin-bottom: 1rem;
            display: block;
        }
        
        .search-empty-state h5 {
            color: #e65100;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .search-empty-state p {
            color: #bf360c;
            margin-bottom: 1.5rem;
        }
        
        .search-suggestions {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #007bff;
        }
        
        .search-suggestions h6 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .search-suggestions ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .search-suggestions li {
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="content">
            <?php include '../partials/header.php'; ?>

            <!-- Start Content-->
            <div class="container-fluid">

                    <!-- Thống kê tổng quan -->
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-2">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Tổng sản phẩm</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo count($products); ?></h2>
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
                                            <?php 
                                            $active_products = count(array_filter($products, function($p) { 
                                                return isset($p['is_active']) && $p['is_active'] == 1; 
                                            }));
                                            ?>
                                            <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Đang hoạt động</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo $active_products; ?></h2>
                                            <div class="progress mt-3">
                                                <?php 
                                                $active_percentage = count($products) > 0 ? ($active_products / count($products)) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-white" style="width: <?php echo $active_percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="icon-wrapper ms-3">
                                            <i class="iconoir-check-circle" style="font-size: 28px;"></i>
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
                                            <?php 
                                            $inactive_products = count(array_filter($products, function($p) { 
                                                return isset($p['is_active']) && $p['is_active'] == 0; 
                                            }));
                                            ?>
                                            <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Không hoạt động</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo $inactive_products; ?></h2>
                                            <div class="progress mt-3">
                                                <?php 
                                                $inactive_percentage = count($products) > 0 ? ($inactive_products / count($products)) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-white" style="width: <?php echo $inactive_percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="icon-wrapper ms-3">
                                            <i class="iconoir-warning-triangle" style="font-size: 28px;"></i>
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
                                            <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Danh mục</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo count($categories); ?></h2>
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
                    </div>

                    <!-- Phần tìm kiếm và lọc -->
                    <div class="search-section mb-4">
                        <form method="GET" id="searchForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <input type="text" name="search" class="form-control me-2" 
                                               placeholder="Tìm kiếm sản phẩm... (Ctrl+K)" 
                                               value="<?php echo htmlspecialchars($search); ?>"
                                               id="searchInput"
                                               title="Nhấn Ctrl+K để focus vào ô tìm kiếm">
                                        <button type="submit" class="btn btn-primary" id="searchBtn">
                                            <i class="iconoir-search me-2" id="searchIcon"></i> 
                                            <span id="searchText">Tìm kiếm</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="category" class="form-select" id="categorySelect">
                                        <option value="">Tất cả danh mục</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 text-end" style="display: flex ;align-items: center; justify-content: flex-end;">
                                    <?php if(!empty($search) || !empty($category_filter)): ?>
                                        <a href="index.php" class="btn btn-outline-secondary quick-action" id="clearFilters">
                                            Xóa bộ lọc
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                        <!-- Nút tạo sản phẩm mới -->
    <div class="row mb-4">
        <div class="col-12">
            <button type="button" class="btn btn-success quick-action" onclick="openCreateProductModal()">
                <i class="iconoir-plus me-2"></i> Thêm sản phẩm mới
            </button>
        </div>
    </div>

                    <!-- Search Results Info -->
                    <?php if(!empty($search) || !empty($category_filter)): ?>
                        <div class="search-results-info">
                            <i class="iconoir-search"></i>
                            <?php 
                            $category_name = 'N/A';
                            if (!empty($category_filter)) {
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $category_filter) {
                                        $category_name = $cat['name'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?php if(!empty($search) && !empty($category_filter)): ?>
                                Tìm thấy <?php echo count($products); ?> sản phẩm với từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>" trong danh mục "<strong><?php echo htmlspecialchars($category_name); ?></strong>"
                            <?php elseif(!empty($search)): ?>
                                Tìm thấy <?php echo count($products); ?> sản phẩm với từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php else: ?>
                                Tìm thấy <?php echo count($products); ?> sản phẩm trong danh mục "<strong><?php echo htmlspecialchars($category_name); ?></strong>"
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Danh sách sản phẩm -->
                    <div class="row content-fade" id="productList">
                        <?php if(empty($products)): ?>
                            <div class="col-12">
                                <?php if(!empty($search) || !empty($category_filter)): ?>
                                    <div class="search-empty-state">
                                        <i class="iconoir-search"></i>
                                        <h5>Không tìm thấy sản phẩm nào</h5>
                                        <p>
                                            <?php if(!empty($search) && !empty($category_filter)): ?>
                                                Không có sản phẩm nào khớp với từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>" trong danh mục đã chọn.
                                            <?php elseif(!empty($search)): ?>
                                                Không có sản phẩm nào khớp với từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                            <?php else: ?>
                                                Không có sản phẩm nào trong danh mục đã chọn.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="card empty-state">
                                        <div class="card-body text-center py-5">
                                            <h5 class="mt-3">Chưa có sản phẩm nào</h5>
                                            <a href="create.php" class="btn btn-primary mt-3 quick-action">
                                                <i class="iconoir-plus me-2"></i> Tạo sản phẩm đầu tiên
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <?php foreach($products as $prod): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="card-body">
                                            <!-- Header với hình ảnh và tên sản phẩm -->
                                            <div class="d-flex align-items-start mb-3">
                                                <?php if(!empty($prod['images'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($prod['images']); ?>" 
                                                     alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                         class="product-image me-3">
                                            <?php else: ?>
                                                    <div class="product-image bg-light d-flex align-items-center justify-content-center me-3">
                                                        <i class="iconoir-image" style="font-size: 24px; color: #6c757d;"></i>
                                                </div>
                                            <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($prod['sku']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Mô tả sản phẩm -->
                                            <p class="card-text mb-3 product-description">
                                                <?php echo htmlspecialchars($prod['description']); ?>
                                            </p>
                                            
                                            <!-- Thông tin giá và tồn kho -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Giá</small>
                                                    <div class="fw-bold text-success fs-6">
                                                        <?php echo number_format($prod['price'], 0, ',', '.'); ?> ₫
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Tồn kho</small>
                                                    <div class="fw-bold text-primary fs-6" id="stock-<?php echo $prod['id']; ?>">
                                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Danh mục -->
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Danh mục:</small>
                                                <div>
                                            <?php 
                                            $cat_name = 'N/A';
                                            $cat_temp_zone = 'ambient';
                                            foreach ($categories as $cat) {
                                                if ($cat['id'] == $prod['category_id']) {
                                                    $cat_name = $cat['name'];
                                                    // Lấy temperature_zone từ vị trí kho thay vì temperature_type từ category
                                                    $cat_temp_zone = $product->getTemperatureZoneFromCategory($prod['category_id']);
                                                    break;
                                                }
                                            }
                                                    ?>
                                                    <span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($cat_name); ?></span>
                                                </div>
                                            </div>
                                            <!-- Thông tin nhiệt độ và độ ẩm -->
                                             <div class="row" style="justify-content: space-between;">
                                                 <!-- Cột trái: Bảo quản -->
                                                 <div class="col-5">
                                                     <div class="mb-3">
                                                         <div>
                                                             <small class="text-muted d-block mb-1">Nhiệt độ bảo quản</small>
                                                         </div>
         
                                                         <div class="d-flex align-items-center gap-2 flex-wrap">
                                                             <?php 
                                                             $temp_labels = [
                                                                 'frozen' => ['Đông lạnh (< -18°C)', 'bg-info-subtle text-info'],
                                                                 'chilled' => ['Lạnh mát (0 - 5°C)', 'bg-primary-subtle text-primary'],
                                                                 'ambient' => ['Nhiệt độ phòng (15 - 33°C)', 'bg-warning-subtle text-warning']
                                                             ];
                                                             $temp_info = $temp_labels[$cat_temp_zone] ?? $temp_labels['ambient'];
                                                             ?>
                                                             <span class="badge rounded-pill <?php echo $temp_info[1]; ?> d-inline-flex align-items-center">
                                                                 <?php echo $temp_info[0]; ?>
                                                             </span>
                                                         </div>
                                                     </div>
                                                     
                                                     <div class="mb-3">
                                                         <div>
                                                             <small class="text-muted d-block mb-1">Độ ẩm bảo quản</small>
                                                         </div>
         
                                                         <div class="d-flex align-items-center gap-2 flex-wrap">
                                                             <?php 
                                                             $humidity_labels = [
                                                                 'frozen' => ['Đông lạnh (85-95%)', 'bg-info-subtle text-info'],
                                                                 'chilled' => ['Lạnh mát (85-90%)', 'bg-primary-subtle text-primary'],
                                                                 'ambient' => ['Phòng (50-60%)', 'bg-warning-subtle text-warning']
                                                             ];
                                                             $humidity_info = $humidity_labels[$cat_temp_zone] ?? $humidity_labels['ambient'];
                                                             ?>
                                                             <span class="badge rounded-pill <?php echo $humidity_info[1]; ?> d-inline-flex align-items-center">
                                                                 <?php echo $humidity_info[0]; ?>
                                                             </span>
                                                         </div>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- Đường phân cách -->
                                                 <div class="col-1 d-flex justify-content-center">
                                                     <div class="vr opacity-25" style="height: 90px;"></div>
                                                 </div>
                                                 
                                                 <!-- Cột phải: Nguy hiểm -->
                                                 <div class="col-5" style="width: 30% !important;">
                                                     <div class="mb-3">
                                                         <div>
                                                             <small class="text-muted">Nhiệt độ nguy hiểm</small>
                                                         </div>
                                                         <?php 
                                                         // Lấy thông tin nhiệt độ (bao gồm dangerous_min/max) từ model
                                                         $temp_info_detail = $product->getTemperatureInfoFromCategory($prod['category_id']);
                                                         $danger_min = $temp_info_detail && isset($temp_info_detail['dangerous_min']) ? $temp_info_detail['dangerous_min'] : null;
                                                         $danger_max = $temp_info_detail ? $temp_info_detail['dangerous_max'] : 50.0;
                                                         ?>
                                                         <span class="badge badge-red rounded-pill bg-danger-subtle text-danger d-inline-flex align-items-center">
                                                             <?php if ($danger_min !== null): ?>
                                                                 < <?php echo $danger_min; ?>°C và > <?php echo $danger_max; ?>°C
                                                             <?php else: ?>
                                                                 > <?php echo $danger_max; ?>°C
                                                             <?php endif; ?>
                                                         </span>
                                                     </div>
                                                     
                                                     <div class="mb-3">
                                                         <div>
                                                             <small class="text-muted">Độ ẩm nguy hiểm</small>
                                                         </div>
                                                         <?php 
                                                         // Lấy thông tin độ ẩm (bao gồm dangerous_min/max) từ model
                                                         $humidity_info_detail = $product->getHumidityInfoFromCategory($prod['category_id']);
                                                         $humidity_danger_min = $humidity_info_detail && isset($humidity_info_detail['dangerous_min']) ? $humidity_info_detail['dangerous_min'] : null;
                                                         $humidity_danger_max = $humidity_info_detail && isset($humidity_info_detail['dangerous_max']) ? $humidity_info_detail['dangerous_max'] : null;
                                                         ?>
                                                         <span class="badge badge-red rounded-pill bg-danger-subtle text-danger d-inline-flex align-items-center">
                                                             <?php if ($humidity_danger_min !== null && $humidity_danger_max !== null): ?>
                                                                 < <?php echo $humidity_danger_min; ?>% và > <?php echo $humidity_danger_max; ?>%
                                                             <?php elseif ($humidity_danger_min !== null): ?>
                                                                 < <?php echo $humidity_danger_min; ?>%
                                                             <?php else: ?>
                                                                 N/A
                                                             <?php endif; ?>
                                                         </span>
                                                     </div>
                                                 </div>
                                             </div>

                                            
                                            <!-- Nút hành động -->
                                            <div class="d-flex justify-content-between">
                                                <button type="button"
                                                        style="border: 1px solid black; color: black;"
                                                        class="btn btn-outline-secondary"
                                                        data-id="<?php echo $prod['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                                        data-sku="<?php echo htmlspecialchars($prod['sku']); ?>"
                                                        data-description="<?php echo htmlspecialchars($prod['description']); ?>"
                                                        data-category-id="<?php echo (int)$prod['category_id']; ?>"
                                                        data-brand="<?php echo htmlspecialchars($prod['brand']); ?>"
                                                        data-price="<?php echo (float)$prod['price']; ?>"
                                                        data-sale-price="<?php echo $prod['sale_price'] !== null ? (float)$prod['sale_price'] : 0; ?>"
                                                        data-stock-quantity="0"
                                                        data-is-active="<?php echo (int)$prod['is_active']; ?>"
                                                        data-images="<?php echo htmlspecialchars($prod['images']); ?>"
                                                        onclick="openViewProductFromButton(this)">
                                                    <i class="iconoir-eye"></i> Xem
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-primary"
                                                        data-id="<?php echo $prod['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                                        data-sku="<?php echo htmlspecialchars($prod['sku']); ?>"
                                                        data-description="<?php echo htmlspecialchars($prod['description']); ?>"
                                                        data-category-id="<?php echo (int)$prod['category_id']; ?>"
                                                        data-brand="<?php echo htmlspecialchars($prod['brand']); ?>"
                                                        data-price="<?php echo (float)$prod['price']; ?>"
                                                        data-sale-price="<?php echo $prod['sale_price'] !== null ? (float)$prod['sale_price'] : 0; ?>"
                                                        data-stock-quantity="0"
                                                        data-is-active="<?php echo (int)$prod['is_active']; ?>"
                                                        data-images="<?php echo htmlspecialchars($prod['images']); ?>"
                                                        onclick="openEditProductFromButton(this)">
                                                    <i class="iconoir-edit"></i> Sửa
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars(addslashes($prod['name'])); ?>')">
                                                    <i class="iconoir-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Footer với thời gian và trạng thái -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="iconoir-calendar"></i> 
                                                    <?php echo date('d/m/Y H:i', strtotime($prod['created_at'])); ?>
                                                </small>
                                                <span class="badge <?php echo (isset($prod['is_active']) && $prod['is_active'] == 1) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                                                    <?php echo (isset($prod['is_active']) && $prod['is_active'] == 1) ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                </span>
                            </div>
                        </div>
                    </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- container -->
        </div>
        <!-- content -->

        
    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    <!-- Bootstrap JS -->
    <script src="../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Simplebar -->
    <script src="../../assets/libs/simplebar/simplebar.min.js"></script>
    
    <!-- Common Admin Layout JavaScript -->
    <script src="../partials/layout.js"></script>
    
    <!-- Include Unified Widgets CSS -->
    <link href="../../assets/css/widget.css" rel="stylesheet" type="text/css" />
    
    <!-- Include Product Creation Modal Widget -->
    <?php include '../../assets/widgets/create-product.php'; ?>
    
    <!-- Include Product Edit Modal Widget -->
    <?php include '../../assets/widgets/edit-product.php'; ?>
    
    <!-- Include Product View Modal Widget -->
    <?php include '../../assets/widgets/view-product.php'; ?>
    
    <!-- Include Unified Widgets JavaScript -->
    <script src="../../assets/js/widget.js"></script>
    
    <script>
        // Function to refresh product list after successful creation
        function refreshProductList() {
            // Reload the page to show updated product list
            window.location.reload();
        }
        
        // Function hiển thị thông báo
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = `
                top: 80px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 8px;
                border: none;
                font-weight: 500;
            `;
            notification.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            document.body.appendChild(notification);
            setTimeout(() => { if (notification.parentNode) { notification.remove(); } }, 3000);
        }
        
        // Enhanced Search and Filter Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Load stock information for all products
            loadAllProductStocks();
            
            const searchForm = document.getElementById('searchForm');
            const searchBtn = document.getElementById('searchBtn');
            const searchIcon = document.getElementById('searchIcon');
            const searchText = document.getElementById('searchText');
            const categorySelect = document.getElementById('categorySelect');
            const productList = document.getElementById('productList');
            const clearFilters = document.getElementById('clearFilters');
            
            // Function to show loading animation
            function showLoading() {
                searchBtn.classList.add('loading');
                searchIcon.style.display = 'none';
                searchText.textContent = 'Đang tìm...';
                searchBtn.disabled = true;
                
                // Add fade out effect to product list
                productList.style.opacity = '0.5';
                productList.style.transform = 'translateY(10px)';
            }
            
            // Function to hide loading animation
            function hideLoading() {
                searchBtn.classList.remove('loading');
                searchIcon.style.display = 'inline';
                searchText.textContent = 'Tìm kiếm';
                searchBtn.disabled = false;
                
                // Add fade in effect to product list
                productList.style.opacity = '1';
                productList.style.transform = 'translateY(0)';
                productList.classList.add('content-fade');
            }
            
            // Handle form submission
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                showLoading();
                
                // Submit form after a short delay for better UX
                setTimeout(() => {
                    searchForm.submit();
                }, 300);
            });
            
            // Handle category filter change
            categorySelect.addEventListener('change', function() {
                showLoading();
                
                // Submit form after a short delay
                setTimeout(() => {
                    searchForm.submit();
                }, 300);
            });
            
            // Handle clear filters
            if (clearFilters) {
                clearFilters.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLoading();
                    
                    // Redirect to clean URL
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 300);
                });
            }
            
            // Add enter key support for search input
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    showLoading();
                    
                    setTimeout(() => {
                        searchForm.submit();
                    }, 300);
                }
            });
            
            // Add real-time search suggestions and input validation
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                const input = this.value.trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Add visual feedback for input
                if (input.length > 0) {
                    this.style.borderColor = '#28a745';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(40,167,69,0.25)';
                } else {
                    this.style.borderColor = '#e9ecef';
                    this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                }
                
                // Debounced search suggestions
                searchTimeout = setTimeout(() => {
                    if (input.length >= 2) {
                        // Could add AJAX search suggestions here
                        showSearchSuggestions(input);
                    } else {
                        hideSearchSuggestions();
                    }
                }, 300);
            });
            
            // Add search input focus effects
            searchInput.addEventListener('focus', function() {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 0 0 0.2rem rgba(0,123,255,0.25)';
            });
            
            searchInput.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
                if (this.value.trim().length === 0) {
                    this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';
                }
            });
            
            // Add search highlighting for results
            const searchKeyword = '<?php echo htmlspecialchars($search); ?>';
            if (searchKeyword) {
                highlightSearchResults(searchKeyword);
            }
        });
        
        // Function to highlight search results
        function highlightSearchResults(keyword) {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                const title = card.querySelector('.card-title');
                const description = card.querySelector('.product-description');
                const sku = card.querySelector('small.text-muted');
                
                if (title && title.textContent.toLowerCase().includes(keyword.toLowerCase())) {
                    title.innerHTML = title.textContent.replace(
                        new RegExp(keyword, 'gi'), 
                        `<mark>$&</mark>`
                    );
                }
                
                if (description && description.textContent.toLowerCase().includes(keyword.toLowerCase())) {
                    description.innerHTML = description.textContent.replace(
                        new RegExp(keyword, 'gi'), 
                        `<mark>$&</mark>`
                    );
                }
                
                if (sku && sku.textContent.toLowerCase().includes(keyword.toLowerCase())) {
                    sku.innerHTML = sku.textContent.replace(
                        new RegExp(keyword, 'gi'), 
                        `<mark>$&</mark>`
                    );
                }
            });
        }
        
        // Function to show search suggestions
        function showSearchSuggestions(input) {
            // Remove existing suggestions
            hideSearchSuggestions();
            
            // Create suggestions container
            const suggestionsContainer = document.createElement('div');
            suggestionsContainer.id = 'searchSuggestions';
            suggestionsContainer.className = 'search-suggestions';
            suggestionsContainer.innerHTML = `
                <h6><i class="iconoir-lightbulb"></i> Gợi ý tìm kiếm:</h6>
                <ul>
                    <li>Tìm theo tên sản phẩm</li>
                    <li>Tìm theo SKU</li>
                    <li>Tìm theo mô tả</li>
                    <li>Kết hợp với bộ lọc danh mục</li>
                </ul>
            `;
            
            // Insert after search form
            const searchForm = document.getElementById('searchForm');
            searchForm.parentNode.insertBefore(suggestionsContainer, searchForm.nextSibling);
            
            // Add fade in animation
            suggestionsContainer.style.opacity = '0';
            suggestionsContainer.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                suggestionsContainer.style.transition = 'all 0.3s ease';
                suggestionsContainer.style.opacity = '1';
                suggestionsContainer.style.transform = 'translateY(0)';
            }, 10);
        }
        
        // Function to hide search suggestions
        function hideSearchSuggestions() {
            const existingSuggestions = document.getElementById('searchSuggestions');
            if (existingSuggestions) {
                existingSuggestions.style.transition = 'all 0.3s ease';
                existingSuggestions.style.opacity = '0';
                existingSuggestions.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (existingSuggestions.parentNode) {
                        existingSuggestions.remove();
                    }
                }, 300);
            }
        }
        
        // Enhanced search with keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.value = '';
                    searchInput.blur();
                    hideSearchSuggestions();
                }
            }
        });
        
        // Function to load stock information for all products
        async function loadAllProductStocks() {
            const productIds = [];
            
            // Collect all product IDs from the page
            document.querySelectorAll('[id^="stock-"]').forEach(element => {
                const productId = element.id.replace('stock-', '');
                productIds.push(productId);
            });
            
            // Load stock for each product
            for (const productId of productIds) {
                try {
                    const response = await fetch(`../iot/api/stock-operations.php?action=get_product_stock_total&product_id=${productId}`);
                    const result = await response.json();
                    
                    const stockElement = document.getElementById(`stock-${productId}`);
                    if (stockElement && result.success) {
                        const totalStock = result.data.total_stock || 0;
                        
                        // Apply styling based on stock level
                        let stockClass = 'text-primary';
                        if (totalStock === 0) {
                            stockClass = 'text-danger';
                        } else if (totalStock <= 10) {
                            stockClass = 'text-warning';
                        } else if (totalStock > 50) {
                            stockClass = 'text-success';
                        }
                        
                        stockElement.innerHTML = `<span class="${stockClass}">${totalStock}</span>`;
                    } else if (stockElement) {
                        stockElement.innerHTML = '<span class="text-muted">N/A</span>';
                    }
                } catch (error) {
                    console.error(`Error loading stock for product ${productId}:`, error);
                    const stockElement = document.getElementById(`stock-${productId}`);
                    if (stockElement) {
                        stockElement.innerHTML = '<span class="text-muted">N/A</span>';
                    }
                }
            }
        }
    </script>

    <!-- Delete Success Message - Fixed Position -->
    <div id="successMessageDeleteProduct" class="success-alert-fixed delete-success">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Sản phẩm đã được xóa thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideDeleteProductSuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</body>
</html>
