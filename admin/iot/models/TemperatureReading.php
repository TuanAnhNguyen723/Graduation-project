<?php
class TemperatureReading {
    private $db;
    private $table_name = "temperature_readings";
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Lấy tất cả dữ liệu nhiệt độ
    public function getAllReadings($limit = 100) {
        $query = "SELECT tr.*, ts.sensor_name, ts.sensor_code, wl.location_code, wl.location_name 
                  FROM temperature_readings tr 
                  JOIN temperature_sensors ts ON tr.sensor_id = ts.id 
                  LEFT JOIN warehouse_locations wl ON ts.location_id = wl.id 
                  ORDER BY tr.reading_timestamp DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ theo cảm biến
    public function getReadingsBySensor($sensorId, $limit = 100) {
        $query = "SELECT tr.*, ts.sensor_name, ts.sensor_code 
                  FROM temperature_readings tr 
                  JOIN temperature_sensors ts ON tr.sensor_id = ts.id 
                  WHERE tr.sensor_id = ? 
                  ORDER BY tr.reading_timestamp DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ theo khoảng thời gian
    public function getReadingsByTimeRange($sensorId, $startTime, $endTime) {
        $query = "SELECT tr.*, ts.sensor_name, ts.sensor_code 
                  FROM temperature_readings tr 
                  JOIN temperature_sensors ts ON tr.sensor_id = ts.id 
                  WHERE tr.sensor_id = ? 
                  AND tr.reading_timestamp BETWEEN ? AND ? 
                  ORDER BY tr.reading_timestamp ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId, $startTime, $endTime]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ mới nhất của tất cả cảm biến
    public function getLatestReadings() {
        $query = "SELECT tr.*, ts.sensor_name, ts.sensor_code, wl.location_code, wl.location_name 
                  FROM temperature_readings tr 
                  JOIN temperature_sensors ts ON tr.sensor_id = ts.id 
                  LEFT JOIN warehouse_locations wl ON ts.location_id = wl.id 
                  WHERE tr.reading_timestamp = (
                      SELECT MAX(reading_timestamp) 
                      FROM temperature_readings tr2 
                      WHERE tr2.sensor_id = tr.sensor_id
                  ) 
                  ORDER BY ts.sensor_name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ mới nhất của một cảm biến
    public function getLatestReadingBySensor($sensorId) {
        $query = "SELECT tr.*, ts.sensor_name, ts.sensor_code 
                  FROM temperature_readings tr 
                  JOIN temperature_sensors ts ON tr.sensor_id = ts.id 
                  WHERE tr.sensor_id = ? 
                  ORDER BY tr.reading_timestamp DESC 
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Thêm dữ liệu nhiệt độ mới
    public function addReading($data) {
        $query = "INSERT INTO temperature_readings (sensor_id, temperature, humidity) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['sensor_id'],
            $data['temperature'],
            $data['humidity']
        ]);
    }
    
    // Thêm dữ liệu nhiệt độ từ API IoT
    public function addReadingFromIoT($sensorCode, $temperature, $humidity = null) {
        // Lấy sensor_id từ sensor_code
        $query = "SELECT id FROM temperature_sensors WHERE sensor_code = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorCode]);
        
        $sensor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sensor) {
            return false;
        }
        
        // Thêm dữ liệu mới
        return $this->addReading([
            'sensor_id' => $sensor['id'],
            'temperature' => $temperature,
            'humidity' => $humidity
        ]);
    }
    
    // Lấy thống kê nhiệt độ
    public function getTemperatureStats() {
        $query = "SELECT 
                    AVG(temperature) as avg_temp,
                    MAX(temperature) as max_temp,
                    MIN(temperature) as min_temp,
                    COUNT(*) as total_readings
                  FROM temperature_readings 
                  WHERE temperature IS NOT NULL";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ cho biểu đồ
    public function getTemperatureChartData($sensorId = null, $hours = 24) {
        $query = "SELECT 
                    DATE_FORMAT(reading_timestamp, '%H:00') as hour,
                    AVG(temperature) as avg_temperature,
                    AVG(humidity) as avg_humidity
                  FROM temperature_readings 
                  WHERE reading_timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)";
        
        if ($sensorId) {
            $query .= " AND sensor_id = ?";
        }
        
        $query .= " GROUP BY hour ORDER BY hour";
        
        $stmt = $this->db->prepare($query);
        if ($sensorId) {
            $stmt->execute([$hours, $sensorId]);
        } else {
            $stmt->execute([$hours]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy thống kê nhiệt độ theo ngày
    public function getDailyStats($sensorId, $date) {
        $query = "SELECT 
                      MIN(temperature) as min_temp,
                      MAX(temperature) as max_temp,
                      AVG(temperature) as avg_temp,
                      MIN(humidity) as min_humidity,
                      MAX(humidity) as max_humidity,
                      AVG(humidity) as avg_humidity,
                      COUNT(*) as reading_count
                  FROM temperature_readings 
                  WHERE sensor_id = ? 
                  AND DATE(reading_timestamp) = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy thống kê nhiệt độ theo tuần
    public function getWeeklyStats($sensorId, $weekStart, $weekEnd) {
        $query = "SELECT 
                      MIN(temperature) as min_temp,
                      MAX(temperature) as max_temp,
                      AVG(temperature) as avg_temp,
                      MIN(humidity) as min_humidity,
                      MAX(humidity) as max_humidity,
                      AVG(humidity) as avg_humidity,
                      COUNT(*) as reading_count
                  FROM temperature_readings 
                  WHERE sensor_id = ? 
                  AND reading_timestamp BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId, $weekStart, $weekEnd]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy dữ liệu nhiệt độ cho biểu đồ (theo giờ)
    public function getHourlyData($sensorId, $date) {
        $query = "SELECT 
                      HOUR(reading_timestamp) as hour,
                      AVG(temperature) as avg_temperature,
                      AVG(humidity) as avg_humidity
                  FROM temperature_readings 
                  WHERE sensor_id = ? 
                  AND DATE(reading_timestamp) = ? 
                  GROUP BY HOUR(reading_timestamp) 
                  ORDER BY hour";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Xóa dữ liệu cũ (dọn dẹp database)
    public function cleanupOldReadings($daysOld = 90) {
        $query = "DELETE FROM temperature_readings 
                  WHERE reading_timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$daysOld]);
    }
    
    // Lấy số lượng dữ liệu nhiệt độ
    public function getReadingsCount($sensorId = null) {
        if ($sensorId) {
            $query = "SELECT COUNT(*) as count FROM temperature_readings WHERE sensor_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$sensorId]);
        } else {
            $query = "SELECT COUNT(*) as count FROM temperature_readings";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['count'] : 0;
    }
}
?>