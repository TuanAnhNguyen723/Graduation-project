<?php
/**
 * API Categories - Quản lý danh mục sản phẩm
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../models/Category.php';
require_once '../config/database.php';

$category = new Category();
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Tạo thông báo khi thêm danh mục mới
 */
function createCategoryNotification($category_id, $category_name, $temperature_type) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $title = "Danh mục mới được thêm";
        $message = "Đã thêm danh mục mới \"{$category_name}\" (Loại nhiệt độ: {$temperature_type}) vào hệ thống.";
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type) 
            VALUES (?, ?, 'product', 'iconoir-folder', 'success', ?, 'category')
        ");
        
        $stmt->execute([$title, $message, $category_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error creating category notification: " . $e->getMessage());
        return false;
    }
}

try {
    switch($method) {
        case 'GET':
            if(isset($_GET['id'])) {
                // Lấy danh mục theo ID
                $result = $category->getById($_GET['id']);
                if($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy danh mục'
                    ]);
                }
            } elseif(isset($_GET['slug'])) {
                // Lấy danh mục theo slug
                $result = $category->getBySlug($_GET['slug']);
                if($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy danh mục'
                    ]);
                }
            } elseif(isset($_GET['parent'])) {
                // Lấy danh mục cha
                $result = $category->getParentCategories();
                $categories = [];
                while($row = $result->fetch()) {
                    $categories[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $categories
                ]);
            } elseif(isset($_GET['sub'])) {
                // Lấy danh mục con
                $result = $category->getSubCategories($_GET['sub']);
                $categories = [];
                while($row = $result->fetch()) {
                    $categories[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $categories
                ]);
            } elseif(isset($_GET['with_products'])) {
                // Lấy danh mục có số lượng sản phẩm
                $result = $category->getCategoriesWithProducts();
                $categories = [];
                while($row = $result->fetch()) {
                    $categories[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $categories
                ]);
            } else {
                // Lấy tất cả danh mục
                $result = $category->getAll();
                $categories = [];
                while($row = $result->fetch()) {
                    $categories[] = $row;
                }
                echo json_encode([
                    'success' => true,
                    'data' => $categories,
                    'total' => count($categories)
                ]);
            }
            break;

        case 'POST':
            // Tạo danh mục mới hoặc cập nhật danh mục
            $data = $_POST;
            
            if(!isset($data['name']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Tên danh mục không được để trống'
                ]);
                break;
            }
            
            // Kiểm tra xem có phải cập nhật không
            if(isset($data['id']) && !empty($data['id'])) {
                // Cập nhật danh mục
                $category_id = (int)$data['id'];
                
                // Lấy thông tin danh mục hiện tại
                $current_category = $category->getById($category_id);
                if(!$current_category) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy danh mục cần cập nhật'
                    ]);
                    break;
                }
                
                // Xử lý upload ảnh mới (nếu có)
                $image_path = $current_category['image']; // Giữ ảnh cũ
                if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../assets/images/categories/';
                    
                    // Tạo thư mục nếu chưa tồn tại
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_info = pathinfo($_FILES['image']['name']);
                    $extension = strtolower($file_info['extension']);
                    
                    // Kiểm tra định dạng file
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($extension, $allowed_extensions)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Chỉ chấp nhận file JPG, PNG, GIF'
                        ]);
                        break;
                    }
                    
                    // Kiểm tra kích thước file (5MB)
                    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'message' => 'File quá lớn. Kích thước tối đa: 5MB'
                        ]);
                        break;
                    }
                    
                    // Tạo tên file mới
                    $filename = 'category_' . time() . '_' . uniqid() . '.' . $extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        // Xóa ảnh cũ nếu có
                        if(!empty($current_category['image']) && file_exists('../' . $current_category['image'])) {
                            unlink('../' . $current_category['image']);
                        }
                        $image_path = 'assets/images/categories/' . $filename;
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Không thể upload file'
                        ]);
                        break;
                    }
                }

                // Cập nhật danh mục
                $category->id = $category_id;
                $category->name = trim($data['name']);
                $category->slug = !empty($data['slug']) ? trim($data['slug']) : $category->createSlug($data['name']);
                $category->description = trim($data['description']);
                $category->parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
                $category->image = $image_path;
                $category->temperature_type = isset($data['temperature_type']) ? $data['temperature_type'] : 'ambient';
                $category->is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
                $category->sort_order = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;

                if($category->update()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cập nhật danh mục thành công',
                        'data' => ['id' => $category_id]
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể cập nhật danh mục'
                    ]);
                }
                break;
            }
            
            // Tạo danh mục mới

            // Xử lý upload ảnh
            $image_path = '';
            if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/categories/';
                
                // Tạo thư mục nếu chưa tồn tại
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_info = pathinfo($_FILES['image']['name']);
                $extension = strtolower($file_info['extension']);
                
                // Kiểm tra định dạng file
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowed_extensions)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Chỉ chấp nhận file JPG, PNG, GIF'
                    ]);
                    break;
                }
                
                // Kiểm tra kích thước file (5MB)
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'File quá lớn. Kích thước tối đa: 5MB'
                    ]);
                    break;
                }
                
                // Tạo tên file mới
                $filename = 'category_' . time() . '_' . uniqid() . '.' . $extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = 'assets/images/categories/' . $filename;
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể upload file'
                    ]);
                    break;
                }
            }

            $category->name = $data['name'];
            $category->slug = isset($data['slug']) ? $data['slug'] : $category->createSlug($data['name']);
            $category->description = isset($data['description']) ? $data['description'] : '';
            $category->parent_id = isset($data['parent_id']) ? $data['parent_id'] : null;
            $category->image = $image_path;
            $category->temperature_type = isset($data['temperature_type']) ? $data['temperature_type'] : 'ambient';
            $category->is_active = isset($data['is_active']) ? $data['is_active'] : 1;
            $category->sort_order = isset($data['sort_order']) ? $data['sort_order'] : 0;

            $id = $category->create();
            if($id) {
                // Tạo thông báo khi thêm danh mục mới
                createCategoryNotification($id, $data['name'], $data['temperature_type']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tạo danh mục thành công',
                    'data' => ['id' => $id]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể tạo danh mục'
                ]);
            }
            break;

        case 'PUT':
            // Cập nhật danh mục
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(!isset($data['id']) || !isset($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID và tên danh mục không được để trống'
                ]);
                break;
            }

            $category->id = $data['id'];
            $category->name = $data['name'];
            $category->slug = isset($data['slug']) ? $data['slug'] : $category->createSlug($data['name']);
            $category->description = isset($data['description']) ? $data['description'] : '';
            $category->parent_id = isset($data['parent_id']) ? $data['parent_id'] : null;
            $category->image = isset($data['image']) ? $data['image'] : '';
            $category->temperature_type = isset($data['temperature_type']) ? $data['temperature_type'] : 'ambient';
            $category->is_active = isset($data['is_active']) ? $data['is_active'] : 1;
            $category->sort_order = isset($data['sort_order']) ? $data['sort_order'] : 0;

            if($category->update()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật danh mục thành công'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể cập nhật danh mục'
                ]);
            }
            break;

        case 'DELETE':
            // Xóa danh mục
            if(isset($_GET['id'])) {
                // Kiểm tra xem danh mục có danh mục con không
                $children_result = $category->getSubCategories($_GET['id']);
                $has_children = false;
                if($children_result) {
                    $children = [];
                    while($row = $children_result->fetch()) {
                        $children[] = $row;
                    }
                    $has_children = count($children) > 0;
                }

                if($has_children) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa danh mục này vì còn có danh mục con. Vui lòng xóa các danh mục con trước.'
                    ]);
                    break;
                }

                if($category->delete($_GET['id'])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Xóa danh mục thành công'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể xóa danh mục. Có thể danh mục đang chứa sản phẩm.'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID danh mục không được để trống'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method không được hỗ trợ'
            ]);
            break;
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
