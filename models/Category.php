<?php
/**
 * Class Category - Quản lý danh mục sản phẩm
 */

require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $slug;
    public $description;
    // public $parent_id; // Bỏ sử dụng danh mục cha
    public $location_id;
    public $image;
    public $is_active;
    public $sort_order;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy tất cả danh mục
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy danh mục theo ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy danh mục theo slug
     */
    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy danh mục cha
     */
    // Bỏ các hàm liên quan danh mục cha

    // Đã bỏ các hàm liên quan danh mục cha/con

    /**
     * Tạo danh mục mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, slug, description, location_id, image, is_active, sort_order) 
                  VALUES (:name, :slug, :description, :location_id, :image, :is_active, :sort_order)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật danh mục
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, slug = :slug, description = :description, 
                      location_id = :location_id, image = :image, 
                      is_active = :is_active, sort_order = :sort_order 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Xóa danh mục
     */
    public function delete($id) {
        // Kiểm tra xem có sản phẩm nào trong danh mục không
        $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":id", $id);
        $check_stmt->execute();
        $result = $check_stmt->fetch();

        if($result['count'] > 0) {
            return false; // Không thể xóa vì có sản phẩm
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Tạo slug từ tên danh mục
     */
    public function createSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Kiểm tra slug đã tồn tại chưa
        $counter = 1;
        $original_slug = $slug;
        while($this->slugExists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Kiểm tra slug đã tồn tại chưa
     */
    private function slugExists($slug) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $slug);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Đếm số danh mục
     */
    public function count() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Lấy danh mục có sản phẩm
     */
    public function getCategoriesWithProducts() {
        $query = "SELECT c.*, COUNT(p.id) as product_count 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN products p ON c.id = p.category_id 
                  WHERE c.is_active = 1 
                  GROUP BY c.id 
                  ORDER BY c.sort_order ASC, c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Kiểm tra tên danh mục đã tồn tại chưa (không phân biệt hoa thường)
     */
    public function nameExists($name, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name))";
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    }
}
?>
