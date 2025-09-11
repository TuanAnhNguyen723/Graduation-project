// Unified widget scripts (IoT + Products)
// Wrapped in IIFEs to avoid global collisions; only expose required functions

// Products widget
(function() {
  let isProductModalOpen = false;

  function openCreateProductModal() {
    const modal = document.getElementById('createProductModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
      isProductModalOpen = true;
      resetCreateProductForm();
      const first = document.getElementById('productName'); if (first) first.focus();
      document.body.style.overflow = 'hidden';
      
      // Load categories when modal opens
      loadCategoriesForCreate();
    }
  }

  function closeCreateProductModal() {
    const modal = document.getElementById('createProductModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isProductModalOpen = false;
      document.body.style.overflow = '';
      resetCreateProductForm();
    }
  }

  function resetCreateProductForm() {
    const form = document.getElementById('createProductForm');
    if (!form) return;
    form.reset();
    clearAllErrors();
    hideSuccessMessage();
    form.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(el => el.classList.remove('error','success'));
  }

  function clearAllErrors() {
    document.querySelectorAll('#createProductModal .field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
  }

  function showFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; errorEl.classList.add('show');
      const input = document.getElementById(fieldId); if (input) { input.classList.add('error'); input.classList.remove('success'); }
    }
  }

  function clearFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { errorEl.textContent=''; errorEl.classList.remove('show'); }
    const input = document.getElementById(fieldId); if (input) input.classList.remove('error');
  }

  function showSuccessMessage() { showCreateProductSuccessMessage(); }
  function hideSuccessMessage() { hideCreateProductSuccessMessage(); }

  function validateCreateProductForm() {
    let isValid = true; clearAllErrors();
    const name = document.getElementById('productName')?.value.trim() || '';
    if (!name) { showFieldError('productName','Tên sản phẩm là bắt buộc'); isValid = false; } else if (name.length < 3) { showFieldError('productName','Tên sản phẩm phải có ít nhất 3 ký tự'); isValid = false; }
    const sku = document.getElementById('productSku')?.value.trim() || '';
    if (!sku) { showFieldError('productSku','Mã SKU là bắt buộc'); isValid = false; } else if (sku.length < 3) { showFieldError('productSku','Mã SKU phải có ít nhất 3 ký tự'); isValid = false; }
    const cat = document.getElementById('productCategory')?.value || '';
    if (!cat) { showFieldError('productCategory','Vui lòng chọn danh mục'); isValid = false; }
    const priceStr = document.getElementById('productPrice')?.value || '';
    if (!priceStr) { showFieldError('productPrice','Giá bán là bắt buộc'); isValid = false; } else if (parseFloat(priceStr) < 0) { showFieldError('productPrice','Giá bán không được âm'); isValid = false; }
    const stockStr = document.getElementById('stockQuantity')?.value || '';
    if (!stockStr) { showFieldError('stockQuantity','Số lượng tồn kho là bắt buộc'); isValid = false; } else if (parseInt(stockStr) < 0) { showFieldError('stockQuantity','Số lượng tồn kho không được âm'); isValid = false; }
    const status = document.getElementById('productStatus')?.value || '';
    if (!status) { showFieldError('productStatus','Vui lòng chọn trạng thái'); isValid = false; }
    return isValid;
  }

  async function submitCreateProductForm() {
    if (!validateCreateProductForm()) return;
    const btn = document.getElementById('createProductSubmitBtn');
    const original = btn?.innerHTML || '';
    try {
      if (btn) { btn.disabled = true; btn.classList.add('loading'); btn.innerHTML = 'Đang tạo...'; }
      const form = document.getElementById('createProductForm');
      const formData = new FormData();
      if (form) {
        Array.from(form.elements).forEach(el => {
          if (!el.name) return;
          if (el.type === 'file' && el.files && el.files.length > 0) formData.append(el.name, el.files[0]);
          else if (el.type !== 'file' && el.value) formData.append(el.name, el.value);
        });
      }
      
      // Lấy thông tin nhiệt độ từ category và thêm vào formData
      const categoryId = document.getElementById('productCategory')?.value;
      if (categoryId) {
        try {
          const tempResponse = await fetch(`../../api/products.php?temperature_info=1&category_id=${categoryId}`);
          if (tempResponse.ok) {
            const tempResult = await tempResponse.json();
            if (tempResult.success && tempResult.data) {
              const tempData = tempResult.data;
              formData.append('ideal_temperature_min', tempData.ideal_min);
              formData.append('ideal_temperature_max', tempData.ideal_max);
              formData.append('dangerous_temperature_min', tempData.dangerous_min);
              formData.append('dangerous_temperature_max', tempData.dangerous_max);
            }
          }
        } catch (tempError) {
          console.error('Error loading temperature info:', tempError);
          // Không dừng quá trình tạo sản phẩm nếu có lỗi lấy nhiệt độ
        }
      }
      
      formData.append('created_at', new Date().toISOString());
      const response = await fetch('../../api/products.php', { method: 'POST', body: formData });
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      if (result.success) {
        showSuccessMessage();
        resetCreateProductForm();
        setTimeout(() => {
          closeCreateProductModal();
          if (typeof window.refreshProductList === 'function') window.refreshProductList(); else window.location.reload();
        }, 2000);
      } else {
        alert('Có lỗi xảy ra: ' + (result.message || 'Không thể tạo sản phẩm'));
      }
    } catch (err) {
      console.error('Error creating product:', err);
      alert('Có lỗi xảy ra khi tạo sản phẩm: ' + err.message);
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove('loading'); btn.innerHTML = original || 'Tạo sản phẩm'; }
    }
  }

  function generateSKU() {
    const name = document.getElementById('productName')?.value.trim() || '';
    if (name.length > 0) {
      const ts = Date.now().toString().slice(-6);
      const prefix = name.substring(0,3).toUpperCase();
      const sku = `${prefix}_${ts}`;
      const skuEl = document.getElementById('productSku'); if (skuEl) skuEl.value = sku;
    }
  }

  function setupRealTimeValidation() {
    const form = document.getElementById('createProductForm'); if (!form) return;
    const productNameInput = document.getElementById('productName');
    if (productNameInput) productNameInput.addEventListener('input', function(){ const v=this.value.trim(); if (v.length>=3){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productName'); } else if (v.length>0){ this.classList.remove('success'); this.classList.add('error'); showFieldError('productName','Tên sản phẩm phải có ít nhất 3 ký tự'); } else { this.classList.remove('success','error'); clearFieldError('productName'); }});
    const productSkuInput = document.getElementById('productSku');
    if (productSkuInput) productSkuInput.addEventListener('input', function(){ const v=this.value.trim(); if (v.length>=3){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productSku'); } else if (v.length>0){ this.classList.remove('success'); this.classList.add('error'); showFieldError('productSku','Mã SKU phải có ít nhất 3 ký tự'); } else { this.classList.remove('success','error'); clearFieldError('productSku'); }});
    const productPriceInput = document.getElementById('productPrice');
    if (productPriceInput) productPriceInput.addEventListener('input', function(){ if (this.value.length>0){ const val=parseFloat(this.value); if (val>=0){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productPrice'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('productPrice','Giá bán không được âm'); } } else { this.classList.remove('success','error'); clearFieldError('productPrice'); }});
    const stockQuantityInput = document.getElementById('stockQuantity');
    if (stockQuantityInput) stockQuantityInput.addEventListener('input', function(){ if (this.value.length>0){ const val=parseInt(this.value); if (val>=0){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('stockQuantity'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('stockQuantity','Số lượng tồn kho không được âm'); } } else { this.classList.remove('success','error'); clearFieldError('stockQuantity'); }});
    const productCategorySelect = document.getElementById('productCategory');
    if (productCategorySelect) productCategorySelect.addEventListener('change', function(){ 
      if (this.value){ 
        this.classList.add('success'); 
        this.classList.remove('error'); 
        clearFieldError('productCategory'); 
        // Cập nhật thông tin nhiệt độ và độ ẩm
        updateTemperatureHumidityInfo(this.value);
      } else { 
        this.classList.remove('success'); 
        this.classList.add('error'); 
        showFieldError('productCategory','Vui lòng chọn danh mục'); 
        // Reset thông tin nhiệt độ và độ ẩm
        resetTemperatureHumidityInfo();
      } 
    });
    const productStatusSelect = document.getElementById('productStatus');
    if (productStatusSelect) productStatusSelect.addEventListener('change', function(){ if (this.value){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productStatus'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('productStatus','Vui lòng chọn trạng thái'); }});
  }

  function setupSKUGeneration() {
    const nameInput = document.getElementById('productName'); if (nameInput) nameInput.addEventListener('blur', generateSKU);
  }

  // Cập nhật thông tin nhiệt độ và độ ẩm khi category thay đổi
  async function updateTemperatureHumidityInfo(categoryId) {
    try {
      const response = await fetch(`../../api/products.php?temperature_info=1&category_id=${categoryId}`);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      
      if (result.success && result.data) {
        const tempData = result.data.temperature;
        const humidityData = result.data.humidity;
        
        // Cập nhật hiển thị nhiệt độ
        updateTemperatureDisplay(tempData);
        
        // Cập nhật hiển thị độ ẩm
        updateHumidityDisplay(humidityData);
        
        // Cập nhật hiển thị nhiệt độ nguy hiểm
        updateTemperatureDangerDisplay(tempData);
        
        // Cập nhật hiển thị độ ẩm nguy hiểm
        updateHumidityDangerDisplay(humidityData);
      }
    } catch (error) {
      console.error('Error loading temperature/humidity info:', error);
      resetTemperatureHumidityInfo();
    }
  }

  // Reset thông tin nhiệt độ và độ ẩm
  function resetTemperatureHumidityInfo() {
    const elements = [
      'temperatureInfo', 'humidityInfo', 
      'temperatureDangerInfo', 'humidityDangerInfo'
    ];
    
    elements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.innerHTML = '<span class="info-text">Chọn danh mục để xem thông tin</span>';
      }
    });
  }

  // Cập nhật hiển thị nhiệt độ
  function updateTemperatureDisplay(tempData) {
    const element = document.getElementById('temperatureInfo');
    if (!element || !tempData) return;
    
    const zone = getZoneFromTemperatureData(tempData);
    const labels = {
      'frozen': ['Đông lạnh', 'bg-info-subtle text-info'],
      'chilled': ['Lạnh mát', 'bg-primary-subtle text-primary'],
      'ambient': ['Nhiệt độ phòng', 'bg-warning-subtle text-warning']
    };
    
    const info = labels[zone] || labels['ambient'];
    element.innerHTML = `<span class="badge ${info[1]}">${info[0]}</span>`;
  }

  // Cập nhật hiển thị độ ẩm
  function updateHumidityDisplay(humidityData) {
    const element = document.getElementById('humidityInfo');
    if (!element || !humidityData) return;
    
    const zone = getZoneFromHumidityData(humidityData);
    const labels = {
      'frozen': ['Đông lạnh (85-95%)', 'bg-info-subtle text-info'],
      'chilled': ['Lạnh mát (85-90%)', 'bg-primary-subtle text-primary'],
      'ambient': ['Phòng (50-60%)', 'bg-warning-subtle text-warning']
    };
    
    const info = labels[zone] || labels['ambient'];
    element.innerHTML = `<span class="badge ${info[1]}">${info[0]}</span>`;
  }

  // Cập nhật hiển thị nhiệt độ nguy hiểm
  function updateTemperatureDangerDisplay(tempData) {
    const element = document.getElementById('temperatureDangerInfo');
    if (!element || !tempData) return;
    
    const dangerMin = tempData.dangerous_min;
    const dangerMax = tempData.dangerous_max;
    
    let text = '';
    if (dangerMin !== null && dangerMin !== undefined) {
      text = `< ${dangerMin}°C và > ${dangerMax}°C`;
    } else {
      text = `> ${dangerMax}°C`;
    }
    
    element.innerHTML = `<span class="badge bg-danger-subtle text-danger">${text}</span>`;
  }

  // Cập nhật hiển thị độ ẩm nguy hiểm
  function updateHumidityDangerDisplay(humidityData) {
    const element = document.getElementById('humidityDangerInfo');
    if (!element || !humidityData) return;
    
    const dangerMin = humidityData.dangerous_min;
    const dangerMax = humidityData.dangerous_max;
    
    let text = '';
    if (dangerMin !== null && dangerMin !== undefined && dangerMax !== null && dangerMax !== undefined) {
      text = `< ${dangerMin}% và > ${dangerMax}%`;
    } else if (dangerMin !== null && dangerMin !== undefined) {
      text = `< ${dangerMin}%`;
    } else {
      text = 'N/A';
    }
    
    element.innerHTML = `<span class="badge bg-danger-subtle text-danger">${text}</span>`;
  }

  // Xác định zone từ dữ liệu nhiệt độ
  function getZoneFromTemperatureData(tempData) {
    if (!tempData) return 'ambient';
    
    const idealMin = tempData.ideal_min;
    if (idealMin <= -18) return 'frozen';
    if (idealMin <= 5) return 'chilled';
    return 'ambient';
  }

  // Xác định zone từ dữ liệu độ ẩm
  function getZoneFromHumidityData(humidityData) {
    if (!humidityData) return 'ambient';
    
    const idealMin = humidityData.ideal_min;
    const idealMax = humidityData.ideal_max;
    
    if (idealMin >= 85) {
      if (idealMax >= 95) return 'frozen';
      return 'chilled';
    }
    return 'ambient';
  }

  // Cập nhật thông tin nhiệt độ và độ ẩm cho edit product
  async function updateEditTemperatureHumidityInfo(categoryId) {
    try {
      const response = await fetch(`../../api/products.php?temperature_info=1&category_id=${categoryId}`);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      
      if (result.success && result.data) {
        const tempData = result.data.temperature;
        const humidityData = result.data.humidity;
        
        // Cập nhật hiển thị nhiệt độ
        updateEditTemperatureDisplay(tempData);
        
        // Cập nhật hiển thị độ ẩm
        updateEditHumidityDisplay(humidityData);
        
        // Cập nhật hiển thị nhiệt độ nguy hiểm
        updateEditTemperatureDangerDisplay(tempData);
        
        // Cập nhật hiển thị độ ẩm nguy hiểm
        updateEditHumidityDangerDisplay(humidityData);
      }
    } catch (error) {
      console.error('Error loading temperature/humidity info for edit:', error);
      resetEditTemperatureHumidityInfo();
    }
  }

  // Reset thông tin nhiệt độ và độ ẩm cho edit product
  function resetEditTemperatureHumidityInfo() {
    const elements = [
      'editTemperatureInfo', 'editHumidityInfo', 
      'editTemperatureDangerInfo', 'editHumidityDangerInfo'
    ];
    
    elements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        element.innerHTML = '<span class="info-text">Chọn danh mục để xem thông tin</span>';
      }
    });
  }

  // Cập nhật hiển thị nhiệt độ cho edit
  function updateEditTemperatureDisplay(tempData) {
    const element = document.getElementById('editTemperatureInfo');
    if (!element || !tempData) return;
    
    const zone = getZoneFromTemperatureData(tempData);
    const labels = {
      'frozen': ['Đông lạnh', 'bg-info-subtle text-info'],
      'chilled': ['Lạnh mát', 'bg-primary-subtle text-primary'],
      'ambient': ['Nhiệt độ phòng', 'bg-warning-subtle text-warning']
    };
    
    const info = labels[zone] || labels['ambient'];
    element.innerHTML = `<span class="badge ${info[1]}">${info[0]}</span>`;
  }

  // Cập nhật hiển thị độ ẩm cho edit
  function updateEditHumidityDisplay(humidityData) {
    const element = document.getElementById('editHumidityInfo');
    if (!element || !humidityData) return;
    
    const zone = getZoneFromHumidityData(humidityData);
    const labels = {
      'frozen': ['Đông lạnh (85-95%)', 'bg-info-subtle text-info'],
      'chilled': ['Lạnh mát (85-90%)', 'bg-primary-subtle text-primary'],
      'ambient': ['Phòng (50-60%)', 'bg-warning-subtle text-warning']
    };
    
    const info = labels[zone] || labels['ambient'];
    element.innerHTML = `<span class="badge ${info[1]}">${info[0]}</span>`;
  }

  // Cập nhật hiển thị nhiệt độ nguy hiểm cho edit
  function updateEditTemperatureDangerDisplay(tempData) {
    const element = document.getElementById('editTemperatureDangerInfo');
    if (!element || !tempData) return;
    
    const dangerMin = tempData.dangerous_min;
    const dangerMax = tempData.dangerous_max;
    
    let text = '';
    if (dangerMin !== null && dangerMin !== undefined) {
      text = `< ${dangerMin}°C và > ${dangerMax}°C`;
    } else {
      text = `> ${dangerMax}°C`;
    }
    
    element.innerHTML = `<span class="badge bg-danger-subtle text-danger">${text}</span>`;
  }

  // Cập nhật hiển thị độ ẩm nguy hiểm cho edit
  function updateEditHumidityDangerDisplay(humidityData) {
    const element = document.getElementById('editHumidityDangerInfo');
    if (!element || !humidityData) return;
    
    const dangerMin = humidityData.dangerous_min;
    const dangerMax = humidityData.dangerous_max;
    
    let text = '';
    if (dangerMin !== null && dangerMin !== undefined && dangerMax !== null && dangerMax !== undefined) {
      text = `< ${dangerMin}% và > ${dangerMax}%`;
    } else if (dangerMin !== null && dangerMin !== undefined) {
      text = `< ${dangerMin}%`;
    } else {
      text = 'N/A';
    }
    
    element.innerHTML = `<span class="badge bg-danger-subtle text-danger">${text}</span>`;
  }

  document.addEventListener('DOMContentLoaded', function(){
    setupRealTimeValidation(); setupSKUGeneration();
    document.addEventListener('click', function(e){ const modal = document.getElementById('createProductModal'); if (modal && e.target === modal) closeCreateProductModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && isProductModalOpen) closeCreateProductModal(); });
  });

  // Load categories for product creation
  async function loadCategoriesForCreate() {
    try {
      const response = await fetch('../../api/categories.php');
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      
      if (result.success && result.data) {
        const categorySelect = document.getElementById('productCategory');
        if (categorySelect) {
          // Clear existing options except the first one
          categorySelect.innerHTML = '<option value="">Chọn danh mục</option>';
          
          // Add new options
          result.data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
          });
          

        }
      }
    } catch (error) {
      console.error('Error loading categories:', error);
    }
  }



  window.openCreateProductModal = openCreateProductModal;
  window.closeCreateProductModal = closeCreateProductModal;
  window.submitCreateProductForm = submitCreateProductForm;
  window.loadCategoriesForCreate = loadCategoriesForCreate;
  window.updateTemperatureHumidityInfo = updateTemperatureHumidityInfo;
  window.resetTemperatureHumidityInfo = resetTemperatureHumidityInfo;
  window.updateTemperatureDisplay = updateTemperatureDisplay;
  window.updateHumidityDisplay = updateHumidityDisplay;
  window.updateTemperatureDangerDisplay = updateTemperatureDangerDisplay;
  window.updateHumidityDangerDisplay = updateHumidityDangerDisplay;
  window.getZoneFromTemperatureData = getZoneFromTemperatureData;
  window.getZoneFromHumidityData = getZoneFromHumidityData;
  // Also expose edit-product helpers from this IIFE where they are defined
  window.updateEditTemperatureHumidityInfo = updateEditTemperatureHumidityInfo;
  window.resetEditTemperatureHumidityInfo = resetEditTemperatureHumidityInfo;
  window.updateEditTemperatureDisplay = updateEditTemperatureDisplay;
  window.updateEditHumidityDisplay = updateEditHumidityDisplay;
  window.updateEditTemperatureDangerDisplay = updateEditTemperatureDangerDisplay;
  window.updateEditHumidityDangerDisplay = updateEditHumidityDangerDisplay;
})();

// Categories widget
(function() {
  let isCategoryModalOpen = false;

  function openCreateCategoryModal() {
    const modal = document.getElementById('createCategoryModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
      isCategoryModalOpen = true;
      resetCreateCategoryForm();
      const first = document.getElementById('categoryName'); if (first) first.focus();
      document.body.style.overflow = 'hidden';
        // Load locations for category select
      loadLocationsForCategoryCreate();
    }
  }

  function closeCreateCategoryModal() {
    const modal = document.getElementById('createCategoryModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isCategoryModalOpen = false;
      document.body.style.overflow = '';
      resetCreateCategoryForm();
    }
  }

  function resetCreateCategoryForm() {
    const form = document.getElementById('createCategoryForm');
    if (!form) return;
    form.reset();
    clearAllErrors();
    hideSuccessMessage();
    form.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(el => el.classList.remove('error','success'));
    
    // Reset image preview
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('categoryImagePreview');
    if (previewContainer) previewContainer.style.display = 'none';
    if (preview) preview.src = '';
  }
  
  // Preview image
  function previewCategoryImage(input) {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('categoryImagePreview');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        previewContainer.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      previewContainer.style.display = 'none';
      preview.src = '';
    }
  }

  function clearAllErrors() {
    document.querySelectorAll('#createCategoryModal .field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
  }

  function showFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; errorEl.classList.add('show');
      const input = document.getElementById(fieldId); if (input) { input.classList.add('error'); input.classList.remove('success'); }
    }
  }

  function clearFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { errorEl.textContent=''; errorEl.classList.remove('show'); }
    const input = document.getElementById(fieldId); if (input) input.classList.remove('error');
  }

  function showSuccessMessage() { showCreateCategorySuccessMessage(); }
  function hideSuccessMessage() { hideCreateCategorySuccessMessage(); }

  function validateCreateCategoryForm() {
    let isValid = true; clearAllErrors();
    const name = document.getElementById('categoryName')?.value.trim() || '';
    if (!name) { showFieldError('categoryName','Tên danh mục là bắt buộc'); isValid = false; } else if (name.length < 3) { showFieldError('categoryName','Tên danh mục phải có ít nhất 3 ký tự'); isValid = false; }
    const status = document.getElementById('categoryStatus')?.value || '';
    if (status === '') { showFieldError('categoryStatus','Vui lòng chọn trạng thái'); isValid = false; }
    return isValid;
  }

  async function submitCreateCategoryForm() {
    if (!validateCreateCategoryForm()) return;
    const btn = document.getElementById('createCategorySubmitBtn');
    const original = btn?.innerHTML || '';
    try {
      if (btn) { btn.disabled = true; btn.classList.add('loading'); btn.innerHTML = 'Đang tạo...'; }
      const form = document.getElementById('createCategoryForm');
      const formData = new FormData();
      if (form) {
        Array.from(form.elements).forEach(el => {
          if (!el.name) return;
          if (el.type === 'file' && el.files && el.files.length > 0) formData.append(el.name, el.files[0]);
          else if (el.type !== 'file' && el.value) formData.append(el.name, el.value);
        });
      }
      formData.append('created_at', new Date().toISOString());
      const response = await fetch('../../api/categories.php', { method: 'POST', body: formData });
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const result = await response.json();
      if (result.success) {
        showSuccessMessage();
        resetCreateCategoryForm();
        setTimeout(() => {
          closeCreateCategoryModal();
          if (typeof window.refreshCategoryList === 'function') window.refreshCategoryList(); else window.location.reload();
        }, 2000);
      } else {
        alert('Có lỗi xảy ra: ' + (result.message || 'Không thể tạo danh mục'));
      }
    } catch (err) {
      console.error('Error creating category:', err);
      alert('Có lỗi xảy ra khi tạo danh mục: ' + err.message);
    } finally {
      if (btn) { btn.disabled = false; btn.classList.remove('loading'); btn.innerHTML = original || 'Tạo danh mục'; }
    }
  }

  function setupRealTimeValidation() {
    const nameInput = document.getElementById('categoryName');
    if (nameInput) nameInput.addEventListener('input', function(){ const v=this.value.trim(); if (v.length>=3){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('categoryName'); } else if (v.length>0){ this.classList.remove('success'); this.classList.add('error'); showFieldError('categoryName','Tên danh mục phải có ít nhất 3 ký tự'); } else { this.classList.remove('success','error'); clearFieldError('categoryName'); }});
    const statusSelect = document.getElementById('categoryStatus');
    if (statusSelect) statusSelect.addEventListener('change', function(){ if (this.value !== ''){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('categoryStatus'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('categoryStatus','Vui lòng chọn trạng thái'); }});
  }

  document.addEventListener('DOMContentLoaded', function(){
    setupRealTimeValidation();
    document.addEventListener('click', function(e){ const modal = document.getElementById('createCategoryModal'); if (modal && e.target === modal) closeCreateCategoryModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && isCategoryModalOpen) closeCreateCategoryModal(); });
  });

  window.openCreateCategoryModal = openCreateCategoryModal;
  window.closeCreateCategoryModal = closeCreateCategoryModal;
  window.submitCreateCategoryForm = submitCreateCategoryForm;
  window.previewCategoryImage = previewCategoryImage;
  
  // Load locations for category create
  async function loadLocationsForCategoryCreate() {
    try {
      const response = await fetch('../../admin/iot/api/locations.php');
      const result = await response.json();
      const select = document.getElementById('categoryLocationId');
      if (select && result.success && Array.isArray(result.data)) {
        select.innerHTML = '<option value="">Chưa gán vị trí</option>';
        result.data.forEach(loc => {
          const opt = document.createElement('option');
          opt.value = loc.id;
          opt.textContent = `${loc.location_name} (${loc.location_code})`;
          select.appendChild(opt);
        });
      }
    } catch (e) { console.error('Load locations failed', e); }
  }
  
  // Edit Category Modal Functions
  let isEditCategoryModalOpen = false;
  let currentEditCategoryId = null;

  function openEditCategoryModal(categoryId, name, slug, description, parentId, locationId, sortOrder, isActive, image, temperatureType, humidityType) {
    currentEditCategoryId = categoryId;
    
    // Populate form fields
    const idEl = document.getElementById('editCategoryId');
    if (idEl) idEl.value = categoryId;
    const nameEl = document.getElementById('editCategoryName');
    if (nameEl) nameEl.value = name || '';
    const slugEl = document.getElementById('editCategorySlug');
    if (slugEl) slugEl.value = slug || '';
    const descEl = document.getElementById('editCategoryDescription');
    if (descEl) descEl.value = description || '';
    const sortEl = document.getElementById('editSortOrder');
    if (sortEl) sortEl.value = (typeof sortOrder !== 'undefined' && sortOrder !== null) ? sortOrder : 0;
    const statusEl = document.getElementById('editCategoryStatus');
    if (statusEl) statusEl.value = (typeof isActive !== 'undefined' && isActive !== null) ? String(isActive) : '1';
    // Nhiệt độ/độ ẩm được ấn định theo vị trí; không còn trường riêng trong danh mục
    
    // Set parent category
    const parentSelect = document.getElementById('editParentId');
    if (parentSelect) {
      parentSelect.value = parentId || '';
    }
    
    // Handle image display
    const currentImageContainer = document.getElementById('currentImageContainer');
    const currentImage = document.getElementById('currentCategoryImage');
    
    if (currentImage && currentImageContainer) {
      if (image && String(image).trim() !== '') {
        currentImage.src = '../../' + image;
        currentImageContainer.style.display = 'block';
      } else {
        currentImageContainer.style.display = 'none';
      }
    }
    
    // Reset image preview
    const previewContainer = document.getElementById('editImagePreviewContainer');
    if (previewContainer) previewContainer.style.display = 'none';
    const fileInput = document.getElementById('editCategoryImage');
    if (fileInput) fileInput.value = '';
    
    // Load parent categories for dropdown
    loadParentCategoriesForEdit();

    // Load locations for edit dropdown và set giá trị hiện tại
    fetch('../../admin/iot/api/locations.php')
      .then(r => r.json())
      .then(result => {
        if (result.success && Array.isArray(result.data)) {
          const select = document.getElementById('editCategoryLocationId');
          if (select) {
            const first = select.firstElementChild;
            select.innerHTML = '';
            if (first) select.appendChild(first);
            result.data.forEach(loc => {
              const opt = document.createElement('option');
              opt.value = loc.id;
              opt.textContent = `${loc.location_name} (${loc.location_code})`;
              select.appendChild(opt);
            });
            // Set selected value
            if (locationId) select.value = String(locationId);
          }
        }
      })
      .catch(err => console.error('Load locations failed', err));
    
    // Show modal
    const modal = document.getElementById('editCategoryModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
    }
  }

  function closeEditCategoryModal() {
    const modal = document.getElementById('editCategoryModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isEditCategoryModalOpen = false;
      document.body.style.overflow = '';
      currentEditCategoryId = null;
    }
  }

  function resetEditCategoryForm() {
    const form = document.getElementById('editCategoryForm');
    if (form) form.reset();
    clearEditCategoryErrors();
    hideEditCategorySuccessMessage();
    
    // Reset image previews
    document.getElementById('editImagePreviewContainer').style.display = 'none';
    document.getElementById('currentImageContainer').style.display = 'none';
  }

  function clearEditCategoryErrors() {
    document.querySelectorAll('#editCategoryModal .field-error').forEach(el => { 
      el.textContent=''; 
      el.classList.remove('show'); 
    });
  }

  function showEditCategoryFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; 
      errorEl.classList.add('show');
      const input = document.getElementById(fieldId); 
      if (input) { 
        input.classList.add('error'); 
        input.classList.remove('success'); 
      }
    }
  }

  function clearEditCategoryFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { 
      errorEl.textContent=''; 
      errorEl.classList.remove('show'); 
    }
    const input = document.getElementById(fieldId); 
    if (input) input.classList.remove('error');
  }

  function showEditCategorySuccessMessage() { 
    const el = document.getElementById('successMessageEditCategory'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideEditCategorySuccessMessage();
      }, 3000);
    }
  }
  
  function hideEditCategorySuccessMessage() { 
    const el = document.getElementById('successMessageEditCategory'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function validateEditCategoryForm() {
    let isValid = true; 
    clearEditCategoryErrors();
    
    const name = document.getElementById('editCategoryName')?.value.trim() || '';
    if (!name) { 
      showEditCategoryFieldError('editCategoryName','Tên danh mục là bắt buộc'); 
      isValid = false; 
    } else if (name.length < 3) { 
      showEditCategoryFieldError('editCategoryName','Tên danh mục phải có ít nhất 3 ký tự'); 
      isValid = false; 
    }
    
    const status = document.getElementById('editCategoryStatus')?.value || '';
    if (status === '') { 
      showEditCategoryFieldError('editCategoryStatus','Vui lòng chọn trạng thái'); 
      isValid = false; 
    }
    
    return isValid;
  }

  async function submitEditCategoryForm() {
    if (!validateEditCategoryForm()) return;
    
    const btn = document.getElementById('editCategorySubmitBtn');
    const original = btn?.innerHTML || '';
    
    try {
      if (btn) { 
        btn.disabled = true; 
        btn.classList.add('loading'); 
        btn.innerHTML = 'Đang cập nhật...'; 
      }
      
      const form = document.getElementById('editCategoryForm');
      const formData = new FormData();
      
      if (form) {
        Array.from(form.elements).forEach(el => {
          if (!el.name) return;
          if (el.type === 'file' && el.files && el.files.length > 0) {
            formData.append(el.name, el.files[0]);
          } else if (el.type !== 'file' && el.value !== undefined) {
            formData.append(el.name, el.value);
          }
        });
      }
      
      // Add category ID
      formData.append('id', currentEditCategoryId);
      
      const response = await fetch('../../api/categories.php', { 
        method: 'POST', 
        body: formData 
      });
      
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      
      const result = await response.json();
      if (result.success) {
        showEditCategorySuccessMessage();
        setTimeout(() => {
          closeEditCategoryModal();
          // Refresh the page to show updated data
          window.location.reload();
        }, 2000);
      } else {
        throw new Error(result.message || 'Không thể cập nhật danh mục');
      }
    } catch (err) {
      console.error('Error updating category:', err);
      alert('Có lỗi xảy ra khi cập nhật danh mục: ' + err.message);
    } finally {
      if (btn) { 
        btn.disabled = false; 
        btn.classList.remove('loading'); 
        btn.innerHTML = original || 'Cập nhật danh mục'; 
      }
    }
  }

  function loadParentCategoriesForEdit() {
    fetch('../../api/categories.php?parent')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          const parentSelect = document.getElementById('editParentId');
          if (parentSelect) {
            // Keep the first option (Không có danh mục cha)
            const firstOption = parentSelect.firstElementChild;
            parentSelect.innerHTML = '';
            parentSelect.appendChild(firstOption);
            
            // Add parent categories, excluding current category
            data.data.forEach(category => {
              if (category.id != currentEditCategoryId) {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                parentSelect.appendChild(option);
              }
            });
          }
        }
      })
      .catch(error => {
        console.error('Error loading parent categories:', error);
      });
  }

  function previewEditCategoryImage(input) {
    const previewContainer = document.getElementById('editImagePreviewContainer');
    const preview = document.getElementById('editCategoryImagePreview');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        previewContainer.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      previewContainer.style.display = 'none';
      preview.src = '';
    }
  }

  // Setup edit category real-time validation
  function setupEditCategoryRealTimeValidation() {
    const nameInput = document.getElementById('editCategoryName');
    if (nameInput) {
      nameInput.addEventListener('input', function(){
        const v = this.value.trim(); 
        if (v.length >= 3){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditCategoryFieldError('editCategoryName'); 
        } else if (v.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditCategoryFieldError('editCategoryName','Tên danh mục phải có ít nhất 3 ký tự'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditCategoryFieldError('editCategoryName'); 
        }
      });
    }
    
    const statusSelect = document.getElementById('editCategoryStatus');
    if (statusSelect) {
      statusSelect.addEventListener('change', function(){
        if (this.value !== ''){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditCategoryFieldError('editCategoryStatus'); 
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditCategoryFieldError('editCategoryStatus','Vui lòng chọn trạng thái'); 
        }
      });
    }
  }

  // Add event listeners for edit modal
  document.addEventListener('DOMContentLoaded', function(){
    setupEditCategoryRealTimeValidation();
    document.addEventListener('click', function(e){ 
      const modal = document.getElementById('editCategoryModal'); 
      if (modal && e.target === modal) closeEditCategoryModal(); 
    });
    document.addEventListener('keydown', function(e){ 
      if (e.key === 'Escape' && isEditCategoryModalOpen) closeEditCategoryModal(); 
    });
  });

  // Export functions to window
  window.openEditCategoryModal = openEditCategoryModal;
  window.closeEditCategoryModal = closeEditCategoryModal;
  window.submitEditCategoryForm = submitEditCategoryForm;
  window.previewEditCategoryImage = previewEditCategoryImage;
  
  // Product Management Functions
  function deleteProduct(productId, productName) {
    if (confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${productName}"?`)) {
      // Hiển thị loading trên button
      const deleteBtn = event.target.closest('.btn-outline-danger');
      const originalText = deleteBtn.innerHTML;
      deleteBtn.disabled = true;
      deleteBtn.innerHTML = '<i class="iconoir-loading"></i> Đang xóa...';
      
      // Gọi API xóa sản phẩm
      fetch(`../../api/products.php?id=${productId}`, {
        method: 'DELETE'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Hiển thị thông báo thành công
          showDeleteProductSuccessMessage();
          
          // Xóa card sản phẩm khỏi giao diện
          const productCard = deleteBtn.closest('.col-lg-4');
          productCard.style.opacity = '0.5';
          productCard.style.transform = 'scale(0.95)';
          
          setTimeout(() => {
            productCard.remove();
            
            // Kiểm tra xem còn sản phẩm nào không
            const remainingProducts = document.querySelectorAll('.product-card');
            if (remainingProducts.length === 0) {
              // Hiển thị trạng thái trống
              const container = document.querySelector('.row');
              container.innerHTML = `
                <div class="col-12 text-center py-5">
                  <div class="empty-state">
                    <i class="iconoir-package" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                    <h4 class="text-muted mb-3">Chưa có sản phẩm nào</h4>
                    <p class="text-muted mb-4">Bắt đầu tạo sản phẩm đầu tiên để quản lý</p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="openCreateProductModal()">
                      <i class="iconoir-plus"></i> Tạo sản phẩm đầu tiên
                    </button>
                  </div>
                </div>
              `;
            }
          }, 300);
        } else {
          throw new Error(data.message || 'Có lỗi xảy ra khi xóa sản phẩm');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi: ' + error.message, 'error');
        
        // Khôi phục button
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
      });
    }
  }

  // Edit Product Modal Functions
  let isEditProductModalOpen = false;
  let currentEditProductId = null;

  function openEditProductModal(productId, name, sku, description, categoryId, brand, price, salePrice, stockQuantity, isActive, images) {
    currentEditProductId = productId;
    
    // Populate form fields
    document.getElementById('editProductId').value = productId;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductSku').value = sku;
    document.getElementById('editProductDescription').value = description;
    document.getElementById('editProductBrand').value = brand;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductSalePrice').value = salePrice;
    document.getElementById('editStockQuantity').value = stockQuantity;
    document.getElementById('editProductStatus').value = isActive;
    
    // Handle image display
    const currentImageContainer = document.getElementById('currentProductImageContainer');
    const currentImage = document.getElementById('currentProductImage');
    
    if (images && images.trim() !== '') {
      currentImage.src = '../../' + images;
      currentImageContainer.style.display = 'block';
    } else {
      currentImageContainer.style.display = 'none';
    }
    
    // Reset image preview
    document.getElementById('editImagePreviewContainer').style.display = 'none';
    document.getElementById('editProductImage').value = '';
    
    // Load categories for dropdown trước, sau đó set giá trị danh mục
    loadCategoriesForEdit().then(() => {
      // Sau khi load xong danh mục, set giá trị danh mục
      const categorySelect = document.getElementById('editProductCategory');
      if (categorySelect && categoryId) {
        categorySelect.value = categoryId;
        // Xóa lỗi validation
        categorySelect.classList.remove('error');
        categorySelect.classList.add('success');
        const errorEl = document.getElementById('editProductCategoryError');
        if (errorEl) {
          errorEl.textContent = '';
          errorEl.classList.remove('show');
        }
      }
    });
    
    // Show modal
    const modal = document.getElementById('editProductModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
      isEditProductModalOpen = true;
      document.body.style.overflow = 'hidden';
      
      // Focus on first field
      const first = document.getElementById('editProductName'); 
      if (first) first.focus();
    }
  }

  function closeEditProductModal() {
    const modal = document.getElementById('editProductModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isEditProductModalOpen = false;
      document.body.style.overflow = '';
      currentEditProductId = null;
    }
  }

  function resetEditProductForm() {
    const form = document.getElementById('editProductForm');
    if (form) form.reset();
    clearEditProductErrors();
    hideEditProductSuccessMessage();
    
    // Reset image previews
    document.getElementById('editImagePreviewContainer').style.display = 'none';
    document.getElementById('currentProductImageContainer').style.display = 'none';
  }

  function clearEditProductErrors() {
    document.querySelectorAll('#editProductModal .field-error').forEach(el => { 
      el.textContent=''; 
      el.classList.remove('show'); 
    });
  }

  function showEditProductFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; 
      errorEl.classList.add('show');
      const input = document.getElementById(fieldId); 
      if (input) { 
        input.classList.add('error'); 
        input.classList.remove('success'); 
      }
    }
  }

  function clearEditProductFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { 
      errorEl.textContent=''; 
      errorEl.classList.remove('show'); 
    }
    const input = document.getElementById(fieldId); 
    if (input) input.classList.remove('error');
  }

  function showEditProductSuccessMessage() { 
    const el = document.getElementById('successMessageEditProduct'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideEditProductSuccessMessage();
      }, 3000);
    }
  }
  
  function hideEditProductSuccessMessage() { 
    const el = document.getElementById('successMessageEditProduct'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function validateEditProductForm() {
    let isValid = true; 
    clearEditProductErrors();
    
    const name = document.getElementById('editProductName')?.value.trim() || '';
    if (!name) { 
      showEditProductFieldError('editProductName','Tên sản phẩm là bắt buộc'); 
      isValid = false; 
    } else if (name.length < 3) { 
      showEditProductFieldError('editProductName','Tên sản phẩm phải có ít nhất 3 ký tự'); 
      isValid = false; 
    }
    
    const sku = document.getElementById('editProductSku')?.value.trim() || '';
    if (!sku) { 
      showEditProductFieldError('editProductSku','SKU là bắt buộc'); 
      isValid = false; 
    }
    
    const price = document.getElementById('editProductPrice')?.value || '';
    if (!price || price <= 0) { 
      showEditProductFieldError('editProductPrice','Giá sản phẩm phải lớn hơn 0'); 
      isValid = false; 
    }
    
    const stockQuantity = document.getElementById('editStockQuantity')?.value || '';
    if (!stockQuantity || stockQuantity < 0) { 
      showEditProductFieldError('editStockQuantity','Số lượng tồn kho phải lớn hơn hoặc bằng 0'); 
      isValid = false; 
    }
    
    const categoryId = document.getElementById('editProductCategory')?.value || '';
    if (!categoryId) { 
      showEditProductFieldError('editProductCategory','Vui lòng chọn danh mục'); 
      isValid = false; 
    }
    
    const status = document.getElementById('editProductStatus')?.value || '';
    if (status === '') { 
      showEditProductFieldError('editProductStatus','Vui lòng chọn trạng thái'); 
      isValid = false; 
    }
    
    return isValid;
  }

  async function submitEditProductForm() {
    if (!validateEditProductForm()) return;
    
    const btn = document.getElementById('editProductSubmitBtn');
    const original = btn?.innerHTML || '';
    
    try {
      if (btn) { 
        btn.disabled = true; 
        btn.classList.add('loading'); 
        btn.innerHTML = 'Đang cập nhật...'; 
      }
      
      const form = document.getElementById('editProductForm');
      const formData = new FormData();
      
      if (form) {
        Array.from(form.elements).forEach(el => {
          if (!el.name) return;
          if (el.type === 'file' && el.files && el.files.length > 0) {
            formData.append(el.name, el.files[0]);
          } else if (el.type !== 'file' && el.value !== undefined) {
            formData.append(el.name, el.value);
          }
        });
      }
      
      // Add product ID
      formData.append('id', currentEditProductId);
      
      const response = await fetch('../../api/products.php', { 
        method: 'POST', 
        body: formData 
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('Server response:', errorText);
        throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
      }
      
      const result = await response.json();
      if (result.success) {
        showEditProductSuccessMessage();
        setTimeout(() => {
          closeEditProductModal();
          // Refresh the page to show updated data
          window.location.reload();
        }, 2000);
      } else {
        throw new Error(result.message || 'Không thể cập nhật sản phẩm');
      }
    } catch (err) {
      console.error('Error updating product:', err);
      alert('Có lỗi xảy ra khi cập nhật sản phẩm: ' + err.message);
    } finally {
      if (btn) { 
        btn.disabled = false; 
        btn.classList.remove('loading'); 
        btn.innerHTML = original || 'Cập nhật sản phẩm'; 
      }
    }
  }

  function loadCategoriesForEdit() {
    return fetch('../../api/categories.php')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          const categorySelect = document.getElementById('editProductCategory');
          if (categorySelect) {
            // Keep the first option (Chọn danh mục)
            const firstOption = categorySelect.firstElementChild;
            categorySelect.innerHTML = '';
            categorySelect.appendChild(firstOption);
            
            // Add categories
            data.data.forEach(category => {
              const option = document.createElement('option');
              option.value = category.id;
              option.textContent = category.name;
              categorySelect.appendChild(option);
            });
          }
        }
      })
      .catch(error => {
        console.error('Error loading categories:', error);
      });
  }

  function previewEditProductImage(input) {
    const previewContainer = document.getElementById('editImagePreviewContainer');
    const preview = document.getElementById('editProductImagePreview');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        previewContainer.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      previewContainer.style.display = 'none';
      preview.src = '';
    }
  }

  // Setup edit product real-time validation
  function setupEditProductRealTimeValidation() {
    const nameInput = document.getElementById('editProductName');
    if (nameInput) {
      nameInput.addEventListener('input', function(){
        const v = this.value.trim(); 
        if (v.length >= 3){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editProductName'); 
        } else if (v.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editProductName','Tên sản phẩm phải có ít nhất 3 ký tự'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditProductFieldError('editProductName'); 
        }
      });
    }
    
    const skuInput = document.getElementById('editProductSku');
    if (skuInput) {
      skuInput.addEventListener('input', function(){
        const v = this.value.trim(); 
        if (v.length > 0){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editProductSku'); 
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editProductSku','SKU là bắt buộc'); 
        }
      });
    }
    
    const priceInput = document.getElementById('editProductPrice');
    if (priceInput) {
      priceInput.addEventListener('input', function(){
        const v = parseFloat(this.value); 
        if (v > 0){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editProductPrice'); 
        } else if (this.value.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editProductPrice','Giá sản phẩm phải lớn hơn 0'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditProductFieldError('editProductPrice'); 
        }
      });
    }
    
    const stockInput = document.getElementById('editStockQuantity');
    if (stockInput) {
      stockInput.addEventListener('input', function(){
        const v = parseInt(this.value); 
        if (v >= 0){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editStockQuantity'); 
        } else if (this.value.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editStockQuantity','Số lượng tồn kho phải lớn hơn hoặc bằng 0'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditProductFieldError('editStockQuantity'); 
        }
      });
    }
    
    const categorySelect = document.getElementById('editProductCategory');
    if (categorySelect) {
      categorySelect.addEventListener('change', function(){
        if (this.value !== ''){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editProductCategory'); 
          // Cập nhật thông tin nhiệt độ và độ ẩm
          updateEditTemperatureHumidityInfo(this.value);
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editProductCategory','Vui lòng chọn danh mục'); 
          // Reset thông tin nhiệt độ và độ ẩm
          resetEditTemperatureHumidityInfo();
        }
      });
    }
    
    const statusSelect = document.getElementById('editProductStatus');
    if (statusSelect) {
      statusSelect.addEventListener('change', function(){
        if (this.value !== ''){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditProductFieldError('editProductStatus'); 
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditProductFieldError('editProductStatus','Vui lòng chọn trạng thái'); 
        }
      });
    }
  }

  // Add event listeners for edit product modal
  document.addEventListener('DOMContentLoaded', function(){
    setupEditProductRealTimeValidation();
    document.addEventListener('click', function(e){ 
      const modal = document.getElementById('editProductModal'); 
      if (modal && e.target === modal) closeEditProductModal(); 
    });
    document.addEventListener('keydown', function(e){ 
      if (e.key === 'Escape' && isEditProductModalOpen) closeEditProductModal(); 
    });
  });

  // Export functions to window
  window.deleteProduct = deleteProduct;
  window.openEditProductModal = openEditProductModal;
  window.closeEditProductModal = closeEditProductModal;
  window.submitEditProductForm = submitEditProductForm;
  window.previewEditProductImage = previewEditProductImage;
  // These helpers are defined in the Products widget IIFE above.
  // Guard to avoid ReferenceError if order changes or when this block is executed on pages without those definitions.
  if (typeof updateEditTemperatureHumidityInfo === 'function') {
    window.updateEditTemperatureHumidityInfo = updateEditTemperatureHumidityInfo;
  }
  if (typeof resetEditTemperatureHumidityInfo === 'function') {
    window.resetEditTemperatureHumidityInfo = resetEditTemperatureHumidityInfo;
  }
  if (typeof updateEditTemperatureDisplay === 'function') {
    window.updateEditTemperatureDisplay = updateEditTemperatureDisplay;
  }
  if (typeof updateEditHumidityDisplay === 'function') {
    window.updateEditHumidityDisplay = updateEditHumidityDisplay;
  }
  if (typeof updateEditTemperatureDangerDisplay === 'function') {
    window.updateEditTemperatureDangerDisplay = updateEditTemperatureDangerDisplay;
  }
  if (typeof updateEditHumidityDangerDisplay === 'function') {
    window.updateEditHumidityDangerDisplay = updateEditHumidityDangerDisplay;
  }
})();

// Sensor Edit Modal Functions
(function() {
  let isEditSensorModalOpen = false;
  let currentEditSensorId = null;

  function openEditSensorModal(sensorId, sensorName, sensorCode, sensorType, locationId, manufacturer, model, serialNumber, installationDate, minThreshold, maxThreshold, status, lastCalibration, description, notes) {
    currentEditSensorId = sensorId;
    
    // Debug log
    console.log('Opening edit sensor modal with locationId:', locationId, 'type:', typeof locationId);
    
    // Populate form fields
    document.getElementById('editSensorId').value = sensorId;
    document.getElementById('editSensorName').value = sensorName;
    document.getElementById('editSensorCode').value = sensorCode;
    document.getElementById('editSensorType').value = sensorType;
    document.getElementById('editManufacturer').value = manufacturer || '';
    document.getElementById('editModel').value = model || '';
    document.getElementById('editSerialNumber').value = serialNumber || '';
    document.getElementById('editInstallationDate').value = installationDate || '';
    document.getElementById('editMinThreshold').value = minThreshold || '';
    document.getElementById('editMaxThreshold').value = maxThreshold || '';
    document.getElementById('editSensorStatus').value = status;
    document.getElementById('editLastCalibration').value = lastCalibration || '';
    document.getElementById('editSensorDescription').value = description || '';
    document.getElementById('editSensorNotes').value = notes || '';
    
    // Load locations for dropdown trước, sau đó set giá trị vị trí
    loadLocationsForEdit().then(() => {
      // Đợi một chút để đảm bảo DOM đã được render
      setTimeout(() => {
        // Sau khi load xong vị trí, set giá trị vị trí
        const locationSelect = document.getElementById('editLocationId');
        console.log('Location select element:', locationSelect);
        console.log('Setting locationId to:', locationId);
        
        if (locationSelect) {
          if (locationId && locationId !== 'null' && locationId !== null) {
            locationSelect.value = locationId;
            console.log('Set location value to:', locationId);
            // Xóa lỗi validation
            locationSelect.classList.remove('error');
            locationSelect.classList.add('success');
          } else {
            locationSelect.value = '';
            console.log('Set location value to empty');
          }
        }
      }, 100);
    }).catch(error => {
      console.error('Error loading locations:', error);
    });
    
    // Show modal
    const modal = document.getElementById('editSensorModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
      isEditSensorModalOpen = true;
      document.body.style.overflow = 'hidden';
      
      // Focus on first field
      const first = document.getElementById('editSensorName'); 
      if (first) first.focus();
    }
  }

  function closeEditSensorModal() {
    const modal = document.getElementById('editSensorModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isEditSensorModalOpen = false;
      document.body.style.overflow = '';
      currentEditSensorId = null;
    }
  }

  function resetEditSensorForm() {
    const form = document.getElementById('editSensorForm');
    if (form) form.reset();
    clearEditSensorErrors();
    hideEditSensorSuccessMessage();
  }

  function clearEditSensorErrors() {
    document.querySelectorAll('#editSensorModal .field-error').forEach(el => { 
      el.textContent=''; 
      el.classList.remove('show'); 
    });
  }

  function showEditSensorFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; 
      errorEl.classList.add('show');
      const input = document.getElementById(fieldId); 
      if (input) { 
        input.classList.add('error'); 
        input.classList.remove('success'); 
      }
    }
  }

  function clearEditSensorFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { 
      errorEl.textContent=''; 
      errorEl.classList.remove('show'); 
    }
    const input = document.getElementById(fieldId); 
    if (input) input.classList.remove('error');
  }

  function showEditSensorSuccessMessage() { 
    const el = document.getElementById('successMessageEditSensor'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideEditSensorSuccessMessage();
      }, 3000);
    }
  }
  
  function hideEditSensorSuccessMessage() { 
    const el = document.getElementById('successMessageEditSensor'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function validateEditSensorForm() {
    let isValid = true; 
    clearEditSensorErrors();
    
    const name = document.getElementById('editSensorName')?.value.trim() || '';
    if (!name) { 
      showEditSensorFieldError('editSensorName','Tên cảm biến là bắt buộc'); 
      isValid = false; 
    } else if (name.length < 3) { 
      showEditSensorFieldError('editSensorName','Tên cảm biến phải có ít nhất 3 ký tự'); 
      isValid = false; 
    }
    
    const code = document.getElementById('editSensorCode')?.value.trim() || '';
    if (!code) { 
      showEditSensorFieldError('editSensorCode','Mã cảm biến là bắt buộc'); 
      isValid = false; 
    } else if (code.length < 3) {
      showEditSensorFieldError('editSensorCode','Mã cảm biến phải có ít nhất 3 ký tự');
      isValid = false;
    }
    
    const type = document.getElementById('editSensorType')?.value || '';
    if (!type) { 
      showEditSensorFieldError('editSensorType','Vui lòng chọn loại cảm biến'); 
      isValid = false; 
    }
    
    const status = document.getElementById('editSensorStatus')?.value || '';
    if (!status) { 
      showEditSensorFieldError('editSensorStatus','Vui lòng chọn trạng thái'); 
      isValid = false; 
    }

    // Validate thresholds - bắt buộc nhập cả 2
    const minThreshold = document.getElementById('editMinThreshold')?.value;
    const maxThreshold = document.getElementById('editMaxThreshold')?.value;
    
    if (!minThreshold || minThreshold.trim() === '') {
      showEditSensorFieldError('editMinThreshold','Ngưỡng tối thiểu là bắt buộc');
      isValid = false;
    }
    
    if (!maxThreshold || maxThreshold.trim() === '') {
      showEditSensorFieldError('editMaxThreshold','Ngưỡng tối đa là bắt buộc');
      isValid = false;
    }
    
    // Kiểm tra ngưỡng tối đa > ngưỡng tối thiểu
    if (minThreshold && maxThreshold && minThreshold.trim() !== '' && maxThreshold.trim() !== '') {
      const min = parseFloat(minThreshold);
      const max = parseFloat(maxThreshold);
      if (isNaN(min) || isNaN(max)) {
        if (isNaN(min)) showEditSensorFieldError('editMinThreshold','Ngưỡng tối thiểu phải là số hợp lệ');
        if (isNaN(max)) showEditSensorFieldError('editMaxThreshold','Ngưỡng tối đa phải là số hợp lệ');
        isValid = false;
      } else if (min >= max) {
        showEditSensorFieldError('editMaxThreshold','Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu');
        isValid = false;
      }
    }
    
    return isValid;
  }

  async function submitEditSensorForm() {
    if (!validateEditSensorForm()) return;
    
    const btn = document.getElementById('editSensorSubmitBtn');
    const original = btn?.innerHTML || '';
    
    try {
      if (btn) { 
        btn.disabled = true; 
        btn.classList.add('loading'); 
        btn.innerHTML = 'Đang cập nhật...'; 
      }
      
      const form = document.getElementById('editSensorForm');
      const formData = new FormData();
      
      if (form) {
        Array.from(form.elements).forEach(el => {
          if (!el.name) return;
          if (el.type !== 'file' && el.value !== undefined) {
            formData.append(el.name, el.value);
          }
        });
      }
      
      // Add sensor ID
      formData.append('id', currentEditSensorId);
      
      const response = await fetch('../api/update-sensor.php', { 
        method: 'POST', 
        body: formData 
      });
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('Server response:', errorText);
        throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
      }
      
      const result = await response.json();
      if (result.success) {
        showEditSensorSuccessMessage();
        setTimeout(() => {
          closeEditSensorModal();
          // Refresh the page to show updated data
          window.location.reload();
        }, 2000);
      } else {
        throw new Error(result.message || 'Không thể cập nhật cảm biến');
      }
    } catch (err) {
      console.error('Error updating sensor:', err);
      alert('Có lỗi xảy ra khi cập nhật cảm biến: ' + err.message);
    } finally {
      if (btn) { 
        btn.disabled = false; 
        btn.classList.remove('loading'); 
        btn.innerHTML = original || 'Cập nhật cảm biến'; 
      }
    }
  }

  function loadLocationsForEdit() {
    return fetch('../api/locations.php')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          const locationSelect = document.getElementById('editLocationId');
          if (locationSelect) {
            // Keep the first option (Chọn vị trí)
            const firstOption = locationSelect.firstElementChild;
            locationSelect.innerHTML = '';
            locationSelect.appendChild(firstOption);
            
            // Add locations
            data.data.forEach(location => {
              const option = document.createElement('option');
              option.value = location.id;
              option.textContent = `${location.location_name} (${location.location_code})`;
              locationSelect.appendChild(option);
            });
          }
        }
      })
      .catch(error => {
        console.error('Error loading locations:', error);
      });
  }

  // Setup edit sensor real-time validation
  function setupEditSensorRealTimeValidation() {
    const nameInput = document.getElementById('editSensorName');
    if (nameInput) {
      nameInput.addEventListener('input', function(){
        const v = this.value.trim(); 
        if (v.length >= 3){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditSensorFieldError('editSensorName'); 
        } else if (v.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditSensorFieldError('editSensorName','Tên cảm biến phải có ít nhất 3 ký tự'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditSensorFieldError('editSensorName'); 
        }
      });
    }
    
    const codeInput = document.getElementById('editSensorCode');
    if (codeInput) {
      codeInput.addEventListener('input', function(){
        const v = this.value.trim(); 
        if (v.length >= 3){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditSensorFieldError('editSensorCode'); 
        } else if (v.length > 0){
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditSensorFieldError('editSensorCode','Mã cảm biến phải có ít nhất 3 ký tự'); 
        } else { 
          this.classList.remove('success','error'); 
          clearEditSensorFieldError('editSensorCode'); 
        }
      });
    }
    
    const typeSelect = document.getElementById('editSensorType');
    if (typeSelect) {
      typeSelect.addEventListener('change', function(){
        if (this.value !== ''){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditSensorFieldError('editSensorType'); 
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditSensorFieldError('editSensorType','Vui lòng chọn loại cảm biến'); 
        }
      });
    }
    
    const statusSelect = document.getElementById('editSensorStatus');
    if (statusSelect) {
      statusSelect.addEventListener('change', function(){
        if (this.value !== ''){
          this.classList.add('success'); 
          this.classList.remove('error'); 
          clearEditSensorFieldError('editSensorStatus'); 
        } else { 
          this.classList.remove('success'); 
          this.classList.add('error'); 
          showEditSensorFieldError('editSensorStatus','Vui lòng chọn trạng thái'); 
        }
      });
    }

    // Validate thresholds
    const minThreshold = document.getElementById('editMinThreshold');
    const maxThreshold = document.getElementById('editMaxThreshold');
    
    if (minThreshold && maxThreshold) {
      const validateThresholds = () => {
        const min = parseFloat(minThreshold.value);
        const max = parseFloat(maxThreshold.value);
        
        if (minThreshold.value && maxThreshold.value && min >= max) {
          showEditSensorFieldError('editMaxThreshold','Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu');
          maxThreshold.classList.add('error');
          maxThreshold.classList.remove('success');
        } else {
          clearEditSensorFieldError('editMaxThreshold');
          if (maxThreshold.value) {
            maxThreshold.classList.add('success');
            maxThreshold.classList.remove('error');
          }
        }
      };
      
      minThreshold.addEventListener('input', validateThresholds);
      maxThreshold.addEventListener('input', validateThresholds);
    }
  }

  // Add event listeners for edit sensor modal
  document.addEventListener('DOMContentLoaded', function(){
    setupEditSensorRealTimeValidation();
    document.addEventListener('click', function(e){ 
      const modal = document.getElementById('editSensorModal'); 
      if (modal && e.target === modal) closeEditSensorModal(); 
    });
    document.addEventListener('keydown', function(e){ 
      if (e.key === 'Escape' && isEditSensorModalOpen) closeEditSensorModal(); 
    });
  });

  // Export functions to window
  window.openEditSensorModal = openEditSensorModal;
  window.closeEditSensorModal = closeEditSensorModal;
  window.submitEditSensorForm = submitEditSensorForm;
})();

// IoT widget
(function() {
  let isSensorModalOpen = false;

  function openCreateSensorModal() {
    const modal = document.getElementById('createSensorModal');
    if (modal) {
      modal.classList.add('show');
      setTimeout(() => { modal.querySelector('.custom-modal').classList.add('show'); }, 10);
      isSensorModalOpen = true;
      resetCreateSensorForm();
      const first = document.getElementById('sensorName'); if (first) first.focus();
      document.body.style.overflow = 'hidden';
    }
  }

  function closeCreateSensorModal() {
    const modal = document.getElementById('createSensorModal');
    if (modal) {
      modal.querySelector('.custom-modal').classList.remove('show');
      setTimeout(() => { modal.classList.remove('show'); }, 300);
      isSensorModalOpen = false;
      document.body.style.overflow = '';
      resetCreateSensorForm();
    }
  }

  function resetCreateSensorForm() {
    const form = document.getElementById('createSensorForm');
    if (!form) return;
    form.reset();
    clearAllErrors();
    hideSuccessMessage();
    form.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(el => el.classList.remove('error','success'));
  }

  function clearAllErrors() {
    document.querySelectorAll('#createSensorModal .field-error').forEach(el => { el.textContent=''; el.classList.remove('show'); });
  }

  function showFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) {
      errorEl.textContent = message; errorEl.classList.add('show');
      const input = document.getElementById(fieldId); if (input) { input.classList.add('error'); input.classList.remove('success'); }
    }
  }

  function clearFieldError(fieldId) {
    const errorEl = document.getElementById(fieldId + 'Error');
    if (errorEl) { errorEl.textContent=''; errorEl.classList.remove('show'); }
    const input = document.getElementById(fieldId); if (input) input.classList.remove('error');
  }

  function showSuccessMessage() { showCreateSensorSuccessMessage(); }
  function hideSuccessMessage() { hideCreateSensorSuccessMessage(); }

  function validateCreateSensorForm() {
    let isValid = true; clearAllErrors();
    const required = [
      { id: 'sensorName', name: 'Tên cảm biến' },
      { id: 'sensorCode', name: 'Mã cảm biến' },
      { id: 'sensorType', name: 'Loại cảm biến' },
      { id: 'locationId', name: 'Vị trí lắp đặt' },
      { id: 'status', name: 'Trạng thái' }
    ];
    required.forEach(f => { const el = document.getElementById(f.id); if (!el || !el.value.trim()) { showFieldError(f.id, `${f.name} là bắt buộc`); isValid = false; } else { clearFieldError(f.id); el.classList.add('success'); }});
    const sensorCode = document.getElementById('sensorCode'); if (sensorCode && sensorCode.value.trim()) { const re=/^[a-zA-Z0-9_]{3,20}$/; if (!re.test(sensorCode.value.trim())) { showFieldError('sensorCode','Mã cảm biến chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự'); isValid=false; } }
    // Validate thresholds - bắt buộc nhập cả 2
    const minT = document.getElementById('minThreshold');
    const maxT = document.getElementById('maxThreshold');
    
    if (!minT || !minT.value || minT.value.trim() === '') {
      showFieldError('minThreshold','Ngưỡng tối thiểu là bắt buộc');
      isValid = false;
    }
    
    if (!maxT || !maxT.value || maxT.value.trim() === '') {
      showFieldError('maxThreshold','Ngưỡng tối đa là bắt buộc');
      isValid = false;
    }
    
    // Kiểm tra ngưỡng tối đa > ngưỡng tối thiểu
    if (minT && maxT && minT.value && maxT.value && minT.value.trim() !== '' && maxT.value.trim() !== '') {
      const min = parseFloat(minT.value);
      const max = parseFloat(maxT.value);
      if (isNaN(min) || isNaN(max)) {
        if (isNaN(min)) showFieldError('minThreshold','Ngưỡng tối thiểu phải là số hợp lệ');
        if (isNaN(max)) showFieldError('maxThreshold','Ngưỡng tối đa phải là số hợp lệ');
        isValid = false;
      } else if (min >= max) {
        showFieldError('maxThreshold','Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu');
        isValid = false;
      }
    }
    return isValid;
  }

  async function submitCreateSensorForm() {
    if (!validateCreateSensorForm()) return;
    const btn = document.getElementById('createSensorSubmitBtn'); let original = btn?.innerHTML || '';
    try {
      if (btn) { btn.innerHTML = '<i class="iconoir-loading me-1"></i>Đang tạo...'; btn.disabled = true; }
      const form = document.getElementById('createSensorForm');
      const formData = new FormData(form);
      formData.append('created_at', new Date().toISOString());
      formData.append('updated_at', new Date().toISOString());
      const response = await fetch('api/create-sensor.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (result.success) {
        showCreateSensorSuccessMessage();
        resetCreateSensorForm();
        setTimeout(() => {
          closeCreateSensorModal();
          if (typeof window.refreshSensorList === 'function') window.refreshSensorList(); else location.reload();
        }, 2000);
      } else {
        showToast('error', 'Lỗi: ' + (result.message || 'Không thể tạo cảm biến'));
        if (result.errors) { Object.keys(result.errors).forEach(field => { showFieldError(field, result.errors[field]); }); }
      }
    } catch (err) {
      console.error('Error creating sensor:', err);
      showToast('error', 'Lỗi kết nối: ' + err.message);
    } finally {
      if (btn) { btn.innerHTML = original || 'Tạo cảm biến'; btn.disabled = false; }
    }
  }

  function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast bg-${type === 'error' ? 'danger' : 'success'} text-white`;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); animation: slideInRight 0.3s ease-out;';
    toast.innerHTML = `<div class="d-flex align-items-center"><i class="iconoir-${type==='error'?'warning-triangle':'check-circle'} me-2"></i><span>${message}</span></div>`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'slideOutRight 0.3s ease-out'; setTimeout(() => { toast.remove(); }, 300); }, 5000);
  }

  const style = document.createElement('style');
  style.textContent = '@keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }';
  document.head.appendChild(style);

  document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('createSensorModal');
    if (modal) modal.addEventListener('click', function(e){ if (e.target === modal) closeCreateSensorModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && isSensorModalOpen) closeCreateSensorModal(); });
    const form = document.getElementById('createSensorForm');
    if (form) {
      const inputs = form.querySelectorAll('.form-input, .form-select, .form-textarea');
      inputs.forEach(input => {
        input.addEventListener('blur', function(){ validateField(this); });
        input.addEventListener('input', function(){ if (this.classList.contains('error')) clearFieldError(this.id); });
      });
    }
  });

  function validateField(input) {
    const fieldId = input.id; const value = input.value.trim();
    clearFieldError(fieldId);
    if (input.hasAttribute('required') && !value) { const fieldName = (input.previousElementSibling?.textContent || 'Trường này').replace('*','').trim(); showFieldError(fieldId, `${fieldName} là bắt buộc`); return false; }
    if (fieldId === 'sensorCode' && value) { const re=/^[a-zA-Z0-9_]{3,20}$/; if (!re.test(value)) { showFieldError(fieldId, 'Mã cảm biến chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài 3-20 ký tự'); return false; } }
    if (value) input.classList.add('success');
    return true;
  }

  // Create Success Message Functions
  function showCreateSensorSuccessMessage() { 
    const el = document.getElementById('successMessageCreateSensor'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideCreateSensorSuccessMessage();
      }, 3000);
    }
  }
  
  function hideCreateSensorSuccessMessage() { 
    const el = document.getElementById('successMessageCreateSensor'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function showCreateProductSuccessMessage() { 
    const el = document.getElementById('successMessageCreateProduct'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideCreateProductSuccessMessage();
      }, 3000);
    }
  }
  
  function hideCreateProductSuccessMessage() { 
    const el = document.getElementById('successMessageCreateProduct'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function showCreateCategorySuccessMessage() { 
    const el = document.getElementById('successMessageCreateCategory'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideCreateCategorySuccessMessage();
      }, 3000);
    }
  }
  
  function hideCreateCategorySuccessMessage() { 
    const el = document.getElementById('successMessageCreateCategory'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  window.openCreateSensorModal = openCreateSensorModal;
  window.closeCreateSensorModal = closeCreateSensorModal;
  window.submitCreateSensorForm = submitCreateSensorForm;
  window.showCreateSensorSuccessMessage = showCreateSensorSuccessMessage;
  window.hideCreateSensorSuccessMessage = hideCreateSensorSuccessMessage;
  window.showCreateProductSuccessMessage = showCreateProductSuccessMessage;
  window.hideCreateProductSuccessMessage = hideCreateProductSuccessMessage;
  window.showCreateCategorySuccessMessage = showCreateCategorySuccessMessage;
  window.hideCreateCategorySuccessMessage = hideCreateCategorySuccessMessage;

  // Delete Success Message Functions
  function showDeleteSensorSuccessMessage() { 
    const el = document.getElementById('successMessageDeleteSensor'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideDeleteSensorSuccessMessage();
      }, 3000);
    }
  }
  
  function hideDeleteSensorSuccessMessage() { 
    const el = document.getElementById('successMessageDeleteSensor'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function showDeleteProductSuccessMessage() { 
    const el = document.getElementById('successMessageDeleteProduct'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideDeleteProductSuccessMessage();
      }, 3000);
    }
  }
  
  function hideDeleteProductSuccessMessage() { 
    const el = document.getElementById('successMessageDeleteProduct'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  function showDeleteCategorySuccessMessage() { 
    const el = document.getElementById('successMessageDeleteCategory'); 
    if (el) {
      el.classList.add('show', 'slide-in');
      // Auto hide after 3 seconds
      setTimeout(() => {
        hideDeleteCategorySuccessMessage();
      }, 3000);
    }
  }
  
  function hideDeleteCategorySuccessMessage() { 
    const el = document.getElementById('successMessageDeleteCategory'); 
    if (el) {
      el.classList.add('slide-out');
      setTimeout(() => {
        el.classList.remove('show', 'slide-in', 'slide-out');
      }, 300);
    }
  }

  window.showDeleteSensorSuccessMessage = showDeleteSensorSuccessMessage;
  window.hideDeleteSensorSuccessMessage = hideDeleteSensorSuccessMessage;
  window.showDeleteProductSuccessMessage = showDeleteProductSuccessMessage;
  window.hideDeleteProductSuccessMessage = hideDeleteProductSuccessMessage;
  window.showDeleteCategorySuccessMessage = showDeleteCategorySuccessMessage;
  window.hideDeleteCategorySuccessMessage = hideDeleteCategorySuccessMessage;
})();


