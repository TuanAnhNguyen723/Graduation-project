<?php
session_start();
require_once '../../../config/database.php';
require_once '../models/WarehouseLocation.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database");
    }
    
    $locationModel = new WarehouseLocation($pdo);
    
    // Lấy dữ liệu
    $locations = $locationModel->getAllLocations();
    $capacityStats = $locationModel->getCapacityStats();
    $capacityStatsByZone = $locationModel->getCapacityStatsByZone();
    $productCounts = $locationModel->getProductCountsPerLocation();
    
} catch(Exception $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <title>Quản lý vị trí kho - IoT System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Quản lý vị trí kho IoT" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../../../assets/images/favicon.ico">
    <!-- App css -->
    <link href="../../../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/widget.css" rel="stylesheet" type="text/css" />
    
    <!-- Common Admin Layout CSS -->
    <link href="../../partials/layout.css" rel="stylesheet" type="text/css" />
    
    <style>
        .location-card {
            border-left: 4px solid #007bff;
        }
        .zone-a { border-left-color: #28a745; }
        .zone-b { border-left-color: #007bff; }
        .zone-c { border-left-color: #ffc107; }
        .zone-d { border-left-color: #dc3545; }
        .capacity-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
            transition: width 0.3s ease;
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
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php else: ?>

                <!-- Add Location Button -->
                <div class="row mb-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="openCreateLocationModal()">
                            <i class="iconoir-plus"></i> Thêm vị trí mới
                        </button>
                    </div>
                </div>

                <!-- Capacity Statistics -->
                <div class="row mb-4">
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

                <!-- Locations Grid -->
                <div class="row">
                    <?php foreach ($locations as $location): ?>
                        <div class="col-xl-4 col-md-6">
                            <div class="card location-card zone-<?php echo strtolower(substr($location['area'], 0, 1)); ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title"><?php echo htmlspecialchars($location['location_name']); ?></h5>
                                            <p class="text-muted mb-0">Khu vực: <?php echo htmlspecialchars($location['area']); ?></p>
                                            <?php 
                                                $zone = $location['temperature_zone'] ?? 'ambient';
                                                $zoneMap = [
                                                  'ambient' => ['Ambient','15-33°C, 50-60%','bg-warning-subtle text-warning','iconoir-sun-light'],
                                                  'chilled' => ['Chilled','0-5°C, 85-90%','bg-primary-subtle text-primary','iconoir-snow'],
                                                  'frozen'  => ['Frozen','≤-18°C, 85-95%','bg-info-subtle text-info','iconoir-ice-cream']
                                                ];
                                                $z = $zoneMap[$zone] ?? $zoneMap['ambient'];
                                            ?>
                                            <div class="mt-1">
                                                <span style="display: flex; align-items: center; gap: 10px;" class="badge <?php echo $z[2]; ?>" title="<?php echo $z[1]; ?>">
                                                    <i class="<?php echo $z[3]; ?>"></i> Mức môi trường: <?php echo $z[0]; ?> (<?php echo $z[1]; ?>)
                                                </span>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($location['area']); ?></span>
                                    </div>
                                    
                                    <?php 
                                        $maxCap = (int)$location['max_capacity'];
                                        $baseUsed = (int)$location['current_capacity'];
                                        $catProducts = isset($productCounts[$location['id']]) ? (int)$productCounts[$location['id']] : 0;
                                        $usedTotal = min($maxCap, max(0, $baseUsed + $catProducts));
                                        $freeTotal = max(0, $maxCap - $usedTotal);
                                        $percentUsed = $maxCap > 0 ? ($usedTotal * 100.0 / $maxCap) : 0;
                                    ?>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Sức chứa:</strong> <?php echo $maxCap; ?> sản phẩm</p>
                                        <p class="mb-1"><strong>Đã sử dụng:</strong> <?php echo $usedTotal; ?> sản phẩm</p>
                                        <p class="mb-1"><strong>Còn trống:</strong> <?php echo $freeTotal; ?> sản phẩm</p>
                                    </div>
                                    
                                    <div class="capacity-bar mb-3">
                                        <div class="capacity-fill" style="width: <?php echo $percentUsed; ?>%"></div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="openEditLocationModalWidget(<?php echo $location['id']; ?>)">
                                            <i class="iconoir-edit"></i> Sửa
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteLocation(<?php echo $location['id']; ?>)">
                                            <i class="iconoir-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php endif; ?>
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

    <?php include '../../../assets/widgets/create-location.php'; ?>
    <?php include '../../../assets/widgets/edit-location.php'; ?>

    <script>
      // Widget-style open/close/submit
      function openCreateLocationModal(){
        const modal = document.getElementById('createLocationModal');
        if (modal) { modal.classList.add('show'); setTimeout(()=>{ const inner=modal.querySelector('.custom-modal'); if(inner){ inner.classList.add('show'); } },10); document.body.style.overflow='hidden'; }
      }
      function closeCreateLocationModal(){
        const modal = document.getElementById('createLocationModal');
        if (modal) { modal.querySelector('.custom-modal').classList.remove('show'); setTimeout(()=>{ modal.classList.remove('show'); },300); document.body.style.overflow=''; }
      }
      async function submitCreateLocationForm(){
        const btn = document.getElementById('createLocationSubmitBtn'); const original = btn?.innerHTML || '';
        try{
          if (btn){ btn.disabled=true; btn.classList.add('loading'); btn.innerHTML='Đang tạo...'; }
          const form = document.getElementById('createLocationForm');
          const fd = new FormData(form);
          const res = await fetch('../api/locations.php', { method: 'POST', body: fd });
          const data = await res.json();
          if (data.success){
            const msg = document.getElementById('successMessageCreateLocation'); if (msg){ msg.classList.add('show','slide-in'); setTimeout(()=>{ msg.classList.remove('show','slide-in'); }, 1500); }
            setTimeout(()=>{ location.reload(); }, 1600);
          } else { alert(data.message || 'Không thể tạo vị trí'); }
        } finally { if (btn){ btn.disabled=false; btn.classList.remove('loading'); btn.innerHTML = original || 'Tạo vị trí'; } }
      }

      async function openEditLocationModalWidget(id){
        const res = await fetch('../api/locations.php?id='+id);
        const result = await res.json();
        if (!result.success || !result.data){ alert('Không tìm thấy vị trí'); return; }
        const form = document.getElementById('editLocationFormWidget');
        form.elements['id'].value = result.data.id;
        document.getElementById('editLocCode').value = result.data.location_code;
        document.getElementById('editLocName').value = result.data.location_name;
        document.getElementById('editLocArea').value = result.data.area;
        document.getElementById('editTempZone').value = result.data.temperature_zone;
        document.getElementById('editMaxCapacity').value = result.data.max_capacity;
        const modal = document.getElementById('editLocationModalWidget');
        if (modal){ modal.classList.add('show'); setTimeout(()=>{ const inner=modal.querySelector('.custom-modal'); if(inner){ inner.classList.add('show'); } },10); document.body.style.overflow='hidden'; }
      }

      function closeEditLocationModal(){
        const modal = document.getElementById('editLocationModalWidget');
        if (modal){ modal.querySelector('.custom-modal').classList.remove('show'); setTimeout(()=>{ modal.classList.remove('show'); },300); document.body.style.overflow=''; }
      }

      async function submitEditLocationForm(){
        const btn = document.getElementById('editLocationSubmitBtn'); const original = btn?.innerHTML || '';
        try{
          if (btn){ btn.disabled=true; btn.classList.add('loading'); btn.innerHTML='Đang cập nhật...'; }
          const form = document.getElementById('editLocationFormWidget');
          const fd = new FormData(form);
          const res = await fetch('../api/locations.php', { method: 'POST', body: fd });
          const data = await res.json();
          if (data.success){
            const msg = document.getElementById('successMessageEditLocation'); if (msg){ msg.classList.add('show','slide-in'); setTimeout(()=>{ msg.classList.remove('show','slide-in'); }, 1500); }
            setTimeout(()=>{ location.reload(); }, 1600);
          } else { alert(data.message || 'Không thể cập nhật vị trí'); }
        } finally { if (btn){ btn.disabled=false; btn.classList.remove('loading'); btn.innerHTML = original || 'Cập nhật vị trí'; } }
      }

      async function confirmDeleteLocation(id){
        if (!confirm('Bạn có chắc chắn muốn xóa vị trí này?')) return;
        const res = await fetch('../api/locations.php?id='+id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success){ location.reload(); } else { alert(data.message || 'Không thể xóa vị trí'); }
      }
    </script>
</body>
</html>
