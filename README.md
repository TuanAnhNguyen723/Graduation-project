# 🎯 Admin Dashboard - Graduation Project

Hệ thống quản trị admin dashboard hoàn chỉnh được xây dựng bằng PHP và MySQL, tập trung vào quản lý sản phẩm và danh mục với đầy đủ chức năng CRUD.

## 🚀 Tính năng chính

### ✨ Dashboard chính
- **Thống kê tổng quan**: Số lượng sản phẩm, danh mục
- **Dữ liệu gần đây**: Sản phẩm và danh mục mới nhất
- **Thao tác nhanh**: Link trực tiếp đến các chức năng chính
- **Giao diện responsive**: Tương thích mọi thiết bị

### 📦 Quản lý sản phẩm
- **Danh sách sản phẩm**: Hiển thị dạng bảng với thông tin chi tiết
- **Tìm kiếm & lọc**: Theo tên, SKU, danh mục
- **Thêm sản phẩm mới**: Form đầy đủ với validation
- **Chỉnh sửa sản phẩm**: Cập nhật thông tin chi tiết
- **Xóa sản phẩm**: Xác nhận trước khi xóa
- **Quản lý hình ảnh**: Preview và upload URL
- **Auto-generate SKU**: Tự động tạo SKU từ tên sản phẩm

### 📁 Quản lý danh mục
- **Danh sách danh mục**: Hiển thị dạng card với thống kê
- **Phân cấp danh mục**: Hỗ trợ danh mục cha-con
- **Tìm kiếm**: Theo tên và mô tả
- **Thêm danh mục mới**: Form với validation
- **Chỉnh sửa danh mục**: Cập nhật thông tin
- **Xóa danh mục**: Kiểm tra ràng buộc trước khi xóa
- **Auto-generate slug**: Tự động tạo slug từ tên

## 🏗️ Cấu trúc thư mục

```
Graduation-project/
├── 📁 admin/                          # Quản trị admin
│   ├── 📁 products/                   # Quản lý sản phẩm
│   │   ├── index.php                  # Danh sách sản phẩm
│   │   ├── create.php                 # Thêm sản phẩm mới
│   │   ├── edit.php                   # Chỉnh sửa sản phẩm
│   │   └── delete.php                 # Xóa sản phẩm
│   └── 📁 categories/                 # Quản lý danh mục
│       ├── index.php                  # Danh sách danh mục
│       ├── create.php                 # Thêm danh mục mới
│       ├── edit.php                   # Chỉnh sửa danh mục
│       └── delete.php                 # Xóa danh mục
├── 📁 config/                         # Cấu hình hệ thống
│   └── database.php                   # Kết nối database
├── 📁 models/                         # Model classes
│   ├── Product.php                    # Class quản lý sản phẩm
│   └── Category.php                   # Class quản lý danh mục
├── 📁 assets/                         # Tài nguyên tĩnh
│   ├── css/                           # Stylesheet
│   ├── js/                            # JavaScript
│   └── images/                        # Hình ảnh
├── index.php                          # Dashboard chính
├── database_schema.sql                # Cấu trúc database
└── README.md                          # Hướng dẫn sử dụng
```

## 🛠️ Yêu cầu hệ thống

### 📋 Phần mềm cần thiết
- **PHP**: 7.4 trở lên
- **MySQL**: 5.7 trở lên hoặc MariaDB 10.2 trở lên
- **Web Server**: Apache hoặc Nginx
- **XAMPP/WAMP**: Để phát triển local

### 🔌 Extensions PHP
- PDO MySQL
- JSON
- mbstring
- fileinfo

## ⚙️ Cài đặt và cấu hình

### 1️⃣ Clone hoặc tải project
```bash
git clone [repository-url]
cd Graduation-project
```

### 2️⃣ Cấu hình database
1. Tạo database mới trong MySQL
2. Import file `database_schema.sql`
3. Cập nhật thông tin kết nối trong `config/database.php`

### 3️⃣ Cấu hình web server
- Đặt project vào thư mục web server (htdocs, www, public_html)
- Đảm bảo quyền ghi cho thư mục uploads (nếu có)

### 4️⃣ Kiểm tra cài đặt
- Truy cập `http://localhost/Graduation-project/`
- Kiểm tra kết nối database
- Test các chức năng CRUD

## 🗄️ Cấu trúc Database

### 📊 Bảng chính

#### `categories` - Danh mục sản phẩm
- `id`: ID tự động tăng
- `name`: Tên danh mục
- `slug`: URL thân thiện
- `description`: Mô tả
- `parent_id`: ID danh mục cha (NULL nếu là danh mục gốc)
- `image`: URL hình ảnh
- `is_active`: Trạng thái hoạt động
- `sort_order`: Thứ tự hiển thị
- `created_at`: Ngày tạo

#### `products` - Sản phẩm
- `id`: ID tự động tăng
- `name`: Tên sản phẩm
- `slug`: URL thân thiện
- `description`: Mô tả chi tiết
- `sku`: Mã sản phẩm duy nhất
- `price`: Giá gốc
- `sale_price`: Giá khuyến mãi
- `stock_quantity`: Số lượng tồn kho
- `category_id`: ID danh mục
- `brand`: Thương hiệu
- `images`: JSON chứa URLs hình ảnh
- `is_active`: Trạng thái hoạt động
- `created_at`: Ngày tạo

## 🔧 Sử dụng hệ thống

### 🏠 Dashboard chính
- Truy cập `index.php` để xem tổng quan
- Xem thống kê sản phẩm và danh mục
- Sử dụng các nút thao tác nhanh

### 📦 Quản lý sản phẩm
1. **Xem danh sách**: `admin/products/index.php`
2. **Thêm mới**: Click "Thêm sản phẩm mới"
3. **Chỉnh sửa**: Click nút "Sửa" trên từng sản phẩm
4. **Xóa**: Click nút "Xóa" và xác nhận
5. **Tìm kiếm**: Sử dụng form tìm kiếm và lọc

### 📁 Quản lý danh mục
1. **Xem danh sách**: `admin/categories/index.php`
2. **Thêm mới**: Click "Thêm danh mục mới"
3. **Chỉnh sửa**: Click nút "Sửa" trên từng danh mục
4. **Xóa**: Click nút "Xóa" và xác nhận
5. **Tìm kiếm**: Sử dụng form tìm kiếm

## 🎨 Giao diện và UX

### ✨ Thiết kế hiện đại
- **Bootstrap 5**: Framework CSS responsive
- **Iconoir Icons**: Bộ icon đẹp và nhất quán
- **Gradient colors**: Màu sắc hiện đại và bắt mắt
- **Hover effects**: Hiệu ứng tương tác mượt mà

### 📱 Responsive Design
- Tương thích mọi kích thước màn hình
- Mobile-first approach
- Touch-friendly interface
- Optimized cho tablet và desktop

### 🎯 User Experience
- **Breadcrumb navigation**: Dễ dàng định hướng
- **Form validation**: Kiểm tra dữ liệu real-time
- **Confirmation dialogs**: Xác nhận trước khi xóa
- **Success/Error messages**: Thông báo rõ ràng
- **Loading states**: Hiển thị trạng thái xử lý

## 🔒 Bảo mật

### 🛡️ Các biện pháp bảo mật
- **Input sanitization**: Làm sạch dữ liệu đầu vào
- **SQL injection prevention**: Sử dụng PDO prepared statements
- **XSS protection**: Sử dụng `htmlspecialchars()`
- **CSRF protection**: Session-based security
- **File upload validation**: Kiểm tra file upload

### ⚠️ Lưu ý bảo mật
- Thay đổi mật khẩu database mặc định
- Cập nhật PHP và MySQL thường xuyên
- Sử dụng HTTPS trong production
- Backup database định kỳ

## 🚀 Tính năng nâng cao

### 🔍 Tìm kiếm và lọc
- **Full-text search**: Tìm kiếm theo nhiều tiêu chí
- **Advanced filtering**: Lọc theo danh mục, trạng thái
- **Real-time results**: Kết quả tìm kiếm tức thì

### 📊 Thống kê và báo cáo
- **Dashboard metrics**: Số liệu tổng quan
- **Category statistics**: Thống kê danh mục
- **Product analytics**: Phân tích sản phẩm

### 🔄 Quản lý dữ liệu
- **Bulk operations**: Thao tác hàng loạt
- **Import/Export**: Nhập/xuất dữ liệu
- **Data backup**: Sao lưu và khôi phục

## 🐛 Xử lý lỗi

### ❌ Lỗi thường gặp
1. **Database connection failed**
   - Kiểm tra thông tin kết nối
   - Đảm bảo MySQL service đang chạy

2. **File not found**
   - Kiểm tra đường dẫn file
   - Đảm bảo quyền truy cập thư mục

3. **Permission denied**
   - Kiểm tra quyền ghi thư mục
   - Cập nhật chmod nếu cần

### 🔧 Debug mode
- Bật error reporting trong PHP
- Kiểm tra error logs
- Sử dụng try-catch để bắt lỗi

## 📈 Phát triển tiếp theo

### 🎯 Tính năng dự kiến
- **Authentication system**: Đăng nhập/đăng ký
- **User management**: Quản lý người dùng
- **Role-based access**: Phân quyền theo vai trò
- **API endpoints**: RESTful API
- **Mobile app**: Ứng dụng di động
- **Advanced analytics**: Báo cáo chi tiết

### 🔧 Cải tiến kỹ thuật
- **Caching system**: Redis/Memcached
- **Queue system**: Xử lý tác vụ nền
- **File storage**: Cloud storage integration
- **Testing**: Unit tests và integration tests
- **CI/CD**: Automated deployment

## 📞 Hỗ trợ

### 💬 Liên hệ
- **Email**: [your-email@example.com]
- **GitHub**: [your-github-profile]
- **Documentation**: [link-to-docs]

### 🆘 Hỗ trợ kỹ thuật
- Kiểm tra README trước
- Xem issues trên GitHub
- Tạo issue mới nếu cần

## 📄 License

Dự án này được phát hành dưới giấy phép [MIT License](LICENSE).

---

## 🎉 Kết luận

Hệ thống Admin Dashboard này cung cấp một nền tảng hoàn chỉnh để quản lý sản phẩm và danh mục trong e-commerce. Với giao diện hiện đại, tính năng đầy đủ và kiến trúc mở rộng, hệ thống có thể đáp ứng nhu cầu của các dự án từ nhỏ đến lớn.

**Chúc bạn thành công với dự án tốt nghiệp! 🚀**
