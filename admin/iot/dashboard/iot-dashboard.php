<?php
require_once 'config/database.php';
require_once 'models/TemperatureSensor.php';
require_once 'models/TemperatureReading.php';
require_once 'models/WarehouseLocation.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sensorModel = new TemperatureSensor($pdo);
    $readingModel = new TemperatureReading($pdo);
    $locationModel = new WarehouseLocation($pdo);
    
    // Lấy dữ liệu cho dashboard
    $sensors = $sensorModel->getAllSensors();
    $latestReadings = $readingModel->getLatestReadings();
    $capacityStats = $locationModel->getCapacityStats();
    $capacityStatsByZone = $locationModel->getCapacityStatsByZone();
    
} catch(PDOException $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard - Quản lý kho thông minh</title>
    
    <!-- CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.min.css" rel="stylesheet">
    <link href="assets/css/icons.min.css" rel="stylesheet">
    <link href="assets/css/custom-override.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="assets/libs/chart.js/chart.min.js"></script>
    
    <style>
        .temperature-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .humidity-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .status-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .capacity-card {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .sensor-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .sensor-item:hover {
            transform: translateY(-5px);
        }
        
        .temperature-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .humidity-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active { background: #28a745; }
        .status-inactive { background: #6c757d; }
        .status-maintenance { background: #ffc107; color: #000; }
        .status-error { background: #dc3545; }
        
        .zone-badge {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .zone-cold { background: #17a2b8; }
        .zone-cool { background: #28a745; }
        .zone-room { background: #ffc107; color: #000; }
        .zone-warm { background: #fd7e14; }
        
        .real-time-indicator {
            width: 10px;
            height: 10px;
            background: #00ff00;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .warehouse-map {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .map-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        
        .map-cell {
            aspect-ratio: 1;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .map-cell:hover {
            transform: scale(1.1);
            z-index: 10;
        }
        
        .map-cell.occupied {
            background: #ff6b6b;
            color: white;
            border-color: #ff5252;
        }
        
        .map-cell.available {
            background: #51cf66;
            color: white;
            border-color: #40c057;
        }
        
        .map-cell.partial {
            background: #ffd43b;
            color: #000;
            border-color: #fcc419;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-thermometer-half text-primary"></i>
                        IoT Dashboard - Quản lý kho thông minh
                    </h1>
                    <div class="d-flex align-items-center">
                        <span class="real-time-indicator"></span>
                        <span class="text-muted">Dữ liệu thời gian thực</span>
                        <span class="badge badge-success ml-2"><?php echo date('H:i:s'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card temperature-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Tổng cảm biến</h6>
                                <h2 class="text-white"><?php echo count($sensors); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-thermometer-half fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card humidity-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Vị trí kho</h6>
                                <h2 class="text-white"><?php echo $capacityStats['total_locations'] ?? 0; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-map-marker-alt fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card status-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Sức chứa sử dụng</h6>
                                <h2 class="text-white"><?php echo round(($capacityStats['avg_utilization'] ?? 0), 1); ?>%</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-pie fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card capacity-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-white-50">Tổng sức chứa</h6>
                                <h2 class="text-white"><?php echo number_format($capacityStats['total_max_capacity'] ?? 0); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cảm biến nhiệt độ thời gian thực -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-thermometer-half text-danger"></i>
                            Cảm biến nhiệt độ thời gian thực
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="sensors-container">
                            <?php foreach ($latestReadings as $reading): ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="sensor-item">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($reading['sensor_name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($reading['location_name']); ?></small>
                                        </div>
                                        <span class="status-badge status-<?php echo $reading['status'] ?? 'active'; ?>">
                                            <?php echo ucfirst($reading['status'] ?? 'active'); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="temperature-value text-danger">
                                                <?php echo number_format($reading['temperature'], 1); ?>°C
                                            </div>
                                            <small class="text-muted">Nhiệt độ</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="humidity-value text-info">
                                                <?php echo number_format($reading['humidity'], 1); ?>%
                                            </div>
                                            <small class="text-muted">Độ ẩm</small>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            Cập nhật: <?php echo date('H:i:s', strtotime($reading['reading_timestamp'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ nhiệt độ -->
        <div class="row mb-4">
            <div class="col-xl-8">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-line text-primary"></i>
                        Biểu đồ nhiệt độ 24 giờ qua
                    </h5>
                    <canvas id="temperatureChart" height="100"></canvas>
                </div>
            </div>
            
            <div class="col-xl-4">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-pie text-success"></i>
                        Sức chứa theo vùng nhiệt độ
                    </h5>
                    <canvas id="capacityChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Bản đồ kho -->
        <div class="row">
            <div class="col-12">
                <div class="warehouse-map">
                    <h5 class="mb-3">
                        <i class="fas fa-map text-info"></i>
                        Bản đồ kho
                    </h5>
                    <div class="map-grid" id="warehouse-map">
                        <!-- Sẽ được tạo bằng JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
    <script>
        // Dữ liệu mẫu cho biểu đồ
        const temperatureData = {
            labels: ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
            datasets: [{
                label: 'Nhiệt độ (°C)',
                data: [22, 21, 20, 19, 21, 23, 25, 26, 25, 24, 23, 22],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        const humidityData = {
            labels: ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
            datasets: [{
                label: 'Độ ẩm (%)',
                data: [65, 68, 70, 72, 68, 60, 55, 50, 55, 60, 63, 65],
                borderColor: '#f093fb',
                backgroundColor: 'rgba(240, 147, 251, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        // Biểu đồ nhiệt độ
        const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
        new Chart(temperatureCtx, {
            type: 'line',
            data: temperatureData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 15,
                        max: 30
                    }
                }
            }
        });

        // Biểu đồ sức chứa
        const capacityCtx = document.getElementById('capacityChart').getContext('2d');
        new Chart(capacityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cold', 'Cool', 'Room', 'Warm'],
                datasets: [{
                    data: [60, 80, 100, 70],
                    backgroundColor: [
                        '#17a2b8',
                        '#28a745',
                        '#ffc107',
                        '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Tạo bản đồ kho
        function createWarehouseMap() {
            const mapContainer = document.getElementById('warehouse-map');
            const areas = ['A', 'B', 'C'];
            const rows = 2;
            const cols = 2;
            
            areas.forEach(area => {
                // Tạo header cho khu vực
                const areaHeader = document.createElement('div');
                areaHeader.className = 'col-12 text-center mb-2';
                areaHeader.style.gridColumn = 'span 6';
                areaHeader.innerHTML = `<h6 class="text-primary">Khu vực ${area}</h6>`;
                mapContainer.appendChild(areaHeader);
                
                // Tạo các ô cho khu vực
                for (let row = 1; row <= rows; row++) {
                    for (let col = 1; col <= cols; col++) {
                        const cell = document.createElement('div');
                        cell.className = 'map-cell';
                        cell.innerHTML = `${area}${row}${col}`;
                        
                        // Phân loại trạng thái
                        if (Math.random() > 0.7) {
                            cell.classList.add('occupied');
                        } else if (Math.random() > 0.4) {
                            cell.classList.add('partial');
                        } else {
                            cell.classList.add('available');
                        }
                        
                        mapContainer.appendChild(cell);
                    }
                }
            });
        }

        // Khởi tạo bản đồ
        createWarehouseMap();

        // Cập nhật dữ liệu thời gian thực
        function updateRealTimeData() {
            // Cập nhật thời gian
            const timeElement = document.querySelector('.badge-success');
            if (timeElement) {
                timeElement.textContent = new Date().toLocaleTimeString('vi-VN');
            }
            
            // Cập nhật dữ liệu cảm biến (sẽ được thay thế bằng API call thực tế)
            // fetch('/api/temperature-readings/latest')
            //     .then(response => response.json())
            //     .then(data => {
            //         // Cập nhật UI với dữ liệu mới
            //     });
        }

        // Cập nhật mỗi 5 giây
        setInterval(updateRealTimeData, 5000);
    </script>
</body>
</html>
