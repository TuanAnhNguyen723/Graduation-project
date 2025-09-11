<?php
class WarehouseLocation {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Lấy tất cả vị trí kho
    public function getAllLocations() {
        $query = "SELECT * FROM warehouse_locations WHERE is_active = 1 ORDER BY area, location_code";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy vị trí theo ID
    public function getLocationById($id) {
        $query = "SELECT * FROM warehouse_locations WHERE id = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy vị trí theo mã
    public function getLocationByCode($locationCode) {
        $query = "SELECT * FROM warehouse_locations WHERE location_code = ? AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$locationCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Tạo vị trí mới
    public function createLocation($data) {
        $query = "INSERT INTO warehouse_locations (location_code, location_name, area, temperature_zone, max_capacity) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['location_code'],
            $data['location_name'],
            $data['area'],
            $data['temperature_zone'],
            $data['max_capacity']
        ]);
    }
    
    // Cập nhật vị trí
    public function updateLocation($id, $data) {
        $query = "UPDATE warehouse_locations 
                  SET location_code = ?, location_name = ?, area = ?, temperature_zone = ?, max_capacity = ? 
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['location_code'],
            $data['location_name'],
            $data['area'],
            $data['temperature_zone'],
            $data['max_capacity'],
            $id
        ]);
    }
    
    // Xóa vị trí (soft delete)
    public function deleteLocation($id) {
        $query = "UPDATE warehouse_locations SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Lấy vị trí theo khu vực
    public function getLocationsByArea($area) {
        $query = "SELECT * FROM warehouse_locations WHERE area = ? AND is_active = 1 ORDER BY location_code";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$area]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy vị trí theo vùng nhiệt độ
    public function getLocationsByTemperatureZone($zone) {
        $query = "SELECT * FROM warehouse_locations WHERE temperature_zone = ? AND is_active = 1 ORDER BY area, location_code";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$zone]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy vị trí còn trống
    public function getAvailableLocations() {
        $query = "SELECT * FROM warehouse_locations 
                  WHERE current_capacity < max_capacity AND is_active = 1 
                  ORDER BY (max_capacity - current_capacity) DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Lấy vị trí đầy
    public function getFullLocations() {
        $query = "SELECT * FROM warehouse_locations 
                  WHERE current_capacity >= max_capacity AND is_active = 1 
                  ORDER BY area, row_number";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Cập nhật sức chứa hiện tại
    public function updateCurrentCapacity($id, $newCapacity) {
        $query = "UPDATE warehouse_locations SET current_capacity = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$newCapacity, $id]);
    }
    
    // Tăng sức chứa hiện tại
    public function increaseCurrentCapacity($id, $amount) {
        $query = "UPDATE warehouse_locations 
                  SET current_capacity = current_capacity + ? 
                  WHERE id = ? AND (current_capacity + ?) <= max_capacity";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$amount, $id, $amount]);
    }
    
    // Giảm sức chứa hiện tại
    public function decreaseCurrentCapacity($id, $amount) {
        $query = "UPDATE warehouse_locations 
                  SET current_capacity = GREATEST(0, current_capacity - ?) 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$amount, $id]);
    }
    
    // Lấy thống kê sức chứa
    public function getCapacityStats() {
        // Lấy thống kê cơ bản
        $query = "SELECT 
                      COUNT(*) as total_locations,
                      SUM(max_capacity) as total_max_capacity
                  FROM warehouse_locations 
                  WHERE is_active = 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $basicStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy số sản phẩm thực tế theo vị trí
        $productCounts = $this->getProductCountsPerLocation();
        $totalCurrentCapacity = array_sum($productCounts);
        
        // Tính tỷ lệ sử dụng trung bình
        $query = "SELECT id, max_capacity FROM warehouse_locations WHERE is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalUtilization = 0;
        $validLocations = 0;
        
        foreach ($locations as $location) {
            $productCount = isset($productCounts[$location['id']]) ? $productCounts[$location['id']] : 0;
            if ($location['max_capacity'] > 0) {
                $utilization = ($productCount * 100.0) / $location['max_capacity'];
                $totalUtilization += $utilization;
                $validLocations++;
            }
        }
        
        $avgUtilization = $validLocations > 0 ? ($totalUtilization / $validLocations) : 0;
        
        return [
            'total_locations' => $basicStats['total_locations'],
            'total_max_capacity' => $basicStats['total_max_capacity'],
            'total_current_capacity' => $totalCurrentCapacity,
            'avg_utilization' => $avgUtilization
        ];
    }
    
    // Lấy thống kê theo vùng nhiệt độ
    public function getCapacityStatsByZone() {
        // Lấy thống kê cơ bản theo zone
        $query = "SELECT 
                      temperature_zone,
                      COUNT(*) as location_count,
                      SUM(max_capacity) as total_max_capacity
                  FROM warehouse_locations 
                  WHERE is_active = 1 
                  GROUP BY temperature_zone 
                  ORDER BY temperature_zone";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $zoneStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy số sản phẩm thực tế theo vị trí
        $productCounts = $this->getProductCountsPerLocation();
        
        // Cập nhật thống kê với số sản phẩm thực tế
        foreach ($zoneStats as &$zone) {
            $zone['total_current_capacity'] = 0;
            $zone['avg_utilization'] = 0;
            
            // Lấy danh sách vị trí trong zone này
            $query = "SELECT id, max_capacity FROM warehouse_locations 
                      WHERE temperature_zone = ? AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$zone['temperature_zone']]);
            $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalUtilization = 0;
            $validLocations = 0;
            
            foreach ($locations as $location) {
                $productCount = isset($productCounts[$location['id']]) ? $productCounts[$location['id']] : 0;
                $zone['total_current_capacity'] += $productCount;
                
                if ($location['max_capacity'] > 0) {
                    $utilization = ($productCount * 100.0) / $location['max_capacity'];
                    $totalUtilization += $utilization;
                    $validLocations++;
                }
            }
            
            $zone['avg_utilization'] = $validLocations > 0 ? ($totalUtilization / $validLocations) : 0;
        }
        
        return $zoneStats;
    }

    // Lấy số sản phẩm theo vị trí (tính qua danh mục gắn với vị trí)
    public function getProductCountsPerLocation() {
        $query = "SELECT wl.id AS location_id, COUNT(p.id) AS product_count
                  FROM warehouse_locations wl
                  LEFT JOIN categories c ON c.location_id = wl.id
                  LEFT JOIN products p ON p.category_id = c.id
                  WHERE wl.is_active = 1
                  GROUP BY wl.id";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['location_id']] = (int)$row['product_count'];
        }
        return $result;
    }
    
    // Kiểm tra mã vị trí đã tồn tại
    public function isLocationCodeExists($locationCode, $excludeId = null) {
        $query = "SELECT id FROM warehouse_locations WHERE location_code = ?";
        $params = [$locationCode];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
    
    // Lấy vị trí gần nhất còn trống
    public function getNearestAvailableLocation($area, $row, $column) {
        $query = "SELECT *, 
                      ABS(row_number - ?) + ABS(column_number - ?) as distance 
                  FROM warehouse_locations 
                  WHERE area = ? AND current_capacity < max_capacity AND is_active = 1 
                  ORDER BY distance, row_number, column_number 
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$row, $column, $area]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy bản đồ kho (tất cả vị trí)
    public function getWarehouseMap() {
        $query = "SELECT * FROM warehouse_locations WHERE is_active = 1 ORDER BY area, location_code";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tổ chức dữ liệu theo cấu trúc bản đồ
        $map = [];
        foreach ($locations as $location) {
            $area = $location['area'];
            if (!isset($map[$area])) { $map[$area] = []; }
            $map[$area][$location['id']] = $location;
        }
        
        return $map;
    }
}
?>
