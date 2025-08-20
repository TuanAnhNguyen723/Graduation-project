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
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .product-card .card-title {
            color: #2c3e50;
            font-size: 1.1rem;
            line-height: 1.3;
        }
        
        .product-card .card-text {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .product-card .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
        
        .product-card .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
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
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-shopping-bag text-primary" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-0 fw-bold text-primary"><?php echo count($products); ?></h3>
                                            <p class="mb-0 text-muted small">Tổng sản phẩm</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-check-circle text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <?php 
                                            $active_products = count(array_filter($products, function($p) { 
                                                return isset($p['is_active']) && $p['is_active'] == 1; 
                                            }));
                                            ?>
                                            <h3 class="mb-0 fw-bold text-success"><?php echo $active_products; ?></h3>
                                            <p class="mb-0 text-muted small">Đang hoạt động</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $active_percentage = count($products) > 0 ? ($active_products / count($products)) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $active_percentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-warning-triangle text-warning" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <?php 
                                            $low_stock_products = count(array_filter($products, function($p) { 
                                                return $p['stock_quantity'] <= 10; 
                                            }));
                                            ?>
                                            <h3 class="mb-0 fw-bold text-warning"><?php echo $low_stock_products; ?></h3>
                                            <p class="mb-0 text-muted small">Sắp hết hàng</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $low_stock_percentage = count($products) > 0 ? ($low_stock_products / count($products)) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-warning" style="width: <?php echo $low_stock_percentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-folder text-info" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-0 fw-bold text-info"><?php echo count($categories); ?></h3>
                                            <p class="mb-0 text-muted small">Danh mục</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần tìm kiếm và lọc -->
                    <div class="search-section">
                        <div class="row">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" 
                                   placeholder="Tìm kiếm sản phẩm..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                <i class="iconoir-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                                <?php if(!empty($search) || !empty($category_filter)): ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="iconoir-close"></i> Xóa bộ lọc
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Nút tạo sản phẩm mới -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="create.php" class="btn btn-success">
                                <i class="iconoir-plus"></i> Thêm sản phẩm mới
                            </a>
                    </div>
                </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="row">
                        <?php if(empty($products)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="iconoir-shopping-bag" style="font-size: 64px; color: #dee2e6;"></i>
                                        <h5 class="mt-3 text-muted">
                                            <?php if(!empty($search) || !empty($category_filter)): ?>
                                                Không tìm thấy sản phẩm nào với bộ lọc hiện tại
                                            <?php else: ?>
                                                Chưa có sản phẩm nào
                                            <?php endif; ?>
                                        </h5>
                                        <?php if(empty($search) && empty($category_filter)): ?>
                                            <a href="create.php" class="btn btn-primary mt-3">
                                                <i class="iconoir-plus"></i> Tạo sản phẩm đầu tiên
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach($products as $prod): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100 border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <!-- Header với hình ảnh và tên sản phẩm -->
                                            <div class="d-flex align-items-start mb-3">
                                                <?php if(!empty($prod['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($prod['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                         class="product-image me-3">
                                            <?php else: ?>
                                                    <div class="product-image bg-light d-flex align-items-center justify-content-center me-3">
                                                        <i class="iconoir-image" style="font-size: 24px; color: #6c757d;"></i>
                                                </div>
                                            <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1 fw-bold"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($prod['sku']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Mô tả sản phẩm -->
                                            <p class="card-text text-muted mb-3">
                                                <?php echo htmlspecialchars(substr($prod['description'], 0, 80)) . (strlen($prod['description']) > 80 ? '...' : ''); ?>
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
                                            <div>
                                                        <span class="badge <?php echo $prod['stock_quantity'] > 10 ? 'bg-success-subtle text-success' : ($prod['stock_quantity'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'); ?>">
                                                            Tồn kho <?php echo $prod['stock_quantity']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Danh mục -->
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Danh mục:</small>
                                                <div>
                                            <?php 
                                            $cat_name = 'N/A';
                                            foreach ($categories as $cat) {
                                                if ($cat['id'] == $prod['category_id']) {
                                                    $cat_name = $cat['name'];
                                                    break;
                                                }
                                            }
                                                    ?>
                                                    <span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($cat_name); ?></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Nút hành động -->
                                            <div class="d-flex justify-content-between mb-3">
                                                <a href="edit.php?id=<?php echo $prod['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="iconoir-edit"></i> Sửa
                                                </a>
                                                <a href="delete.php?id=<?php echo $prod['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                    <i class="iconoir-trash"></i> Xóa
                                                </a>
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
</body>
</html>
