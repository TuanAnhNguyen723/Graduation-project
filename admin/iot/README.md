# IoT System - Hệ thống quản lý kho thông minh

## Tổng quan
Hệ thống IoT được tích hợp vào dự án PHP hiện tại để quản lý kho hàng thông minh với các cảm biến nhiệt độ và độ ẩm.

## Cấu trúc thư mục
```
admin/iot/
├── index.php              # Dashboard chính IoT
├── sensors/
│   └── index.php         # Quản lý cảm biến
├── locations/
│   └── index.php         # Quản lý vị trí kho
├── models/
│   ├── TemperatureSensor.php    # Model quản lý cảm biến
│   ├── TemperatureReading.php   # Model quản lý dữ liệu nhiệt độ
│   └── WarehouseLocation.php    # Model quản lý vị trí kho
├── api/
│   ├── iot-sensor.php           # API nhận dữ liệu từ cảm biến
│   └── dashboard-data.php       # API cung cấp dữ liệu cho dashboard
├── dashboard/
│   └── test_iot_api.html       # Trang test API
├── test_db.php                  # Test kết nối database
└── README.md                    # File này
```

## Tính năng chính

### 1. IoT Dashboard (`index.php`)
- Hiển thị tổng quan hệ thống
- Biểu đồ nhiệt độ theo thời gian thực
- Trạng thái cảm biến
- Bản đồ kho hàng với thống kê sức chứa

### 2. Quản lý cảm biến (`sensors/index.php`)
- Xem danh sách cảm biến
- Thêm/sửa/xóa cảm biến
- Theo dõi trạng thái hoạt động

### 3. Quản lý vị trí kho (`locations/index.php`)
- Quản lý các vị trí trong kho
- Theo dõi sức chứa và tỷ lệ sử dụng
- Phân vùng theo nhiệt độ

### 4. API Endpoints
- `POST /api/iot-sensor.php` - Nhận dữ liệu từ cảm biến
- `GET /api/iot-sensor.php` - Lấy dữ liệu cảm biến
- `GET /api/dashboard-data.php` - Lấy dữ liệu cho dashboard

## Cài đặt và sử dụng

### 1. Kiểm tra database
Chạy file `test_db.php` để kiểm tra kết nối database:
```
http://localhost/Graduation-project/admin/iot/test_db.php
```

### 2. Truy cập dashboard
- Dashboard chính: `http://localhost/Graduation-project/admin/iot/`
- Quản lý cảm biến: `http://localhost/Graduation-project/admin/iot/sensors/`
- Quản lý vị trí: `http://localhost/Graduation-project/admin/iot/locations/`

### 3. Test API
Sử dụng trang test API: `http://localhost/Graduation-project/admin/iot/dashboard/test_iot_api.html`

## Cấu trúc Database

### Bảng `temperature_sensors`
- `id` - ID cảm biến
- `sensor_name` - Tên cảm biến
- `sensor_code` - Mã cảm biến (unique)
- `location_id` - ID vị trí trong kho
- `sensor_type` - Loại cảm biến (temperature/humidity/both)
- `status` - Trạng thái (active/inactive/maintenance/error)
- `is_active` - Trạng thái hoạt động

### Bảng `temperature_readings`
- `id` - ID bản ghi
- `sensor_id` - ID cảm biến
- `temperature` - Nhiệt độ (°C)
- `humidity` - Độ ẩm (%)
- `reading_timestamp` - Thời gian ghi nhận

### Bảng `warehouse_locations`
- `id` - ID vị trí
- `location_code` - Mã vị trí (unique)
- `location_name` - Tên vị trí
- `area` - Khu vực
- `temperature_zone` - Vùng nhiệt độ (cold/cool/room/warm)
- `max_capacity` - Sức chứa tối đa
- `current_capacity` - Sức chứa hiện tại
- `is_active` - Trạng thái hoạt động

## Gửi dữ liệu từ cảm biến

### Cách 1: Sử dụng API
```bash
curl -X POST http://localhost/Graduation-project/admin/iot/api/iot-sensor.php \
  -d "sensor_id=1" \
  -d "temperature=25.5" \
  -d "humidity=65.0"
```

### Cách 2: Sử dụng form test
Mở trang `test_iot_api.html` và sử dụng form để test API.

## Tùy chỉnh

### Thêm cảm biến mới
1. Thêm vào bảng `temperature_sensors`
2. Cập nhật model `TemperatureSensor.php` nếu cần

### Thêm vị trí mới
1. Thêm vào bảng `warehouse_locations`
2. Cập nhật model `WarehouseLocation.php` nếu cần

### Tùy chỉnh dashboard
Chỉnh sửa file `index.php` để thay đổi giao diện và thêm tính năng mới.

## Xử lý lỗi

### Lỗi kết nối database
- Kiểm tra file `config/database.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra thông tin đăng nhập database

### Lỗi hiển thị dữ liệu
- Kiểm tra cấu trúc bảng database
- Đảm bảo các model methods trả về đúng format dữ liệu
- Kiểm tra console browser để xem lỗi JavaScript

## Phát triển thêm

### Tính năng có thể thêm
- Alert system khi nhiệt độ vượt ngưỡng
- Báo cáo định kỳ
- Export dữ liệu
- Mobile app
- Real-time notifications

### Cải thiện hiệu suất
- Caching dữ liệu
- Pagination cho danh sách lớn
- Optimize database queries
- CDN cho assets

## Hỗ trợ
Nếu gặp vấn đề, hãy kiểm tra:
1. File log PHP
2. Console browser
3. Network tab trong Developer Tools
4. File `test_db.php` để kiểm tra database
