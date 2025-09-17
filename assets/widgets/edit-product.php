<!-- Custom Modal for Editing Product -->
<div id="editProductModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-edit"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Chỉnh sửa sản phẩm</h4>
                    <p class="modal-subtitle">Cập nhật thông tin sản phẩm</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditProductModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form -->
            <form id="editProductForm" class="product-form">
                <input type="hidden" id="editProductId" name="id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editProductName" class="field-label">
                                Tên sản phẩm <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editProductName" name="name" placeholder="Nhập tên sản phẩm" required>
                            </div>
                            <div class="field-error" id="editProductNameError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editProductSku" class="field-label">
                                SKU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editProductSku" name="sku" placeholder="Nhập mã SKU" required>
                            </div>
                            <div class="field-error" id="editProductSkuError"></div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editProductCategory" class="field-label">
                                Danh mục <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editProductCategory" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <!-- Options sẽ được populate bằng JavaScript -->
                                </select>
                            </div>
                            <div class="field-error" id="editProductCategoryError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editProductBrand" class="field-label">Thương hiệu</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editProductBrand" name="brand" placeholder="Nhập thương hiệu">
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
                            <label for="editProductPrice" class="field-label">
                                Giá gốc <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="editProductPrice" name="price" placeholder="0" min="0" step="1000" required>
                                <span class="input-suffix">₫</span>
                            </div>
                            <div class="field-error" id="editProductPriceError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editProductSalePrice" class="field-label">Giá khuyến mãi</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="editProductSalePrice" name="sale_price" placeholder="0" min="0" step="1000">
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
                                <div class="info-display" id="editTemperatureInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label class="field-label">Độ ẩm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="editHumidityInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">Nhiệt độ nguy hiểm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="editTemperatureDangerInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label class="field-label">Độ ẩm nguy hiểm</label>
                            <div class="input-wrapper">
                                <div class="info-display" id="editHumidityDangerInfo">
                                    <span class="info-text">Chọn danh mục để xem thông tin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-package" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Trạng thái sản phẩm</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editProductStatus" class="field-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editProductStatus" name="is_active" required>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                            <div class="field-error" id="editProductStatusError"></div>
                        </div>
                    </div>
                </div>

                <!-- Image Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-camera" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Hình ảnh sản phẩm</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editProductImage" class="field-label">Hình ảnh sản phẩm</label>
                            <div class="input-wrapper">
                                <input type="file" class="form-input" id="editProductImage" name="image" accept="image/*" onchange="previewEditProductImage(this)">
                                <div class="file-upload-info">
                                    <small>Hỗ trợ: JPG, PNG, GIF (Max: 5MB). Để trống nếu không muốn thay đổi.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div class="form-field" id="editImagePreviewContainer" style="display: none;">
                        <label class="field-label">Xem trước ảnh mới</label>
                        <div class="image-preview-wrapper">
                            <img id="editProductImagePreview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <!-- Current Image Display -->
                    <div class="form-field" id="currentProductImageContainer">
                        <label class="field-label">Ảnh hiện tại</label>
                        <div class="image-preview-wrapper">
                            <img id="currentProductImage" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
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
                        <label for="editProductDescription" class="field-label">Mô tả sản phẩm</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="editProductDescription" name="description" placeholder="Mô tả chi tiết về sản phẩm, tính năng, lợi ích..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditProductModal()">
                    Hủy
                </button>
                <button type="button" id="editProductSubmitBtn" class="btn btn-primary" onclick="submitEditProductForm()" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease;">
                    Cập nhật sản phẩm
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message - Fixed Position -->
    <div id="successMessageEditProduct" class="success-alert-fixed">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Sản phẩm đã được cập nhật thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideEditProductSuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
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
