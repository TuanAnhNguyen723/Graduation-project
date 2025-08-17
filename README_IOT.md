# 🚀 Hệ thống IoT Quản lý kho thông minh

## 📋 Tổng quan

Hệ thống IoT quản lý kho thông minh được tích hợp vào dự án Graduation Project hiện tại, cung cấp khả năng giám sát nhiệt độ thời gian thực, quản lý vị trí kho và theo dõi sản phẩm một cách thông minh.

## ✨ Tính năng chính

### 🔥 Giám sát nhiệt độ thời gian thực
- Hiển thị nhiệt độ và độ ẩm từ các cảm biến IoT
- Biểu đồ nhiệt độ theo thời gian
- Cảnh báo khi nhiệt độ vượt ngưỡng an toàn
- Lưu trữ lịch sử dữ liệu nhiệt độ

### 🗺️ Quản lý vị trí kho thông minh
- Hệ thống tọa độ kho (A1, B2, C3...)
- Bản đồ kho trực quan
- Theo dõi sức chứa và trạng thái vị trí
- Tối ưu hóa bố trí sản phẩm

### 📊 Dashboard thông minh
- Thống kê tổng quan kho
- Biểu đồ trực quan
- Cập nhật dữ liệu thời gian thực
- Giao diện responsive

### 🔌 API tích hợp
- API nhận dữ liệu từ cảm biến IoT
- API cung cấp dữ liệu cho dashboard
- Hỗ trợ CORS và validation
- JSON response chuẩn

## 🗄️ Cấu trúc Database

### Bảng mới được thêm:

1. **`warehouse_locations`** - Quản lý vị trí trong kho
2. **`temperature_sensors`** - Quản lý cảm biến nhiệt độ
3. **`temperature_readings`** - Lưu trữ dữ liệu nhiệt độ
4. **`product_locations`** - Liên kết sản phẩm với vị trí
5. **`alerts`** - Hệ thống cảnh báo
6. **`suppliers`** - Quản lý nhà cung cấp
7. **`inventory_transactions`** - Lịch sử giao dịch kho
8. **`maintenance_schedules`** - Lịch bảo trì thiết bị

### Cột mới được thêm vào bảng hiện có:
- **`products`**: expiry_date, min_stock_level, max_stock_level, weight, dimensions, storage_conditions
- **`categories`**: temperature_requirements, humidity_requirements

## 🚀 Cài đặt và sử dụng

### 1. Cài đặt Database
```sql
-- Chạy file database_schema.sql để tạo các bảng IoT mới
mysql -u username -p database_name < database_schema.sql
```

### 2. Cấu hình Database
Chỉnh sửa file `config/database.php` với thông tin kết nối database của bạn:
```php
$host = 'localhost';
$dbname = 'graduation_db';
$username = 'your_username';
$password = 'your_password';
```

### 3. Truy cập Dashboard
```
http://your-domain/iot-dashboard.php
```

### 4. Test API
```
http://your-domain/test/test_iot_api.html
```

## 🔌 API Endpoints

### 1. Gửi dữ liệu từ cảm biến IoT
```
POST /api/iot-sensor.php
Content-Type: application/json

{
    "sensor_code": "SENSOR-A1-001",
    "temperature": 25.5,
    "humidity": 65.0
}
```

### 2. Lấy dữ liệu dashboard
```
GET /api/dashboard-data.php?type=overview
GET /api/dashboard-data.php?type=temperature_chart&sensor_id=1
GET /api/dashboard-data.php?type=warehouse_map
GET /api/dashboard-data.php?type=sensor_status
```

### 3. Lấy dữ liệu cảm biến
```
GET /api/iot-sensor.php?sensor_code=SENSOR-A1-001&limit=10
```

## 📱 Giao diện Dashboard

### Thống kê tổng quan
- Tổng số cảm biến
- Tổng vị trí kho
- Tỷ lệ sử dụng sức chứa
- Tổng sức chứa

### Cảm biến nhiệt độ thời gian thực
- Hiển thị nhiệt độ và độ ẩm
- Trạng thái cảm biến
- Thời gian cập nhật cuối

### Biểu đồ
- Biểu đồ nhiệt độ 24 giờ
- Biểu đồ sức chứa theo vùng nhiệt độ

### Bản đồ kho
- Hiển thị trạng thái vị trí
- Màu sắc phân biệt trạng thái
- Tương tác hover

## 🧪 Test và Debug

### File test API
- `test/test_iot_api.html` - Giao diện test API
- Test gửi dữ liệu từ cảm biến
- Test lấy dữ liệu dashboard
- Test lấy dữ liệu cảm biến

### Kiểm tra Database
```sql
-- Kiểm tra cảm biến
SELECT * FROM temperature_sensors;

-- Kiểm tra dữ liệu nhiệt độ
SELECT * FROM temperature_readings ORDER BY reading_timestamp DESC LIMIT 10;

-- Kiểm tra vị trí kho
SELECT * FROM warehouse_locations;
```

## 🔧 Tùy chỉnh

### Thêm cảm biến mới
1. Thêm vào bảng `temperature_sensors`
2. Cập nhật dashboard để hiển thị
3. Kiểm tra API nhận dữ liệu

### Thay đổi ngưỡng cảnh báo
1. Cập nhật logic trong model
2. Thêm vào bảng `alerts`
3. Hiển thị trên dashboard

### Tùy chỉnh giao diện
1. Chỉnh sửa CSS trong `iot-dashboard.php`
2. Thay đổi layout và màu sắc
3. Thêm biểu đồ mới

## 🚨 Xử lý lỗi

### Lỗi kết nối Database
- Kiểm tra thông tin kết nối trong `config/database.php`
- Đảm bảo database đã được tạo
- Kiểm tra quyền truy cập user

### Lỗi API
- Kiểm tra log error của web server
- Validate dữ liệu đầu vào
- Kiểm tra CORS headers

### Lỗi Dashboard
- Kiểm tra console browser
- Kiểm tra network requests
- Validate dữ liệu từ database

## 🔮 Phát triển tương lai

### Tính năng dự kiến
1. **Hệ thống cảnh báo thông minh**
   - Email/SMS notifications
   - Webhook integrations
   - Escalation rules

2. **Machine Learning**
   - Dự báo nhiệt độ
   - Tối ưu hóa bố trí kho
   - Predictive maintenance

3. **Mobile App**
   - iOS/Android app
   - Push notifications
   - Offline mode

4. **Tích hợp nâng cao**
   - ERP systems
   - Warehouse management systems
   - IoT platforms

## 📞 Hỗ trợ

Nếu gặp vấn đề hoặc cần hỗ trợ:
1. Kiểm tra log errors
2. Xem file README này
3. Test API endpoints
4. Kiểm tra database schema

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

---

**🎯 Mục tiêu**: Xây dựng hệ thống quản lý kho thông minh với IoT để tối ưu hóa quy trình và nâng cao hiệu quả hoạt động.
