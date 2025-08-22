<?php
/**
 * API Products - Quản lý sản phẩm
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../models/Product.php';

$product = new Product();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if(isset($_GET['id'])) {
                // Lấy sản phẩm theo ID
                $result = $product->getById($_GET['id']);
                if($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success => false',
                        'message' => 'Không tìm thấy sản phẩm'
                    ]);
                }
            } elseif(isset($_GET['slug'])) {
                // Lấy sản phẩm theo slug
                $result = $product->getBySlug($_GET['slug']);
                if($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success => false',
                        'message' => 'Không tìm thấy sản phẩm'
                    ]);
                }
            } elseif(isset($_GET['category'])) {
                // Lấy sản phẩm theo danh mục
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;
                
                $result = $product->getByCategory($_GET['category'], $limit, $offset);
                $products = [];
                while($row = $result->fetch()) {
                    $products[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $products,
                    'total' => count($products)
                ]);
            } elseif(isset($_GET['search'])) {
                // Tìm kiếm sản phẩm
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;
                
                $result = $product->search($_GET['search'], $limit, $offset);
                $products = [];
                while($row = $result->fetch()) {
                    $products[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $products,
                    'total' => count($products)
                ]);
            } elseif(isset($_GET['featured'])) {
                // Lấy sản phẩm nổi bật
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
                $result = $product->getFeaturedProducts($limit);
                $products = [];
                while($row = $result->fetch()) {
                    $products[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $products
                ]);
            } elseif(isset($_GET['price_range'])) {
                // Lấy sản phẩm theo khoảng giá
                if(isset($_GET['min_price']) && isset($_GET['max_price'])) {
                    $min_price = (float)$_GET['min_price'];
                    $max_price = (float)$_GET['max_price'];
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                    
                    $result = $product->getByPriceRange($min_price, $max_price, $limit);
                    $products = [];
                    while($row = $result->fetch()) {
                        $products[] = $row;
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $products
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success => false',
                        'message' => 'min_price và max_price không được để trống'
                    ]);
                }
            } else {
                // Lấy tất cả sản phẩm
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : null;
                
                $result = $product->getAll($limit, $offset);
                $products = [];
                while($row = $result->fetch()) {
                    $products[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $products,
                    'total' => count($products)
                ]);
            }
            break;

        case 'POST':
            // Tạo sản phẩm mới hoặc cập nhật sản phẩm
            // Xử lý cả JSON và FormData
            $data = [];
            if (isset($_POST) && !empty($_POST)) {
                // FormData từ widget
                $data = $_POST;
            } else {
                // JSON data
                $data = json_decode(file_get_contents("php://input"), true);
            }
            
            if(!isset($data['name']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Tên sản phẩm không được để trống'
                ]);
                break;
            }
            
            // Kiểm tra xem có phải cập nhật không
            if(isset($data['id']) && !empty($data['id'])) {
                // Cập nhật sản phẩm
                $product_id = (int)$data['id'];
                
                // Lấy thông tin sản phẩm hiện tại
                $current_product = $product->getById($product_id);
                if(!$current_product) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy sản phẩm cần cập nhật'
                    ]);
                    break;
                }
                
                // Kiểm tra SKU đã tồn tại chưa (trừ chính nó)
                if(isset($data['sku']) && !empty($data['sku'])) {
                    if($product->skuExists($data['sku'], $product_id)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'SKU đã tồn tại'
                        ]);
                        break;
                    }
                }
                
                // Xử lý upload hình ảnh mới (nếu có)
                $image_path = $current_product['images']; // Giữ ảnh cũ
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../assets/images/products/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Xóa ảnh cũ nếu có
                            if(!empty($current_product['images']) && file_exists('../' . $current_product['images'])) {
                                unlink('../' . $current_product['images']);
                            }
                            $image_path = 'assets/images/products/' . $filename;
                        }
                    }
                }

                // Cập nhật sản phẩm
                $product->id = $product_id;
                $product->name = $data['name'];
                $product->slug = isset($data['slug']) ? $data['slug'] : $product->createSlug($data['name']);
                $product->description = isset($data['description']) ? $data['description'] : '';
                $product->sku = isset($data['sku']) ? $data['sku'] : $current_product['sku'];
                $product->price = isset($data['price']) ? (float)$data['price'] : 0;
                $product->sale_price = isset($data['sale_price']) ? (float)$data['sale_price'] : null;
                $product->stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
                $product->category_id = isset($data['category_id']) ? (int)$data['category_id'] : null;
                $product->brand = isset($data['brand']) ? $data['brand'] : '';
                $product->images = $image_path;
                $product->is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

                if($product->update()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cập nhật sản phẩm thành công',
                        'data' => ['id' => $product_id]
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể cập nhật sản phẩm'
                    ]);
                }
                break;
            }
            
            // Tạo sản phẩm mới

            if(!isset($data['sku']) || empty($data['sku'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'SKU không được để trống'
                ]);
                break;
            }

            // Kiểm tra SKU đã tồn tại chưa
            if($product->skuExists($data['sku'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'SKU đã tồn tại'
                ]);
                break;
            }

            // Xử lý upload hình ảnh
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $filename = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = 'assets/images/products/' . $filename;
                    }
                }
            }

            $product->name = $data['name'];
            $product->slug = isset($data['slug']) ? $data['slug'] : $product->createSlug($data['name']);
            $product->description = isset($data['description']) ? $data['description'] : '';
            $product->sku = $data['sku'];
            $product->price = isset($data['price']) ? (float)$data['price'] : 0;
            $product->sale_price = isset($data['sale_price']) ? (float)$data['sale_price'] : null;
            $product->stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $product->category_id = isset($data['category_id']) ? (int)$data['category_id'] : null;
            $product->brand = isset($data['brand']) ? $data['brand'] : '';
            $product->images = $image_path; // Sử dụng images như trong model
            $product->is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            $id = $product->create();
            if($id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tạo sản phẩm thành công',
                    'data' => ['id' => $id]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể tạo sản phẩm'
                ]);
            }
            break;

        case 'PUT':
            // Cập nhật sản phẩm
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(!isset($data['id']) || !isset($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success => false',
                    'message' => 'ID và tên sản phẩm không được để trống'
                ]);
                break;
            }

            // Kiểm tra SKU đã tồn tại chưa (nếu thay đổi)
            if(isset($data['sku']) && $product->skuExists($data['sku'], $data['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success => false',
                    'message' => 'SKU đã tồn tại'
                ]);
                break;
            }

            $product->id = $data['id'];
            $product->name = $data['name'];
            $product->slug = isset($data['slug']) ? $data['slug'] : $product->createSlug($data['name']);
            $product->description = isset($data['description']) ? $data['description'] : '';
            $product->sku = isset($data['sku']) ? $data['sku'] : '';
            $product->price = isset($data['price']) ? (float)$data['price'] : 0;
            $product->sale_price = isset($data['sale_price']) ? (float)$data['sale_price'] : null;
            $product->stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $product->category_id = isset($data['category_id']) ? (int)$data['category_id'] : null;
            $product->brand = isset($data['brand']) ? $data['brand'] : '';
            $product->images = isset($data['images']) ? $data['images'] : '';
            $product->is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            if($product->update()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật sản phẩm thành công'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success => false',
                    'message' => 'Không thể cập nhật sản phẩm'
                ]);
            }
            break;

        case 'DELETE':
            // Xóa sản phẩm
            if(isset($_GET['id'])) {
                if($product->delete($_GET['id'])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Xóa sản phẩm thành công'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa sản phẩm'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID sản phẩm không được để trống'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success => false',
                'message' => 'Method không được hỗ trợ'
            ]);
            break;
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success => false',
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
