<!-- Custom Modal for Creating New Category -->
<div id="createCategoryModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-folder"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Thêm Danh Mục Mới</h4>
                    <p class="modal-subtitle">Nhập thông tin chi tiết về danh mục</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCreateCategoryModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form -->
            <form id="createCategoryForm" class="product-form">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="categoryName" class="field-label">
                                Tên danh mục <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="categoryName" name="name" placeholder="Nhập tên danh mục" required>
                            </div>
                            <div class="field-error" id="categoryNameError"></div>
                        </div>

                        <div class="form-field">
                            <label for="categorySlug" class="field-label">Slug URL</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="categorySlug" name="slug" placeholder="Tự động tạo từ tên danh mục">
                                <small class="text-muted">Để trống để tự động tạo</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid three-columns">
                        <div class="form-field">
                            <label for="parentId" class="field-label">Danh mục cha</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="parentId" name="parent_id">
                                    <option value="">Không có danh mục cha</option>
                                    <?php
                                    if (isset($parent_categories) && $parent_categories) {
                                        while ($row = $parent_categories->fetch()) {
                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="temperatureType" class="field-label">Loại nhiệt độ <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <select class="form-select" id="temperatureType" name="temperature_type" required>
                                    <option value="ambient">Nhiệt độ phòng (15-33°C)</option>
                                    <option value="chilled">Lạnh mát (0-5°C)</option>
                                    <option value="frozen">Đông lạnh (<-18°C)</option>
                                </select>
                                <small class="text-muted">Chọn loại nhiệt độ phù hợp cho danh mục</small>
                            </div>
                            <div class="field-error" id="temperatureTypeError"></div>
                        </div>

                        <div class="form-field">
                            <label for="sortOrder" class="field-label">Thứ tự</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="sortOrder" name="sort_order" placeholder="0" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="categoryStatus" class="field-label">Trạng thái <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <select class="form-select" id="categoryStatus" name="is_active" required>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                            <div class="field-error" id="categoryStatusError"></div>
                        </div>
                    </div>
                </div>

                <!-- Image & Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-camera" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Hình ảnh & Mô tả</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="categoryImage" class="field-label">Hình ảnh danh mục</label>
                            <div class="input-wrapper">
                                <input type="file" class="form-input" id="categoryImage" name="image" accept="image/*" onchange="previewCategoryImage(this)">
                                <div class="file-upload-info">
                                    <small>Hỗ trợ: JPG, PNG, GIF (Max: 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div class="form-field" id="imagePreviewContainer" style="display: none;">
                        <label class="field-label">Xem trước ảnh</label>
                        <div class="image-preview-wrapper">
                            <img id="categoryImagePreview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div class="form-field full-width">
                        <label for="categoryDescription" class="field-label">Mô tả</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="categoryDescription" name="description" placeholder="Mô tả chi tiết về danh mục..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateCategoryModal()">
                    Hủy
                </button>
                <button type="button" id="createCategorySubmitBtn" class="btn btn-primary" onclick="submitCreateCategoryForm()" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease;">
                    Tạo danh mục
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message - Fixed Position -->
    <div id="successMessageCreateCategory" class="success-alert-fixed">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Danh mục đã được tạo thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideCreateCategorySuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</div>


