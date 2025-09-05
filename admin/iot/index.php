<?php
session_start();
require_once '../../config/database.php';
require_once 'models/TemperatureSensor.php';
require_once 'models/TemperatureReading.php';
require_once 'models/WarehouseLocation.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }
    
    $sensorModel = new TemperatureSensor($pdo);
    $readingModel = new TemperatureReading($pdo);
    $locationModel = new WarehouseLocation($pdo);
    
    // Lấy dữ liệu
    $sensors = $sensorModel->getAllSensors();
    $latestReadings = $readingModel->getLatestReadings();
    $temperatureStats = $readingModel->getTemperatureStats();
    $locations = $locationModel->getAllLocations();
    $capacityStats = $locationModel->getCapacityStats(); // Lấy thống kê sức chứa
    
} catch(Exception $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>IoT Dashboard - IoT System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="IoT Dashboard cho hệ thống quản lý kho" name="description" />
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Common Admin Layout CSS -->
    <link href="../partials/layout.css" rel="stylesheet" type="text/css" />
    
    <!-- Unified Widgets CSS -->
    <link href="../../assets/css/widget.css" rel="stylesheet" type="text/css" />
    
    <style>
        .temperature-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sensor-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .location-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .warehouse-map {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            min-height: 400px;
        }
        .zone {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            background: white;
        }
        .zone-a { border-color: #28a745; }
        .zone-b { border-color: #007bff; }
        .zone-c { border-color: #ffc107; }
        .zone-d { border-color: #dc3545; }
        .sensor-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .sensor-online { background-color: #28a745; }
        .sensor-offline { background-color: #dc3545; }
        .sensor-warning { background-color: #ffc107; }
        
        /* Chart styling */
        .chart-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        
        .chart-legend {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            display: inline-block;
        }
        
        /* Sensor status styling */
        .sensor-status-item {
            background: #f8f9fa;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef !important;
        }
        
        .sensor-status-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Custom button styling */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .page-title {
            color: #495057;
            font-weight: 600;
            margin: 0;
        }

        .custom-css {
            display: flex;
            align-items: center;
        }

        /* Sensor status scrollable area */
        #sensorStatus {
            height: 420px; /* cố định chiều cao */
            overflow-y: auto; /* cho phép cuộn dọc */
            padding-right: 6px;
            -webkit-overflow-scrolling: touch;
        }
        #sensorStatus::-webkit-scrollbar { width: 6px; }
        #sensorStatus::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
        #sensorStatus::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        #sensorStatus::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
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

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php else: ?>

                <!-- Header with Add Button -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="page-title">
                                <i class="iconoir-dashboard me-2"></i>
                                IoT Dashboard
                            </h2>
                            <button type="button" class="btn btn-primary btn-lg" onclick="openCreateSensorModal()">
                                <i class="iconoir-plus me-2"></i>
                                Thêm Cảm Biến Mới
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Overview Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card temperature-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="text-white"><?php echo count($sensors); ?></h4>
                                        <p class="text-white-50 mb-0">Cảm biến hoạt động</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-cpu text-white" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card sensor-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="text-white"><?php echo count($locations); ?></h4>
                                        <p class="text-white-50 mb-0">Vị trí kho</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-map-pin text-white" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card location-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="text-white"><?php echo $temperatureStats['avg_temp'] ?? 'N/A'; ?>°C</h4>
                                        <p class="text-white-50 mb-0">Nhiệt độ trung bình</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-cloud-sunny text-white" style="font-size: 3rem;"></i>
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
                                        <h4 class="text-white"><?php echo $temperatureStats['max_temp'] ?? 'N/A'; ?>°C</h4>
                                        <p class="text-white-50 mb-0">Nhiệt độ cao nhất</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="iconoir-fire-flame text-white" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="header-title mb-0">
                                        <i class="iconoir-cloud-sunny text-primary me-2"></i>
                                        Biểu đồ nhiệt độ theo thời gian
                                    </h4>
                                    <div class="btn-group btn-group-sm" role="group" style="border: 1px solid #ddd;">
                                        <button type="button" class="btn btn-outline-primary active" onclick="updateChartPeriod('24h')">24h</button>
                                        <button type="button" class="btn btn-outline-primary" onclick="updateChartPeriod('7d')">7 ngày</button>
                                        <button type="button" class="btn btn-outline-primary" onclick="updateChartPeriod('30d')">30 ngày</button>
                                    </div>
                                </div>
                                <div class="chart-container" style="position: relative; height: 350px;">
                                    <canvas id="temperatureChart"></canvas>
                                </div>
                                <div class="text-center mt-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="chart-legend bg-primary me-2"></div>
                                                <small class="text-muted">Nhiệt độ (°C)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="chart-legend bg-success me-2"></div>
                                                <small class="text-muted">Độ ẩm (%)</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="chart-legend bg-warning me-2"></div>
                                                <small class="text-muted">Trung bình</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="chart-legend bg-info me-2"></div>
                                                <small class="text-muted">Cảm biến</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">
                                    <i class="iconoir-cpu text-success me-2"></i>
                                    Trạng thái cảm biến
                                </h4>
                                <div id="sensorStatus">
                                    <?php foreach ($sensors as $sensor): ?>
                                        <div class="sensor-status-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="sensor-indicator <?php echo $sensor['status'] === 'active' ? 'sensor-online' : 'sensor-offline'; ?> me-2"></span>
                                                    <div>
                                                        <strong class="d-block"><?php echo htmlspecialchars($sensor['sensor_name']); ?></strong>
                                                        <small class="text-muted"><?php echo htmlspecialchars($sensor['sensor_code']); ?></small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-<?php echo $sensor['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($sensor['status']); ?>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <div class="mt-1 small text-muted">
                                                    <?php if (!empty($sensor['installation_date'])): ?>
                                                        <div class="custom-css"><i class="iconoir-calendar me-1"></i> Lắp đặt: <?php echo date('d/m/Y', strtotime($sensor['installation_date'])); ?></div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($sensor['last_calibration'])): ?>
                                                        <div class="custom-css"><i class="iconoir-calendar me-1"></i> Hiệu chuẩn cuối: <?php echo date('d/m/Y', strtotime($sensor['last_calibration'])); ?></div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($sensor['serial_number'])): ?>
                                                        <div class="custom-css"><i class="iconoir-hash me-1"></i> Serial: <?php echo htmlspecialchars($sensor['serial_number']); ?></div>
                                                    <?php endif; ?>

                                                    <?php if (isset($sensor['min_threshold']) || isset($sensor['max_threshold'])): ?>
                                                        <div class="custom-css"><i class="iconoir-warning-triangle me-1"></i> Ngưỡng: 
                                                            <?php echo ($sensor['min_threshold'] !== null && $sensor['min_threshold'] !== '' ? htmlspecialchars($sensor['min_threshold']) : '—'); ?> - 
                                                            <?php echo ($sensor['max_threshold'] !== null && $sensor['max_threshold'] !== '' ? htmlspecialchars($sensor['max_threshold']) : '—'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview of Capacity -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Thống kê sức chứa kho hàng</h4>
                                <div class="row">
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h4 class="text-primary"><?php echo $capacityStats['total_max_capacity'] ?? 0; ?></h4>
                                                        <p class="text-muted mb-0">Tổng sức chứa</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="iconoir-box text-primary" style="font-size: 3rem;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h4 class="text-success"><?php echo $capacityStats['total_current_capacity'] ?? 0; ?></h4>
                                                        <p class="text-muted mb-0">Đã sử dụng</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="iconoir-package text-success" style="font-size: 3rem;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h4 class="text-info"><?php echo ($capacityStats['total_max_capacity'] ?? 0) - ($capacityStats['total_current_capacity'] ?? 0); ?></h4>
                                                        <p class="text-muted mb-0">Còn trống</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="iconoir-arrow-down text-info" style="font-size: 3rem;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h4 class="text-warning"><?php echo round(($capacityStats['avg_utilization'] ?? 0), 1); ?>%</h4>
                                                        <p class="text-muted mb-0">Tỷ lệ sử dụng</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="iconoir-percentage text-warning" style="font-size: 3rem;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Map -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Bản đồ kho hàng</h4>
                                <div class="warehouse-map">
                                    <div class="row">
                                        <?php foreach ($locations as $location): ?>
                                            <div class="col-md-3">
                                                <div class="zone zone-<?php echo strtolower(substr($location['area'], 0, 1)); ?>">
                                                    <h6><?php echo htmlspecialchars($location['location_name']); ?></h6>
                                                    <p class="mb-1"><strong>Khu vực:</strong> <?php echo htmlspecialchars($location['area']); ?></p>
                                                    <p class="mb-1"><strong>Sức chứa:</strong> <?php echo $location['max_capacity']; ?> sản phẩm</p>
                                                    <p class="mb-1"><strong>Đã sử dụng:</strong> <?php echo $location['current_capacity']; ?> sản phẩm</p>
                                                    <div class="progress mb-2">
                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo ($location['current_capacity'] / $location['max_capacity']) * 100; ?>%" aria-valuenow="<?php echo $location['current_capacity']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $location['max_capacity']; ?>"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            </div>
            <!-- container -->
        </div>
        <!-- content -->

        <!-- Include Widgets -->
        <?php include '../../assets/widgets/create-sensor.php'; ?>
        
    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    <!-- Bootstrap JS -->
    <script src="../../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Simplebar -->
    <script src="../../assets/libs/simplebar/simplebar.min.js"></script>
    
    <!-- Common Admin Layout JavaScript -->
    <script src="../../admin/partials/layout.js"></script>
    
    <!-- Unified Widgets JavaScript -->
    <script src="../../assets/js/widget.js"></script>

    <script>
        // Dữ liệu mẫu cho biểu đồ
        const chartData = {
            '24h': {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                temperature: [22, 21, 23, 26, 28, 25, 23],
                humidity: [65, 68, 62, 58, 55, 60, 64],
                average: [22.5, 22.8, 23.2, 25.1, 26.8, 25.3, 23.8]
            },
            '7d': {
                labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                temperature: [23, 24, 25, 26, 27, 25, 24],
                humidity: [60, 58, 55, 52, 50, 55, 58],
                average: [23.2, 24.1, 24.8, 25.5, 26.2, 25.1, 24.3]
            },
            '30d': {
                labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
                temperature: [24, 25, 26, 25],
                humidity: [58, 55, 52, 56],
                average: [24.2, 25.1, 25.8, 25.3]
            }
        };

        let currentPeriod = '24h';
        let temperatureChart;

        // Khởi tạo biểu đồ nhiệt độ
        function initTemperatureChart() {
            const ctx = document.getElementById('temperatureChart').getContext('2d');
            
            // Gradient cho background
            const gradientBg = ctx.createLinearGradient(0, 0, 0, 400);
            gradientBg.addColorStop(0, 'rgba(75, 192, 192, 0.3)');
            gradientBg.addColorStop(1, 'rgba(75, 192, 192, 0.05)');

            const gradientBg2 = ctx.createLinearGradient(0, 0, 0, 400);
            gradientBg2.addColorStop(0, 'rgba(255, 99, 132, 0.3)');
            gradientBg2.addColorStop(1, 'rgba(255, 99, 132, 0.05)');

            temperatureChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData[currentPeriod].labels,
                    datasets: [
                        {
                            label: 'Nhiệt độ (°C)',
                            data: chartData[currentPeriod].temperature,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: gradientBg,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(75, 192, 192)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Độ ẩm (%)',
                            data: chartData[currentPeriod].humidity,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: gradientBg2,
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(255, 99, 132)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Trung bình',
                            data: chartData[currentPeriod].average,
                            borderColor: 'rgb(255, 205, 86)',
                            backgroundColor: 'rgba(255, 205, 86, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: false,
                            pointBackgroundColor: 'rgb(255, 205, 86)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(75, 192, 192, 0.5)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y + (context.datasetIndex === 0 ? '°C' : '%');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Thời gian',
                                color: '#6c757d',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Nhiệt độ (°C)',
                                color: '#6c757d',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return value + '°C';
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Độ ẩm (%)',
                                color: '#6c757d',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverBackgroundColor: '#fff',
                            hoverBorderColor: 'rgb(75, 192, 192)',
                            hoverBorderWidth: 3
                        }
                    }
                }
            });
        }

        // Cập nhật biểu đồ theo thời gian
        function updateChartPeriod(period) {
            currentPeriod = period;
            
            // Cập nhật trạng thái button
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Cập nhật dữ liệu biểu đồ
            temperatureChart.data.labels = chartData[period].labels;
            temperatureChart.data.datasets[0].data = chartData[period].temperature;
            temperatureChart.data.datasets[1].data = chartData[period].humidity;
            temperatureChart.data.datasets[2].data = chartData[period].average;
            
            temperatureChart.update('active');
        }

        // Khởi tạo biểu đồ khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            initTemperatureChart();
        });

        // Cập nhật dữ liệu theo thời gian thực (mỗi 30 giây)
        setInterval(() => {
            // Ở đây bạn có thể gọi API để lấy dữ liệu mới
            console.log('Cập nhật dữ liệu...');
            
            // Cập nhật dữ liệu mẫu (thay thế bằng API call thực tế)
            if (currentPeriod === '24h') {
                const newTemp = Math.floor(Math.random() * 10) + 20; // 20-30°C
                const newHumidity = Math.floor(Math.random() * 20) + 50; // 50-70%
                
                // Thêm dữ liệu mới vào cuối
                temperatureChart.data.labels.push(new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}));
                temperatureChart.data.datasets[0].data.push(newTemp);
                temperatureChart.data.datasets[1].data.push(newHumidity);
                temperatureChart.data.datasets[2].data.push((newTemp + newHumidity) / 2);
                
                // Giữ chỉ 7 điểm dữ liệu
                if (temperatureChart.data.labels.length > 7) {
                    temperatureChart.data.labels.shift();
                    temperatureChart.data.datasets[0].data.shift();
                    temperatureChart.data.datasets[1].data.shift();
                    temperatureChart.data.datasets[2].data.shift();
                }
                
                temperatureChart.update('active');
            }
        }, 30000);
    </script>
</body>
</html>
