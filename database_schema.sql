-- ==============================================================
-- CẬP NHẬT DATABASE HIỆN TẠI
-- ==============================================================


-- Liên kết danh mục với vị trí kho
ALTER TABLE categories ADD COLUMN IF NOT EXISTS location_id INT NULL AFTER parent_id;
ALTER TABLE categories ADD CONSTRAINT fk_categories_location_id FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL;

-- Chuẩn hóa: bỏ cấu hình mức nhiệt/ẩm ở danh mục; dùng theo vị trí kho
ALTER TABLE categories DROP COLUMN IF EXISTS temperature_type;
-- Bỏ trường humidity_type ở danh mục (đã dùng theo vị trí)
ALTER TABLE categories DROP COLUMN IF EXISTS humidity_type;


ALTER TABLE warehouse_locations
MODIFY COLUMN temperature_zone ENUM('frozen','chilled','ambient')
    NOT NULL DEFAULT 'ambient'
    COMMENT 'Mức môi trường áp dụng đồng thời cho nhiệt độ/độ ẩm';

--Cập nhật cột humidity vào temperature_sensor
ALTER TABLE temperature_sensors
ADD COLUMN humidity DECIMAL(5,2) AFTER current_temperature;
-- ==============================================================
-- DATABASE SCHEMA CHO DỰ ÁN GRADUATION PROJECT
-- Hệ thống quản lý E-commerce & IoT Warehouse Management
-- ==============================================================

-- TẠO DATABASE
CREATE DATABASE IF NOT EXISTS graduation_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE graduation_project;

-- ==============================================================
-- BẢNG CORE E-COMMERCE
-- ==============================================================

-- BẢNG CATEGORIES (DANH MỤC SẢN PHẨM)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT NULL,
    location_id INT DEFAULT NULL,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL
);

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
);

-- ==============================================================
-- BẢNG IOT WAREHOUSE MANAGEMENT
-- ==============================================================

-- BẢNG WAREHOUSE_LOCATIONS (VỊ TRÍ TRONG KHO)
CREATE TABLE IF NOT EXISTS warehouse_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(50) UNIQUE NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    area VARCHAR(100) NOT NULL,
    temperature_zone ENUM('frozen','chilled','ambient') DEFAULT 'ambient' COMMENT 'Mức môi trường áp dụng đồng thời cho nhiệt độ/độ ẩm',
    max_capacity INT NOT NULL DEFAULT 0,
    current_capacity INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- BẢNG TEMPERATURE_SENSORS (CẢM BIẾN NHIỆT ĐỘ)
CREATE TABLE IF NOT EXISTS temperature_sensors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_name VARCHAR(255) NOT NULL,
    sensor_code VARCHAR(100) UNIQUE NOT NULL,
    location_id INT,
    sensor_type ENUM('temperature', 'humidity', 'both') DEFAULT 'both',
    status ENUM('active', 'inactive', 'maintenance', 'error') DEFAULT 'active',
    current_temperature DECIMAL(5,2),
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
);

-- BẢNG TEMPERATURE_READINGS (DỮ LIỆU NHIỆT ĐỘ)
CREATE TABLE IF NOT EXISTS temperature_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    humidity DECIMAL(5,2),
    reading_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES temperature_sensors(id) ON DELETE CASCADE
);

-- BẢNG PRODUCT_LOCATIONS (LIÊN KẾT SẢN PHẨM - VỊ TRÍ KHO)
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
);

-- ==============================================================
-- INSERT DỮ LIỆU MẪU
-- ==============================================================

-- INSERT DỮ LIỆU MẪU CHO BẢNG CATEGORIES
INSERT INTO categories (name, slug, description) VALUES
('Điện tử', 'dien-tu', 'Sản phẩm điện tử và công nghệ'),
('Thời trang', 'thoi-trang', 'Quần áo và phụ kiện thời trang'),
('Nhà cửa', 'nha-cua', 'Đồ dùng gia đình và nội thất'),
('Sách', 'sach', 'Sách vở và tài liệu học tập');

-- INSERT DỮ LIỆU MẪU CHO BẢNG PRODUCTS
INSERT INTO products (name, slug, description, sku, price, sale_price, stock_quantity, category_id, brand) VALUES
('iPhone 15 Pro', 'iphone-15-pro', 'Điện thoại thông minh cao cấp', 'IP15P001', 25000000, 23000000, 50, 1, 'Apple'),
('Samsung Galaxy S24', 'samsung-galaxy-s24', 'Điện thoại Android flagship', 'SG24S001', 22000000, 20000000, 45, 1, 'Samsung'),
('Áo thun nam', 'ao-thun-nam', 'Áo thun cotton 100%', 'ATN001', 150000, 120000, 200, 2, 'Local Brand'),
('Sofa phòng khách', 'sofa-phong-khach', 'Sofa 3 chỗ ngồi hiện đại', 'SPK001', 8000000, 7500000, 10, 3, 'Home Decor'),
('Sách Lập trình PHP', 'sach-lap-trinh-php', 'Hướng dẫn lập trình PHP từ cơ bản', 'SLPHP001', 150000, 135000, 100, 4, 'Tech Books');

-- INSERT DỮ LIỆU MẪU CHO BẢNG WAREHOUSE_LOCATIONS
INSERT INTO warehouse_locations (location_code, location_name, area, row_number, column_number, shelf_number, temperature_zone, max_capacity) VALUES
('A-01-01', 'Khu A - Hàng 1 - Cột 1', 'A', 1, 1, 1, 'ambient', 1000),
('A-01-02', 'Khu A - Hàng 1 - Cột 2', 'A', 1, 2, 1, 'ambient', 1000),
('B-01-01', 'Khu B - Hàng 1 - Cột 1', 'B', 1, 1, 1, 'chilled', 800),
('B-01-02', 'Khu B - Hàng 1 - Cột 2', 'B', 1, 2, 1, 'chilled', 800),
('C-01-01', 'Khu C - Hàng 1 - Cột 1', 'C', 1, 1, 1, 'frozen', 600),
('C-01-02', 'Khu C - Hàng 1 - Cột 2', 'C', 1, 2, 1, 'frozen', 600);

-- INSERT DỮ LIỆU MẪU CHO BẢNG TEMPERATURE_SENSORS
INSERT INTO temperature_sensors (sensor_name, sensor_code, location_id, sensor_type) VALUES
('Cảm biến A1', 'SENSOR_A1', 1, 'both'),
('Cảm biến A2', 'SENSOR_A2', 2, 'both'),
('Cảm biến B1', 'SENSOR_B1', 3, 'both'),
('Cảm biến B2', 'SENSOR_B2', 4, 'both'),
('Cảm biến C1', 'SENSOR_C1', 5, 'both'),
('Cảm biến C2', 'SENSOR_C2', 6, 'both');

-- INSERT DỮ LIỆU MẪU CHO BẢNG TEMPERATURE_READINGS
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

-- ==============================================================
-- TẠO INDEXES ĐỂ TỐI ƯU HIỆU SUẤT
-- ==============================================================

-- Indexes cho bảng categories
CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_categories_parent_id ON categories(parent_id);
CREATE INDEX idx_categories_is_active ON categories(is_active);
CREATE INDEX idx_categories_location_id ON categories(location_id);

-- Indexes cho bảng products
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_products_price ON products(price);

-- Indexes cho bảng warehouse_locations
CREATE INDEX idx_warehouse_locations_location_code ON warehouse_locations(location_code);
CREATE INDEX idx_warehouse_locations_area ON warehouse_locations(area);
CREATE INDEX idx_warehouse_locations_temperature_zone ON warehouse_locations(temperature_zone);

-- Indexes cho bảng temperature_sensors
CREATE INDEX idx_temperature_sensors_sensor_code ON temperature_sensors(sensor_code);
CREATE INDEX idx_temperature_sensors_location_id ON temperature_sensors(location_id);
CREATE INDEX idx_temperature_sensors_status ON temperature_sensors(status);

-- Indexes cho bảng temperature_readings
CREATE INDEX idx_temperature_readings_sensor_id ON temperature_readings(sensor_id);
CREATE INDEX idx_temperature_readings_timestamp ON temperature_readings(reading_timestamp);

-- Indexes cho bảng product_locations
CREATE INDEX idx_product_locations_product_id ON product_locations(product_id);
CREATE INDEX idx_product_locations_location_id ON product_locations(location_id);

-- ==============================================================
-- BẢNG NOTIFICATIONS (THÔNG BÁO)
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
);

-- Indexes cho bảng notifications
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_related ON notifications(related_type, related_id);

-- ==============================================================
-- INSERT DỮ LIỆU MẪU CHO BẢNG NOTIFICATIONS
-- ==============================================================

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
-- CẬP NHẬT AUTO_INCREMENT
-- ==============================================================

ALTER TABLE categories AUTO_INCREMENT = 5;
ALTER TABLE products AUTO_INCREMENT = 6;
ALTER TABLE warehouse_locations AUTO_INCREMENT = 7;
ALTER TABLE temperature_sensors AUTO_INCREMENT = 7;
ALTER TABLE temperature_readings AUTO_INCREMENT = 19;
ALTER TABLE notifications AUTO_INCREMENT = 9;
