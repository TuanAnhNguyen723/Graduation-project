<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../models/TemperatureSensor.php';
require_once '../models/TemperatureReading.php';
require_once '../models/WarehouseLocation.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sensorModel = new TemperatureSensor($pdo);
    $readingModel = new TemperatureReading($pdo);
    $locationModel = new WarehouseLocation($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'GET') {
        throw new Exception('Chỉ hỗ trợ phương thức GET');
    }
    
    $dataType = $_GET['type'] ?? 'overview';
    
    switch ($dataType) {
        case 'overview':
            // Dữ liệu tổng quan
            $sensors = $sensorModel->getAllSensors();
            $latestReadings = $readingModel->getLatestReadings();
            $capacityStats = $locationModel->getCapacityStats();
            $capacityStatsByZone = $locationModel->getCapacityStatsByZone();
            
            $response = [
                'success' => true,
                'data' => [
                    'sensors' => $sensors,
                    'latest_readings' => $latestReadings,
                    'capacity_stats' => $capacityStats,
                    'capacity_stats_by_zone' => $capacityStatsByZone,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        case 'temperature_chart':
            // Dữ liệu cho biểu đồ nhiệt độ
            $sensorId = $_GET['sensor_id'] ?? null;
            $days = intval($_GET['days'] ?? 1);
            
            if (!$sensorId) {
                throw new Exception('Thiếu sensor_id');
            }
            
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime("-$days days"));
            
            $hourlyData = $readingModel->getHourlyData($sensorId, $endDate);
            $dailyStats = $readingModel->getDailyStats($sensorId, $endDate);
            
            $response = [
                'success' => true,
                'data' => [
                    'hourly_data' => $hourlyData,
                    'daily_stats' => $dailyStats,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ];
            break;
            
        case 'warehouse_map':
            // Dữ liệu bản đồ kho
            $warehouseMap = $locationModel->getWarehouseMap();
            $availableLocations = $locationModel->getAvailableLocations();
            $fullLocations = $locationModel->getFullLocations();
            
            $response = [
                'success' => true,
                'data' => [
                    'warehouse_map' => $warehouseMap,
                    'available_locations' => $availableLocations,
                    'full_locations' => $fullLocations
                ]
            ];
            break;
            
        case 'sensor_status':
            // Trạng thái cảm biến
            $sensors = $sensorModel->getAllSensors();
            $sensorStatuses = [];
            
            foreach ($sensors as $sensor) {
                $latestReading = $readingModel->getLatestReadingBySensor($sensor['id']);
                $sensorStatuses[] = [
                    'sensor' => $sensor,
                    'latest_reading' => $latestReading,
                    'status' => $sensor['status']
                ];
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'sensor_statuses' => $sensorStatuses,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        case 'alerts':
            // Dữ liệu cảnh báo (sẽ được implement sau)
            $response = [
                'success' => true,
                'data' => [
                    'alerts' => [],
                    'message' => 'Tính năng cảnh báo sẽ được implement sau'
                ]
            ];
            break;
            
        default:
            throw new Exception('Loại dữ liệu không được hỗ trợ');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi database: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>
