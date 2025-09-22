<?php
session_start();
require_once '../../../config/database.php';
require_once '../models/ProductLocation.php';
require_once '../../../models/Product.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }
    
    $productLocation = new ProductLocation($pdo);
    $product = new Product($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'get_products':
                    // Lấy danh sách sản phẩm để nhập/xuất (chỉ sản phẩm có cùng temperature_zone với vị trí)
                    $locationId = $_GET['location_id'] ?? '';
                    
                    if (!$locationId) {
                        throw new Exception('Location ID is required');
                    }
                    
                    // Lấy temperature_zone của vị trí
                    $stmt = $pdo->prepare("
                        SELECT temperature_zone 
                        FROM warehouse_locations 
                        WHERE id = ? AND is_active = 1
                    ");
                    $stmt->execute([$locationId]);
                    $location = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$location) {
                        throw new Exception('Location not found');
                    }
                    
                    $temperatureZone = $location['temperature_zone'];
                    
                    // Lấy danh sách sản phẩm có cùng temperature_zone
                    $stmt = $pdo->prepare("
                        SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.images, 
                               c.name as category_name, wl.temperature_zone
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN warehouse_locations wl ON c.location_id = wl.id
                        WHERE p.is_active = 1 
                        AND (wl.temperature_zone = ? OR wl.temperature_zone IS NULL)
                        ORDER BY p.name
                    ");
                    $stmt->execute([$temperatureZone]);
                    $productList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $productList,
                        'location_temperature_zone' => $temperatureZone
                    ]);
                    break;
                    
                case 'get_location_products':
                    // Lấy danh sách sản phẩm tại vị trí
                    $locationId = $_GET['location_id'] ?? '';
                    if (!$locationId) {
                        throw new Exception('Location ID is required');
                    }
                    
                    $stmt = $pdo->prepare("
                        SELECT pl.*, p.name AS product_name, p.sku, p.price, p.sale_price, p.images,
                               wl.location_code, wl.location_name, c.name as category_name
                        FROM product_locations pl
                        JOIN products p ON pl.product_id = p.id
                        JOIN warehouse_locations wl ON pl.location_id = wl.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE pl.location_id = ? AND pl.quantity > 0
                        ORDER BY p.name
                    ");
                    $stmt->execute([$locationId]);
                    $allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $allocations
                    ]);
                    break;
                    
                case 'get_product_stock':
                    // Lấy thông tin tồn kho của sản phẩm tại vị trí
                    $productId = $_GET['product_id'] ?? '';
                    $locationId = $_GET['location_id'] ?? '';
                    
                    if (!$productId || !$locationId) {
                        throw new Exception('Product ID and Location ID are required');
                    }
                    
                    // Lấy thông tin sản phẩm
                    $stmt = $pdo->prepare("
                        SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.images,
                               c.name as category_name
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ? AND p.is_active = 1
                    ");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Lấy tồn kho hiện tại tại vị trí
                    $allocations = $productLocation->getAllocations($productId, $locationId);
                    $currentStock = 0;
                    
                    if (!empty($allocations)) {
                        $currentStock = (int)$allocations[0]['quantity'];
                    }
                    
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'current_stock' => $currentStock,
                        'allocations' => [$product] // Trả về thông tin sản phẩm
                    ]
                ]);
                break;
                
            case 'get_product_stock_total':
                // Lấy tổng tồn kho của sản phẩm tại tất cả vị trí
                $productId = $_GET['product_id'] ?? '';
                
                if (!$productId) {
                    throw new Exception('Product ID is required');
                }
                
                // Lấy tổng số lượng tồn kho
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(quantity), 0) as total_stock
                    FROM product_locations 
                    WHERE product_id = ?
                ");
                $stmt->execute([$productId]);
                $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalStock = (int)$totalResult['total_stock'];
                
                // Lấy chi tiết tồn kho theo từng vị trí
                $stmt = $pdo->prepare("
                    SELECT wl.location_name, pl.quantity
                    FROM product_locations pl
                    JOIN warehouse_locations wl ON pl.location_id = wl.id
                    WHERE pl.product_id = ? AND pl.quantity > 0
                    ORDER BY pl.quantity DESC
                ");
                $stmt->execute([$productId]);
                $locationStocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total_stock' => $totalStock,
                        'locations' => $locationStocks
                    ]
                ]);
                break;
                    
                case 'check_capacity':
                    // Kiểm tra sức chứa của vị trí
                    $locationId = $_GET['location_id'] ?? '';
                    $quantity = $_GET['quantity'] ?? 0;
                    
                    if (!$locationId) {
                        throw new Exception('Location ID is required');
                    }
                    
                    // Lấy thông tin vị trí
                    $stmt = $pdo->prepare("SELECT * FROM warehouse_locations WHERE id = ? AND is_active = 1");
                    $stmt->execute([$locationId]);
                    $location = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$location) {
                        throw new Exception('Location not found');
                    }
                    
                    $maxCapacity = (int)$location['max_capacity'];
                    $currentCapacity = (int)$location['current_capacity'];
                    $availableCapacity = $maxCapacity - $currentCapacity;
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'max_capacity' => $maxCapacity,
                            'current_capacity' => $currentCapacity,
                            'available_capacity' => $availableCapacity,
                            'requested_quantity' => (int)$quantity,
                            'can_import' => $availableCapacity >= (int)$quantity
                        ]
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'import':
                    // Nhập hàng
                    $productId = $_POST['product_id'] ?? '';
                    $locationId = $_POST['location_id'] ?? '';
                    $quantity = $_POST['quantity'] ?? 0;
                    $notes = $_POST['notes'] ?? '';
                    
                    if (!$productId || !$locationId || $quantity <= 0) {
                        throw new Exception('Invalid input data');
                    }
                    
                    // Kiểm tra sức chứa
                    $stmt = $pdo->prepare("SELECT * FROM warehouse_locations WHERE id = ? AND is_active = 1");
                    $stmt->execute([$locationId]);
                    $location = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$location) {
                        throw new Exception('Location not found');
                    }
                    
                    $maxCapacity = (int)$location['max_capacity'];
                    $currentCapacity = (int)$location['current_capacity'];
                    $availableCapacity = $maxCapacity - $currentCapacity;
                    
                    if ($availableCapacity < $quantity) {
                        throw new Exception("Không đủ sức chứa. Còn trống: {$availableCapacity}, yêu cầu: {$quantity}");
                    }
                    
                    // Thực hiện nhập hàng
                    $result = $productLocation->allocateProduct($productId, $locationId, $quantity);
                    
                    if ($result) {
                        // Ghi log nhập hàng
                        $stmt = $pdo->prepare("INSERT INTO stock_transactions (product_id, location_id, transaction_type, quantity, notes, created_at) VALUES (?, ?, 'import', ?, ?, NOW())");
                        $stmt->execute([$productId, $locationId, $quantity, $notes]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Nhập hàng thành công',
                            'data' => [
                                'product_id' => $productId,
                                'location_id' => $locationId,
                                'quantity' => $quantity
                            ]
                        ]);
                    } else {
                        throw new Exception('Không thể nhập hàng');
                    }
                    break;
                    
                case 'export':
                    // Xuất hàng
                    $productId = $_POST['product_id'] ?? '';
                    $locationId = $_POST['location_id'] ?? '';
                    $quantity = $_POST['quantity'] ?? 0;
                    $notes = $_POST['notes'] ?? '';
                    
                    if (!$productId || !$locationId) {
                        throw new Exception('Thiếu thông tin sản phẩm hoặc vị trí');
                    }
                    
                    if ($quantity <= 0) {
                        throw new Exception('Số lượng không hợp lý');
                    }
                    
                    // Kiểm tra tồn kho
                    $allocations = $productLocation->getAllocations($productId, $locationId);
                    $currentStock = 0;
                    
                    if (!empty($allocations)) {
                        $currentStock = (int)$allocations[0]['quantity'];
                    }
                    
                    if ($currentStock < $quantity) {
                        throw new Exception("Không đủ hàng để xuất. Hiện có: {$currentStock}, yêu cầu: {$quantity}");
                    }
                    
                    // Thực hiện xuất hàng
                    $result = $productLocation->deallocateProduct($productId, $locationId, $quantity);
                    
                    if ($result) {
                        // Ghi log xuất hàng
                        $stmt = $pdo->prepare("INSERT INTO stock_transactions (product_id, location_id, transaction_type, quantity, notes, created_at) VALUES (?, ?, 'export', ?, ?, NOW())");
                        $stmt->execute([$productId, $locationId, $quantity, $notes]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Xuất hàng thành công',
                            'data' => [
                                'product_id' => $productId,
                                'location_id' => $locationId,
                                'quantity' => $quantity
                            ]
                        ]);
                    } else {
                        throw new Exception('Không thể xuất hàng');
                    }
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
