<?php
/**
 * API Locations - Lấy danh sách vị trí kho
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../config/database.php';
require_once '../models/WarehouseLocation.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method không được hỗ trợ'
        ]);
        exit;
    }

    // Kết nối database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }

    $locationModel = new WarehouseLocation($pdo);

    // Lấy tất cả vị trí
    $locations = $locationModel->getAllLocations();

    echo json_encode([
        'success' => true,
        'data' => $locations,
        'total' => count($locations)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
