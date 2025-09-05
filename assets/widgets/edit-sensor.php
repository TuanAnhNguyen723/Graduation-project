<!-- Custom Modal for Editing Sensor -->
<div id="editSensorModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-edit"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Chỉnh sửa cảm biến</h4>
                    <p class="modal-subtitle">Cập nhật thông tin cảm biến IoT</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditSensorModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <!-- Main Form -->
            <form id="editSensorForm" class="sensor-form">
                <input type="hidden" id="editSensorId" name="id">
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-cpu" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editSensorName" class="field-label">
                                Tên cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editSensorName" name="sensor_name" placeholder="Nhập tên cảm biến" required>
                            </div>
                            <div class="field-error" id="editSensorNameError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editSensorCode" class="field-label">
                                Mã cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editSensorCode" name="sensor_code" placeholder="Nhập mã cảm biến" required>
                            </div>
                            <div class="field-error" id="editSensorCodeError"></div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editSensorType" class="field-label">
                                Loại cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editSensorType" name="sensor_type" required>
                                    <option value="">Chọn loại cảm biến</option>
                                    <option value="temperature">Nhiệt độ</option>
                                    <option value="humidity">Độ ẩm</option>
                                    <option value="both">Cả hai</option>
                                </select>
                            </div>
                            <div class="field-error" id="editSensorTypeError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editLocationId" class="field-label">Vị trí lắp đặt</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editLocationId" name="location_id">
                                    <option value="">Chọn vị trí</option>
                                    <!-- Options sẽ được populate bằng JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hardware Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-tools" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin phần cứng</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editManufacturer" class="field-label">Nhà sản xuất</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editManufacturer" name="manufacturer" placeholder="Nhập nhà sản xuất">
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="editModel" class="field-label">Model</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editModel" name="model" placeholder="Nhập model">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editSerialNumber" class="field-label">Số serial</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="editSerialNumber" name="serial_number" placeholder="Nhập số serial">
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="editInstallationDate" class="field-label">Ngày lắp đặt</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="editInstallationDate" name="installation_date">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Threshold Settings Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-warning-triangle" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Cài đặt ngưỡng</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editMinThreshold" class="field-label">Ngưỡng tối thiểu (°C)</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="editMinThreshold" name="min_threshold" placeholder="0" step="0.1">
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="editMaxThreshold" class="field-label">Ngưỡng tối đa (°C)</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="editMaxThreshold" name="max_threshold" placeholder="50" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Maintenance Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-settings" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Trạng thái & Bảo trì</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="editSensorStatus" class="field-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="editSensorStatus" name="status" required>
                                    <option value="active">Hoạt động</option>
                                    <option value="maintenance">Bảo trì</option>
                                    <option value="error">Lỗi</option>
                                    <option value="inactive">Không hoạt động</option>
                                </select>
                            </div>
                            <div class="field-error" id="editSensorStatusError"></div>
                        </div>

                        <div class="form-field">
                            <label for="editLastCalibration" class="field-label">Ngày hiệu chuẩn cuối</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="editLastCalibration" name="last_calibration">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-notes" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Mô tả & Ghi chú</h5>
                    </div>
                    
                    <div class="form-field full-width">
                        <label for="editSensorDescription" class="field-label">Mô tả cảm biến</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="editSensorDescription" name="description" placeholder="Mô tả chi tiết về cảm biến, chức năng, đặc điểm..."></textarea>
                        </div>
                    </div>

                    <div class="form-field full-width">
                        <label for="editSensorNotes" class="field-label">Ghi chú bổ sung</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="editSensorNotes" name="notes" placeholder="Ghi chú về bảo trì, sửa chữa, cài đặt đặc biệt..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditSensorModal()">
                    Hủy
                </button>
                <button type="button" id="editSensorSubmitBtn" class="btn btn-primary" onclick="submitEditSensorForm()" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease;">
                    Cập nhật cảm biến
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message - Fixed Position -->
    <div id="successMessageEditSensor" class="success-alert-fixed">
        <div class="alert-icon">
            <i class="iconoir-check-circle"></i>
        </div>
        <div class="alert-content">
            <h5>Thành công!</h5>
            <p>Cảm biến đã được cập nhật thành công</p>
        </div>
        <button type="button" class="alert-close" onclick="hideEditSensorSuccessMessage()">
            <i class="iconoir-xmark"></i>
        </button>
    </div>
</div>
