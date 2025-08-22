<!-- Custom Modal for Creating New Product -->
<div id="createProductModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-shopping-bag"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Thêm Sản Phẩm Mới</h4>
                    <p class="modal-subtitle">Nhập thông tin chi tiết về sản phẩm</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCreateProductModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Success Message -->
            <div id="successMessage" class="success-alert">
                <div class="alert-icon">
                    <i class="iconoir-check-circle"></i>
                </div>
                <div class="alert-content">
                    <h5>Thành công!</h5>
                    <p>Sản phẩm đã được tạo thành công</p>
                </div>
            </div>
            
            <!-- Main Form -->
            <form id="createProductForm" class="product-form">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="productName" class="field-label">
                                Tên sản phẩm <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="productName" name="name" placeholder="Nhập tên sản phẩm" required>
                            </div>
                            <div class="field-error" id="productNameError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productSku" class="field-label">
                                Mã SKU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="productSku" name="sku" placeholder="VD: PROD_001" required>
                            </div>
                            <div class="field-error" id="productSkuError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productSlug" class="field-label">Slug URL</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="productSlug" name="slug" placeholder="Tự động tạo từ tên sản phẩm">
                                <small class="text-muted">Để trống để tự động tạo</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid three-columns">
                        <div class="form-field">
                            <label for="productCategory" class="field-label">
                                Danh mục <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="productCategory" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php
                                    // Lấy danh sách danh mục từ database
                                    if (isset($categories) && is_array($categories)) {
                                        foreach ($categories as $category) {
                                            echo '<option value="' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="field-error" id="productCategoryError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productPrice" class="field-label">
                                Giá bán <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="productPrice" name="price" placeholder="0" required>
                                <span class="input-suffix">₫</span>
                            </div>
                            <div class="field-error" id="productPriceError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productSalePrice" class="field-label">Giá khuyến mãi</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="productSalePrice" name="sale_price" placeholder="0">
                                <span class="input-suffix">₫</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Inventory Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-package"></i>
                        <h5>Thông tin tồn kho</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="stockQuantity" class="field-label">
                                Số lượng tồn kho <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="stockQuantity" name="stock_quantity" placeholder="0" min="0" required>
                            </div>
                            <div class="field-error" id="stockQuantityError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productBrand" class="field-label">Thương hiệu</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="productBrand" name="brand" placeholder="Nhập thương hiệu">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Image & Status Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-image-plus"></i>
                        <h5>Hình ảnh & Trạng thái</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="productImage" class="field-label">Hình ảnh sản phẩm</label>
                            <div class="input-wrapper">
                                <input type="file" class="form-input" id="productImage" name="image" accept="image/*">
                                <div class="file-upload-info">
                                    <small>Hỗ trợ: JPG, PNG, GIF (Max: 5MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="productStatus" class="field-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="productStatus" name="is_active" required>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-notes"></i>
                        <h5>Mô tả & Thông tin bổ sung</h5>
                    </div>
                    
                    <div class="form-field full-width">
                        <label for="productDescription" class="field-label">Mô tả sản phẩm</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="productDescription" name="description" placeholder="Mô tả chi tiết về sản phẩm, tính năng, lợi ích..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateProductModal()">
                    Hủy
                </button>
                <button type="button" id="createProductSubmitBtn" class="btn btn-primary" onclick="submitCreateProductForm()">
                    Tạo sản phẩm
                </button>
            </div>
        </div>
    </div>
</div>

