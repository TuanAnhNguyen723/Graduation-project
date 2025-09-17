<!-- Modal for Export Stock -->
<div id="exportStockModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-minus"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Xuất hàng khỏi kho</h4>
                    <p class="modal-subtitle" id="exportLocationName">Chọn sản phẩm và số lượng để xuất</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeExportStockModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="custom-modal-body">
            <form id="exportStockForm" class="product-form">
                <input type="hidden" id="exportLocationId" name="location_id">
                
                <!-- Product Selection Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-shopping-bag" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Chọn sản phẩm</h5>
                    </div>
                    
                    <div class="form-field">
                        <label for="exportProductSelect" class="field-label">
                            Sản phẩm <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <select class="form-select" id="exportProductSelect" name="product_id" required>
                                <option value="">Chọn sản phẩm để xuất</option>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="field-error" id="exportProductError"></div>
                    </div>
                    
                    <!-- Product Info Display -->
                    <div id="exportProductInfo" class="product-info-display" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>SKU:</label>
                                    <span id="exportProductSku">-</span>
                                </div>
                                <div class="info-item">
                                    <label>Danh mục:</label>
                                    <span id="exportProductCategory">-</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Giá:</label>
                                    <span id="exportProductPrice">-</span>
                                </div>
                                <div class="info-item">
                                    <label>Hiện có:</label>
                                    <span id="exportCurrentStock">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-package" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Số lượng xuất</h5>
                    </div>
                    
                    <div class="form-field">
                        <label for="exportQuantity" class="field-label">
                            Số lượng <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="number" class="form-input" id="exportQuantity" name="quantity" placeholder="0" min="1" required>
                        </div>
                        <div class="field-error" id="exportQuantityError"></div>
                    </div>
                    
                    <!-- Stock Check -->
                    <div id="exportStockInfo" class="stock-info-display" style="display: none;">
                        <div class="alert alert-info">
                            <i class="iconoir-info-circle"></i>
                            <span id="exportStockText">Kiểm tra tồn kho...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Notes Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-notes" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Ghi chú</h5>
                    </div>
                    
                    <div class="form-field">
                        <label for="exportNotes" class="field-label">Ghi chú xuất hàng</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="exportNotes" name="notes" placeholder="Ghi chú về lý do xuất hàng (tùy chọn)"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeExportStockModal()">
                    Hủy
                </button>
                <button type="button" id="exportStockSubmitBtn" class="btn btn-warning" onclick="submitExportStockForm()" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease;">
                    Xuất hàng
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message -->
    <div id="successMessageExportStock" class="success-alert-fixed">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Đã xuất hàng thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideExportStockSuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</div>

<style>
/* Export Stock Modal Styling */
#exportStockModal .custom-modal {
    max-width: 600px;
    width: 90%;
}

#exportStockModal .custom-modal-header {
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
}

#exportStockModal .header-icon {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
}

#exportStockModal .modal-title {
    color: white;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

#exportStockModal .modal-subtitle {
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-size: 0.9rem;
}

.product-info-display {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.product-info-display:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.product-info-display .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.product-info-display .info-item:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

.product-info-display .info-item label {
    font-weight: 600;
    color: #495057;
    margin-right: 15px;
    font-size: 0.9rem;
}

.product-info-display .info-item span {
    color: #6c757d;
    font-weight: 500;
    background: white;
    padding: 4px 12px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    font-size: 0.85rem;
}

.stock-info-display {
    margin-top: 15px;
}

.stock-info-display .alert {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
    padding: 15px;
    border-radius: 10px;
    border: none;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.stock-info-display .alert:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stock-info-display .alert i {
    font-size: 18px;
    flex-shrink: 0;
}

.stock-info-display .alert-success {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.stock-info-display .alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 4px solid #ffc107;
}

.stock-info-display .alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Form enhancements */
#exportStockModal .form-field {
    margin-bottom: 1.5rem;
}

#exportStockModal .field-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

#exportStockModal .form-input,
#exportStockModal .form-select,
#exportStockModal .form-textarea {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

#exportStockModal .form-input:focus,
#exportStockModal .form-select:focus,
#exportStockModal .form-textarea:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
    transform: translateY(-1px);
}

#exportStockModal .required {
    color: #dc3545;
    font-weight: bold;
}

/* Button enhancements */
#exportStockModal .btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
}

#exportStockModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

#exportStockModal .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
    color: white;
}

#exportStockModal .btn-secondary:hover {
    background: linear-gradient(135deg, #545b62 0%, #3d4449 100%);
    color: white;
}

/* Section headers */
#exportStockModal .section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f8f9fa;
}

#exportStockModal .section-header h5 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Loading animation */
#exportStockModal .loading {
    position: relative;
    pointer-events: none;
}

#exportStockModal .loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #ffffff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
