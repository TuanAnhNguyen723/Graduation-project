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
                                        $actualUsed = isset($productCounts[$location['id']]) ? (int)$productCounts[$location['id']] : 0;
                                        $freeTotal = max(0, $maxCap - $actualUsed);
                                        $percentUsed = $maxCap > 0 ? ($actualUsed * 100.0 / $maxCap) : 0;
                                    ?>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Sức chứa:</strong> <?php echo $maxCap; ?> sản phẩm</p>
                                        <p class="mb-1"><strong>Đã sử dụng:</strong> <?php echo $actualUsed; ?> sản phẩm</p>
                                        <p class="mb-1"><strong>Còn trống:</strong> <?php echo $freeTotal; ?> sản phẩm</p>
                                    </div>
                                    
                                    <div class="capacity-bar mb-3">
                                        <div class="capacity-fill" style="width: <?php echo $percentUsed; ?>%"></div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="action-buttons-container">
                                            <button class="btn btn-xs btn-success box-center" onclick="openImportStockModal(<?php echo $location['id']; ?>, '<?php echo htmlspecialchars($location['location_name']); ?>')">
                                                <i class="iconoir-plus"></i> Nhập
                                            </button>
                                            <button class="btn btn-xs btn-warning box-center" onclick="openExportStockModal(<?php echo $location['id']; ?>, '<?php echo htmlspecialchars($location['location_name']); ?>')">
                                                <i class="iconoir-minus"></i> Xuất
                                            </button>
                                            <button class="btn btn-xs btn-info box-center" onclick="viewLocationStock(<?php echo $location['id']; ?>, '<?php echo htmlspecialchars($location['location_name']); ?>')">
                                                <i class="iconoir-eye"></i> Xem
                                            </button>
                                            <button class="btn btn-xs btn-outline-primary box-center" onclick="openEditLocationModalWidget(<?php echo $location['id']; ?>)">
                                            <i class="iconoir-edit"></i> Sửa
                                        </button>
                                            <button class="btn btn-xs btn-outline-danger box-center" onclick="confirmDeleteLocation(<?php echo $location['id']; ?>)">
                                            <i class="iconoir-trash"></i> Xóa
                                        </button>
                                        </div>
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
    
    <!-- Widget JavaScript -->
    <script src="../../../assets/js/widget.js"></script>

    <?php include '../../../assets/widgets/create-location.php'; ?>
    <?php include '../../../assets/widgets/edit-location.php'; ?>
    <?php include '../../../assets/widgets/import-stock.php'; ?>
    <?php include '../../../assets/widgets/export-stock.php'; ?>
    <?php include '../../../assets/widgets/view-location-stock.php'; ?>

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
        try {
          // Kiểm tra xem vị trí có sản phẩm không
          const checkResponse = await fetch(`../api/locations.php?action=check_products&id=${id}`);
          
          if (!checkResponse.ok) {
            throw new Error(`HTTP error! status: ${checkResponse.status}`);
          }
          
          const checkText = await checkResponse.text();
          let checkData;
          
          try {
            checkData = JSON.parse(checkText);
          } catch (parseError) {
            console.error('Response is not valid JSON:', checkText);
            throw new Error('Server trả về dữ liệu không hợp lệ');
          }
          
          if (checkData.has_products) {
            showLocationStockWarningAlert(checkData.location_name || 'Vị trí này');
            return;
          }
          
          if (window.showConfirmToast) {
            window.showConfirmToast('warning', 'Bạn có chắc chắn muốn xóa vị trí?', 'Vị trí này sẽ bị xóa vĩnh viễn.', () => {
              // Thực hiện xóa trong callback
              executeDeleteLocation(id);
            });
            return;
          } else {
            if (!confirm('Bạn có chắc chắn muốn xóa vị trí này?')) return;
            executeDeleteLocation(id);
          }
        } catch (error) {
          console.error('Error checking/deleting location:', error);
          alert('Có lỗi xảy ra khi xóa vị trí: ' + error.message);
        }
      }

      async function executeDeleteLocation(id) {
        try {
          const res = await fetch('../api/locations.php?id='+id, { method: 'DELETE' });
            
          if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
          }
          
          const responseText = await res.text();
          let data;
          
          try {
            data = JSON.parse(responseText);
          } catch (parseError) {
            console.error('Response is not valid JSON:', responseText);
            throw new Error('Server trả về dữ liệu không hợp lệ');
          }
          
          if (data.success){ 
            location.reload(); 
          } else { 
            alert(data.message || 'Không thể xóa vị trí'); 
          }
        } catch (error) {
          console.error('Error checking/deleting location:', error);
          alert('Có lỗi xảy ra khi xóa vị trí: ' + error.message);
        }
      }

      // Stock Operations Functions
      async function openImportStockModal(locationId, locationName) {
        // Reset form và ẩn thông tin sản phẩm cũ
        document.getElementById('importLocationId').value = locationId;
        document.getElementById('importLocationName').textContent = `Nhập hàng vào: ${locationName}`;
        
        // Reset form fields
        document.getElementById('importProductSelect').value = '';
        document.getElementById('importQuantity').value = '';
        
        // Ẩn thông tin sản phẩm và capacity info
        hideProductInfo('import');
        document.getElementById('importCapacityInfo').style.display = 'none';
        
        // Reset product info display
        document.getElementById('importProductSku').textContent = '-';
        document.getElementById('importProductCategory').textContent = '-';
        document.getElementById('importProductPrice').textContent = '-';
        document.getElementById('importCurrentStock').textContent = '-';
        
        // Load products (chỉ sản phẩm có cùng temperature_zone với vị trí)
        try {
          const response = await fetch(`../api/stock-operations.php?action=get_products&location_id=${locationId}`);
          const result = await response.json();
          
          if (result.success) {
            const select = document.getElementById('importProductSelect');
            select.innerHTML = '<option value="">Chọn sản phẩm để nhập</option>';
            
            if (result.data.length === 0) {
              const option = document.createElement('option');
              option.value = '';
              option.textContent = `Không có sản phẩm phù hợp với mức môi trường (${result.location_temperature_zone || 'ambient'})`;
              option.disabled = true;
              select.appendChild(option);
            } else {
              result.data.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${product.sku})`;
                select.appendChild(option);
              });
            }
            
            // Hiển thị thông tin mức môi trường
            const locationZone = result.location_temperature_zone || 'ambient';
            const zoneLabels = {
              'frozen': 'Đông lạnh',
              'chilled': 'Lạnh Mát', 
              'ambient': 'Nhiệt độ phòng'
            };
            
            const zoneInfo = document.getElementById('importZoneInfo');
            if (zoneInfo) {
              zoneInfo.innerHTML = `
                <div class="alert alert-info mb-3 box-center">
                  <i class="iconoir-snow-flake"></i>
                  <strong>Mức môi trường:</strong> ${zoneLabels[locationZone] || zoneLabels['ambient']}
                </div>
              `;
            }
          }
        } catch (error) {
          console.error('Error loading products:', error);
          showInsufficientStockAlert('Không thể tải danh sách sản phẩm phù hợp');
        }
        
        const modal = document.getElementById('importStockModal');
        if (modal) {
          modal.classList.add('show');
          setTimeout(() => {
            const inner = modal.querySelector('.custom-modal');
            if (inner) inner.classList.add('show');
          }, 10);
          document.body.style.overflow = 'hidden';
        }
      }

      function closeImportStockModal() {
        const modal = document.getElementById('importStockModal');
        if (modal) {
          modal.querySelector('.custom-modal').classList.remove('show');
          setTimeout(() => {
            modal.classList.remove('show');
          }, 300);
          document.body.style.overflow = '';
        }
      }

      // Add event listeners for product selection in import modal
      document.addEventListener('DOMContentLoaded', function() {
        const importProductSelect = document.getElementById('importProductSelect');
        const exportProductSelect = document.getElementById('exportProductSelect');
        
        if (importProductSelect) {
          importProductSelect.addEventListener('change', function() {
            handleProductSelection(this.value, 'import');
          });
        }
        
        if (exportProductSelect) {
          exportProductSelect.addEventListener('change', function() {
            handleProductSelection(this.value, 'export');
          });
        }
        
        // Quantity input validation
        const importQuantity = document.getElementById('importQuantity');
        const exportQuantity = document.getElementById('exportQuantity');
        
        if (importQuantity) {
          importQuantity.addEventListener('input', function() {
            handleQuantityInput(this.value, 'import');
          });
        }
        
        if (exportQuantity) {
          exportQuantity.addEventListener('input', function() {
            handleQuantityInput(this.value, 'export');
          });
        }
        
        // Stock view search and sort event listeners
        const viewStockSearch = document.getElementById('viewStockSearch');
        const viewStockSortBy = document.getElementById('viewStockSortBy');
        const viewStockSortOrder = document.getElementById('viewStockSortOrder');
        
        if (viewStockSearch) {
          viewStockSearch.addEventListener('input', function() {
            applyStockFiltersAndSort();
          });
        }
        
        if (viewStockSortBy) {
          viewStockSortBy.addEventListener('change', function() {
            applyStockFiltersAndSort();
          });
        }
        
        if (viewStockSortOrder) {
          viewStockSortOrder.addEventListener('change', function() {
            applyStockFiltersAndSort();
          });
        }
      });

      async function handleProductSelection(productId, type) {
        if (!productId) {
          hideProductInfo(type);
          return;
        }
        
        try {
          const locationId = type === 'import' ? 
            document.getElementById('importLocationId').value : 
            document.getElementById('exportLocationId').value;
          
          if (!locationId) {
            console.error('Location ID not found for', type);
            return;
          }
          
          let response, result;
          
          if (type === 'import') {
            // For import: get all products compatible with location
            response = await fetch(`../api/stock-operations.php?action=get_products&location_id=${locationId}`);
            result = await response.json();
            
            if (result.success) {
              const product = result.data.find(p => p.id == productId);
              if (product) {
                showProductInfo(product, type);
              }
            }
          } else {
            // For export: get products currently in this location
            response = await fetch(`../api/stock-operations.php?action=get_location_products&location_id=${locationId}`);
            result = await response.json();
            
            if (result.success) {
              const allocation = result.data.find(a => a.product_id == productId);
              if (allocation) {
                // Create product object from allocation data
                const product = {
                  id: allocation.product_id,
                  name: allocation.product_name,
                  sku: allocation.sku,
                  price: allocation.price,
                  category_name: allocation.category_name,
                  current_stock: allocation.quantity
                };
                showProductInfo(product, type);
              }
            }
          }
        } catch (error) {
          console.error('Error loading product info:', error);
        }
      }

      async function handleQuantityInput(quantity, type) {
        const locationId = type === 'import' ? document.getElementById('importLocationId').value : document.getElementById('exportLocationId').value;
        
        if (type === 'import' && locationId && quantity > 0) {
          try {
            const response = await fetch(`../api/stock-operations.php?action=check_capacity&location_id=${locationId}&quantity=${quantity}`);
            const result = await response.json();
            
            if (result.success) {
              showCapacityInfo(result.data);
            }
          } catch (error) {
          }
        }
      }

      async function showProductInfo(product, type) {
        const prefix = type === 'import' ? 'import' : 'export';
        
        const productInfoElement = document.getElementById(`${prefix}ProductInfo`);
        if (productInfoElement) {
          productInfoElement.style.display = 'block';
        }
        
        const skuElement = document.getElementById(`${prefix}ProductSku`);
        if (skuElement) skuElement.textContent = product.sku || 'N/A';
        
        const categoryElement = document.getElementById(`${prefix}ProductCategory`);
        if (categoryElement) categoryElement.textContent = product.category_name || 'N/A';
        
        const priceElement = document.getElementById(`${prefix}ProductPrice`);
        if (priceElement) priceElement.textContent = product.price ? new Intl.NumberFormat('vi-VN').format(product.price) + ' ₫' : 'N/A';
        
        const stockElement = document.getElementById(`${prefix}CurrentStock`);
        if (stockElement) {
          if (type === 'export' && product.current_stock !== undefined) {
            // For export: show actual stock in location
            stockElement.textContent = product.current_stock;
          } else if (type === 'import') {
            // For import: get actual stock in this location
            try {
              const locationId = document.getElementById(`${prefix}LocationId`).value;
              const response = await fetch(`../api/stock-operations.php?action=get_location_products&location_id=${locationId}`);
              const result = await response.json();
              
              if (result.success) {
                const allocation = result.data.find(a => a.product_id == product.id);
                if (allocation) {
                  stockElement.textContent = allocation.quantity;
                } else {
                  stockElement.textContent = '0';
                }
              } else {
                stockElement.textContent = '0';
              }
            } catch (error) {
              console.error('Error loading current stock:', error);
              stockElement.textContent = '0';
            }
          } else {
            stockElement.textContent = '0';
          }
        }
      }

      function hideProductInfo(type) {
        const prefix = type === 'import' ? 'import' : 'export';
        const element = document.getElementById(`${prefix}ProductInfo`);
        if (element) {
          element.style.display = 'none';
        }
      }

      function showCapacityInfo(data) {
        
        const capacityInfo = document.getElementById('importCapacityInfo');
        const capacityText = document.getElementById('importCapacityText');
        
        
        if (!capacityInfo) {
          console.error('Capacity info element not found');
          return;
        }
        
        capacityInfo.style.display = 'block';
        
        let alertElement = capacityInfo.querySelector('.alert');
        
        // Tạo alert element nếu không tồn tại
        if (!alertElement) {
          alertElement = document.createElement('div');
          alertElement.className = 'alert alert-info';
          alertElement.innerHTML = '<i class="iconoir-info-circle"></i><span id="importCapacityText">Kiểm tra sức chứa...</span>';
          capacityInfo.appendChild(alertElement);
        }
        
        if (data.can_import) {
          alertElement.className = 'alert alert-success';
          if (capacityText) {
            capacityText.innerHTML = `Có thể nhập ${data.requested_quantity} sản phẩm. Còn trống: ${data.available_capacity}`;
          }
        } else {
          alertElement.className = 'alert alert-danger';
          if (capacityText) {
            capacityText.innerHTML = `Không đủ sức chứa. Còn trống: ${data.available_capacity}`;
          }
        }
      }

      async function submitImportStockForm() {
        const btn = document.getElementById('importStockSubmitBtn');
        const original = btn?.innerHTML || '';
        
        try {
          if (btn) {
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = 'Đang nhập...';
          }
          
          const form = document.getElementById('importStockForm');
          const formData = new FormData(form);
          formData.append('action', 'import');
          
          const response = await fetch('../api/stock-operations.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            const msg = document.getElementById('successMessageImportStock');
            if (msg) {
              msg.classList.add('show', 'slide-in');
              setTimeout(() => {
                msg.classList.remove('show', 'slide-in');
              }, 1500);
            }
            setTimeout(() => {
              location.reload();
            }, 1600);
          } else {
            showInsufficientStockAlert(result.message || 'Không thể nhập hàng');
          }
        } catch (error) {
          console.error('Error importing stock:', error);
          alert('Có lỗi xảy ra khi nhập hàng');
        } finally {
          if (btn) {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = original || 'Nhập hàng';
          }
        }
      }

      async function openExportStockModal(locationId, locationName) {
        // Reset form và ẩn thông tin sản phẩm cũ
        document.getElementById('exportLocationId').value = locationId;
        document.getElementById('exportLocationName').textContent = `Xuất hàng từ: ${locationName}`;
        
        // Reset form fields
        document.getElementById('exportProductSelect').value = '';
        document.getElementById('exportQuantity').value = '';
        
        // Ẩn thông tin sản phẩm
        hideProductInfo('export');
        
        // Reset product info display
        document.getElementById('exportProductSku').textContent = '-';
        document.getElementById('exportProductCategory').textContent = '-';
        document.getElementById('exportProductPrice').textContent = '-';
        document.getElementById('exportCurrentStock').textContent = '-';
        
        // Load products in this location
        try {
          const response = await fetch(`../api/stock-operations.php?action=get_location_products&location_id=${locationId}`);
          const result = await response.json();
          
          if (result.success) {
            const select = document.getElementById('exportProductSelect');
            select.innerHTML = '<option value="">Chọn sản phẩm để xuất</option>';
            
            result.data.forEach(allocation => {
              const option = document.createElement('option');
              option.value = allocation.product_id;
              option.textContent = `${allocation.product_name} (${allocation.sku}) - Còn: ${allocation.quantity}`;
              select.appendChild(option);
            });
          }
        } catch (error) {
          console.error('Error loading location products:', error);
        }
        
        const modal = document.getElementById('exportStockModal');
        if (modal) {
          modal.classList.add('show');
          setTimeout(() => {
            const inner = modal.querySelector('.custom-modal');
            if (inner) inner.classList.add('show');
          }, 10);
          document.body.style.overflow = 'hidden';
        }
      }

      function closeExportStockModal() {
        const modal = document.getElementById('exportStockModal');
        if (modal) {
          modal.querySelector('.custom-modal').classList.remove('show');
          setTimeout(() => {
            modal.classList.remove('show');
          }, 300);
          document.body.style.overflow = '';
        }
      }

      async function submitExportStockForm() {
        const btn = document.getElementById('exportStockSubmitBtn');
        const original = btn?.innerHTML || '';
        
        try {
          if (btn) {
            btn.disabled = true;
            btn.classList.add('loading');
            btn.innerHTML = 'Đang xuất...';
          }
          
          const form = document.getElementById('exportStockForm');
          const formData = new FormData(form);
          formData.append('action', 'export');
          
          const response = await fetch('../api/stock-operations.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            const msg = document.getElementById('successMessageExportStock');
            if (msg) {
              msg.classList.add('show', 'slide-in');
              setTimeout(() => {
                msg.classList.remove('show', 'slide-in');
              }, 1500);
            }
            setTimeout(() => {
              location.reload();
            }, 1600);
          } else {
            showInsufficientStockAlert(result.message || 'Không thể xuất hàng');
          }
        } catch (error) {
          console.error('Error exporting stock:', error);
          alert('Có lỗi xảy ra khi xuất hàng');
        } finally {
          if (btn) {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.innerHTML = original || 'Xuất hàng';
          }
        }
      }

      async function viewLocationStock(locationId, locationName) {
        document.getElementById('viewStockLocationName').textContent = `Hàng trong kho: ${locationName}`;
        
        // Show loading
        document.getElementById('viewStockLoading').style.display = 'block';
        document.getElementById('viewStockContent').style.display = 'none';
        
        const modal = document.getElementById('viewLocationStockModal');
        if (modal) {
          modal.classList.add('show');
          setTimeout(() => {
            const inner = modal.querySelector('.custom-modal');
            if (inner) inner.classList.add('show');
          }, 10);
          document.body.style.overflow = 'hidden';
        }
        
        // Load stock data
        try {
          const response = await fetch(`../api/stock-operations.php?action=get_location_products&location_id=${locationId}`);
          const result = await response.json();
          
          if (result.success) {
            displayLocationStock(result.data);
          } else {
            throw new Error(result.message || 'Không thể tải dữ liệu');
          }
        } catch (error) {
          console.error('Error loading location stock:', error);
          document.getElementById('viewStockLoading').innerHTML = `
            <div class="alert alert-danger">
              <i class="iconoir-warning-triangle"></i>
              Có lỗi xảy ra khi tải dữ liệu: ${error.message}
            </div>
          `;
        }
      }

      function displayLocationStock(stockData) {
        document.getElementById('viewStockLoading').style.display = 'none';
        document.getElementById('viewStockContent').style.display = 'block';
        
        // Store original data for filtering and sorting
        window.currentStockData = stockData;
        
        // Apply current filters and sorting
        applyStockFiltersAndSort();
      }

      function applyStockFiltersAndSort() {
        if (!window.currentStockData) return;
        
        let filteredData = [...window.currentStockData];
        
        // Apply search filter
        const searchTerm = document.getElementById('viewStockSearch').value.toLowerCase();
        if (searchTerm) {
          filteredData = filteredData.filter(item => 
            item.product_name.toLowerCase().includes(searchTerm) ||
            item.sku.toLowerCase().includes(searchTerm) ||
            (item.category_name && item.category_name.toLowerCase().includes(searchTerm))
          );
        }
        
        // Apply sorting
        const sortBy = document.getElementById('viewStockSortBy').value;
        const sortOrder = document.getElementById('viewStockSortOrder').value;
        
        filteredData.sort((a, b) => {
          let aValue, bValue;
          
          switch (sortBy) {
            case 'name':
              aValue = a.product_name.toLowerCase();
              bValue = b.product_name.toLowerCase();
              break;
            case 'quantity':
              aValue = parseInt(a.quantity);
              bValue = parseInt(b.quantity);
              break;
            case 'value':
              aValue = parseFloat(a.price || 0) * parseInt(a.quantity);
              bValue = parseFloat(b.price || 0) * parseInt(b.quantity);
              break;
            default:
              aValue = a.product_name.toLowerCase();
              bValue = b.product_name.toLowerCase();
          }
          
          if (sortOrder === 'desc') {
            return aValue > bValue ? -1 : aValue < bValue ? 1 : 0;
          } else {
            return aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
          }
        });
        
        // Update summary with filtered data
        let totalProducts = filteredData.length;
        let totalQuantity = 0;
        let totalValue = 0;
        
        filteredData.forEach(item => {
          totalQuantity += parseInt(item.quantity);
          totalValue += parseFloat(item.price || 0) * parseInt(item.quantity);
        });
        
        document.getElementById('viewStockTotalProducts').textContent = totalProducts;
        document.getElementById('viewStockTotalQuantity').textContent = totalQuantity;
        document.getElementById('viewStockTotalValue').textContent = new Intl.NumberFormat('vi-VN').format(totalValue) + ' ₫';
        
        // Update table
        const tbody = document.getElementById('viewStockTableBody');
        tbody.innerHTML = '';
        
        if (filteredData.length === 0) {
          document.getElementById('viewStockEmpty').style.display = 'block';
          return;
        }
        
        document.getElementById('viewStockEmpty').style.display = 'none';
        
        filteredData.forEach(item => {
          const row = document.createElement('tr');
          row.className = 'stock-item-row';
          
          const imageHtml = item.images ? 
            `<img src="../../../${item.images}" class="stock-image" alt="${item.product_name}">` :
            `<div class="stock-image bg-light d-flex align-items-center justify-content-center">
               <i class="iconoir-image text-muted"></i>
             </div>`;
          
          const quantityClass = item.quantity > 50 ? 'stock-quantity-high' : 
                               item.quantity > 20 ? 'stock-quantity-medium' : 'stock-quantity-low';
          
          const itemValue = parseFloat(item.price || 0) * parseInt(item.quantity);
          
          row.innerHTML = `
            <td>${imageHtml}</td>
            <td>${item.product_name}</td>
            <td><code>${item.sku}</code></td>
            <td>${item.category_name || 'N/A'}</td>
            <td><span class="badge ${quantityClass}">${item.quantity}</span></td>
            <td>${new Intl.NumberFormat('vi-VN').format(item.price || 0)} ₫</td>
            <td class="stock-value">${new Intl.NumberFormat('vi-VN').format(itemValue)} ₫</td>
          `;
          
          tbody.appendChild(row);
        });
      }

      function closeViewLocationStockModal() {
        const modal = document.getElementById('viewLocationStockModal');
        if (modal) {
          modal.querySelector('.custom-modal').classList.remove('show');
          setTimeout(() => {
            modal.classList.remove('show');
          }, 300);
          document.body.style.overflow = '';
        }
      }

      async function quickExportStock(productId, locationId, productName, maxQuantity) {
        const quantity = prompt(`Nhập số lượng muốn xuất cho sản phẩm "${productName}" (tối đa: ${maxQuantity}):`, '1');
        
        if (!quantity || isNaN(quantity) || quantity <= 0 || quantity > maxQuantity) {
          alert('Số lượng không hợp lệ');
          return;
        }
        
        try {
          const formData = new FormData();
          formData.append('action', 'export');
          formData.append('product_id', productId);
          formData.append('location_id', locationId);
          formData.append('quantity', quantity);
          formData.append('notes', 'Xuất hàng nhanh từ danh sách kho');
          
          const response = await fetch('../api/stock-operations.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            alert('Xuất hàng thành công');
            // Reload stock data
            viewLocationStock(locationId, document.getElementById('viewStockLocationName').textContent.replace('Hàng trong kho: ', ''));
          } else {
            showInsufficientStockAlert(result.message || 'Không thể xuất hàng');
          }
        } catch (error) {
          console.error('Error exporting stock:', error);
          alert('Có lỗi xảy ra khi xuất hàng');
        }
      }

      function exportStockReport() {
        // TODO: Implement stock report export
        alert('Tính năng xuất báo cáo đang được phát triển');
      }

      function hideImportStockSuccessMessage() {
        const msg = document.getElementById('successMessageImportStock');
        if (msg) {
          msg.classList.remove('show', 'slide-in');
        }
      }

      function hideExportStockSuccessMessage() {
        const msg = document.getElementById('successMessageExportStock');
        if (msg) {
          msg.classList.remove('show', 'slide-in');
        }
      }

      // Custom insufficient stock alert
      function showInsufficientStockAlert(message) {
        const panel = document.createElement('div');
        panel.style.cssText = `
          position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.4);
        `;
        const box = document.createElement('div');
        box.style.cssText = `
          width: 480px; max-width: 90vw; background: #fff; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);
          padding: 24px; animation: fadeInQuick 0.3s ease-out; border: 2px solid #dc3545;
        `;
        
        // Header với icon và tiêu đề
        const head = document.createElement('div');
        head.style.cssText = 'display:flex; align-items:center; gap:12px; margin-bottom:16px;';
        head.innerHTML = `
          <div style="width:48px; height:48px; background:linear-gradient(135deg, #dc3545, #c82333); border-radius:12px; display:flex; align-items:center; justify-content:center;">
            <i class="iconoir-warning-triangle" style="font-size:24px; color:#fff;"></i>
          </div>
          <div>
            <div style="font-weight:700; font-size:18px; color:#721c24;">Không đủ hàng để xuất</div>
            <div style="font-size:14px; color:#a71e2c; margin-top:2px;">Số lượng tồn kho không đủ</div>
          </div>
        `;
        
        // Body với thông tin chi tiết
        const body = document.createElement('div');
        body.style.cssText = 'margin-bottom:20px;';
        body.innerHTML = `
          <div style="background:#f8d7da; border:1px solid #f5c2c7; border-radius:8px; padding:16px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <i class="iconoir-package" style="color:#721c24; font-size:16px;"></i>
              <span style="color:#721c24; font-weight:500;">${message}</span>
            </div>
          </div>
          <div style="color:#6b7280; font-size:14px; line-height:1.5;">
            Vui lòng kiểm tra lại số lượng tồn kho hiện tại hoặc giảm số lượng muốn xuất.
          </div>
        `;
        
        // Actions
        const actions = document.createElement('div');
        actions.style.cssText = 'display:flex; gap:12px; justify-content:flex-end;';
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.style.cssText = 'background:linear-gradient(135deg, #dc3545, #c82333); border:none; padding:10px 24px; border-radius:8px; font-weight:600;';
        okBtn.innerHTML = '<i class="iconoir-check" style="margin-right:6px;"></i>Đã hiểu';
        okBtn.onclick = () => document.body.removeChild(panel);
        
        actions.appendChild(okBtn);
        
        box.appendChild(head); 
        box.appendChild(body); 
        box.appendChild(actions);
        panel.appendChild(box);
        
        // Click outside to close
        panel.addEventListener('click', (e) => { 
          if (e.target === panel) document.body.removeChild(panel); 
        });
        
        // ESC key to close
        const handleEsc = (e) => {
          if (e.key === 'Escape') {
            document.body.removeChild(panel);
            document.removeEventListener('keydown', handleEsc);
          }
        };
        document.addEventListener('keydown', handleEsc);
        
        document.body.appendChild(panel);
      }

      // Custom location stock warning alert
      function showLocationStockWarningAlert(locationName) {
        const panel = document.createElement('div');
        panel.style.cssText = `
          position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.4);
        `;
        const box = document.createElement('div');
        box.style.cssText = `
          width: 480px; max-width: 90vw; background: #fff; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);
          padding: 24px; animation: fadeInQuick 0.3s ease-out; border: 2px solid #fbbf24;
        `;
        
        // Header với icon và tiêu đề
        const head = document.createElement('div');
        head.style.cssText = 'display:flex; align-items:center; gap:12px; margin-bottom:16px;';
        head.innerHTML = `
          <div style="width:48px; height:48px; background:linear-gradient(135deg, #fbbf24, #f59e0b); border-radius:12px; display:flex; align-items:center; justify-content:center;">
            <i class="iconoir-warning-triangle" style="font-size:24px; color:#fff;"></i>
          </div>
          <div>
            <div style="font-weight:700; font-size:18px; color:#92400e;">Không thể xóa vị trí</div>
            <div style="font-size:14px; color:#a16207; margin-top:2px;">Vị trí còn chứa sản phẩm</div>
          </div>
        `;
        
        // Body với thông tin chi tiết
        const body = document.createElement('div');
        body.style.cssText = 'margin-bottom:20px;';
        body.innerHTML = `
          <div style="background:#fef3c7; border:1px solid #fbbf24; border-radius:8px; padding:16px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
              <i class="iconoir-map-pin" style="color:#92400e; font-size:16px;"></i>
              <span style="font-weight:600; color:#92400e;">Vị trí: "${locationName}"</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <i class="iconoir-package" style="color:#92400e; font-size:16px;"></i>
              <span style="color:#92400e;">Còn chứa <strong>sản phẩm</strong> bên trong</span>
            </div>
          </div>
          <div style="color:#6b7280; font-size:14px; line-height:1.5;">
            Để xóa vị trí này, bạn cần xuất hết sản phẩm trước. 
            Hãy sử dụng chức năng <strong>Xuất hàng</strong> để làm rỗng vị trí.
          </div>
        `;
        
        // Actions
        const actions = document.createElement('div');
        actions.style.cssText = 'display:flex; gap:12px; justify-content:flex-end;';
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.style.cssText = 'background:linear-gradient(135deg, #3b82f6, #1d4ed8); border:none; padding:10px 24px; border-radius:8px; font-weight:600;';
        okBtn.innerHTML = '<i class="iconoir-check" style="margin-right:6px;"></i>Đã hiểu';
        okBtn.onclick = () => document.body.removeChild(panel);
        
        actions.appendChild(okBtn);
        
        box.appendChild(head); 
        box.appendChild(body); 
        box.appendChild(actions);
        panel.appendChild(box);
        
        // Click outside to close
        panel.addEventListener('click', (e) => { 
          if (e.target === panel) document.body.removeChild(panel); 
        });
        
        // ESC key to close
        const handleEsc = (e) => {
          if (e.key === 'Escape') {
            document.body.removeChild(panel);
            document.removeEventListener('keydown', handleEsc);
          }
        };
        document.addEventListener('keydown', handleEsc);
        
        document.body.appendChild(panel);
      }
    </script>

    <style>
    /* Custom alert animations */
    @keyframes fadeInQuick { 
      from { opacity: 0; transform: scale(0.95); } 
      to { opacity: 1; transform: scale(1); } 
    }
    
    /* Action buttons styling */
    .action-buttons-container {
        display: flex;
        flex-wrap: nowrap;
        gap: 4px;
        justify-content: space-between;
        align-items: center;
    }

    /* Extra small button size */
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.375rem;
        min-width: auto;
        flex: 1;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-xs i {
        font-size: 0.7rem;
        margin-right: 2px;
    }

    .box-center {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .action-buttons-container {
            flex-wrap: wrap;
            gap: 2px;
        }
        
        .btn-xs {
            flex: 1 1 calc(50% - 1px);
            font-size: 0.7rem;
            padding: 0.2rem 0.3rem;
        }
        
        .btn-xs i {
            display: none; /* Hide icons on very small screens */
        }
    }

    @media (min-width: 577px) and (max-width: 768px) {
        .btn-xs {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    }
    </style>
</body>
</html>
