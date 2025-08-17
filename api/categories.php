<?php
/**
 * API Categories - Quản lý danh mục sản phẩm
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../models/Category.php';

$category = new Category();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if(isset($_GET['id'])) {
                // Lấy danh mục theo ID
                $result = $category->getById($_GET['id']);
                if($result) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Không tìm thấy danh mục'
                    ]);
                }
            } elseif(isset($_GET['slug'])) {
                // Lấy danh mục theo slug
                $result = $category->getBySlug($_GET['slug']);
                if($result) {
                    echo json_encode([
                        'status' => 'success',
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'error',
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
                    'status' => 'success',
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
                    'status' => 'success',
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
                    'status' => 'success',
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
                    'status' => 'success',
                    'data' => $categories,
                    'total' => count($categories)
                ]);
            }
            break;

        case 'POST':
            // Tạo danh mục mới
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(!isset($data['name']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Tên danh mục không được để trống'
                ]);
                break;
            }

            $category->name = $data['name'];
            $category->slug = isset($data['slug']) ? $data['slug'] : $category->createSlug($data['name']);
            $category->description = isset($data['description']) ? $data['description'] : '';
            $category->parent_id = isset($data['parent_id']) ? $data['parent_id'] : null;
            $category->image = isset($data['image']) ? $data['image'] : '';
            $category->is_active = isset($data['is_active']) ? $data['is_active'] : 1;
            $category->sort_order = isset($data['sort_order']) ? $data['sort_order'] : 0;

            $id = $category->create();
            if($id) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Tạo danh mục thành công',
                    'data' => ['id' => $id]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
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
                    'status' => 'error',
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
            $category->is_active = isset($data['is_active']) ? $data['is_active'] : 1;
            $category->sort_order = isset($data['sort_order']) ? $data['sort_order'] : 0;

            if($category->update()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Cập nhật danh mục thành công'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Không thể cập nhật danh mục'
                ]);
            }
            break;

        case 'DELETE':
            // Xóa danh mục
            if(isset($_GET['id'])) {
                if($category->delete($_GET['id'])) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Xóa danh mục thành công'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Không thể xóa danh mục. Có thể danh mục đang chứa sản phẩm.'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'ID danh mục không được để trống'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method không được hỗ trợ'
            ]);
            break;
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
