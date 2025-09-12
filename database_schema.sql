-- ==============================================================
-- TẠO DATABASE
-- ==============================================================
CREATE DATABASE IF NOT EXISTS graduation_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE graduation_project;

-- ==============================================================
-- BẢNG IOT WAREHOUSE MANAGEMENT (TẠO TRƯỚC VÌ LÀ CHA)
-- ==============================================================

-- Update 9/12/2025
-- Xóa cột min_threshold
ALTER TABLE `temperature_sensors` DROP COLUMN IF EXISTS `min_threshold`;

-- Xóa cột max_threshold  
ALTER TABLE `temperature_sensors` DROP COLUMN IF EXISTS `max_threshold`;


-- Migration: Remove parent_id from categories safely (FK + column)
-- Lưu ý: Đặt sau khi bảng categories đã tồn tại
-- Xóa khóa ngoại tham chiếu đến parent_id (nếu có)
SET @fk_parent := (
  SELECT rc.CONSTRAINT_NAME
  FROM information_schema.REFERENTIAL_CONSTRAINTS rc
  JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
   AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
   AND rc.TABLE_NAME = kcu.TABLE_NAME
  WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
    AND rc.TABLE_NAME = 'categories'
    AND kcu.COLUMN_NAME = 'parent_id'
  LIMIT 1
);
SET @sql_drop_fk = IF(@fk_parent IS NOT NULL,
  CONCAT('ALTER TABLE `categories` DROP FOREIGN KEY `', @fk_parent, '`'),
  NULL
);
PREPARE stmt FROM IFNULL(@sql_drop_fk, 'SELECT 1');
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Xóa cột parent_id nếu tồn tại (MySQL 8.0.29+ hỗ trợ IF EXISTS)
ALTER TABLE `categories` DROP COLUMN IF EXISTS `parent_id`;

-- BẢNG WAREHOUSE_LOCATIONS (VỊ TRÍ TRONG KHO)
CREATE TABLE IF NOT EXISTS warehouse_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(50) UNIQUE NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    area VARCHAR(100) NOT NULL,
    row_number INT,
    column_number INT,
    shelf_number INT,
    temperature_zone ENUM('frozen','chilled','ambient') NOT NULL DEFAULT 'ambient'
        COMMENT 'Mức môi trường áp dụng đồng thời cho nhiệt độ/độ ẩm',
    max_capacity INT NOT NULL DEFAULT 0,
    current_capacity INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- BẢNG CORE E-COMMERCE
-- ==============================================================

-- BẢNG CATEGORIES (DANH MỤC SẢN PHẨM)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    -- parent_id INT DEFAULT NULL, -- Đã bỏ sử dụng danh mục cha
    location_id INT DEFAULT NULL,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- BẢNG PRODUCTS (SẢN PHẨM)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    sku VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    brand VARCHAR(100),
    images TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================================================
-- BẢNG TEMPERATURE_SENSORS
-- ==============================================================
CREATE TABLE IF NOT EXISTS temperature_sensors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_name VARCHAR(255) NOT NULL,
    sensor_code VARCHAR(100) UNIQUE NOT NULL,
    location_id INT,
    sensor_type ENUM('temperature', 'humidity', 'both') DEFAULT 'both',
    status ENUM('active', 'inactive', 'maintenance', 'error') DEFAULT 'active',
    current_temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    manufacturer VARCHAR(255) NULL,
    model VARCHAR(255) NULL,
    serial_number VARCHAR(255) NULL,
    installation_date DATE NULL,
    last_calibration DATE,
    description TEXT NULL,
    min_threshold DECIMAL(10,2) NULL,
    max_threshold DECIMAL(10,2) NULL,
    notes TEXT NULL,
    next_calibration DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==============================================================
-- BẢNG TEMPERATURE_READINGS
-- ==============================================================
CREATE TABLE IF NOT EXISTS temperature_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity DECIMAL(5,2),
    reading_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES temperature_sensors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- BẢNG PRODUCT_LOCATIONS (LIÊN KẾT SẢN PHẨM - VỊ TRÍ KHO)
-- ==============================================================
CREATE TABLE IF NOT EXISTS product_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    location_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    reserved_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_product_location (product_id, location_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- BẢNG NOTIFICATIONS
-- ==============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('product', 'sensor', 'system', 'alert') DEFAULT 'system',
    icon VARCHAR(100) DEFAULT 'iconoir-bell',
    icon_color VARCHAR(50) DEFAULT 'primary',
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT DEFAULT NULL COMMENT 'ID của sản phẩm, cảm biến hoặc entity liên quan',
    related_type VARCHAR(50) DEFAULT NULL COMMENT 'Loại entity liên quan: product, sensor, category',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==============================================================
-- INSERT DỮ LIỆU MẪU
-- ==============================================================

-- CATEGORIES
INSERT INTO categories (name, slug, description) VALUES
('Điện tử', 'dien-tu', 'Sản phẩm điện tử và công nghệ'),
('Thời trang', 'thoi-trang', 'Quần áo và phụ kiện thời trang'),
('Nhà cửa', 'nha-cua', 'Đồ dùng gia đình và nội thất'),
('Sách', 'sach', 'Sách vở và tài liệu học tập');

-- PRODUCTS
INSERT INTO products (name, slug, description, sku, price, sale_price, stock_quantity, category_id, brand) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'Điện thoại thông minh cao cấp', 'IP15P001', 25000000, 23000000, 50, 1, 'Apple'),
('Samsung Galaxy S24', 'samsung-galaxy-s24', 'Điện thoại Android flagship', 'SG24S001', 22000000, 20000000, 45, 1, 'Samsung'),
('Áo thun nam', 'ao-thun-nam', 'Áo thun cotton 100%', 'ATN001', 150000, 120000, 200, 2, 'Local Brand'),
('Sofa phòng khách', 'sofa-phong-khach', 'Sofa 3 chỗ ngồi hiện đại', 'SPK001', 8000000, 7500000, 10, 3, 'Home Decor'),
('Sách Lập trình PHP', 'sach-lap-trinh-php', 'Hướng dẫn lập trình PHP từ cơ bản', 'SLPHP001', 150000, 135000, 100, 4, 'Tech Books');

-- WAREHOUSE_LOCATIONS
INSERT INTO warehouse_locations (location_code, location_name, area, row_number, column_number, shelf_number, temperature_zone, max_capacity) VALUES
('A-01-01', 'Khu A - Hàng 1 - Cột 1', 'A', 1, 1, 1, 'ambient', 1000),
('A-01-02', 'Khu A - Hàng 1 - Cột 2', 'A', 1, 2, 1, 'ambient', 1000),
('B-01-01', 'Khu B - Hàng 1 - Cột 1', 'B', 1, 1, 1, 'chilled', 800),
('B-01-02', 'Khu B - Hàng 1 - Cột 2', 'B', 1, 2, 1, 'chilled', 800),
('C-01-01', 'Khu C - Hàng 1 - Cột 1', 'C', 1, 1, 1, 'frozen', 600),
('C-01-02', 'Khu C - Hàng 1 - Cột 2', 'C', 1, 2, 1, 'frozen', 600);

-- TEMPERATURE_SENSORS
INSERT INTO temperature_sensors (sensor_name, sensor_code, location_id, sensor_type) VALUES
('Cảm biến A1', 'SENSOR_A1', 1, 'both'),
('Cảm biến A2', 'SENSOR_A2', 2, 'both'),
('Cảm biến B1', 'SENSOR_B1', 3, 'both'),
('Cảm biến B2', 'SENSOR_B2', 4, 'both'),
('Cảm biến C1', 'SENSOR_C1', 5, 'both'),
('Cảm biến C2', 'SENSOR_C2', 6, 'both');

-- TEMPERATURE_READINGS
INSERT INTO temperature_readings (sensor_id, temperature, humidity) VALUES
(1, 22.5, 65.0),
(1, 22.8, 64.5),
(1, 23.1, 63.8),
(2, 22.3, 66.2),
(2, 22.7, 65.8),
(2, 23.0, 65.1),
(3, 24.2, 58.5),
(3, 24.5, 58.0),
(3, 24.8, 57.5),
(4, 24.0, 59.2),
(4, 24.3, 58.8),
(4, 24.6, 58.3),
(5, 26.1, 52.0),
(5, 26.4, 51.5),
(5, 26.7, 51.0),
(6, 25.8, 53.2),
(6, 26.1, 52.8),
(6, 26.4, 52.3);

-- NOTIFICATIONS
INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type, is_read, created_at) VALUES
('Sản phẩm mới được thêm', 'Đã thêm sản phẩm mới "iPhone 15 Pro" vào hệ thống.', 'product', 'iconoir-shopping-bag', 'primary', 1, 'product', 0, NOW() - INTERVAL 2 MINUTE),
('Danh mục đã được cập nhật', 'Danh mục "Điện tử" đã được cập nhật thành công.', 'product', 'iconoir-folder', 'success', 1, 'category', 0, NOW() - INTERVAL 10 MINUTE),
('Cảm biến nhiệt độ báo động', 'Nhiệt độ tại vị trí A-01-01 vượt quá ngưỡng cho phép (25°C).', 'alert', 'iconoir-thermometer', 'warning', 1, 'sensor', 0, NOW() - INTERVAL 15 MINUTE),
('Cảm biến hoạt động bình thường', 'Cảm biến SENSOR_B1 đã hoạt động trở lại bình thường.', 'sensor', 'iconoir-check-circle', 'success', 3, 'sensor', 1, NOW() - INTERVAL 1 HOUR),
('Hệ thống cập nhật', 'Hệ thống đã được cập nhật lên phiên bản mới nhất.', 'system', 'iconoir-settings', 'info', NULL, NULL, 1, NOW() - INTERVAL 2 HOUR),
('Cảnh báo độ ẩm cao', 'Độ ẩm tại vị trí C-01-01 đang ở mức cao (85%).', 'alert', 'iconoir-water-drop', 'danger', 5, 'sensor', 0, NOW() - INTERVAL 30 MINUTE),
('Sản phẩm sắp hết hàng', 'Sản phẩm "Samsung Galaxy S24" chỉ còn 5 sản phẩm trong kho.', 'product', 'iconoir-warning-triangle', 'warning', 2, 'product', 0, NOW() - INTERVAL 45 MINUTE),
('Bảo trì cảm biến', 'Cảm biến SENSOR_C2 đang trong quá trình bảo trì.', 'sensor', 'iconoir-settings', 'info', 6, 'sensor', 1, NOW() - INTERVAL 3 HOUR);

-- ==============================================================
-- AUTO_INCREMENT
-- ==============================================================
ALTER TABLE categories AUTO_INCREMENT = 5;
ALTER TABLE products AUTO_INCREMENT = 6;
ALTER TABLE warehouse_locations AUTO_INCREMENT = 7;
ALTER TABLE temperature_sensors AUTO_INCREMENT = 7;
ALTER TABLE temperature_readings AUTO_INCREMENT = 19;
ALTER TABLE notifications AUTO_INCREMENT = 9;
