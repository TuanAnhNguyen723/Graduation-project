<?php
require_once '../../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        echo "Không thể kết nối database";
        exit;
    }
    
    echo "✅ Kết nối database thành công!<br>";
    
    // Test query warehouse_locations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM warehouse_locations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📦 Số lượng vị trí kho: " . $result['count'] . "<br>";
    
    // Test query temperature_sensors
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM temperature_sensors");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "🌡️ Số lượng cảm biến: " . $result['count'] . "<br>";
    
    // Test query temperature_readings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM temperature_readings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Số lượng dữ liệu nhiệt độ: " . $result['count'] . "<br>";
    
    // Test sample data
    echo "<br><strong>Dữ liệu mẫu:</strong><br>";
    
    $stmt = $pdo->query("SELECT * FROM warehouse_locations LIMIT 2");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>Warehouse Locations: " . print_r($locations, true) . "</pre>";
    
    $stmt = $pdo->query("SELECT * FROM temperature_sensors LIMIT 2");
    $sensors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>Temperature Sensors: " . print_r($sensors, true) . "</pre>";
    
} catch(Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
