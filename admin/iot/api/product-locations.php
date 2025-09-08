<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../../config/database.php';
require_once '../models/ProductLocation.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        throw new Exception('Không thể kết nối database');
    }

    $model = new ProductLocation($pdo);
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
            $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;
            $allocs = $model->getAllocations($productId, $locationId);
            echo json_encode([
                'success' => true,
                'data' => $allocs,
                'count' => count($allocs)
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $action = $input['action'] ?? 'allocate';
            $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            $locationId = isset($input['location_id']) ? (int)$input['location_id'] : 0;
            $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;

            if ($productId <= 0 || $locationId <= 0 || $quantity <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu hoặc sai tham số product_id/location_id/quantity']);
                break;
            }

            $ok = false;
            if ($action === 'allocate') {
                $ok = $model->allocateProduct($productId, $locationId, $quantity);
            } elseif ($action === 'deallocate') {
                $ok = $model->deallocateProduct($productId, $locationId, $quantity);
            } elseif ($action === 'move') {
                $toLocationId = isset($input['to_location_id']) ? (int)$input['to_location_id'] : 0;
                if ($toLocationId <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Thiếu to_location_id cho hành động move']);
                    break;
                }
                $ok = $model->moveProduct($productId, $locationId, $toLocationId, $quantity);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
                break;
            }

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Thực hiện thành công']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể thực hiện thao tác']);
            }
            break;

        case 'DELETE':
            // Xóa toàn bộ phân bổ của 1 sản phẩm tại 1 vị trí
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
            if ($productId <= 0 || $locationId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu product_id/location_id']);
                break;
            }

            // Lấy quantity hiện có để giảm capacity tương ứng
            $rows = $model->getAllocations($productId, $locationId);
            $qty = $rows && isset($rows[0]['quantity']) ? (int)$rows[0]['quantity'] : 0;
            if ($qty > 0) {
                $model->deallocateProduct($productId, $locationId, $qty);
            }
            echo json_encode(['success' => true, 'message' => 'Đã xóa phân bổ']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


