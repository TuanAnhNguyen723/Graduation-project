<!-- Custom Modal for Editing Category -->
<div id="editCategoryModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-edit"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Chỉnh sửa danh mục</h4>
                    <p class="modal-subtitle">Cập nhật thông tin danh mục</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditCategoryModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form -->
            <form id="editCategoryForm" class="product-form">
                <input type="hidden" id="editCategoryId" name="id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editCategoryName" class="field-label">
                                Tên danh mục <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editCategoryName" name="name" placeholder="Nhập tên danh mục" required>
                            </div>
                            <div class="field-error" id="editCategoryNameError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editCategorySlug" class="field-label">Slug URL</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editCategorySlug" name="slug" placeholder="Tự động tạo từ tên danh mục">
                                <small class="text-muted">Để trống để tự động tạo</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid three-columns">
                        <div class="form-field">
                            <label for="editParentId" class="field-label">Danh mục cha</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editParentId" name="parent_id">
                                    <option value="">Không có danh mục cha</option>
                                    <!-- Options sẽ được populate bằng JavaScript -->
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="editCategoryLocationId" class="field-label">Vị trí kho</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editCategoryLocationId" name="location_id">
                                    <option value="">Chưa gán vị trí</option>
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-field">
                            <label for="editSortOrder" class="field-label">Thứ tự</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="editSortOrder" name="sort_order" placeholder="0" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editCategoryStatus" class="field-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editCategoryStatus" name="is_active" required>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                            <div class="field-error" id="editCategoryStatusError"></div>
                        </div>
                    </div>
                </div>

                <!-- Image & Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-camera" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Hình ảnh & Mô tả</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editCategoryImage" class="field-label">Hình ảnh danh mục</label>
                            <div class="input-wrapper">
                                <input type="file" class="form-input" id="editCategoryImage" name="image" accept="image/*" onchange="previewEditCategoryImage(this)">
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
                            <img id="editCategoryImagePreview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <!-- Current Image Display -->
                    <div class="form-field" id="currentImageContainer">
                        <label class="field-label">Ảnh hiện tại</label>
                        <div class="image-preview-wrapper">
                            <img id="currentCategoryImage" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div class="form-field full-width">
                        <label for="editCategoryDescription" class="field-label">Mô tả</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="editCategoryDescription" name="description" placeholder="Mô tả chi tiết về danh mục..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditCategoryModal()">
                    Hủy
                </button>
                <button type="button" id="editCategorySubmitBtn" class="btn btn-primary" onclick="submitEditCategoryForm()" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease;">
                    Cập nhật danh mục
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message - Fixed Position -->
    <div id="successMessageEditCategory" class="success-alert-fixed">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Danh mục đã được cập nhật thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideEditCategorySuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</div>
