<?php
session_start();
require_once '../../../config/database.php';
require_once '../models/TemperatureSensor.php';
require_once '../models/WarehouseLocation.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }
    
    $sensorModel = new TemperatureSensor($pdo);
    $locationModel = new WarehouseLocation($pdo);
    
    // Lấy dữ liệu
    $sensors = $sensorModel->getAllSensors();
    $locations = $locationModel->getAllLocations();
    
} catch(Exception $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý cảm biến - IoT System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý cảm biến IoT" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../../../assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Common Admin Layout CSS -->
    <link href="../../partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        .sensor-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .sensor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        
        .sensor-card .card-title {
            color: #2c3e50;
            font-size: 1.1rem;
            line-height: 1.3;
        }
        
        .sensor-card .card-text {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .sensor-card .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
        
        .sensor-card .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../../partials/sidebar.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="content">
            <?php include '../../partials/header.php'; ?>

            <!-- Start Content-->
            <div class="container-fluid">

        <!-- Thống kê cảm biến -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Tổng cảm biến</h6>
                                <h2 class="text-white"><?php echo count($sensors); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="iconoir-cpu fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Cảm biến hoạt động</h6>
                                <h2 class="text-white"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'active'; })); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="iconoir-check-circle fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Cảm biến bảo trì</h6>
                                <h2 class="text-white"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'maintenance'; })); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="iconoir-tools fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Cảm biến lỗi</h6>
                                <h2 class="text-white"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'error'; })); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="iconoir-warning-triangle fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách cảm biến -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Danh sách cảm biến
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($sensors)): ?>
                            <!-- Sensors Grid -->
                <div class="row">
                    <?php foreach ($sensors as $sensor): ?>
                        <div class="col-xl-4 col-md-6">
                            <div class="card sensor-card sensor-<?php echo $sensor['status']; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title"><?php echo htmlspecialchars($sensor['sensor_name']); ?></h5>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($sensor['location_name'] ?? 'Chưa gán vị trí'); ?></p>
                                        </div>
                                        <span class="badge bg-<?php echo $sensor['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($sensor['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-primary"><?php echo $sensor['current_temperature'] ?? 'N/A'; ?>°C</h4>
                                            <p class="text-muted mb-0">Nhiệt độ hiện tại</p>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-info"><?php echo $sensor['humidity'] ?? 'N/A'; ?>%</h4>
                                            <p class="text-muted mb-0">Độ ẩm</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted d-block">
                                            <i class="iconoir-calendar"></i> Cập nhật: <?php echo $sensor['updated_at'] ? date('d/m/Y H:i', strtotime($sensor['updated_at'])) : 'N/A'; ?>
                                        </small>
                                        <div class="mt-1 small text-muted">
                                            <?php if (!empty($sensor['manufacturer']) || !empty($sensor['model'])): ?>
                                                <div><i class="iconoir-cog"></i>
                                                    <?php echo htmlspecialchars(trim(($sensor['manufacturer'] ?? '') . ' ' . ($sensor['model'] ?? ''))); ?>
                                                </div>
                                            <?php endif; ?>

                        						<?php if (!empty($sensor['serial_number'])): ?>
                                                <div><i class="iconoir-hash"></i> Serial: <?php echo htmlspecialchars($sensor['serial_number']); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['installation_date'])): ?>
                                                <div><i class="iconoir-calendar"></i> Lắp đặt: <?php echo date('d/m/Y', strtotime($sensor['installation_date'])); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['last_calibration'])): ?>
                                                <div><i class="iconoir-calendar"></i> Hiệu chuẩn cuối: <?php echo date('d/m/Y', strtotime($sensor['last_calibration'])); ?></div>
                                            <?php endif; ?>

                                            <?php if (isset($sensor['min_threshold']) || isset($sensor['max_threshold'])): ?>
                                                <div><i class="iconoir-warning-triangle"></i> Ngưỡng: 
                                                    <?php echo ($sensor['min_threshold'] !== null && $sensor['min_threshold'] !== '' ? htmlspecialchars($sensor['min_threshold']) : '—'); ?> - 
                                                    <?php echo ($sensor['max_threshold'] !== null && $sensor['max_threshold'] !== '' ? htmlspecialchars($sensor['max_threshold']) : '—'); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['description'])): ?>
                                                <div class="mt-1"><i class="iconoir-notes"></i>
                                                    <?php $desc = (string)$sensor['description']; echo htmlspecialchars(mb_substr($desc, 0, 80)) . (mb_strlen($desc) > 80 ? '…' : ''); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['notes'])): ?>
                                                <div class="mt-1"><i class="iconoir-notes"></i>
                                                    <?php $notes = (string)$sensor['notes']; echo htmlspecialchars(mb_substr($notes, 0, 80)) . (mb_strlen($notes) > 80 ? '…' : ''); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editSensor(<?php echo $sensor['id']; ?>)">
                                            <i class="iconoir-edit"></i> Sửa
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSensor(<?php echo $sensor['id']; ?>)">
                                            <i class="iconoir-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h5 class="text-muted">Chưa có cảm biến nào</h5>
                                <p class="text-muted">Hãy thêm cảm biến đầu tiên để bắt đầu giám sát</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSensorModal">
                                    <i class="iconoir-plus"></i>
                                    Thêm cảm biến đầu tiên
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thêm cảm biến -->
    <div class="modal fade" id="addSensorModal" tabindex="-1" aria-labelledby="addSensorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSensorModalLabel">Thêm cảm biến mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSensorForm">
                        <div class="mb-3">
                            <label for="sensorName" class="form-label">Tên cảm biến</label>
                            <input type="text" class="form-control" id="sensorName" required>
                        </div>
                        <div class="mb-3">
                            <label for="sensorCode" class="form-label">Mã cảm biến</label>
                            <input type="text" class="form-control" id="sensorCode" required>
                        </div>
                        <div class="mb-3">
                            <label for="sensorType" class="form-label">Loại cảm biến</label>
                            <select class="form-select" id="sensorType" required>
                                <option value="temperature">Nhiệt độ</option>
                                <option value="humidity">Độ ẩm</option>
                                <option value="both">Cả hai</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="locationId" class="form-label">Vị trí lắp đặt</label>
                            <select class="form-select" id="locationId">
                                <option value="">Chọn vị trí</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>">
                                    <?php echo htmlspecialchars($location['location_name']); ?> (<?php echo htmlspecialchars($location['location_code']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveSensor()">Lưu</button>
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
    <script src="../../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="../../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Simplebar -->
    <script src="../../../assets/libs/simplebar/simplebar.min.js"></script>
    
    <!-- Common Admin Layout JavaScript -->
    <script src="../../../admin/partials/layout.js"></script>

    <script>
        // Lưu cảm biến mới
        function saveSensor() {
            const form = document.getElementById('addSensorForm');
            const formData = new FormData();
            
            formData.append('sensor_name', document.getElementById('sensorName').value);
            formData.append('sensor_code', document.getElementById('sensorCode').value);
            formData.append('sensor_type', document.getElementById('sensorType').value);
            formData.append('location_id', document.getElementById('locationId').value);
            
            // Gửi request tạo cảm biến
            fetch('../api/create-sensor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thêm cảm biến thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                alert('Lỗi kết nối: ' + error.message);
            });
        }

        // Chỉnh sửa cảm biến
        function editSensor(sensorId) {
            alert('Chức năng chỉnh sửa sẽ được implement sau!');
        }

        // Xóa cảm biến
        function deleteSensor(sensorId) {
            if (confirm('Bạn có chắc chắn muốn xóa cảm biến này?')) {
                // Gửi request xóa cảm biến
                fetch('../api/delete-sensor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ sensor_id: sensorId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Xóa cảm biến thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Lỗi kết nối: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
