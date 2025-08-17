<?php
require_once 'models/Category.php';

$category = new Category();

// Lấy danh sách danh mục
$categories_result = $category->getAll();
$categories = [];
while($row = $categories_result->fetch()) {
    $categories[] = $row;
}

// Xử lý tạo danh mục mới
$message = '';
$message_type = '';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_category') {
    try {
        $category->name = $_POST['name'];
        $category->description = $_POST['description'];
        $category->parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $category->sort_order = (int)$_POST['sort_order'];
        $category->is_active = 1;
        
        if($category->create()) {
            $message = 'Tạo danh mục thành công!';
            $message_type = 'success';
            header('Location: ecommerce-categories.php');
            exit;
        } else {
            $message = 'Không thể tạo danh mục!';
            $message_type = 'error';
        }
    } catch(Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Xử lý xóa danh mục
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if($category->delete($delete_id)) {
        $message = 'Xóa danh mục thành công!';
        $message_type = 'success';
        header('Location: ecommerce-categories.php');
        exit;
    } else {
        $message = 'Không thể xóa danh mục! Có thể danh mục đang chứa sản phẩm.';
        $message_type = 'error';
    }
}

// Lấy danh mục cha cho dropdown
$parent_categories = [];
$parent_result = $category->getParentCategories();
while($row = $parent_result->fetch()) {
    $parent_categories[] = $row;
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
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <style>
        .category-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .category-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .category-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .category-description {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .category-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #6c757d;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .hierarchy-indicator {
            color: #007bff;
            font-size: 12px;
        }
    </style>
</head>

<body>
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
                        <h5 class="mb-0 fw-semibold text-truncate">Quản lý danh mục</h5>
                    </li>                   
                </ul>
            </nav>
        </div>
    </div>

    <div class="page-wrapper-img">
        <div class="page-wrapper-img-inner">
            <div class="sidebar-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="#">E-commerce</a></li>
                                        <li class="breadcrumb-item active">Danh mục</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Quản lý danh mục sản phẩm</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="page-wrapper-inner">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Hiển thị thông báo -->
                    <?php if(!empty($message)): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nút tạo danh mục mới -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                <i class="iconoir-plus"></i> Thêm danh mục mới
                            </button>
                        </div>
                    </div>

                    <!-- Thống kê -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Tổng danh mục</h5>
                                    <h3><?php echo count($categories); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Danh mục cha</h5>
                                    <h3><?php echo count($parent_categories); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Danh mục con</h5>
                                    <h3><?php echo count($categories) - count($parent_categories); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Danh mục hoạt động</h5>
                                    <h3><?php echo count(array_filter($categories, function($cat) { return $cat['is_active']; })); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách danh mục -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Danh sách danh mục</h4>
                                    
                                    <?php if(empty($categories)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="iconoir-folder" style="font-size: 48px; color: #dee2e6;"></i>
                                            <h5 class="mt-3">Chưa có danh mục nào</h5>
                                            <p>Hãy tạo danh mục đầu tiên để bắt đầu quản lý sản phẩm</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach($categories as $cat): ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="category-item">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="category-name">
                                                                <?php echo htmlspecialchars($cat['name']); ?>
                                                                <?php if($cat['parent_id']): ?>
                                                                    <span class="hierarchy-indicator">
                                                                        <i class="iconoir-subdirectory-right"></i>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                        onclick="editCategory(<?php echo $cat['id']; ?>)">
                                                                    <i class="iconoir-edit"></i>
                                                                </button>
                                                                <a href="?delete=<?php echo $cat['id']; ?>" 
                                                                   class="btn btn-outline-danger btn-sm"
                                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                                    <i class="iconoir-trash"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if(!empty($cat['description'])): ?>
                                                            <div class="category-description">
                                                                <?php echo htmlspecialchars($cat['description']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="category-meta">
                                                            <span>
                                                                <i class="iconoir-hash"></i> ID: <?php echo $cat['id']; ?>
                                                            </span>
                                                            <span>
                                                                <i class="iconoir-sort"></i> Thứ tự: <?php echo $cat['sort_order']; ?>
                                                            </span>
                                                            <span class="<?php echo $cat['is_active'] ? 'text-success' : 'text-danger'; ?>">
                                                                <i class="iconoir-circle"></i> 
                                                                <?php echo $cat['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <?php if($cat['parent_id']): ?>
                                                            <?php 
                                                            $parent_name = 'Không xác định';
                                                            foreach($categories as $parent_cat) {
                                                                if($parent_cat['id'] == $cat['parent_id']) {
                                                                    $parent_name = $parent_cat['name'];
                                                                    break;
                                                                }
                                                            }
                                                            ?>
                                                            <div class="mt-2">
                                                                <small class="text-muted">
                                                                    <i class="iconoir-arrow-up"></i> 
                                                                    Danh mục cha: <?php echo htmlspecialchars($parent_name); ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tạo danh mục mới -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">Thêm danh mục mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_category">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên danh mục *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Danh mục cha</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">Không có (danh mục gốc)</option>
                                        <?php foreach($parent_categories as $parent_cat): ?>
                                            <option value="<?php echo $parent_cat['id']; ?>">
                                                <?php echo htmlspecialchars($parent_cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Thứ tự sắp xếp</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script>
        // Hàm chỉnh sửa danh mục
        function editCategory(id) {
            // TODO: Implement edit functionality
            alert('Chức năng chỉnh sửa danh mục ID: ' + id + ' sẽ được phát triển sau');
        }

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
