<?php /* Create Location Modal (overlay style) */ ?>
<div id="createLocationModal" class="custom-modal-overlay">
    <div class="custom-modal" style="max-width:600px;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon"><i class="iconoir-map-pin"></i></div>
                <div class="header-text">
                    <h4 class="modal-title">Thêm vị trí kho</h4>
                    <p class="modal-subtitle">Cấu hình mã, tên, khu vực, mức môi trường</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCreateLocationModal()"><i class="iconoir-xmark"></i></button>
        </div>
        <div class="custom-modal-body">
            <form id="createLocationForm" class="product-form">
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="locCode">Mã vị trí <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="locCode" name="location_code" class="form-input" type="text" placeholder="VD: A-01-01" required></div>
                    </div>
                    <div class="form-field">
                        <label class="field-label" for="locName">Tên vị trí <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="locName" name="location_name" class="form-input" type="text" placeholder="Kệ A - Hàng 1" required></div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="locArea">Khu vực <span class="required">*</span></label>
                        <div class="input-wrapper"><input id="locArea" name="area" class="form-input" type="text" placeholder="A/B/C..." required></div>
                    </div>
                    <div class="form-field">
                        <label class="field-label" for="tempZone">Mức môi trường <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <select id="tempZone" name="temperature_zone" class="form-select" required>
                                <option value="ambient">Ambient (15-33°C, 50-60%)</option>
                                <option value="chilled">Chilled (0-5°C, 85-90%)</option>
                                <option value="frozen">Frozen (≤-18°C, 85-95%)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label" for="maxCap">Sức chứa tối đa</label>
                        <div class="input-wrapper"><input id="maxCap" name="max_capacity" class="form-input" type="number" min="0" value="0"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateLocationModal()">Hủy</button>
                <button type="button" id="createLocationSubmitBtn" class="btn btn-primary" onclick="submitCreateLocationForm()">Tạo vị trí</button>
            </div>
        </div>
    </div>
    <div id="successMessageCreateLocation" class="success-alert-fixed">
        <div class="alert-icon"><i class="iconoir-check-circle"></i></div>
        <div class="alert-content"><h5>Thành công!</h5><p>Đã tạo vị trí</p></div>
        <button type="button" class="alert-close" onclick="this.parentNode.classList.remove('show')"><i class="iconoir-xmark"></i></button>
    </div>
    <style>
      .custom-modal-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(7,11,25,.45);z-index:2000}
      .custom-modal-overlay.show{display:flex !important}
      .custom-modal{width:100%;max-width:640px}
    </style>
</div>


