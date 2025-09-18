<?php
session_start();
require_once '../../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }
    
    // Lấy tham số phân trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 6;
    $offset = ($page - 1) * $limit;
    
    // Lấy tham số lọc
    $type_filter = isset($_GET['type']) ? $_GET['type'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    
    // Xây dựng query
    $where_conditions = [];
    $params = [];
    
    if (!empty($type_filter)) {
        $where_conditions[] = "type = ?";
        $params[] = $type_filter;
    }
    
    if ($status_filter === 'read') {
        $where_conditions[] = "is_read = 1";
    } elseif ($status_filter === 'unread') {
        $where_conditions[] = "is_read = 0";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Đếm tổng số thông báo
    $count_sql = "SELECT COUNT(*) as total FROM notifications $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_notifications = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_notifications / $limit);
    
    // Lấy danh sách thông báo
    $sql = "SELECT * FROM notifications $where_clause ORDER BY created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Đếm thống kê
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN type = 'product' THEN 1 ELSE 0 END) as product,
        SUM(CASE WHEN type = 'sensor' THEN 1 ELSE 0 END) as sensor,
        SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system,
        SUM(CASE WHEN type = 'alert' THEN 1 ELSE 0 END) as alert
        FROM notifications";
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
    $notifications = [];
    $stats = ['total' => 0, 'unread' => 0, 'product' => 0, 'sensor' => 0, 'system' => 0, 'alert' => 0];
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý thông báo - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý thông báo hệ thống" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Font Consistency CSS -->
    <link href="../../assets/css/font-consistency.css" rel="stylesheet" type="text/css" />
    
    <!-- Common Admin Layout CSS -->
    <link href="../partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        .notification-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .notification-card.unread {
            border-left-color: #007bff;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
        }
        
        .notification-card.read {
            border-left-color: #e9ecef;
            background-color: #f8f9fa;
            opacity: 0.8;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .filter-badge {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-badge:hover {
            transform: scale(1.05);
        }
        
        .filter-badge.active {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }
        
        .pagination-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .pagination {
                justify-content: center;
            }
            
            .pagination .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="content">
            <?php include '../partials/header.php'; ?>

            <!-- Start Content-->
            <div class="container-fluid">
                <!-- Danh sách thông báo -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Danh sách thông báo</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger">
                                        <strong>Lỗi:</strong> <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($notifications)): ?>
                                    <div class="row" id="notifications-list">
                                        <?php foreach ($notifications as $notification): ?>
                                            <div class="col-12 mb-3">
                                                <div class="card notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                                                     data-id="<?php echo $notification['id']; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0">
                                                                <div class="notification-icon bg-<?php echo $notification['icon_color']; ?>-subtle text-<?php echo $notification['icon_color']; ?>">
                                                                    <i class="<?php echo $notification['icon']; ?>"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                                        <small class="text-muted">
                                                                            <i class="iconoir-calendar"></i> 
                                                                            <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="badge bg-<?php echo $notification['icon_color']; ?>">
                                                                            <?php echo ucfirst($notification['type']); ?>
                                                                        </span>
                                                                        <?php if (!$notification['is_read']): ?>
                                                                            <button class="btn btn-sm btn-success" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                                                <i class="iconoir-check"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                                                            <i class="iconoir-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Phân trang -->
                                    <?php if ($total_pages > 1): ?>
                                        <div class="d-flex justify-content-between align-items-center mt-4">
                                            <div class="pagination-info">
                                                Hiển thị <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_notifications); ?> 
                                                trong tổng số <?php echo $total_notifications; ?> thông báo
                                            </div>
                                            
                                            <nav aria-label="Phân trang thông báo">
                                                <ul class="pagination mb-0">
                                                    <!-- Trang trước -->
                                                    <?php if ($page > 1): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>" title="Trang trước">
                                                                <i class="iconoir-arrow-left"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Các trang -->
                                                    <?php 
                                                    $start_page = max(1, $page - 2);
                                                    $end_page = min($total_pages, $page + 2);
                                                    
                                                    // Hiển thị dấu ... nếu cần
                                                    if ($start_page > 1): ?>
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                                        </li>
                                                    <?php endfor; ?>
                                                    
                                                    <!-- Hiển thị dấu ... nếu cần -->
                                                    <?php if ($end_page < $total_pages): ?>
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Trang sau -->
                                                    <?php if ($page < $total_pages): ?>
                                                        <li class="page-item">
                                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $type_filter; ?>&status=<?php echo $status_filter; ?>" title="Trang sau">
                                                                <i class="iconoir-arrow-right"></i>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </nav>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-muted mt-4">
                                            Hiển thị tất cả <?php echo $total_notifications; ?> thông báo
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="iconoir-bell text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="text-muted mt-3">Không có thông báo nào</h5>
                                        <p class="text-muted">Chưa có thông báo nào phù hợp với bộ lọc của bạn</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- container -->
        </div>
        <!-- content -->
    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    <!-- Bootstrap JS -->
    <script src="../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Simplebar -->
    <script src="../../assets/libs/simplebar/simplebar.min.js"></script>
    
    <!-- Common Admin Layout JavaScript -->
    <script src="../partials/layout.js"></script>

    <script>
        // Lọc theo loại
        function filterByType(type) {
            const url = new URL(window.location);
            if (type) {
                url.searchParams.set('type', type);
            } else {
                url.searchParams.delete('type');
            }
            url.searchParams.delete('page'); // Reset về trang 1
            window.location.href = url.toString();
        }
        
        // Lọc theo trạng thái
        function filterByStatus(status) {
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            url.searchParams.delete('page'); // Reset về trang 1
            window.location.href = url.toString();
        }
        
        // Đánh dấu đã đọc
        function markAsRead(notificationId) {
            fetch('../../api/notifications.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${notificationId}"]`);
                    if (card) {
                        card.classList.remove('unread');
                        card.classList.add('read');
                        // Ẩn nút đánh dấu đã đọc
                        const markBtn = card.querySelector('button[onclick*="markAsRead"]');
                        if (markBtn) markBtn.remove();
                    }
                    // Cập nhật thống kê
                    refreshNotifications();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi đánh dấu đã đọc');
            });
        }
        
        // Xóa thông báo
        function deleteNotification(notificationId) {
            // Sử dụng custom confirm dialog
            if (typeof showConfirmToast === 'function') {
                showConfirmToast(
                    'Xóa thông báo',
                    'Bạn có chắc chắn muốn xóa thông báo này?',
                    () => executeDeleteNotification(notificationId)
                );
            } else {
                if (confirm('Bạn có chắc chắn muốn xóa thông báo này?')) {
                    executeDeleteNotification(notificationId);
                }
            }
        }
        
        // Thực hiện xóa thông báo
        function executeDeleteNotification(notificationId) {
            fetch('../../api/notifications.php?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${notificationId}"]`);
                    if (card) {
                        // Tìm container cha (.col-12) và xóa nó
                        const container = card.closest('.col-12');
                        if (container) {
                            container.remove();
                        } else {
                            // Fallback: xóa card trực tiếp
                            card.remove();
                        }
                    }
                    // Cập nhật thống kê
                    refreshNotifications();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi xóa thông báo');
            });
        }
        
        // Đánh dấu tất cả đã đọc
        function markAllAsRead() {
            if (confirm('Bạn có chắc chắn muốn đánh dấu tất cả thông báo đã đọc?')) {
                fetch('../../api/notifications.php?action=mark_read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi khi đánh dấu tất cả đã đọc');
                });
            }
        }
        
        // Xóa tất cả đã đọc
        function deleteAllRead() {
            // Sử dụng custom confirm dialog
            if (typeof showConfirmToast === 'function') {
                showConfirmToast(
                    'Xóa tất cả thông báo đã đọc',
                    'Bạn có chắc chắn muốn xóa tất cả thông báo đã đọc? Hành động này không thể hoàn tác!',
                    () => executeDeleteAllRead()
                );
            } else {
                if (confirm('Bạn có chắc chắn muốn xóa tất cả thông báo đã đọc? Hành động này không thể hoàn tác!')) {
                    executeDeleteAllRead();
                }
            }
        }
        
        // Thực hiện xóa tất cả thông báo đã đọc
        function executeDeleteAllRead() {
            // Tìm tất cả thông báo đã đọc và xóa
            const readCards = document.querySelectorAll('.notification-card.read');
            const promises = [];
            
            readCards.forEach(card => {
                const notificationId = card.getAttribute('data-id');
                promises.push(
                    fetch('../../api/notifications.php?action=delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ notification_id: notificationId })
                    })
                );
            });
            
            Promise.all(promises)
            .then(responses => {
                // Kiểm tra tất cả responses
                let allSuccess = true;
                responses.forEach(response => {
                    if (!response.ok) {
                        allSuccess = false;
                    }
                });
                
                if (allSuccess) {
                    // Xóa tất cả cards đã đọc khỏi DOM
                    readCards.forEach(card => {
                        const container = card.closest('.col-12');
                        if (container) {
                            container.remove();
                        } else {
                            card.remove();
                        }
                    });
                    // Cập nhật thống kê
                    refreshNotifications();
                } else {
                    alert('Một số thông báo không thể xóa được');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi xóa thông báo');
            });
        }
        
        // Làm mới trang
        function refreshNotifications() {
            location.reload();
        }
    </script>
</body>
</html>
