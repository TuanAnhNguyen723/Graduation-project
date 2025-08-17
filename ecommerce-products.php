<?php
require_once 'models/Product.php';
require_once 'models/Category.php';

$product = new Product();
$category = new Category();

// Lấy danh sách sản phẩm
$products_result = $product->getAll();
$products = [];
while($row = $products_result->fetch()) {
    $products[] = $row;
}

// Lấy danh sách danh mục
$categories_result = $category->getAll();
$categories = [];
while($row = $categories_result->fetch()) {
    $categories[] = $row;
}

// Xử lý tìm kiếm
$search_results = [];
$search_keyword = '';
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_keyword = $_GET['search'];
    $search_result = $product->search($search_keyword);
    while($row = $search_result->fetch()) {
        $search_results[] = $row;
    }
}

// Xử lý lọc theo danh mục
$filtered_products = [];
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = $_GET['category'];
    $filtered_result = $product->getByCategory($category_id);
    while($row = $filtered_result->fetch()) {
        $filtered_products[] = $row;
    }
}

// Xử lý tạo sản phẩm mới
$message = '';
$message_type = '';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    try {
        $product->name = $_POST['name'];
        $product->sku = $_POST['sku'];
        $product->description = $_POST['description'];
        $product->price = (float)$_POST['price'];
        $product->sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $product->stock_quantity = (int)$_POST['stock_quantity'];
        $product->category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $product->brand = $_POST['brand'];
        $product->is_active = 1;
        
        if($product->create()) {
            $message = 'Tạo sản phẩm thành công!';
            $message_type = 'success';
            // Refresh trang để hiển thị sản phẩm mới
            header('Location: ecommerce-products.php');
            exit;
        } else {
            $message = 'Không thể tạo sản phẩm!';
            $message_type = 'error';
        }
    } catch(Exception $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Xử lý xóa sản phẩm
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if($product->delete($delete_id)) {
        $message = 'Xóa sản phẩm thành công!';
        $message_type = 'success';
        header('Location: ecommerce-products.php');
        exit;
    } else {
        $message = 'Không thể xóa sản phẩm!';
        $message_type = 'error';
    }
}

// Xác định danh sách sản phẩm để hiển thị
$display_products = $products;
if(!empty($search_results)) {
    $display_products = $search_results;
} elseif(!empty($filtered_products)) {
    $display_products = $filtered_products;
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý sản phẩm | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý sản phẩm E-commerce" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <!-- DataTables CSS -->
    <link href="assets/libs/simple-datatables/style.css" rel="stylesheet" type="text/css" />
    
    <style>
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
                        <h5 class="mb-0 fw-semibold text-truncate">Quản lý sản phẩm</h5>
                    </li>                   
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li class="hide-phone app-search">
                        <form role="search" method="GET" action="">
                            <input type="search" name="search" class="form-control top-search mb-0" 
                                   placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                            <button type="submit"><i class="iconoir-search"></i></button>
                        </form>
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
                                        <li class="breadcrumb-item active">Sản phẩm</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Quản lý sản phẩm</h4>
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

                    <!-- Phần tìm kiếm và lọc -->
                    <div class="search-section">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="GET" action="" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" 
                                           placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" action="" class="d-flex">
                                    <select name="category" class="form-select me-2">
                                        <option value="">Tất cả danh mục</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-secondary">Lọc</button>
                                    <?php if(isset($_GET['category']) || isset($_GET['search'])): ?>
                                        <a href="ecommerce-products.php" class="btn btn-outline-secondary ms-2">Xóa bộ lọc</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Nút tạo sản phẩm mới -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                                <i class="iconoir-plus"></i> Thêm sản phẩm mới
                            </button>
                        </div>
                    </div>

                    <!-- Bảng sản phẩm -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Danh sách sản phẩm</h4>
                                    <p class="text-muted font-14">
                                        Tổng cộng: <strong><?php echo count($display_products); ?></strong> sản phẩm
                                    </p>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Hình ảnh</th>
                                                    <th>Tên sản phẩm</th>
                                                    <th>SKU</th>
                                                    <th>Danh mục</th>
                                                    <th>Giá</th>
                                                    <th>Tồn kho</th>
                                                    <th>Trạng thái</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(empty($display_products)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">
                                                            <?php if(!empty($search_keyword)): ?>
                                                                Không tìm thấy sản phẩm nào với từ khóa "<?php echo htmlspecialchars($search_keyword); ?>"
                                                            <?php elseif(!empty($_GET['category'])): ?>
                                                                Không có sản phẩm nào trong danh mục này
                                                            <?php else: ?>
                                                                Chưa có sản phẩm nào
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach($display_products as $prod): ?>
                                                        <tr>
                                                            <td><?php echo $prod['id']; ?></td>
                                                            <td>
                                                                <?php if(!empty($prod['images'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($prod['images']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                                                                         class="product-image">
                                                                <?php else: ?>
                                                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="iconoir-image"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <h5 class="font-14 mb-1 fw-normal"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                                                <span class="text-muted font-13"><?php echo htmlspecialchars($prod['brand']); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($prod['sku']); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info-subtle text-info">
                                                                    <?php echo htmlspecialchars($prod['category_name'] ?? 'Chưa phân loại'); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <h5 class="font-14 mb-1 fw-normal">
                                                                    <?php echo number_format($prod['price'], 0, ',', '.'); ?> ₫
                                                                </h5>
                                                                <?php if($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
                                                                    <span class="text-danger font-13">
                                                                        <?php echo number_format($prod['sale_price'], 0, ',', '.'); ?> ₫
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge <?php echo $prod['stock_quantity'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo $prod['stock_quantity']; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge <?php echo $prod['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo $prod['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                            onclick="editProduct(<?php echo $prod['id']; ?>)">
                                                                        <i class="iconoir-edit"></i>
                                                                    </button>
                                                                    <a href="?delete=<?php echo $prod['id']; ?>" 
                                                                       class="btn btn-sm btn-outline-danger"
                                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                                        <i class="iconoir-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tạo sản phẩm mới -->
    <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProductModalLabel">Thêm sản phẩm mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_product">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU *</label>
                                    <input type="text" class="form-control" id="sku" name="sku" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá gốc *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Thương hiệu</label>
                                    <input type="text" class="form-control" id="brand" name="brand">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
    <script src="assets/libs/simple-datatables/simple-datatables.js"></script>
    <script>
        // Khởi tạo DataTable
        document.addEventListener('DOMContentLoaded', function() {
            const table = new simpleDatatables.DataTable('.table', {
                searchable: true,
                fixedHeight: true,
                perPage: 10
            });
        });

        // Hàm chỉnh sửa sản phẩm
        function editProduct(id) {
            // TODO: Implement edit functionality
            alert('Chức năng chỉnh sửa sản phẩm ID: ' + id + ' sẽ được phát triển sau');
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
