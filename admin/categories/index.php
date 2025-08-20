<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Category.php';

$category = new Category();

// Lấy danh sách danh mục
$categories_result = $category->getAll();
$categories = [];
while($row = $categories_result->fetch()) {
    $categories[] = $row;
}

// Lấy thống kê
$total_categories = $category->count();
$parent_categories = $category->getParentCategories();
$parent_count = $parent_categories->rowCount();

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
    
    <style>
        .category-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        
        .category-card .card-title {
            color: #2c3e50;
            font-size: 1.1rem;
            line-height: 1.3;
        }
        
        .category-card .card-text {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .category-card .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
        
        .category-card .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .category-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
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
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-folder text-primary" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-0 fw-bold text-primary"><?php echo $total_categories; ?></h3>
                                            <p class="mb-0 text-muted small">Tổng số danh mục</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-folder-plus text-success" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-0 fw-bold text-success"><?php echo $parent_count; ?></h3>
                                            <p class="mb-0 text-muted small">Danh mục cha</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $parent_percentage = $total_categories > 0 ? ($parent_count / $total_categories) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $parent_percentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="iconoir-folder-minus text-info" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-0 fw-bold text-info"><?php echo $total_categories - $parent_count; ?></h3>
                                            <p class="mb-0 text-muted small">Danh mục con</p>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $child_percentage = $total_categories > 0 ? (($total_categories - $parent_count) / $total_categories) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-info" style="width: <?php echo $child_percentage; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần tìm kiếm -->
                    <div class="search-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <form method="GET" action="" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" 
                                           placeholder="Tìm kiếm danh mục theo tên hoặc mô tả..." 
                                           value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="iconoir-search"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="iconoir-close"></i> Xóa bộ lọc
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Nút tạo danh mục mới -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="create.php" class="btn btn-success">
                                <i class="iconoir-plus"></i> Thêm danh mục mới
                            </a>
                        </div>
                    </div>

                    <!-- Danh sách danh mục -->
                    <div class="row">
                        <?php if(empty($display_categories)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="iconoir-folder" style="font-size: 64px; color: #dee2e6;"></i>
                                        <h5 class="mt-3 text-muted">
                                            <?php if(!empty($search_keyword)): ?>
                                                Không tìm thấy danh mục nào với từ khóa "<?php echo htmlspecialchars($search_keyword); ?>"
                                            <?php else: ?>
                                                Chưa có danh mục nào
                                            <?php endif; ?>
                                        </h5>
                                        <?php if(empty($search_keyword)): ?>
                                            <a href="create.php" class="btn btn-primary mt-3">
                                                <i class="iconoir-plus"></i> Tạo danh mục đầu tiên
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach($display_categories as $cat): ?>
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div class="card category-card h-100 border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <!-- Header với hình ảnh và tên danh mục -->
                                            <div class="d-flex align-items-start mb-3">
                                                <?php if(!empty($cat['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($cat['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($cat['name']); ?>" 
                                                         class="category-image me-3">
                                                <?php else: ?>
                                                    <div class="category-image bg-light d-flex align-items-center justify-content-center me-3">
                                                        <i class="iconoir-folder" style="font-size: 24px; color: #6c757d;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1 fw-bold"><?php echo htmlspecialchars($cat['name']); ?></h5>
                                                    <small class="text-muted">Slug: <?php echo htmlspecialchars($cat['slug']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Mô tả danh mục -->
                                            <p class="card-text text-muted mb-3">
                                                <?php echo htmlspecialchars(substr($cat['description'], 0, 80)) . (strlen($cat['description']) > 80 ? '...' : ''); ?>
                                            </p>
                                            
                                            <!-- Thông tin thứ tự và trạng thái -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Thứ tự</small>
                                                    <div class="fw-bold text-primary fs-6">
                                                        <?php echo $cat['sort_order']; ?>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Trạng thái</small>
                                                    <div>
                                                        <span class="badge <?php echo $cat['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                            <?php echo $cat['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Nút hành động -->
                                            <div class="d-flex justify-content-between mb-3">
                                                <a href="edit.php?id=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="iconoir-edit"></i> Sửa
                                                </a>
                                                <a href="delete.php?id=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                    <i class="iconoir-trash"></i> Xóa
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Footer với thời gian -->
                                        <div class="card-footer bg-transparent border-top">
                                            <div class="d-flex justify-content-center">
                                                <small class="text-muted">
                                                    <i class="iconoir-calendar"></i> 
                                                    <?php echo date('d/m/Y H:i', strtotime($cat['created_at'])); ?>
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
</body>
</html>
