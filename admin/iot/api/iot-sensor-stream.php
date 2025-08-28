<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/TemperatureSensor.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');


try {
    $db = new Database();
    $pdo = $db->getConnection();
    $sensorModel = new TemperatureSensor($pdo);

    while (ob_get_level() > 0) {
    ob_end_flush();
    }

    // Vòng lặp gửi dữ liệu liên tục
    while (true) {
        $sensors = $sensorModel->getAllSensors();

        echo "data: " . json_encode($sensors, JSON_UNESCAPED_UNICODE) . "\n\n";
        ob_flush();
        flush();

        // Delay 2 giây trước khi gửi tiếp
        sleep(2);
    }
} catch (Exception $e) {
    echo "data: " . json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE) . "\n\n";
    ob_flush();
    flush();
}
?>
