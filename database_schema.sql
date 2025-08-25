-- ==============================================================
-- CẬP NHẬT DATABASE HIỆN TẠI
-- ==============================================================

-- Thêm trường temperature_type vào bảng categories nếu chưa có
ALTER TABLE categories ADD COLUMN IF NOT EXISTS temperature_type ENUM('frozen', 'chilled', 'ambient') DEFAULT 'ambient' COMMENT 'Loại nhiệt độ: frozen(≤-18°C), chilled(0-5°C), ambient(15-33°C)';

-- Cập nhật dữ liệu mẫu với temperature_type
UPDATE categories SET temperature_type = 'ambient' WHERE temperature_type IS NULL OR temperature_type = '';
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
    image VARCHAR(255),
    temperature_type ENUM('frozen', 'chilled', 'ambient') DEFAULT 'ambient' COMMENT 'Loại nhiệt độ: frozen(≤-18°C), chilled(0-5°C), ambient(15-33°C)',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
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
    row_number INT,
    column_number INT,
    shelf_number INT,
    temperature_zone VARCHAR(50),
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
('A-01-01', 'Khu A - Hàng 1 - Cột 1', 'A', 1, 1, 1, 'cool', 1000),
('A-01-02', 'Khu A - Hàng 1 - Cột 2', 'A', 1, 2, 1, 'cool', 1000),
('B-01-01', 'Khu B - Hàng 1 - Cột 1', 'B', 1, 1, 1, 'normal', 800),
('B-01-02', 'Khu B - Hàng 1 - Cột 2', 'B', 1, 2, 1, 'normal', 800),
('C-01-01', 'Khu C - Hàng 1 - Cột 1', 'C', 1, 1, 1, 'warm', 600),
('C-01-02', 'Khu C - Hàng 1 - Cột 2', 'C', 1, 2, 1, 'warm', 600);

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

-- ==============================================================
-- CẬP NHẬT AUTO_INCREMENT
-- ==============================================================

ALTER TABLE categories AUTO_INCREMENT = 5;
ALTER TABLE products AUTO_INCREMENT = 6;
ALTER TABLE warehouse_locations AUTO_INCREMENT = 7;
ALTER TABLE temperature_sensors AUTO_INCREMENT = 7;
ALTER TABLE temperature_readings AUTO_INCREMENT = 19;
