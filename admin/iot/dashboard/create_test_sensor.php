<?php
// Sử dụng đường dẫn tuyệt đối
require_once __DIR__ . '/../../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra xem cảm biến test đã tồn tại chưa
    $stmt_check = $conn->prepare("SELECT id FROM temperature_sensors WHERE sensor_code = 'TEST_SENSOR_001'");
    $stmt_check->execute();
    
    if ($stmt_check->fetch()) {
        echo "✅ Cảm biến TEST_SENSOR_001 đã tồn tại trong database.";
    } else {
        // Tạo cảm biến test mới
        $stmt = $conn->prepare("
            INSERT INTO temperature_sensors (sensor_name, sensor_code, location_id, sensor_type, created_at) 
            VALUES ('Cảm biến Test', 'TEST_SENSOR_001', 1, 'both', NOW())
        ");
        
        if ($stmt->execute()) {
            echo "✅ Đã tạo cảm biến test TEST_SENSOR_001 thành công!";
        } else {
            echo "❌ Lỗi khi tạo cảm biến test.";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
