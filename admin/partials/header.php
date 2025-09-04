<?php
// Tính toán đường dẫn tương đối đến thư mục gốc
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);

// Tìm vị trí của 'admin' trong đường dẫn
$admin_index = array_search('admin', $path_parts);

if ($admin_index !== false) {
    // Tính số cấp cần lùi lại để đến thư mục gốc
    $depth = count($path_parts) - $admin_index - 1;
    $root_path = str_repeat('../', $depth);
} else {
    // Nếu không tìm thấy 'admin', sử dụng đường dẫn mặc định
    $root_path = '';
}
?>
<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid" style="display: flex !important; align-items: center !important; justify-content: space-between !important; flex-direction: row !important;">
        <!-- Page Title -->
        <div class="page-title" style="flex-shrink: 0; min-width: 200px; margin-left: 1rem;">
            <h5 class="mb-0 fw-semibold text-dark">Admin Dashboard</h5>
            <small class="text-muted">Quản lý hệ thống E-commerce & IoT</small>
        </div>

        <!-- Search Bar -->
        <div class="search-container" style="flex: 1; max-width: 500px; margin: 0 1rem; flex-shrink: 0;">
            <form class="search-form">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="iconoir-search text-muted"></i>
                    </span>
                    <input type="search" class="form-control border-start-0 ps-0" 
                           placeholder="Tìm kiếm sản phẩm, danh mục, cảm biến...">
                    <button class="btn btn-primary" type="submit">
                        Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Actions -->
        <div class="navbar-actions" style="display: flex; align-items: center; gap: 0.5rem; min-width: 300px; justify-content: flex-end; flex-shrink: 0;">
            <!-- Notifications -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-secondary position-relative" 
                        type="button" data-bs-toggle="dropdown" id="notificationDropdown">
                    <i class="iconoir-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge">
                        0
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Thông báo</h6>
                        <a href="#" class="text-decoration-none text-muted small" id="markAllReadBtn">Đánh dấu đã đọc</a>
                    </div>
                    <div class="dropdown-body p-0" style="max-height: 350px !important; overflow-y: auto !important;" id="notificationList">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="text-muted mt-2">Đang tải thông báo...</p>
                        </div>
                    </div>
                    <div class="dropdown-footer text-center p-3 border-top">
                        <a href="<?php echo $root_path; ?>admin/notifications/" class="text-decoration-none text-primary fw-semibold">Xem tất cả thông báo</a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
                        type="button" data-bs-toggle="dropdown">
                    <img src="<?php echo $root_path; ?>assets/images/users/avatar-1.jpg" 
                         alt="Admin" class="rounded-circle me-2" width="32" height="32">
                    <div class="text-start">
                        <div class="fw-semibold text-dark">Admin</div>
                        <small class="text-muted">Quản trị viên</small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header p-3 border-bottom">
                        <h6 class="mb-0 fw-semibold">Xin chào, Admin!</h6>
                        <small class="text-muted">Quản trị viên hệ thống</small>
                    </li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-user me-2"></i>Hồ sơ cá nhân
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-settings me-2"></i>Cài đặt
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="iconoir-lock me-2"></i>Khóa màn hình
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#">
                        <i class="iconoir-log-out me-2"></i>Đăng xuất
                    </a></li>
                </ul>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="btn btn-link d-lg-none p-0" id="toggle-sidebar" style="flex-shrink: 0;">
            <i class="iconoir-menu fs-4"></i>
        </button>
    </div>
</div>
<!-- end Topbar -->

<style>
/* CSS cho nút đánh dấu đã đọc */
.btn-mark-read {
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
    border: 2px solid #28a745 !important;
    background-color: #28a745 !important;
    color: white !important;
    font-size: 14px !important;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
}

.btn-mark-read:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
    transform: scale(1.1) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4) !important;
}

.btn-mark-read:active {
    transform: scale(0.95) !important;
}

.btn-mark-read:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.5) !important;
}

/* Hiệu ứng khi đánh dấu đã đọc */
.notification-item.marking-read {
    opacity: 0.6;
    transition: opacity 0.3s ease;
}

/* Thông báo chưa đọc - sáng và nổi bật */
.notification-item.unread {
    background-color: #ffffff !important;
    border-left: 4px solid #007bff !important;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1) !important;
    opacity: 1 !important;
}

.notification-item.unread h6 {
    color: #212529 !important;
    font-weight: 600 !important;
}

.notification-item.unread p {
    color: #495057 !important;
}


/* Thông báo đã đọc - mờ và nhạt */
.notification-item.marked-read {
    background-color: #f8f9fa !important;
    opacity: 0.7 !important;
    border-left: 4px solid #e9ecef !important;
    transition: all 0.3s ease !important;
}

.notification-item.marked-read h6 {
    color: #6c757d !important;
    font-weight: 400 !important;
}

.notification-item.marked-read p {
    color: #adb5bd !important;
}

.notification-item.marked-read small {
    color: #adb5bd !important;
}

/* Avatar của thông báo đã đọc cũng mờ đi */
.notification-item.marked-read .avatar-sm {
    opacity: 0.6 !important;
}

/* Animation cho nút */
@keyframes checkmark {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.btn-mark-read.animate {
    animation: checkmark 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications when page loads
    loadNotifications();
    loadUnreadCount();
    
    // Auto refresh notifications every 30 seconds
    setInterval(function() {
        loadUnreadCount();
    }, 30000);
    
    // Load notifications when dropdown is shown
    document.getElementById('notificationDropdown').addEventListener('click', function() {
        loadNotifications();
    });
    
    // Mark all as read
    document.getElementById('markAllReadBtn').addEventListener('click', function(e) {
        e.preventDefault();
        markAllAsRead();
    });
});

function loadNotifications() {
    fetch('<?php echo $root_path; ?>api/notifications.php?action=list&limit=10')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.data);
            } else {
                showNotificationError(data.message);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showNotificationError('Lỗi khi tải thông báo');
        });
}

function loadUnreadCount() {
    fetch('<?php echo $root_path; ?>api/notifications.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notificationBadge');
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'block' : 'none';
            }
        })
        .catch(error => {
            console.error('Error loading unread count:', error);
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center p-4">
                <i class="iconoir-bell text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Không có thông báo nào</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const iconClass = getIconClass(notification.icon_color);
        const isRead = notification.is_read === 1 || notification.is_read === '1' || notification.is_read === true;
        const buttonClass = isRead ? 'btn-outline-success' : 'btn-success';
        const buttonTitle = isRead ? 'Đã đọc' : 'Đánh dấu đã đọc';
        const itemClass = isRead ? 'marked-read' : 'unread';
        
        html += `
            <div class="dropdown-item p-3 border-bottom notification-item ${itemClass}" data-id="${notification.id}">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm ${iconClass.bg} ${iconClass.text} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="${notification.icon}"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-semibold">${notification.title}</h6>
                        <p class="text-muted mb-1 small">${notification.message}</p>
                        <small class="text-muted">${notification.time_ago}</small>
                    </div>
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm ${buttonClass} btn-mark-read" onclick="markAsRead(${notification.id})" title="${buttonTitle}" ${isRead ? 'disabled' : ''}>
                            <i class="iconoir-check"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function getIconClass(iconColor) {
    const colorMap = {
        'primary': { bg: 'bg-primary-subtle', text: 'text-primary' },
        'success': { bg: 'bg-success-subtle', text: 'text-success' },
        'warning': { bg: 'bg-warning-subtle', text: 'text-warning' },
        'danger': { bg: 'bg-danger-subtle', text: 'text-danger' },
        'info': { bg: 'bg-info-subtle', text: 'text-info' }
    };
    
    return colorMap[iconColor] || colorMap['primary'];
}

function markAsRead(notificationId) {
    const item = document.querySelector(`[data-id="${notificationId}"]`);
    const button = item.querySelector('.btn-mark-read');
    
    if (item && button) {
        // Thêm hiệu ứng đang xử lý
        item.classList.add('marking-read');
        button.classList.add('animate');
        
        // Disable button để tránh click nhiều lần
        button.disabled = true;
        button.innerHTML = '<i class="iconoir-refresh"></i>';
    }
    
    fetch('<?php echo $root_path; ?>api/notifications.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Thêm hiệu ứng đã đánh dấu
            if (item) {
                item.classList.remove('marking-read', 'unread');
                item.classList.add('marked-read');
                
                // Thay đổi nút thành trạng thái đã đọc
                if (button) {
                    button.innerHTML = '<i class="iconoir-check"></i>';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-success');
                    button.disabled = true;
                    button.title = 'Đã đọc';
                }
            }
            
            // Reload unread count
            loadUnreadCount();
        } else {
            // Khôi phục trạng thái nếu lỗi
            if (item) {
                item.classList.remove('marking-read');
            }
            if (button) {
                button.innerHTML = '<i class="iconoir-check"></i>';
                button.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        
        // Khôi phục trạng thái nếu lỗi
        if (item) {
            item.classList.remove('marking-read');
        }
        if (button) {
            button.innerHTML = '<i class="iconoir-check"></i>';
            button.disabled = false;
        }
    });
}

function markAllAsRead() {
    fetch('<?php echo $root_path; ?>api/notifications.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload notifications
            loadNotifications();
            loadUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function showNotificationError(message) {
    const container = document.getElementById('notificationList');
    container.innerHTML = `
        <div class="text-center p-4">
            <i class="iconoir-warning-triangle text-danger" style="font-size: 2rem;"></i>
            <p class="text-danger mt-2">${message}</p>
            <button class="btn btn-sm btn-primary" onclick="loadNotifications()">Thử lại</button>
        </div>
    `;
}
</script>
