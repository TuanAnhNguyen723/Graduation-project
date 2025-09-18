<?php
require_once '../auth_check.php';
require_once '../../config/database.php';
require_once '../../models/Category.php';
require_once '../../models/Product.php';

$category = new Category();

// Lấy danh sách danh mục
$categories_result = $category->getAll();
$categories = [];
while($row = $categories_result->fetch()) {
    $categories[] = $row;
}

// Lấy thống kê
$total_categories = $category->count();

// Xử lý tìm kiếm
$search_results = [];
$search_keyword = '';
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_keyword = $_GET['search'];
    // Tìm kiếm theo tên hoặc mô tả
    $search_results = array_filter($categories, function($cat) use ($search_keyword) {
        return stripos($cat['name'], $search_keyword) !== false || 
               stripos($cat['description'], $search_keyword) !== false;
    });
}

// Xác định danh sách danh mục để hiển thị
$display_categories = $categories;
if(!empty($search_results)) {
    $display_categories = $search_results;
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý danh mục | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý danh mục sản phẩm" name="description" />
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
    
    <!-- Include Unified Widgets CSS -->
    <link href="../../assets/css/widget.css" rel="stylesheet" type="text/css" />
    
    <style>
        #createCategoryModal .custom-modal .section-header i {
            background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%) !important;
            color: white !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 18px !important;
        }
        
        #createCategoryModal .custom-modal .btn-primary {
            background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%) !important;
            color: white !important;
            border-color: #0d6efd !important;
        }
        
        #createCategoryModal .custom-modal .btn-primary:hover {
            background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(13,110,253,0.35) !important;
        }
        
        /* Enhanced Category Cards */
        .category-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .category-card::before {
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
        
        .category-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .category-card:hover::before {
            transform: scaleX(1);
        }
        
        .category-card .card-body {
            padding: 0.75rem;
        }
        
        .category-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .category-card:hover .category-image {
            border-color: #007bff;
            transform: scale(1.05);
        }
        
        .category-card .card-title {
            color: #2c3e50;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        
        .category-card .card-text {
            font-size: 0.8rem;
            line-height: 1.4;
            color: #6c757d;
            margin-bottom: 0.6rem;
        }
        
        .category-card .badge {
            font-size: 0.65rem;
            padding: 0.3em 0.6em;
            border-radius: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }
        
        .category-card .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            font-size: 0.7rem;
        }
        
        .category-card .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .category-card .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
        }
        
        .category-card .btn-outline-primary:hover {
            background: #007bff;
            color: white;
        }
        
        .category-card .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .category-card .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }
        
        /* Image Preview Styles */
        .image-preview-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .image-preview-wrapper img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
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
                                            <?php 
                                            $active_categories = count(array_filter($categories, function($cat) { 
                                                return isset($cat['is_active']) && $cat['is_active'] == 1; 
                                            }));
                                            ?>
                                            <h6 class="card-title text-white-50 mb-2 fw-semibold text-uppercase letter-spacing-1">Đang hoạt động</h6>
                                            <h2 class="mb-0 fw-bold"><?php echo $active_categories; ?></h2>
                                            <div class="progress mt-3">
                                                <?php 
                                                $active_percentage = $total_categories > 0 ? ($active_categories / $total_categories) * 100 : 0;
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
                    </div>

                    <!-- Phần tìm kiếm và lọc -->
                    <div class="search-section mb-4">
                        <form method="GET" id="searchForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex">
                                        <input type="text" name="search" class="form-control me-2" 
                                               placeholder="Tìm kiếm danh mục... (Ctrl+K)" 
                                               value="<?php echo htmlspecialchars($search_keyword); ?>"
                                               id="searchInput"
                                               title="Nhấn Ctrl+K để focus vào ô tìm kiếm">
                                        <button type="submit" class="btn btn-primary" id="searchBtn">
                                            <i class="iconoir-search me-2" id="searchIcon"></i> 
                                            <span id="searchText">Tìm kiếm</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end" style="display: flex; align-items: center; justify-content: flex-end;">
                                    <?php if(!empty($search_keyword)): ?>
                                        <a href="index.php" class="btn btn-outline-secondary quick-action" id="clearFilters">
                                            Xóa bộ lọc
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Nút tạo danh mục mới -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-success quick-action" onclick="openCreateCategoryModal()">
                                <i class="iconoir-plus me-2"></i> Thêm danh mục mới
                            </button>
                        </div>
                    </div>

                    <!-- Search Results Info -->
                    <?php if(!empty($search_keyword)): ?>
                        <div class="search-results-info">
                            <i class="iconoir-search"></i>
                            Tìm thấy <?php echo count($display_categories); ?> danh mục với từ khóa "<strong><?php echo htmlspecialchars($search_keyword); ?></strong>"
                        </div>
                    <?php endif; ?>

                    <!-- Danh sách danh mục -->
                    <div class="row content-fade" id="categoryList">
                        <?php if(empty($display_categories)): ?>
                            <div class="col-12">
                                <?php if(!empty($search_keyword)): ?>
                                    <div class="search-empty-state">
                                        <i class="iconoir-search"></i>
                                        <h5>Không tìm thấy danh mục nào</h5>
                                        <p>Không có danh mục nào khớp với từ khóa "<strong><?php echo htmlspecialchars($search_keyword); ?></strong>".</p>
                                    </div>
                                <?php else: ?>
                                    <div class="card empty-state">
                                        <div class="card-body text-center py-5">
                                            <h5 class="mt-3">Chưa có danh mục nào</h5>
                                            <button type="button" class="btn btn-primary mt-3 quick-action" onclick="openCreateCategoryModal()">
                                                <i class="iconoir-plus me-2"></i> Tạo danh mục đầu tiên
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach($display_categories as $cat): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card category-card h-100">
                                        <div class="card-body">
                                            <!-- Header với hình ảnh và tên danh mục -->
                                            <div class="d-flex align-items-start mb-3">
                                                <?php if(!empty($cat['image'])): ?>
                                                    <img src="../../<?php echo htmlspecialchars($cat['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                                                         class="category-image me-3">
                                                <?php else: ?>
                                                    <div class="category-image bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; border-radius: 10px; border: 2px solid #f8f9fa;">
                                                        <i class="iconoir-folder" style="font-size: 24px; color: #6c757d;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($cat['name']); ?></h5>
                                                    <small class="text-muted">Slug: <?php echo htmlspecialchars($cat['slug']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Mô tả danh mục -->
                                            <p class="card-text mb-3">
                                                <?php echo htmlspecialchars(substr($cat['description'], 0, 80)) . (strlen($cat['description']) > 80 ? '...' : ''); ?>
                                            </p>
                                            
                                            <!-- Thông tin cơ bản -->
                                            <div class="row text-center mb-3">
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Thứ tự</small>
                                                    <div class="fw-bold text-primary fs-6">
                                                        <?php echo $cat['sort_order']; ?>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Nhiệt độ</small>
                                                    <div>
                                                        <?php 
                                                        // Lấy temperature_zone từ vị trí kho thay vì temperature_type từ category
                                                        $product = new Product();
                                                        $zone = $product->getTemperatureZoneFromCategory($cat['id']);
                                                        
                                                        $temp_labels = [
                                                            'frozen' => ['Đông lạnh', 'bg-info-subtle text-info'],
                                                            'chilled' => ['Lạnh mát', 'bg-primary-subtle text-primary'],
                                                            'ambient' => ['Nhiệt độ phòng', 'bg-warning-subtle text-warning']
                                                        ];
                                                        $temp_info = $temp_labels[$zone] ?? $temp_labels['ambient'];
                                                        ?>
                                                        <span class="badge <?php echo $temp_info[1]; ?>">
                                                            <?php echo $temp_info[0]; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Độ ẩm</small>
                                                    <div>
                                                        <?php 
                                                        // Lấy temperature_zone từ vị trí kho (cùng zone cho cả nhiệt độ và độ ẩm)
                                                        $zone = $product->getTemperatureZoneFromCategory($cat['id']);
                                                        
                                                        $humidity_labels = [
                                                            'frozen' => ['Đông lạnh (85-95%)', 'bg-info-subtle text-info'],
                                                            'chilled' => ['Lạnh mát (85-90%)', 'bg-primary-subtle text-primary'],
                                                            'ambient' => ['Phòng (50-60%)', 'bg-warning-subtle text-warning']
                                                        ];
                                                        $humidity_info = $humidity_labels[$zone] ?? $humidity_labels['ambient'];
                                                        ?>
                                                        <span class="badge <?php echo $humidity_info[1]; ?>">
                                                            <?php echo $humidity_info[0]; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Thông tin nguy hiểm và trạng thái -->
                                            <div class="row text-center mb-3">
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Trạng thái</small>
                                                    <div>
                                                        <span class="badge <?php echo (isset($cat['is_active']) && $cat['is_active'] == 1) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                                                            <?php echo (isset($cat['is_active']) && $cat['is_active'] == 1) ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Nhiệt độ nguy hiểm</small>
                                                    <div>
                                                        <?php 
                                                        // Lấy thông tin nhiệt độ chi tiết từ model Product
                                                        $product = new Product();
                                                        $temp_info_detail = $product->getTemperatureInfoFromCategory($cat['id']);
                                                        
                                                        if ($temp_info_detail) {
                                                            $danger_min = isset($temp_info_detail['dangerous_min']) ? $temp_info_detail['dangerous_min'] : null;
                                                            $danger_max = $temp_info_detail['dangerous_max'] ?? 50.0;
                                                            
                                                            if ($danger_min !== null) {
                                                                echo '<span class="badge bg-danger-subtle text-danger">';
                                                                echo '< ' . $danger_min . '°C và > ' . $danger_max . '°C';
                                                                echo '</span>';
                                                            } else {
                                                                echo '<span class="badge bg-danger-subtle text-danger">';
                                                                echo '> ' . $danger_max . '°C';
                                                                echo '</span>';
                                                            }
                                                        } else {
                                                            echo '<span class="badge bg-secondary-subtle text-secondary">Chưa gán vị trí</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block mb-1">Độ ẩm nguy hiểm</small>
                                                    <div>
                                                        <?php 
                                                        // Lấy thông tin độ ẩm chi tiết từ model Product
                                                        $humidity_info_detail = $product->getHumidityInfoFromCategory($cat['id']);
                                                        
                                                        if ($humidity_info_detail) {
                                                            $humidity_danger_min = isset($humidity_info_detail['dangerous_min']) ? $humidity_info_detail['dangerous_min'] : null;
                                                            $humidity_danger_max = isset($humidity_info_detail['dangerous_max']) ? $humidity_info_detail['dangerous_max'] : null;
                                                            
                                                            if ($humidity_danger_min !== null && $humidity_danger_max !== null) {
                                                                echo '<span class="badge bg-danger-subtle text-danger">';
                                                                echo '< ' . $humidity_danger_min . '% và > ' . $humidity_danger_max . '%';
                                                                echo '</span>';
                                                            } elseif ($humidity_danger_min !== null) {
                                                                echo '<span class="badge bg-danger-subtle text-danger">';
                                                                echo '< ' . $humidity_danger_min . '%';
                                                                echo '</span>';
                                                            } else {
                                                                echo '<span class="badge bg-secondary-subtle text-secondary">Chưa gán vị trí</span>';
                                                            }
                                                        } else {
                                                            echo '<span class="badge bg-secondary-subtle text-secondary">Chưa gán vị trí</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Nút hành động -->
                                            <div class="d-flex justify-content-between mt-5">
                                                <button style="border: 1px solid black; color: black;" type="button" 
                                                        class="btn btn-outline-secondary"
                                                        data-id="<?php echo $cat['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                                        data-slug="<?php echo htmlspecialchars($cat['slug']); ?>"
                                                        data-description="<?php echo htmlspecialchars($cat['description']); ?>"
                                                        data-location-id="<?php echo $cat['location_id'] ?: ''; ?>"
                                                        data-sort-order="<?php echo (int)$cat['sort_order']; ?>"
                                                        data-is-active="<?php echo (int)$cat['is_active']; ?>"
                                                        data-image="<?php echo htmlspecialchars($cat['image']); ?>"
                                                        onclick="openViewCategoryFromButton(this)">
                                                    <i class="iconoir-eye"></i> Xem
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-primary"
                                                        data-id="<?php echo $cat['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                                        data-slug="<?php echo htmlspecialchars($cat['slug']); ?>"
                                                        data-description="<?php echo htmlspecialchars($cat['description']); ?>"
                                                        data-location-id="<?php echo $cat['location_id'] ?: ''; ?>"
                                                        data-sort-order="<?php echo (int)$cat['sort_order']; ?>"
                                                        data-is-active="<?php echo (int)$cat['is_active']; ?>"
                                                        data-image="<?php echo htmlspecialchars($cat['image']); ?>"
                                                        onclick="openEditCategoryFromButton(this)">
                                                    <i class="iconoir-edit"></i> Sửa
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')">
                                                    <i class="iconoir-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Footer với thời gian -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="iconoir-calendar"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($cat['created_at'])); ?>
                                                </small>
                                                <small class="text-muted">
                                                    <?php if (!empty($cat['location_id'])): ?>
                                                        Vị trí kho: <span class="badge bg-info-subtle text-info">ID <?php echo (int)$cat['location_id']; ?></span>
                                                    <?php else: ?>
                                                        Vị trí kho: <span class="badge bg-secondary-subtle text-secondary">Chưa gán</span>
                                                    <?php endif; ?>
                                                </small>
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
    
    <script>
        // Function xóa danh mục
        function deleteCategory(categoryId, categoryName) {
            const triggerBtn = (typeof event !== 'undefined' && event && event.target) ? event.target.closest('.btn-outline-danger') : null;
            const proceed = () => {
                // Hiển thị loading trên button
                const deleteBtn = triggerBtn;
                const originalText = deleteBtn ? deleteBtn.innerHTML : '';
                if (deleteBtn) { deleteBtn.disabled = true; deleteBtn.innerHTML = '<i class="iconoir-loading"></i> Đang xóa...'; }
                
                // Gọi API xóa danh mục
                fetch(`../../api/categories.php?id=${categoryId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hiển thị thông báo thành công
                        showDeleteCategorySuccessMessage();
                        
                        // Xóa card danh mục khỏi giao diện
                        const categoryCard = deleteBtn ? deleteBtn.closest('.col-lg-4') : null;
                        if (categoryCard) {
                            categoryCard.style.opacity = '0.5';
                            categoryCard.style.transform = 'scale(0.95)';
                        }
                        
                        setTimeout(() => {
                            if (categoryCard) categoryCard.remove();
                            
                            // Kiểm tra xem còn danh mục nào không
                            const remainingCategories = document.querySelectorAll('.category-card');
                            if (remainingCategories.length === 0) {
                                // Hiển thị trạng thái trống
                                const container = document.querySelector('.row');
                                container.innerHTML = `
                                    <div class="col-12 text-center py-5">
                                        <div class="empty-state">
                                            <i class="iconoir-folder" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                                            <h4 class="text-muted mb-3">Chưa có danh mục nào</h4>
                                            <p class="text-muted mb-4">Bắt đầu tạo danh mục đầu tiên để quản lý sản phẩm</p>
                                            <button type="button" class="btn btn-primary btn-lg" onclick="openCreateCategoryModal()">
                                                <i class="iconoir-plus"></i> Tạo danh mục đầu tiên
                                            </button>
                                        </div>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        throw new Error(data.message || 'Có lỗi xảy ra khi xóa danh mục');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Lỗi: ' + error.message, 'error');
                    
                    // Khôi phục button
                    if (deleteBtn) { deleteBtn.disabled = false; deleteBtn.innerHTML = originalText; }
                });
            };
            if (window.showConfirmToast) {
                window.showConfirmToast('warning', 'Bạn có chắc chắn muốn xóa danh mục?', `"${categoryName}" sẽ bị xóa vĩnh viễn.`, proceed);
            } else {
                if (confirm(`Bạn có chắc chắn muốn xóa danh mục "${categoryName}"?`)) proceed();
            }
        }
        
        // Function hiển thị notification
        function showNotification(message, type = 'info') {
            // Tạo notification element
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
            
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Thêm vào body
            document.body.appendChild(notification);
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }
        
        // Enhanced Search and Filter Functionality for Categories
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const searchBtn = document.getElementById('searchBtn');
            const searchIcon = document.getElementById('searchIcon');
            const searchText = document.getElementById('searchText');
            const categoryList = document.getElementById('categoryList');
            const clearFilters = document.getElementById('clearFilters');
            
            // Function to show loading animation
            function showLoading() {
                searchBtn.classList.add('loading');
                searchIcon.style.display = 'none';
                searchText.textContent = 'Đang tìm...';
                searchBtn.disabled = true;
                
                // Add fade out effect to category list
                categoryList.style.opacity = '0.5';
                categoryList.style.transform = 'translateY(10px)';
            }
            
            // Function to hide loading animation
            function hideLoading() {
                searchBtn.classList.remove('loading');
                searchIcon.style.display = 'inline';
                searchText.textContent = 'Tìm kiếm';
                searchBtn.disabled = false;
                
                // Add fade in effect to category list
                categoryList.style.opacity = '1';
                categoryList.style.transform = 'translateY(0)';
                categoryList.classList.add('content-fade');
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
            const searchKeyword = '<?php echo htmlspecialchars($search_keyword); ?>';
            if (searchKeyword) {
                highlightSearchResults(searchKeyword);
            }
        });
        
        // Function to highlight search results
        function highlightSearchResults(keyword) {
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                const title = card.querySelector('.card-title');
                const description = card.querySelector('.card-text');
                
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
                    <li>Tìm theo tên danh mục</li>
                    <li>Tìm theo slug</li>
                    <li>Tìm theo mô tả</li>
                    <li>Sử dụng từ khóa ngắn gọn</li>
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
    </script>
    
    <!-- Include Widgets Modal -->
    <?php include '../../assets/widgets/create-category.php'; ?>
    <?php include '../../assets/widgets/edit-category.php'; ?>
    <?php include '../../assets/widgets/view-category.php'; ?>
    
    <!-- Include Unified Widgets JavaScript -->
    <script src="../../assets/js/widget.js"></script>

    <!-- Delete Success Message - Fixed Position -->
    <div id="successMessageDeleteCategory" class="success-alert-fixed delete-success">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Danh mục đã được xóa thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideDeleteCategorySuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</body>
</html>
