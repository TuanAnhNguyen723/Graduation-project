<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['sensor_id'])) {
        throw new Exception('Missing sensor_id parameter');
    }
    
    $sensorId = (int)$input['sensor_id'];
    
    if ($sensorId <= 0) {
        throw new Exception('Invalid sensor_id');
    }
    
    // Include database connection
    require_once '../../../config/database.php';
    require_once '../models/TemperatureSensor.php';
    
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    $sensorModel = new TemperatureSensor($pdo);
    
    // Check if sensor exists
    $sensor = $sensorModel->getSensorById($sensorId);
    if (!$sensor) {
        throw new Exception('Sensor not found');
    }
    
    // Delete sensor (soft delete)
    $result = $sensorModel->deleteSensor($sensorId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Sensor deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete sensor');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
