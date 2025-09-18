<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../../config/database.php';
require_once '../models/TemperatureReading.php';
require_once '../models/TemperatureSensor.php';

try {
    // $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        throw new Exception('Không thể kết nối database');
    }
    
    $readingModel = new TemperatureReading($pdo);
    $sensorModel = new TemperatureSensor($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            // Nhận dữ liệu từ cảm biến IoT
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dữ liệu đầu vào không hợp lệ');
            }
            
            // Validate dữ liệu
            $requiredFields = ['sensor_code', 'temperature'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    throw new Exception("Thiếu trường bắt buộc: $field");
                }
            }
            
            $sensorCode = $input['sensor_code'];
            $temperature = floatval($input['temperature']);
            $humidity = isset($input['humidity']) ? floatval($input['humidity']) : null;
            $timestamp = isset($input['timestamp']) ? $input['timestamp'] : null;
            
            // Validate giá trị nhiệt độ
            if ($temperature < -50 || $temperature > 100) {
                throw new Exception('Nhiệt độ không hợp lệ (-50°C đến 100°C)');
            }
            
            // Validate độ ẩm nếu có
            if ($humidity !== null && ($humidity < 0 || $humidity > 100)) {
                throw new Exception('Độ ẩm không hợp lệ (0% đến 100%)');
            }
            
            // Lưu reading
            $success = $readingModel->addReadingFromIoT($sensorCode, $temperature, $humidity);
            if (!$success) {
                throw new Exception('Không thể lưu dữ liệu');
            }

            // Cập nhật giá trị hiện tại cảm biến
            $sensorModel->updateCurrentValues($sensorCode, $temperature, $humidity);

            // Lấy thông tin cảm biến (kèm zone vị trí)
            $sensor = $sensorModel->getSensorByCode($sensorCode);

            // Xác định ngưỡng nguy hiểm theo zone vị trí
            $zone = $sensor['temperature_zone'] ?? 'ambient';
            $tempDangerMin = null; $tempDangerMax = null; $humidityDangerMin = null; $humidityDangerMax = null;
            if ($zone === 'frozen') {
                $tempDangerMax = -18.0; // > -18°C là nguy hiểm
                $humidityDangerMin = 85.0; $humidityDangerMax = 95.0;
            } elseif ($zone === 'chilled') {
                $tempDangerMax = 8.0; // > 8°C là nguy hiểm
                $humidityDangerMin = 85.0; $humidityDangerMax = 90.0;
            } else { // ambient
                $tempDangerMin = 0.0; $tempDangerMax = 37.0; // <0°C hoặc >37°C
                $humidityDangerMin = 50.0; $humidityDangerMax = 60.0; // ngoài khoảng 50-60%
            }

            // Kiểm tra vi phạm để bắn thông báo
            $alerts = [];
            $violatedTemp = ($tempDangerMin !== null && $temperature < $tempDangerMin) || ($tempDangerMax !== null && $temperature > $tempDangerMax);
            $violatedHumidity = ($humidity !== null) && (($humidityDangerMin !== null && $humidity < $humidityDangerMin) || ($humidityDangerMax !== null && $humidity > $humidityDangerMax));

            // Kiểm tra trạng thái hồi phục (từ vi phạm về bình thường)
            $recoveredTemp = false;
            $recoveredHumidity = false;

            // Nếu có vi phạm -> kiểm tra và tạo notification (tránh spam)
            if ($violatedTemp || $violatedHumidity) {
                require_once '../../../config/database.php';
                $db2 = new Database();
                $conn2 = $db2->getConnection();
                
                // Kiểm tra notification gần đây (trong 30 phút) cho cảm biến này
                $stmt_check = $conn2->prepare("
                    SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE related_id = ? AND related_type = 'sensor' 
                    AND type = 'sensor' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                    AND (title LIKE '%nhiệt độ%' OR title LIKE '%độ ẩm%')
                ");
                $stmt_check->execute([$sensor['id'] ?? null]);
                $recent_notifications = $stmt_check->fetch(PDO::FETCH_ASSOC)['count'];

                // Chỉ tạo notification mới nếu chưa có notification nào trong 30 phút gần đây
                if ($recent_notifications == 0) {
                    if ($violatedTemp) {
                        $alerts[] = [
                            'title' => 'Cảnh báo nhiệt độ vượt ngưỡng',
                            'message' => sprintf('Cảm biến %s tại %s: Nhiệt độ %.2f°C vượt ngưỡng an toàn (%s%s%s).',
                                $sensor['sensor_name'] ?? $sensorCode,
                                $sensor['location_name'] ?? 'Không rõ vị trí',
                                $temperature,
                                $tempDangerMin !== null ? $tempDangerMin . '°C - ' : '',
                                $tempDangerMax !== null ? $tempDangerMax . '°C' : '',
                                ($tempDangerMin === null && $tempDangerMax !== null) ? ' (giới hạn trên)' : ''
                            ),
                            'icon' => 'iconoir-temperature-high',
                            'icon_color' => 'danger'
                        ];
                    }

                    if ($violatedHumidity) {
                        $alerts[] = [
                            'title' => 'Cảnh báo độ ẩm vượt ngưỡng',
                            'message' => sprintf('Cảm biến %s tại %s: Độ ẩm %.2f%% vượt ngưỡng an toàn (%s - %s%%).',
                                $sensor['sensor_name'] ?? $sensorCode,
                                $sensor['location_name'] ?? 'Không rõ vị trí',
                                $humidity,
                                number_format($humidityDangerMin, 0),
                                number_format($humidityDangerMax, 0)
                            ),
                            'icon' => 'iconoir-droplet',
                            'icon_color' => 'warning'
                        ];
                    }

                    // Tạo notification mới
                    if (!empty($alerts)) {
                        $stmt = $conn2->prepare("INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type) VALUES (?, ?, 'sensor', ?, ?, ?, 'sensor')");
                        foreach ($alerts as $a) {
                            $stmt->execute([
                                $a['title'],
                                $a['message'],
                                $a['icon'],
                                $a['icon_color'],
                                $sensor['id'] ?? null
                            ]);
                        }
                    }
                }
            }

            // Kiểm tra và tạo notification hồi phục nếu cần
            if (!$violatedTemp && !$violatedHumidity) {
                require_once '../../../config/database.php';
                $db2 = new Database();
                $conn2 = $db2->getConnection();
                
                // Kiểm tra xem có notification cảnh báo gần đây không
                $stmt_check_recovery = $conn2->prepare("
                    SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE related_id = ? AND related_type = 'sensor' 
                    AND type = 'sensor' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    AND (title LIKE '%nhiệt độ%' OR title LIKE '%độ ẩm%')
                    AND title NOT LIKE '%hồi phục%'
                ");
                $stmt_check_recovery->execute([$sensor['id'] ?? null]);
                $recent_alerts = $stmt_check_recovery->fetch(PDO::FETCH_ASSOC)['count'];

                // Kiểm tra xem đã có notification hồi phục gần đây chưa
                $stmt_check_recovery_notification = $conn2->prepare("
                    SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE related_id = ? AND related_type = 'sensor' 
                    AND type = 'sensor' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
                    AND title LIKE '%hồi phục%'
                ");
                $stmt_check_recovery_notification->execute([$sensor['id'] ?? null]);
                $recent_recovery = $stmt_check_recovery_notification->fetch(PDO::FETCH_ASSOC)['count'];

                // Nếu có cảnh báo gần đây nhưng chưa có notification hồi phục
                if ($recent_alerts > 0 && $recent_recovery == 0) {
                    $recovery_alerts = [];
                    
                    // Tạo notification hồi phục nhiệt độ
                    if (!$violatedTemp) {
                        $recovery_alerts[] = [
                            'title' => 'Cảm biến nhiệt độ đã hồi phục',
                            'message' => sprintf('Cảm biến %s tại %s: Nhiệt độ %.2f°C đã trở về mức bình thường.',
                                $sensor['sensor_name'] ?? $sensorCode,
                                $sensor['location_name'] ?? 'Không rõ vị trí',
                                $temperature
                            ),
                            'icon' => 'iconoir-check-circle',
                            'icon_color' => 'success'
                        ];
                    }

                    // Tạo notification hồi phục độ ẩm
                    if (!$violatedHumidity && $humidity !== null) {
                        $recovery_alerts[] = [
                            'title' => 'Cảm biến độ ẩm đã hồi phục',
                            'message' => sprintf('Cảm biến %s tại %s: Độ ẩm %.2f%% đã trở về mức bình thường.',
                                $sensor['sensor_name'] ?? $sensorCode,
                                $sensor['location_name'] ?? 'Không rõ vị trí',
                                $humidity
                            ),
                            'icon' => 'iconoir-check-circle',
                            'icon_color' => 'success'
                        ];
                    }

                    // Tạo notification hồi phục
                    if (!empty($recovery_alerts)) {
                        $stmt_recovery = $conn2->prepare("INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type) VALUES (?, ?, 'sensor', ?, ?, ?, 'sensor')");
                        foreach ($recovery_alerts as $recovery_alert) {
                            $stmt_recovery->execute([
                                $recovery_alert['title'],
                                $recovery_alert['message'],
                                $recovery_alert['icon'],
                                $recovery_alert['icon_color'],
                                $sensor['id'] ?? null
                            ]);
                        }
                        $alerts = array_merge($alerts, $recovery_alerts);
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => 'Dữ liệu đã được lưu thành công',
                'data' => [
                    'sensor_code' => $sensorCode,
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'sensor_info' => $sensor,
                    'violations_detected' => [
                        'temperature' => $violatedTemp,
                        'humidity' => $violatedHumidity
                    ],
                    'alerts_created' => count($alerts),
                    'notification_spam_prevented' => ($violatedTemp || $violatedHumidity) && count($alerts) == 0,
                    'recovery_notifications' => count($alerts) - ($violatedTemp ? 1 : 0) - ($violatedHumidity ? 1 : 0)
                ]
            ];
            http_response_code(201);
            break;
            
        case 'GET':
            // Lấy dữ liệu nhiệt độ
            $sensorCode = $_GET['sensor_code'] ?? null;
            $limit = intval($_GET['limit'] ?? 100);
            $startTime = $_GET['start_time'] ?? null;
            $endTime = $_GET['end_time'] ?? null;
            
            if ($sensorCode) {
                // Lấy thông tin cảm biến
                $sensor = $sensorModel->getSensorById($sensorCode);
                if (!$sensor) {
                    throw new Exception('Không tìm thấy cảm biến');
                }
                
                if ($startTime && $endTime) {
                    // Lấy dữ liệu theo khoảng thời gian
                    $data = $readingModel->getReadingsByTimeRange($sensor['id'], $startTime, $endTime);
                } else {
                    // Lấy dữ liệu mới nhất
                    $data = $readingModel->getReadingsBySensor($sensor['id'], $limit);
                }
                
                $response = [
                    'success' => true,
                    'sensor' => $sensor,
                    'data' => $data,
                    'count' => count($data)
                ];
            } else {
                // Lấy dữ liệu của tất cả cảm biến
                // $data = $readingModel->getAllReadings($limit);
                $sensors = $sensorModel->getAllSensors();
                
                $response = [
                    'success' => true,
                    'data' => $data,
                    'count' => count($data)
                ];
            }
            break;
            
        default:
            throw new Exception('Phương thức không được hỗ trợ');
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