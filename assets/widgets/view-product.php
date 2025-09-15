<!-- Custom Modal for Viewing Product -->
<div id="viewProductModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-eye"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Xem chi tiết sản phẩm</h4>
                    <p class="modal-subtitle">Thông tin sản phẩm đã lưu</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewProductModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form (read-only) -->
            <form id="viewProductForm" class="product-form">
                <input type="hidden" id="viewProductId" name="id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewProductName" class="field-label">
                                Tên sản phẩm
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewProductName" name="name" placeholder="Tên sản phẩm" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewProductSku" class="field-label">
                                SKU
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewProductSku" name="sku" placeholder="Mã SKU" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewProductCategory" class="field-label">
                                Danh mục
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewProductCategory" name="category_id" disabled>
                                    <option value="">Chọn danh mục</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewProductBrand" class="field-label">Thương hiệu</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewProductBrand" name="brand" placeholder="Thương hiệu" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-cash" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin giá</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewProductPrice" class="field-label">
                                Giá gốc
                            </label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="viewProductPrice" name="price" placeholder="0" min="0" step="1000" disabled>
                                <span class="input-suffix">₫</span>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewProductSalePrice" class="field-label">Giá khuyến mãi</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="viewProductSalePrice" name="sale_price" placeholder="0" min="0" step="1000" disabled>
                                <span class="input-suffix">₫</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Temperature & Humidity Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-temperature-high" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin môi trường</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">Nhiệt độ</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="viewTemperatureInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label class="field-label">Độ ẩm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="viewHumidityInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">Nhiệt độ nguy hiểm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="viewTemperatureDangerInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label class="field-label">Độ ẩm nguy hiểm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="viewHumidityDangerInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-package" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin tồn kho</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewStockQuantity" class="field-label">
                                Số lượng tồn kho
                            </label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="viewStockQuantity" name="stock_quantity" placeholder="0" min="0" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewProductStatus" class="field-label">
                                Trạng thái
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewProductStatus" name="is_active" disabled>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-camera" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Hình ảnh sản phẩm</h5>
                    </div>

                    <!-- Current Image Display -->
                    <div class="form-field" id="currentViewProductImageContainer">
                        <label class="field-label">Ảnh hiện tại</label>
                        <div class="image-preview-wrapper">
                            <img id="currentViewProductImage" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-notes" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Mô tả & Thông tin bổ sung</h5>
                    </div>
                    
                    <div class="form-field full-width">
                        <label for="viewProductDescription" class="field-label">Mô tả sản phẩm</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="viewProductDescription" name="description" placeholder="Mô tả" disabled></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeViewProductModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.info-display {
    padding: 12px 16px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    min-height: 40px;
    display: flex;
    align-items: center;
}

.info-display .info-text {
    color: #6c757d;
    font-style: italic;
}

.info-display .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}

.info-display .badge.bg-info-subtle {
    background-color: #d1ecf1 !important;
    color: #0c5460 !important;
}

.info-display .badge.bg-primary-subtle {
    background-color: #cce7ff !important;
    color: #004085 !important;
}

.info-display .badge.bg-warning-subtle {
    background-color: #fff3cd !important;
    color: #856404 !important;
}

.info-display .badge.bg-danger-subtle {
    background-color: #f8d7da !important;
    color: #721c24 !important;
}
</style>

