<?php
class TemperatureSensor {
    private $db;
    private $table_name = "temperature_sensors";

    public function __construct($db) {
        $this->db = $db;
    }
    
    // Lấy tất cả cảm biến
    public function getAllSensors() {
        $query = "SELECT ts.*, wl.location_code, wl.location_name 
                  FROM temperature_sensors ts 
                  LEFT JOIN warehouse_locations wl ON ts.location_id = wl.id 
                  WHERE ts.is_active = 1 
                  ORDER BY ts.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy cảm biến theo ID
    public function getSensorById($id) {
        $query = "SELECT ts.*, wl.location_code, wl.location_name 
                  FROM temperature_sensors ts 
                  LEFT JOIN warehouse_locations wl ON ts.location_id = wl.id 
                  WHERE ts.id = ? AND ts.is_active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy cảm biến theo code
    public function getSensorByCode($sensorCode) {
        $query = "SELECT ts.*, wl.location_code, wl.location_name, wl.temperature_zone 
                  FROM temperature_sensors ts 
                  LEFT JOIN warehouse_locations wl ON ts.location_id = wl.id 
                  WHERE ts.sensor_code = ? AND ts.is_active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sensorCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật giá trị hiện tại
    public function updateCurrentValues($sensorCode, $temperature, $humidity = null) {
        $query = "UPDATE temperature_sensors 
                  SET current_temperature = ?, humidity = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE sensor_code = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$temperature, $humidity, $sensorCode]);
    }
    
    // Tạo cảm biến mới
    public function createSensor($data) {
        $query = "INSERT INTO temperature_sensors (sensor_name, sensor_code, location_id, sensor_type, status, last_calibration, next_calibration) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['sensor_name'],
            $data['sensor_code'],
            $data['location_id'],
            $data['sensor_type'],
            $data['status'] ?? 'active',
            $data['last_calibration'],
            $data['next_calibration']
        ]);
    }
    
    // Cập nhật cảm biến
    public function updateSensor($id, $data) {
        $query = "UPDATE temperature_sensors 
                  SET sensor_name = ?, sensor_code = ?, location_id = ?, sensor_type = ?, 
                      manufacturer = ?, model = ?, serial_number = ?, installation_date = ?,
                      status = ?, last_calibration = ?, 
                      description = ?, notes = ?, updated_at = ? 
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['sensor_name'],
            $data['sensor_code'],
            $data['location_id'],
            $data['sensor_type'],
            $data['manufacturer'],
            $data['model'],
            $data['serial_number'],
            $data['installation_date'],
            $data['status'],
            $data['last_calibration'],
            $data['description'],
            $data['notes'],
            $data['updated_at'],
            $id
        ]);
    }
    
    // Xóa cảm biến (soft delete)
    public function deleteSensor($id) {
        $query = "UPDATE temperature_sensors SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Lấy trạng thái cảm biến
    public function getSensorStatus($id) {
        $query = "SELECT status FROM temperature_sensors WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['status'] : null;
    }
    
    // Cập nhật trạng thái cảm biến
    public function updateSensorStatus($id, $status) {
        $query = "UPDATE temperature_sensors SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $id]);
    }
    
    // Lấy cảm biến theo vị trí
    public function getSensorsByLocation($locationId) {
        $query = "SELECT * FROM temperature_sensors WHERE location_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$locationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy cảm biến theo loại
    public function getSensorsByType($type) {
        $query = "SELECT * FROM temperature_sensors WHERE sensor_type = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Kiểm tra mã cảm biến đã tồn tại
    public function isSensorCodeExists($sensorCode, $excludeId = null) {
        $query = "SELECT id FROM temperature_sensors WHERE sensor_code = ?";
        $params = [$sensorCode];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // Kiểm tra mã cảm biến đã tồn tại (alias)
    public function sensorCodeExists($sensorCode, $excludeId = null) {
        return $this->isSensorCodeExists($sensorCode, $excludeId);
    }


}
?>