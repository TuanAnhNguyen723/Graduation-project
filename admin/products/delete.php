<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/Product.php';

$product = new Product();

// Kiểm tra ID sản phẩm
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Lấy thông tin sản phẩm
$product_data = $product->getById($product_id);
if(!$product_data) {
    header('Location: index.php');
    exit;
}

$message = '';
$message_type = '';

// Xử lý xác nhận xóa
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Xóa sản phẩm
        if($product->delete($product_id)) {
            $message = 'Xóa sản phẩm thành công!';
            $message_type = 'success';
            
            // Chuyển hướng sau 2 giây
            header("refresh:2;url=index.php");
        } else {
            throw new Exception('Có lỗi xảy ra khi xóa sản phẩm');
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
    <title>Xóa sản phẩm | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Xóa sản phẩm" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <style>
        .breadcrumb-nav {
            background: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .delete-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Quản lý sản phẩm</a></li>
                    <li class="breadcrumb-item active">Xóa sản phẩm</li>
                </ol>
            </nav>
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
                        <h5 class="mb-0 fw-semibold text-truncate">Xóa sản phẩm</h5>
                    </li>                   
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="iconoir-arrow-left"></i> Quay lại
                        </a>
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
                                        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="index.php">Sản phẩm</a></li>
                                        <li class="breadcrumb-item active">Xóa</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Xóa sản phẩm</h4>
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
                    <!-- Thông báo -->
                    <?php if(!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        
                        <?php if($message_type === 'success'): ?>
                            <div class="text-center">
                                <p class="text-muted">Đang chuyển hướng về trang quản lý sản phẩm...</p>
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Cảnh báo xóa -->
                        <div class="delete-warning">
                            <div class="d-flex align-items-center mb-3">
                                <i class="iconoir-warning-triangle text-warning" style="font-size: 24px; margin-right: 10px;"></i>
                                <h5 class="mb-0 text-warning">Cảnh báo: Hành động không thể hoàn tác!</h5>
                            </div>
                            <p class="mb-0">Bạn đang chuẩn bị xóa sản phẩm này. Hành động này sẽ xóa vĩnh viễn sản phẩm khỏi hệ thống và không thể khôi phục lại.</p>
                        </div>

                        <!-- Thông tin sản phẩm cần xóa -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title">Thông tin sản phẩm cần xóa</h4>
                                        
                                        <div class="product-info">
                                            <div class="row">
                                                <div class="col-md-3 text-center">
                                                    <?php if(!empty($product_data['images'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product_data['images']); ?>" 
                                                             alt="<?php echo htmlspecialchars($product_data['name']); ?>" 
                                                             class="product-image mb-3">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center mb-3">
                                                            <i class="iconoir-image" style="font-size: 48px; color: #dee2e6;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-9">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <td width="150"><strong>ID:</strong></td>
                                                            <td><?php echo $product_data['id']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Tên sản phẩm:</strong></td>
                                                            <td><?php echo htmlspecialchars($product_data['name']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>SKU:</strong></td>
                                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($product_data['sku']); ?></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Danh mục:</strong></td>
                                                            <td>
                                                                <span class="badge bg-info-subtle text-info">
                                                                    <?php echo htmlspecialchars($product_data['category_name'] ?? 'Chưa phân loại'); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Giá:</strong></td>
                                                            <td>
                                                                <h6 class="mb-1"><?php echo number_format($product_data['price'], 0, ',', '.'); ?> ₫</h6>
                                                                <?php if($product_data['sale_price'] && $product_data['sale_price'] < $product_data['price']): ?>
                                                                    <span class="text-danger">
                                                                        <?php echo number_format($product_data['sale_price'], 0, ',', '.'); ?> ₫
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Tồn kho:</strong></td>
                                                            <td>
                                                                <span class="badge <?php echo $product_data['stock_quantity'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo $product_data['stock_quantity']; ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Trạng thái:</strong></td>
                                                            <td>
                                                                <span class="badge <?php echo $product_data['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo $product_data['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Ngày tạo:</strong></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($product_data['created_at'])); ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Form xác nhận xóa -->
                                        <form method="POST" action="" onsubmit="return confirmDelete()">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between">
                                                        <a href="index.php" class="btn btn-outline-secondary">
                                                            <i class="iconoir-arrow-left"></i> Hủy bỏ
                                                        </a>
                                                        <button type="submit" name="confirm_delete" class="btn btn-danger btn-lg">
                                                            <i class="iconoir-trash"></i> Xác nhận xóa sản phẩm
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/js/app.js"></script>
    <script>
        function confirmDelete() {
            return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác!');
        }
    </script>
</body>
</html>
