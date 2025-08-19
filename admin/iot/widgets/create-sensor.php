<!-- Custom Modal for Creating New IoT Sensor -->
<div id="createSensorModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <!-- Modal Header -->
        <div class="custom-modal-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="iconoir-cpu"></i>
                </div>
                <div class="header-text">
                    <h4 class="modal-title">Thêm Cảm Biến IoT Mới</h4>
                    <p class="modal-subtitle">Nhập thông tin chi tiết về cảm biến</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCreateSensorModal()">
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
                    <p>Cảm biến đã được tạo thành công</p>
                </div>
            </div>
            
            <!-- Main Form -->
            <form id="createSensorForm" class="sensor-form">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-info-circle"></i>
                        <h5>Thông tin cơ bản</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="sensorName" class="field-label">
                                Tên cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="sensorName" name="sensor_name" placeholder="Nhập tên cảm biến" required>
                            </div>
                            <div class="field-error" id="sensorNameError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="sensorCode" class="field-label">
                                Mã cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="sensorCode" name="sensor_code" placeholder="VD: SENSOR_001" required>
                            </div>
                            <div class="field-error" id="sensorCodeError"></div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="sensorType" class="field-label">
                                Loại cảm biến <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="sensorType" name="sensor_type" required>
                                    <option value="">Chọn loại cảm biến</option>
                                    <option value="temperature">🌡️ Nhiệt độ</option>
                                    <option value="humidity">💧 Độ ẩm</option>
                                    <option value="pressure">📊 Áp suất</option>
                                    <option value="motion">🏃 Chuyển động</option>
                                    <option value="light">💡 Ánh sáng</option>
                                    <option value="gas">☁️ Khí gas</option>
                                </select>
                            </div>
                            <div class="field-error" id="sensorTypeError"></div>
                        </div>
                        
                        <div class="form-field">
                            <label for="locationId" class="field-label">
                                Vị trí lắp đặt <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="locationId" name="location_id" required>
                                    <option value="">Chọn vị trí</option>
                                    <?php
                                    // Lấy danh sách vị trí từ database
                                    if (isset($locations) && is_array($locations)) {
                                        foreach ($locations as $location) {
                                            echo '<option value="' . $location['id'] . '">' . htmlspecialchars($location['location_name']) . ' - ' . htmlspecialchars($location['area']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="field-error" id="locationIdError"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Technical Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-settings"></i>
                        <h5>Thông tin kỹ thuật</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="manufacturer" class="field-label">Nhà sản xuất</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="manufacturer" name="manufacturer" placeholder="VD: DHT22, BME280">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="model" class="field-label">Model</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="model" name="model" placeholder="VD: DHT22, BME280">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="serialNumber" class="field-label">Số serial</label>
                            <div class="input-wrapper">
                                <input type="text" class="form-input" id="serialNumber" name="serial_number" placeholder="Nhập số serial nếu có">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="status" class="field-label">
                                Trạng thái <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                    <option value="maintenance">Bảo trì</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Installation & Maintenance Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-calendar"></i>
                        <h5>Lắp đặt & Bảo trì</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="installationDate" class="field-label">Ngày lắp đặt</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="installationDate" name="installation_date">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="lastCalibration" class="field-label">Lần hiệu chuẩn cuối</label>
                            <div class="input-wrapper">
                                <input type="date" class="form-input" id="lastCalibration" name="last_calibration">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Thresholds Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-warning-triangle"></i>
                        <h5>Ngưỡng cảnh báo</h5>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="minThreshold" class="field-label">Ngưỡng tối thiểu</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="minThreshold" name="min_threshold" step="0.1" placeholder="VD: 20.5">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="maxThreshold" class="field-label">Ngưỡng tối đa</label>
                            <div class="input-wrapper">
                                <input type="number" class="form-input" id="maxThreshold" name="max_threshold" step="0.1" placeholder="VD: 30.5">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="iconoir-notes"></i>
                        <h5>Mô tả & Ghi chú</h5>
                    </div>
                    
                    <div class="form-field full-width">
                        <label for="description" class="field-label">Mô tả</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="description" name="description" placeholder="Mô tả chi tiết về cảm biến, vị trí lắp đặt, mục đích sử dụng..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-field full-width">
                        <label for="notes" class="field-label">Ghi chú</label>
                        <div class="input-wrapper">
                            <textarea class="form-textarea" id="notes" name="notes" placeholder="Ghi chú bổ sung, hướng dẫn sử dụng..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="custom-modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateSensorModal()">
                    Hủy
                </button>
                <button type="button" id="createSensorSubmitBtn" class="btn btn-primary" onclick="submitCreateSensorForm()">
                    Tạo cảm biến
                </button>
            </div>
        </div>
    </div>
</div>
