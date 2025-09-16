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
    <link href="../../../admin/partials/layout.css" rel="stylesheet" type="text/css" />
    
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

        .center-box {
            gap: 6px;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include '../../../admin/partials/sidebar.php'; ?>

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
        <div class="content">
            <?php include '../../../admin/partials/header.php'; ?>

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
                                <h2 class="text-white" id="total-sensors"><?php echo count($sensors); ?></h2>
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
                                <h2 class="text-white" id="active-sensors"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'active'; })); ?></h2>
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
                                <h2 class="text-white" id="maintenance-sensors"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'maintenance'; })); ?></h2>
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
                                <h2 class="text-white" id="error-sensors"><?php echo count(array_filter($sensors, function($s) { return $s['status'] === 'error'; })); ?></h2>
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
                <div class="row" id="sensor-container">
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
                                            <?php if (!empty($sensor['manufacturer'])): ?>
                                                <div><i class="iconoir-industry"></i> NSX: <?php echo htmlspecialchars($sensor['manufacturer']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($sensor['model'])): ?>
                                                <div><i class="iconoir-tools"></i> Model: <?php echo htmlspecialchars($sensor['model']); ?></div>
                                            <?php endif; ?>

                        						<?php if (!empty($sensor['serial_number'])): ?>
                                                <div><i class="iconoir-ticket"></i> Serial: <?php echo htmlspecialchars($sensor['serial_number']); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['installation_date'])): ?>
                                                <div><i class="iconoir-calendar"></i> Lắp đặt: <?php echo date('d/m/Y', strtotime($sensor['installation_date'])); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($sensor['last_calibration'])): ?>
                                                <div><i class="iconoir-calendar"></i> Hiệu chuẩn cuối: <?php echo date('d/m/Y', strtotime($sensor['last_calibration'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <button style="border: 1px solid black; color: black;" class="btn btn-sm btn-outline-secondary"
                                                data-id="<?php echo $sensor['id']; ?>"
                                                data-sensor-name="<?php echo htmlspecialchars($sensor['sensor_name']); ?>"
                                                data-sensor-code="<?php echo htmlspecialchars($sensor['sensor_code']); ?>"
                                                data-sensor-type="<?php echo htmlspecialchars($sensor['sensor_type']); ?>"
                                                data-location-id="<?php echo $sensor['location_id'] ? (int)$sensor['location_id'] : ''; ?>"
                                                data-manufacturer="<?php echo htmlspecialchars($sensor['manufacturer'] ?? ''); ?>"
                                                data-model="<?php echo htmlspecialchars($sensor['model'] ?? ''); ?>"
                                                data-serial-number="<?php echo htmlspecialchars($sensor['serial_number'] ?? ''); ?>"
                                                data-installation-date="<?php echo htmlspecialchars($sensor['installation_date'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($sensor['status']); ?>"
                                                data-last-calibration="<?php echo htmlspecialchars($sensor['last_calibration'] ?? ''); ?>"
                                                data-description="<?php echo htmlspecialchars($sensor['description'] ?? ''); ?>"
                                                data-notes="<?php echo htmlspecialchars($sensor['notes'] ?? ''); ?>"
                                                onclick="openViewSensorFromButton(this)">
                                            <i class="iconoir-eye"></i> Xem
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-id="<?php echo $sensor['id']; ?>"
                                                data-sensor-name="<?php echo htmlspecialchars($sensor['sensor_name']); ?>"
                                                data-sensor-code="<?php echo htmlspecialchars($sensor['sensor_code']); ?>"
                                                data-sensor-type="<?php echo htmlspecialchars($sensor['sensor_type']); ?>"
                                                data-location-id="<?php echo $sensor['location_id'] ? (int)$sensor['location_id'] : ''; ?>"
                                                data-manufacturer="<?php echo htmlspecialchars($sensor['manufacturer'] ?? ''); ?>"
                                                data-model="<?php echo htmlspecialchars($sensor['model'] ?? ''); ?>"
                                                data-serial-number="<?php echo htmlspecialchars($sensor['serial_number'] ?? ''); ?>"
                                                data-installation-date="<?php echo htmlspecialchars($sensor['installation_date'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($sensor['status']); ?>"
                                                data-last-calibration="<?php echo htmlspecialchars($sensor['last_calibration'] ?? ''); ?>"
                                                data-description="<?php echo htmlspecialchars($sensor['description'] ?? ''); ?>"
                                                data-notes="<?php echo htmlspecialchars($sensor['notes'] ?? ''); ?>"
                                                onclick="openEditSensorFromButton(this)">
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
    <!-- Simplebar -->
    <script src="../../../assets/libs/simplebar/simplebar.min.js"></script>
    
    <!-- Common Admin Layout JavaScript -->
    <script src="../../../admin/partials/layout.js"></script>
    
    <!-- Include Unified Widgets CSS -->
    <link href="../../../assets/css/widget.css" rel="stylesheet" type="text/css" />
    
    <!-- Include Sensor Edit & View Modal Widgets -->
    <?php include '../../../assets/widgets/edit-sensor.php'; ?>
    <?php include '../../../assets/widgets/view-sensor.php'; ?>
    
    <!-- Include Unified Widgets JavaScript -->
    <script src="../../../assets/js/widget.js"></script>

    <script>
        // Khởi tạo Bootstrap dropdown
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo tất cả dropdown
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>

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

        // Function hiển thị thông báo
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = `
                top: 80px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 8px;
                border: none;
                font-weight: 500;
            `;
            notification.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            document.body.appendChild(notification);
            setTimeout(() => { if (notification.parentNode) { notification.remove(); } }, 3000);
        }

        // Xóa cảm biến
        function deleteSensor(sensorId) {
            if (confirm('Bạn có chắc chắn muốn xóa cảm biến này?')) {
                // Hiển thị loading trên button
                const deleteBtn = event.target.closest('.btn-outline-danger');
                const originalText = deleteBtn.innerHTML;
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="iconoir-loading"></i> Đang xóa...';
                
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
                        // Hiển thị thông báo thành công
                        showDeleteSensorSuccessMessage();
                        
                        // Xóa card cảm biến khỏi giao diện
                        const sensorCard = deleteBtn.closest('.col-xl-4');
                        sensorCard.style.opacity = '0.5';
                        sensorCard.style.transform = 'scale(0.95)';
                        
                        setTimeout(() => {
                            sensorCard.remove();
                            
                            // Kiểm tra xem còn cảm biến nào không
                            const remainingSensors = document.querySelectorAll('.sensor-card');
                            if (remainingSensors.length === 0) {
                                // Hiển thị trạng thái trống
                                const container = document.querySelector('.row');
                                container.innerHTML = `
                                    <div class="col-12 text-center py-5">
                                        <div class="empty-state">
                                            <i class="iconoir-cpu" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                                            <h4 class="text-muted mb-3">Chưa có cảm biến nào</h4>
                                            <p class="text-muted mb-4">Bắt đầu thêm cảm biến đầu tiên để giám sát</p>
                                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addSensorModal">
                                                <i class="iconoir-plus"></i> Thêm cảm biến đầu tiên
                                            </button>
                                        </div>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        throw new Error(data.error || 'Có lỗi xảy ra khi xóa cảm biến');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Lỗi: ' + error.message, 'error');
                    
                    // Khôi phục button
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = originalText;
                });
            }
        }
        // Hàm render lại giao diện cảm biến từ dữ liệu mới
        function renderSensors(sensors) {
            const container = document.getElementById("sensor-container");
            container.innerHTML = "";

            // Đếm số lượng cảm biến theo trạng thái
            let activeCount = 0;
            let maintenanceCount = 0;
            let errorCount = 0;

            sensors.forEach(sensor => {
                // Đếm trạng thái
                if (sensor.status === 'active') activeCount++;
                else if (sensor.status === 'maintenance') maintenanceCount++;
                else if (sensor.status === 'error') errorCount++;
                // Format thời gian
                let updatedAt = sensor.updated_at 
                    ? new Date(sensor.updated_at).toLocaleString("vi-VN") 
                    : "N/A";

                let installationDate = sensor.installation_date
                    ? new Date(sensor.installation_date).toLocaleDateString("vi-VN")
                    : null;

                let lastCalibration = sensor.last_calibration
                    ? new Date(sensor.last_calibration).toLocaleDateString("vi-VN")
                    : null;

                // Format description, notes
                let description = sensor.description 
                    ? (sensor.description.length > 80 ? sensor.description.substring(0,80) + "…" : sensor.description)
                    : "";

                let notes = sensor.notes 
                    ? (sensor.notes.length > 80 ? sensor.notes.substring(0,80) + "…" : sensor.notes)
                    : "";

                container.innerHTML += `
                    <div class="col-xl-4 col-md-6">
                        <div class="card sensor-card sensor-${sensor.status}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title">${sensor.sensor_name}</h5>
                                        <p class="text-muted mb-0">${sensor.location_name ?? 'Chưa gán vị trí'}</p>
                                    </div>
                                    <span class="badge bg-${
                                        sensor.status === 'active' ? 'success' : 
                                        (sensor.status === 'maintenance' ? 'warning' : 'danger')
                                    }">
                                        ${sensor.status}
                                    </span>
                                </div>

                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-primary">${sensor.current_temperature ?? 'N/A'}°C</h4>
                                        <p class="text-muted mb-0">Nhiệt độ hiện tại</p>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-info">${sensor.humidity ?? 'N/A'}%</h4>
                                        <p class="text-muted mb-0">Độ ẩm</p>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <small class="text-muted center-box">
                                        <i class="iconoir-calendar"></i> Cập nhật: ${updatedAt}
                                    </small>
                                    <div class="mt-1 small text-muted">
                                        ${sensor.manufacturer ? `
                                            <div class="center-box"><i class="iconoir-industry"></i> NSX: ${sensor.manufacturer}</div>` : ""
                                        }
                                        ${sensor.model ? `
                                            <div class="center-box"><i class="iconoir-tools"></i> Model: ${sensor.model}</div>` : ""
                                        }

                                        ${sensor.serial_number ? `
                                            <div class="center-box"><i class="iconoir-barcode"></i> Serial: ${sensor.serial_number}</div>` : ""
                                        }

                                        ${installationDate ? `
                                            <div class="center-box"><i class="iconoir-calendar"></i> Lắp đặt: ${installationDate}</div>` : ""
                                        }

                                        ${lastCalibration ? `
                                            <div class="center-box"><i class="iconoir-calendar"></i> Hiệu chuẩn cuối: ${lastCalibration}</div>` : ""
                                        }
                                    </div>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button style="border: 1px solid black; color: black;" class="btn btn-sm btn-outline-secondary"
                                            data-id="${sensor.id}"
                                            data-sensor-name="${sensor.sensor_name || ''}"
                                            data-sensor-code="${sensor.sensor_code || ''}"
                                            data-sensor-type="${sensor.sensor_type || ''}"
                                            data-location-id="${sensor.location_id ? sensor.location_id : ''}"
                                            data-manufacturer="${sensor.manufacturer || ''}"
                                            data-model="${sensor.model || ''}"
                                            data-serial-number="${sensor.serial_number || ''}"
                                            data-installation-date="${sensor.installation_date || ''}"
                                            data-status="${sensor.status || ''}"
                                            data-last-calibration="${sensor.last_calibration || ''}"
                                            data-description="${sensor.description || ''}"
                                            data-notes="${sensor.notes || ''}"
                                            onclick="openViewSensorFromButton(this)">
                                        <i class="iconoir-eye"></i> Xem
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-id="${sensor.id}"
                                            data-sensor-name="${sensor.sensor_name || ''}"
                                            data-sensor-code="${sensor.sensor_code || ''}"
                                            data-sensor-type="${sensor.sensor_type || ''}"
                                            data-location-id="${sensor.location_id ? sensor.location_id : ''}"
                                            data-manufacturer="${sensor.manufacturer || ''}"
                                            data-model="${sensor.model || ''}"
                                            data-serial-number="${sensor.serial_number || ''}"
                                            data-installation-date="${sensor.installation_date || ''}"
                                            data-status="${sensor.status || ''}"
                                            data-last-calibration="${sensor.last_calibration || ''}"
                                            data-description="${sensor.description || ''}"
                                            data-notes="${sensor.notes || ''}"
                                            onclick="openEditSensorFromButton(this)">
                                        <i class="iconoir-edit"></i> Sửa
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSensor(${sensor.id})">
                                        <i class="iconoir-trash"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
        
            // Cập nhật thống kê
            document.getElementById("total-sensors").textContent = sensors.length;
            document.getElementById("active-sensors").textContent = activeCount;
            document.getElementById("maintenance-sensors").textContent = maintenanceCount;
            document.getElementById("error-sensors").textContent = errorCount;
        }

        // Kết nối SSE
        const evtSource = new EventSource("../api/iot-sensor-stream.php");

        evtSource.onmessage = function(event) {
            try {
                const sensors = JSON.parse(event.data);
                renderSensors(sensors);
        } catch (e) {
            console.error("Lỗi parse SSE:", e, event.data);
        }
    };

    evtSource.onerror = function(err) {
        console.error("Lỗi SSE:", err);
    };
    </script>

    <!-- Delete Success Message - Fixed Position -->
    <div id="successMessageDeleteSensor" class="success-alert-fixed delete-success">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Cảm biến đã được xóa thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideDeleteSensorSuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</body>
</html>
