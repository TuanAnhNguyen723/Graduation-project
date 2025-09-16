<!-- Custom Modal for Viewing Sensor -->
<div id="viewSensorModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-eye"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Xem chi tiết cảm biến</h4>
                    <p class="modal-subtitle">Thông tin cảm biến IoT</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewSensorModal()">
                <i class="iconoir-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="custom-modal-body">
            <form id="viewSensorForm" class="sensor-form">
                <input type="hidden" id="viewSensorId" name="id">

                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-cpu" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewSensorName" class="field-label">Tên cảm biến</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewSensorName" name="sensor_name" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewSensorCode" class="field-label">Mã cảm biến</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewSensorCode" name="sensor_code" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewSensorType" class="field-label">Loại cảm biến</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewSensorType" name="sensor_type" disabled>
                                    <option value="">Chọn loại cảm biến</option>
                                    <option value="temperature">Nhiệt độ</option>
                                    <option value="humidity">Độ ẩm</option>
                                    <option value="both">Cả hai</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewLocationId" class="field-label">Vị trí lắp đặt</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewLocationId" name="location_id" disabled>
                                    <option value="">Chọn vị trí</option>
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
                            <label for="viewManufacturer" class="field-label">Nhà sản xuất</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewManufacturer" name="manufacturer" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewModel" class="field-label">Model</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewModel" name="model" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label for="viewSerialNumber" class="field-label">Số serial</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="viewSerialNumber" name="serial_number" disabled>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewInstallationDate" class="field-label">Ngày lắp đặt</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="viewInstallationDate" name="installation_date" disabled>
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
                            <label for="viewSensorStatus" class="field-label">Trạng thái</label>
                            <div class="input-wrapper">
                                <select class="form-select" id="viewSensorStatus" name="status" disabled>
                                    <option value="active">Hoạt động</option>
                                    <option value="maintenance">Bảo trì</option>
                                    <option value="error">Lỗi</option>
                                    <option value="inactive">Không hoạt động</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="viewLastCalibration" class="field-label">Ngày hiệu chuẩn cuối</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="viewLastCalibration" name="last_calibration" disabled>
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
                        <label for="viewSensorDescription" class="field-label">Mô tả cảm biến</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="viewSensorDescription" name="description" disabled></textarea>
                        </div>
                    </div>

                    <div class="form-field full-width">
                        <label for="viewSensorNotes" class="field-label">Ghi chú bổ sung</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="viewSensorNotes" name="notes" disabled></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeViewSensorModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

