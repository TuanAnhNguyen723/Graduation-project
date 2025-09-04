<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

class NotificationAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Lấy danh sách thông báo
    public function getNotifications($limit = 10, $unread_only = false) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM notifications";
            
            if ($unread_only) {
                $sql .= " WHERE is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format thời gian hiển thị
            foreach ($notifications as &$notification) {
                $notification['time_ago'] = $this->timeAgo($notification['created_at']);
                $notification['created_at_formatted'] = date('d/m/Y H:i', strtotime($notification['created_at']));
            }
            
            return [
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi lấy thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Đếm số thông báo chưa đọc
    public function getUnreadCount() {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'count' => (int)$result['count']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi đếm thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Đánh dấu thông báo đã đọc
    public function markAsRead($notification_id) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $result = $stmt->execute([$notification_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Đã đánh dấu thông báo đã đọc'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể cập nhật thông báo'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Đánh dấu tất cả thông báo đã đọc
    public function markAllAsRead() {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            $result = $stmt->execute();
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Đã đánh dấu tất cả thông báo đã đọc'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể cập nhật thông báo'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Xóa thông báo
    public function deleteNotification($notification_id) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
            $result = $stmt->execute([$notification_id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Đã xóa thông báo'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể xóa thông báo'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi xóa thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Tạo thông báo mới
    public function createNotification($title, $message, $type = 'system', $icon = 'iconoir-bell', $icon_color = 'primary', $related_id = null, $related_type = null) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO notifications (title, message, type, icon, icon_color, related_id, related_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$title, $message, $type, $icon, $icon_color, $related_id, $related_type]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Đã tạo thông báo mới',
                    'id' => $conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo thông báo'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo thông báo: ' . $e->getMessage()
            ];
        }
    }
    
    // Tính thời gian hiển thị
    private function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'Vừa xong';
        if ($time < 3600) return floor($time/60) . ' phút trước';
        if ($time < 86400) return floor($time/3600) . ' giờ trước';
        if ($time < 2592000) return floor($time/86400) . ' ngày trước';
        if ($time < 31536000) return floor($time/2592000) . ' tháng trước';
        return floor($time/31536000) . ' năm trước';
    }
}

// Xử lý request
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$api = new NotificationAPI();

switch ($method) {
    case 'GET':
        switch ($action) {
            case 'list':
                $limit = $_GET['limit'] ?? 10;
                $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
                echo json_encode($api->getNotifications($limit, $unread_only));
                break;
                
            case 'count':
                echo json_encode($api->getUnreadCount());
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Action không hợp lệ'
                ]);
                break;
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'mark_read':
                $notification_id = $input['notification_id'] ?? null;
                if ($notification_id) {
                    echo json_encode($api->markAsRead($notification_id));
                } else {
                    echo json_encode($api->markAllAsRead());
                }
                break;
                
            case 'delete':
                $notification_id = $input['notification_id'] ?? null;
                if ($notification_id) {
                    echo json_encode($api->deleteNotification($notification_id));
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Thiếu notification_id'
                    ]);
                }
                break;
                
            case 'create':
                $title = $input['title'] ?? '';
                $message = $input['message'] ?? '';
                $type = $input['type'] ?? 'system';
                $icon = $input['icon'] ?? 'iconoir-bell';
                $icon_color = $input['icon_color'] ?? 'primary';
                $related_id = $input['related_id'] ?? null;
                $related_type = $input['related_type'] ?? null;
                
                echo json_encode($api->createNotification($title, $message, $type, $icon, $icon_color, $related_id, $related_type));
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Action không hợp lệ'
                ]);
                break;
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Method không được hỗ trợ'
        ]);
        break;
}
?>