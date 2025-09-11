<?php /* Edit Location Modal (overlay style) */ ?>
<div id="editLocationModalWidget" class="custom-modal-overlay">
    <div class="custom-modal" style="max-width:600px;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #f02d8f 0%, #d70b71 100%);">
            <div class="header-content">
                <div class="header-icon"><i class="iconoir-edit"></i></div>
                <div class="header-text">
                    <h4 class="modal-title">Chỉnh sửa vị trí</h4>
                    <p class="modal-subtitle">Cập nhật thông tin vị trí kho</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditLocationModal()"><i class="iconoir-xmark"></i></button>
        </div>
        <div class="custom-modal-body">
            <form id="editLocationFormWidget" class="product-form">
                <input type="hidden" name="id" />
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="editLocCode">Mã vị trí <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="editLocCode" name="location_code" class="form-input" type="text" required></div>
                    </div>
                    <div class="form-field">
                        <label class="field-label" for="editLocName">Tên vị trí <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="editLocName" name="location_name" class="form-input" type="text" required></div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="editLocArea">Khu vực <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="editLocArea" name="area" class="form-input" type="text" required></div>
                    </div>
                    <div class="form-field">
                        <label class="field-label" for="editTempZone">Mức môi trường <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <select id="editTempZone" name="temperature_zone" class="form-select" required>
                                <option value="ambient">Ambient (15-33°C, 50-60%)</option>
                                <option value="chilled">Chilled (0-5°C, 85-90%)</option>
                                <option value="frozen">Frozen (≤-18°C, 85-95%)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="editMaxCapacity">Sức chứa tối đa</label>
                        <div class="input-wrapper"><input id="editMaxCapacity" name="max_capacity" class="form-input" type="number" min="0"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditLocationModal()">Hủy</button>
                <button type="button" id="editLocationSubmitBtn" class="btn btn-primary" onclick="submitEditLocationForm()">Cập nhật vị trí</button>
            </div>
        </div>
    </div>
    <div id="successMessageEditLocation" class="success-alert-fixed">
        <div class="alert-icon"><i class="iconoir-check-circle"></i></div>
        <div class="alert-content"><h5>Thành công!</h5><p>Đã cập nhật vị trí</p></div>
        <button type="button" class="alert-close" onclick="this.parentNode.classList.remove('show')"><i class="iconoir-xmark"></i></button>
    </div>
    <style>
      .custom-modal-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(7,11,25,.45);z-index:2000}
      .custom-modal-overlay.show{display:flex !important}
      .custom-modal{width:100%;max-width:640px}
    </style>
</div>


