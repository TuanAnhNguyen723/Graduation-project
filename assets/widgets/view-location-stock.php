<!-- Modal for Viewing Location Stock -->
<div id="viewLocationStockModal" class="custom-modal-overlay">
    <div class="custom-modal" style="max-width: 900px;">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-eye"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Hàng trong kho</h4>
                    <p class="modal-subtitle" id="viewStockLocationName">Danh sách sản phẩm tại vị trí này</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewLocationStockModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Loading State -->
            <div id="viewStockLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2">Đang tải danh sách sản phẩm...</p>
            </div>
            
            <!-- Stock List -->
            <div id="viewStockContent" style="display: none;">
                <!-- Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Tổng sản phẩm</h5>
                                <h3 id="viewStockTotalProducts">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Tổng số lượng</h5>
                                <h3 id="viewStockTotalQuantity">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Giá trị tồn kho</h5>
                                <h3 id="viewStockTotalValue">0 ₫</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="position-relative">
                            <input type="text" class="form-control" id="viewStockSearch" placeholder="Tìm kiếm theo tên, SKU hoặc danh mục...">
                            <div class="input-group-append position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                                <span class="text-muted">
                                    <i class="iconoir-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="viewStockSortBy">
                            <option value="name">Sắp xếp theo tên</option>
                            <option value="quantity">Sắp xếp theo số lượng</option>
                            <option value="value">Sắp xếp theo giá trị</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="viewStockSortOrder">
                            <option value="asc">Tăng dần</option>
                            <option value="desc">Giảm dần</option>
                        </select>
                    </div>
                </div>
                
                <!-- Stock Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>SKU</th>
                                <th>Danh mục</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Giá trị</th>
                            </tr>
                        </thead>
                        <tbody id="viewStockTableBody">
                            <!-- Stock items will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div id="viewStockEmpty" class="text-center py-5" style="display: none;">
                    <i class="iconoir-package" style="font-size: 4rem; color: #6c757d;"></i>
                    <h5 class="mt-3">Không có sản phẩm nào</h5>
                    <p class="text-muted">Vị trí này chưa có sản phẩm nào được lưu trữ.</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeViewLocationStockModal()">
                    Đóng
                </button>
                <button type="button" class="btn btn-primary" onclick="exportStockReport()">
                    <i class="iconoir-download"></i> Xuất báo cáo
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* View Location Stock Modal Styling */
#viewLocationStockModal .custom-modal {
    max-width: 1200px;
    width: 95%;
}

#viewLocationStockModal .custom-modal-header {
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
}

#viewLocationStockModal .header-icon {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
}

#viewLocationStockModal .modal-title {
    color: white;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

#viewLocationStockModal .modal-subtitle {
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-size: 0.9rem;
}

.stock-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.stock-image:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stock-quantity-badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}

.stock-quantity-high {
    background-color: #d1ecf1;
    color: #0c5460;
}

.stock-quantity-medium {
    background-color: #fff3cd;
    color: #856404;
}

.stock-quantity-low {
    background-color: #f8d7da;
    color: #721c24;
}

.stock-value {
    font-weight: 600;
    color: #28a745;
}

/* Enhanced search and filter styling */
#viewStockSearch {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

#viewStockSearch:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23,162,184,0.25);
    transform: translateY(-1px);
}

#viewStockSortBy, #viewStockSortOrder {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

#viewStockSortBy:focus, #viewStockSortOrder:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23,162,184,0.25);
}

/* Enhanced table styling */
#viewStockTableBody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

/* Summary cards enhancement */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.table th {
    color: white !important;
}
</style>
