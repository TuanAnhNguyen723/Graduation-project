<?php
/**
 * Class Product - Quản lý sản phẩm
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $slug;
    public $description;
    public $sku;
    public $price;
    public $sale_price;
    public $stock_quantity;
    public $category_id;
    public $brand;
    public $images;
    public $is_active;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy tất cả sản phẩm
     */
    public function getAll($search = '', $category_filter = '', $limit = null, $offset = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        
        $params = array();
        
        // Thêm điều kiện tìm kiếm
        if (!empty($search)) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = "%" . $search . "%";
        }
        
        // Thêm điều kiện lọc theo danh mục
        if (!empty($category_filter)) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_filter;
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        if($limit) {
            $query .= " LIMIT :limit";
            if($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind các parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy sản phẩm theo ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy sản phẩm theo slug
     */
    public function getBySlug($slug) {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.slug = :slug";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":slug", $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy sản phẩm theo danh mục
     */
    public function getByCategory($category_id, $limit = null, $offset = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id AND p.is_active = 1 
                  ORDER BY p.created_at DESC";
        
        if($limit) {
            $query .= " LIMIT :limit";
            if($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        
        if($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public function search($keyword, $limit = null, $offset = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE (p.name LIKE :keyword OR p.description LIKE :keyword OR p.sku LIKE :keyword) 
                  AND p.is_active = 1 
                  ORDER BY p.created_at DESC";
        
        if($limit) {
            $query .= " LIMIT :limit";
            if($offset) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%" . $keyword . "%";
        $stmt->bindParam(":keyword", $keyword);
        
        if($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            if($offset) {
                $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * Tạo sản phẩm mới
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, slug, description, sku, price, sale_price, stock_quantity, 
                   category_id, brand, images, is_active) 
                  VALUES (:name, :slug, :description, :sku, :price, :sale_price, 
                          :stock_quantity, :category_id, :brand, :images, :is_active)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sku = htmlspecialchars(strip_tags($this->sku));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        // Không xử lý images với htmlspecialchars vì có thể chứa đường dẫn file
        $this->images = $this->images ?: '';

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":sale_price", $this->sale_price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":images", $this->images);
        $stmt->bindParam(":is_active", $this->is_active);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, slug = :slug, description = :description, 
                      sku = :sku, price = :price, sale_price = :sale_price, 
                      stock_quantity = :stock_quantity, category_id = :category_id, 
                      brand = :brand, images = :images, is_active = :is_active 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->sku = htmlspecialchars(strip_tags($this->sku));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        // Không xử lý images với htmlspecialchars vì có thể chứa đường dẫn file
        $this->images = $this->images ?: '';

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":sale_price", $this->sale_price);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":images", $this->images);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Xóa sản phẩm
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Cập nhật số lượng tồn kho
     */
    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table_name . " SET stock_quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Tạo slug từ tên sản phẩm
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
     * Kiểm tra SKU đã tồn tại chưa
     */
    public function skuExists($sku, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE sku = :sku";
        if($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sku", $sku);
        if($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Đếm số sản phẩm
     */
    public function count($category_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        if($category_id) {
            $query .= " WHERE category_id = :category_id";
        }
        
        $stmt = $this->conn->prepare($query);
        if($category_id) {
            $stmt->bindParam(":category_id", $category_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Lấy sản phẩm nổi bật
     */
    public function getFeaturedProducts($limit = 8) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lấy sản phẩm theo giá
     */
    public function getByPriceRange($min_price, $max_price, $limit = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.price BETWEEN :min_price AND :max_price AND p.is_active = 1 
                  ORDER BY p.price ASC";
        
        if($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":min_price", $min_price);
        $stmt->bindParam(":max_price", $max_price);
        
        if($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
?>
