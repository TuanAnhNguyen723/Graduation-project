<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Cấu hình đăng nhập đơn giản
$valid_credentials = [
    'admin' => 'abc123'
];

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($valid_credentials);
        break;
        
    case 'logout':
        handleLogout();
        break;
        
    case 'check':
        checkLoginStatus();
        break;
        
    default:
        // Xử lý POST request cho đăng nhập
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleLogin($valid_credentials);
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method không được hỗ trợ'
            ]);
        }
        break;
}

function handleLogin($valid_credentials) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Method không hợp lệ');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Nếu không có JSON input, sử dụng POST data
        if (!$input) {
            $input = [
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
        }
        
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        
        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception('Vui lòng nhập đầy đủ thông tin đăng nhập');
        }
        
        // Kiểm tra credentials
        if (!isset($valid_credentials[$username]) || $valid_credentials[$username] !== $password) {
            throw new Exception('Tên đăng nhập hoặc mật khẩu không đúng');
        }
        
        // Đăng nhập thành công
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        // Trả về response
        $response = [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'username' => $username,
                'login_time' => date('Y-m-d H:i:s'),
                'redirect_url' => '../'
            ]
        ];
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode($response);
        } else {
            // Nếu là form submit thông thường, redirect
            header('Location: ../?success=' . urlencode('Đăng nhập thành công!'));
            exit();
        }
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode($response);
        } else {
            // Nếu là form submit thông thường, redirect với error
            header('Location: ../login.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

function handleLogout() {
    try {
        // Xóa session
        session_unset();
        session_destroy();
        
        // Tạo session mới để tránh session fixation
        session_start();
        session_regenerate_id(true);
        
        $response = [
            'success' => true,
            'message' => 'Đăng xuất thành công',
            'redirect_url' => '../login.php'
        ];
        
        // Nếu là AJAX request, trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode($response);
        } else {
            // Redirect về trang đăng nhập
            header('Location: ../login.php?success=' . urlencode('Đăng xuất thành công!'));
            exit();
        }
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Lỗi khi đăng xuất: ' . $e->getMessage()
        ];
        
        http_response_code(500);
        echo json_encode($response);
    }
}

function checkLoginStatus() {
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    
    $response = [
        'success' => true,
        'logged_in' => $isLoggedIn,
        'data' => [
            'username' => $isLoggedIn ? ($_SESSION['admin_username'] ?? 'admin') : null,
            'login_time' => $isLoggedIn ? ($_SESSION['admin_login_time'] ?? null) : null,
            'session_duration' => $isLoggedIn ? (time() - ($_SESSION['admin_login_time'] ?? time())) : 0
        ]
    ];
    
    echo json_encode($response);
}

// Helper function để kiểm tra đăng nhập (có thể include từ các file khác)
function requireLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../login.php?error=' . urlencode('Vui lòng đăng nhập để tiếp tục'));
        exit();
    }
}
?>
