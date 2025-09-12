<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';
require_once '../models/TemperatureSensor.php';

/**
 * Tạo thông báo khi thêm cảm biến mới
 */
function createSensorNotification($sensor_id, $sensor_name, $sensor_code, $sensor_type, $pdo) {
    try {
        $title = "Cảm biến mới được thêm";
        $message = "Đã thêm cảm biến mới \"{$sensor_name}\" (Mã: {$sensor_code}, Loại: {$sensor_type}) vào hệ thống.";
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type) 
            VALUES (?, ?, 'sensor', 'iconoir-cpu', 'info', ?, 'sensor')
        ");
        
        $stmt->execute([$title, $message, $sensor_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error creating sensor notification: " . $e->getMessage());
        return false;
    }
}

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Chỉ hỗ trợ phương thức POST');
    }
    
    // Kết nối database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception('Không thể kết nối database');
    }
    
    // Lấy dữ liệu từ form
    $sensorName = trim($_POST['sensor_name'] ?? '');
    $sensorCode = trim($_POST['sensor_code'] ?? '');
    $sensorType = trim($_POST['sensor_type'] ?? '');
    $locationId = trim($_POST['location_id'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $serialNumber = trim($_POST['serial_number'] ?? '');
    $installationDate = trim($_POST['installation_date'] ?? '');
    $lastCalibration = trim($_POST['last_calibration'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate dữ liệu bắt buộc
    $errors = [];
    
    if (empty($sensorName)) {
        $errors['sensorName'] = 'Tên cảm biến là bắt buộc';
    }
    
    if (empty($sensorCode)) {
        $errors['sensorCode'] = 'Mã cảm biến là bắt buộc';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $sensorCode)) {
        $errors['sensorCode'] = 'Mã cảm biến chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự';
    }
    
    if (empty($sensorType)) {
        $errors['sensorType'] = 'Loại cảm biến là bắt buộc';
    }
    
    if (empty($locationId)) {
        $errors['locationId'] = 'Vị trí lắp đặt là bắt buộc';
    }
    
    if (empty($status)) {
        $errors['status'] = 'Trạng thái là bắt buộc';
    }
    
    
    // Chuẩn hóa loại cảm biến theo schema DB
    $allowedSensorTypes = ['temperature', 'humidity', 'both'];
    if (!in_array($sensorType, $allowedSensorTypes, true)) {
        // Có thể báo lỗi hoặc ép về 'both' để tương thích
        $sensorType = 'both';
    }
    
    // Kiểm tra mã cảm biến đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM temperature_sensors WHERE sensor_code = ?");
    $stmt->execute([$sensorCode]);
    if ($stmt->fetchColumn() > 0) {
        $errors['sensorCode'] = 'Mã cảm biến đã tồn tại trong hệ thống';
    }
    
    // Nếu có lỗi, trả về
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Chuẩn bị dữ liệu theo đúng cột trong bảng temperature_sensors (bao gồm các cột mới)
    $data = [
        'sensor_name' => $sensorName,
        'sensor_code' => $sensorCode,
        'location_id' => (empty($locationId) ? null : (int)$locationId),
        'sensor_type' => $sensorType,
        'status' => $status,
        'manufacturer' => $manufacturer ?: null,
        'model' => $model ?: null,
        'serial_number' => $serialNumber ?: null,
        'installation_date' => $installationDate ?: null,
        'last_calibration' => $lastCalibration ?: null,
        'description' => $description ?: null,
        'notes' => $notes ?: null,
        // next_calibration: để null hoặc tính toán nếu cần trong tương lai
        'next_calibration' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Tạo câu lệnh SQL
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO temperature_sensors ($columns) VALUES ($placeholders)";
    
    // Thực thi câu lệnh
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);
    
    if ($result) {
        $sensorId = $pdo->lastInsertId();
        
        // Tạo thông báo khi thêm cảm biến mới
        createSensorNotification($sensorId, $sensorName, $sensorCode, $sensorType, $pdo);
        
        // Trả về thành công
        echo json_encode([
            'success' => true,
            'message' => 'Cảm biến đã được tạo thành công',
            'sensor_id' => $sensorId,
            'data' => $data
        ]);
        
        // Log hoạt động (nếu cần)
        error_log("New IoT sensor created: ID=$sensorId, Code=$sensorCode, Name=$sensorName");
        
    } else {
        throw new Exception('Không thể tạo cảm biến trong database');
    }
    
} catch (Exception $e) {
    // Log lỗi
    error_log("Error creating IoT sensor: " . $e->getMessage());
    
    // Trả về lỗi
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}
?>
