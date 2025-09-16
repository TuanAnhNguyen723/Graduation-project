<!-- Custom Modal for Viewing Category -->
<div id="viewCategoryModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-eye"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Xem chi tiết danh mục</h4>
                    <p class="modal-subtitle">Thông tin danh mục đã lưu</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewCategoryModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form (read-only) -->
            <form id="viewCategoryForm" class="product-form">
                <input type="hidden" id="viewCategoryId" name="id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewCategoryName" class="field-label">
                                Tên danh mục
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewCategoryName" name="name" placeholder="Tên danh mục" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewCategorySlug" class="field-label">Slug URL</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewCategorySlug" name="slug" placeholder="Slug" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid three-columns">
                        <div class="form-field">
                            <label for="viewCategoryLocationId" class="field-label">Vị trí kho</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewCategoryLocationId" name="location_id" disabled>
                                    <option value="">Chưa gán vị trí</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewSortOrder" class="field-label">Thứ tự</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="viewSortOrder" name="sort_order" placeholder="0" min="0" value="0" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewCategoryStatus" class="field-label">
                                Trạng thái
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewCategoryStatus" name="is_active" disabled>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image & Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-camera" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Hình ảnh & Mô tả</h5>
                    </div>

                    <!-- Current Image Display -->
                    <div class="form-field" id="currentViewCategoryImageContainer">
                        <label class="field-label">Ảnh hiện tại</label>
                        <div class="image-preview-wrapper">
                            <img id="currentViewCategoryImage" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div class="form-field full-width">
                        <label for="viewCategoryDescription" class="field-label">Mô tả</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="viewCategoryDescription" name="description" placeholder="Mô tả chi tiết về danh mục..." disabled></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeViewCategoryModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

