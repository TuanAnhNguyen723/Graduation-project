<?php
/**
 * API Update Sensor - Cập nhật thông tin cảm biến
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../config/database.php';
require_once '../models/TemperatureSensor.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method không được hỗ trợ'
        ]);
        exit;
    }

    // Lấy dữ liệu từ FormData
    $sensor_id = $_POST['id'] ?? null;
    $sensor_name = $_POST['sensor_name'] ?? '';
    $sensor_code = $_POST['sensor_code'] ?? '';
    $sensor_type = $_POST['sensor_type'] ?? '';
    $location_id = $_POST['location_id'] ?? null;
    $manufacturer = $_POST['manufacturer'] ?? '';
    $model = $_POST['model'] ?? '';
    $serial_number = $_POST['serial_number'] ?? '';
    $installation_date = $_POST['installation_date'] ?? null;
    $min_threshold = $_POST['min_threshold'] ?? null;
    $max_threshold = $_POST['max_threshold'] ?? null;
    $status = $_POST['status'] ?? 'active';
    $last_calibration = $_POST['last_calibration'] ?? null;
    $description = $_POST['description'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validation
    if (!$sensor_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID cảm biến không được để trống'
        ]);
        exit;
    }

    if (empty($sensor_name)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tên cảm biến không được để trống'
        ]);
        exit;
    }

    if (empty($sensor_code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mã cảm biến không được để trống'
        ]);
        exit;
    }

    if (empty($sensor_type)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Loại cảm biến không được để trống'
        ]);
        exit;
    }

    if (empty($status)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Trạng thái không được để trống'
        ]);
        exit;
    }

    // Validate thresholds - bắt buộc nhập cả 2
    if (empty($min_threshold)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ngưỡng tối thiểu là bắt buộc'
        ]);
        exit;
    }
    
    if (empty($max_threshold)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ngưỡng tối đa là bắt buộc'
        ]);
        exit;
    }
    
    if (!is_numeric($min_threshold)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ngưỡng tối thiểu phải là số hợp lệ'
        ]);
        exit;
    }
    
    if (!is_numeric($max_threshold)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ngưỡng tối đa phải là số hợp lệ'
        ]);
        exit;
    }
    
    // Kiểm tra ngưỡng tối đa > ngưỡng tối thiểu
    $min = floatval($min_threshold);
    $max = floatval($max_threshold);
    if ($min >= $max) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu'
        ]);
        exit;
    }

    // Kết nối database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }

    $sensorModel = new TemperatureSensor($pdo);

    // Kiểm tra cảm biến có tồn tại không
    $existingSensor = $sensorModel->getSensorById($sensor_id);
    if (!$existingSensor) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy cảm biến cần cập nhật'
        ]);
        exit;
    }

    // Kiểm tra mã cảm biến có trùng lặp không (trừ chính nó)
    if ($sensorModel->sensorCodeExists($sensor_code, $sensor_id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mã cảm biến đã tồn tại'
        ]);
        exit;
    }

    // Chuẩn bị dữ liệu cập nhật
    $updateData = [
        'sensor_name' => trim($sensor_name),
        'sensor_code' => trim($sensor_code),
        'sensor_type' => $sensor_type,
        'location_id' => $location_id ? intval($location_id) : null,
        'manufacturer' => trim($manufacturer),
        'model' => trim($model),
        'serial_number' => trim($serial_number),
        'installation_date' => $installation_date ?: null,
        'min_threshold' => $min_threshold ? floatval($min_threshold) : null,
        'max_threshold' => $max_threshold ? floatval($max_threshold) : null,
        'status' => $status,
        'last_calibration' => $last_calibration ?: null,
        'description' => trim($description),
        'notes' => trim($notes),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Cập nhật cảm biến
    $result = $sensorModel->updateSensor($sensor_id, $updateData);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật cảm biến thành công',
            'data' => [
                'id' => $sensor_id,
                'sensor_name' => $updateData['sensor_name'],
                'sensor_code' => $updateData['sensor_code']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Không thể cập nhật cảm biến'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
?>
