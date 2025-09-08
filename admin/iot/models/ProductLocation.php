<?php
class ProductLocation {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Lấy tất cả phân bổ (tùy chọn filter theo product_id/location_id)
    public function getAllocations($productId = null, $locationId = null) {
        $sql = "SELECT pl.*, p.name AS product_name, p.sku, wl.location_code, wl.location_name
                FROM product_locations pl
                JOIN products p ON pl.product_id = p.id
                JOIN warehouse_locations wl ON pl.location_id = wl.id
                WHERE 1=1";

        $params = [];
        if ($productId) {
            $sql .= " AND pl.product_id = ?";
            $params[] = $productId;
        }
        if ($locationId) {
            $sql .= " AND pl.location_id = ?";
            $params[] = $locationId;
        }
        $sql .= " ORDER BY wl.area, wl.row_number, wl.column_number";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tổng tồn theo sản phẩm (gộp nhiều vị trí)
    public function getTotalQuantityByProduct($productId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantity),0) AS total_qty FROM product_locations WHERE product_id = ?");
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row ? $row['total_qty'] : 0);
    }

    // Lấy tồn theo vị trí
    public function getTotalQuantityByLocation($locationId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantity),0) AS total_qty FROM product_locations WHERE location_id = ?");
        $stmt->execute([$locationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row ? $row['total_qty'] : 0);
    }

    // Thêm/cộng dồn phân bổ
    public function allocateProduct($productId, $locationId, $quantity) {
        if ($quantity <= 0) return false;

        // Upsert theo (product_id, location_id)
        $sql = "INSERT INTO product_locations (product_id, location_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$productId, $locationId, $quantity]);
        if (!$ok) return false;

        // Cập nhật current_capacity vị trí
        $capSql = "UPDATE warehouse_locations SET current_capacity = LEAST(max_capacity, current_capacity + ?) WHERE id = ?";
        $capStmt = $this->db->prepare($capSql);
        $capStmt->execute([$quantity, $locationId]);

        return true;
    }

    // Giảm/thu hồi phân bổ
    public function deallocateProduct($productId, $locationId, $quantity) {
        if ($quantity <= 0) return false;

        // Giảm số lượng nhưng không âm
        $sql = "UPDATE product_locations
                SET quantity = GREATEST(0, quantity - ?)
                WHERE product_id = ? AND location_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantity, $productId, $locationId]);

        // Xóa hàng nếu về 0 để gọn bảng
        $this->db->prepare("DELETE FROM product_locations WHERE product_id = ? AND location_id = ? AND quantity <= 0")
                 ->execute([$productId, $locationId]);

        // Trừ current_capacity vị trí nhưng không âm
        $capSql = "UPDATE warehouse_locations SET current_capacity = GREATEST(0, current_capacity - ?) WHERE id = ?";
        $capStmt = $this->db->prepare($capSql);
        $capStmt->execute([$quantity, $locationId]);

        return true;
    }

    // Di chuyển từ vị trí A sang B
    public function moveProduct($productId, $fromLocationId, $toLocationId, $quantity) {
        if ($quantity <= 0) return false;

        $this->db->beginTransaction();
        try {
            $this->deallocateProduct($productId, $fromLocationId, $quantity);
            $this->allocateProduct($productId, $toLocationId, $quantity);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>


