<?php
/**
 * API Locations - Lấy danh sách vị trí kho
 */

// Bật error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Không hiển thị error trực tiếp
ini_set('log_errors', 1); // Ghi log error

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../config/database.php';
require_once '../models/WarehouseLocation.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'OPTIONS') { http_response_code(200); echo json_encode(['success'=>true]); exit; }

    // Kết nối database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }

    $locationModel = new WarehouseLocation($pdo);

    if ($method === 'GET') {
        // Kiểm tra xem vị trí có sản phẩm không
        if (isset($_GET['action']) && $_GET['action'] === 'check_products') {
            try {
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success'=>false,'message'=>'Thiếu id']);
                    exit;
                }
                
                $hasProducts = $locationModel->hasProductsInLocation((int)$id);
                $location = $locationModel->getLocationById((int)$id);
                $locationName = $location ? $location['location_name'] : 'Vị trí này';
                echo json_encode(['success'=>true,'has_products'=>$hasProducts,'location_name'=>$locationName]);
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success'=>false,'message'=>'Lỗi khi kiểm tra sản phẩm: ' . $e->getMessage()]);
                exit;
            }
        }
        
        if (isset($_GET['id'])) {
            $loc = $locationModel->getLocationById((int)$_GET['id']);
            if ($loc) {
                echo json_encode(['success'=>true,'data'=>$loc]);
            } else {
                http_response_code(404);
                echo json_encode(['success'=>false,'message'=>'Không tìm thấy vị trí']);
            }
            exit;
        }
        $locations = $locationModel->getAllLocations();
        echo json_encode(['success'=>true,'data'=>$locations,'total'=>count($locations)]);
        exit;
    }

    if ($method === 'POST') {
        // Hỗ trợ cả create và update: nếu có id => update
        $id = $_POST['id'] ?? null;
        $payload = [
            'location_code' => trim($_POST['location_code'] ?? ''),
            'location_name' => trim($_POST['location_name'] ?? ''),
            'area' => trim($_POST['area'] ?? ''),
            'temperature_zone' => trim($_POST['temperature_zone'] ?? 'ambient'),
            'max_capacity' => (int)($_POST['max_capacity'] ?? 0)
        ];
        if (empty($payload['location_code']) || empty($payload['location_name']) || empty($payload['area'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Thiếu trường bắt buộc']);
            exit;
        }
        if ($id) {
            // Kiểm tra mã vị trí trùng lặp khi cập nhật
            if ($locationModel->isLocationCodeExists($payload['location_code'], (int)$id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mã vị trí "' . $payload['location_code'] . '" đã tồn tại trong hệ thống. Vui lòng chọn mã khác.',
                    'error_type' => 'duplicate_code',
                    'field' => 'location_code'
                ]);
                exit;
            }
            
            $ok = $locationModel->updateLocation((int)$id, $payload);
            echo json_encode(['success'=>$ok,'message'=>$ok?'Cập nhật vị trí thành công':'Không thể cập nhật vị trí']);
        } else {
            // Kiểm tra mã vị trí trùng lặp khi tạo mới
            if ($locationModel->isLocationCodeExists($payload['location_code'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Mã vị trí "' . $payload['location_code'] . '" đã tồn tại trong hệ thống. Vui lòng chọn mã khác.',
                    'error_type' => 'duplicate_code',
                    'field' => 'location_code'
                ]);
                exit;
            }
            
            $ok = $locationModel->createLocation($payload);
            echo json_encode(['success'=>$ok,'message'=>$ok?'Tạo vị trí thành công':'Không thể tạo vị trí']);
        }
        exit;
    }

    if ($method === 'DELETE') {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) { 
                http_response_code(400); 
                echo json_encode(['success'=>false,'message'=>'Thiếu id']); 
                exit; 
            }
            $ok = $locationModel->deleteLocation((int)$id);
            echo json_encode(['success'=>$ok,'message'=>$ok?'Đã xóa vị trí':'Không thể xóa vị trí']);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Lỗi khi xóa vị trí: ' . $e->getMessage()]);
            exit;
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    
    // Xử lý lỗi trùng lặp mã vị trí một cách đẹp mắt
    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'location_code') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Mã vị trí đã tồn tại trong hệ thống. Vui lòng chọn mã khác.',
            'error_type' => 'duplicate_code',
            'field' => 'location_code'
        ]);
    } else {
        // Lỗi khác - hiển thị thông báo chung
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xử lý yêu cầu. Vui lòng thử lại sau.',
            'error_type' => 'server_error'
        ]);
    }
}
?>
