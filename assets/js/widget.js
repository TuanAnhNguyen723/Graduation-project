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

  function showSuccessMessage() { const el = document.querySelector('#createProductModal #successMessage'); if (el) el.classList.add('show'); }
  function hideSuccessMessage() { const el = document.querySelector('#createProductModal #successMessage'); if (el) el.classList.remove('show'); }

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
        }, 1500);
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
    if (productCategorySelect) productCategorySelect.addEventListener('change', function(){ if (this.value){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productCategory'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('productCategory','Vui lòng chọn danh mục'); }});
    const productStatusSelect = document.getElementById('productStatus');
    if (productStatusSelect) productStatusSelect.addEventListener('change', function(){ if (this.value){ this.classList.add('success'); this.classList.remove('error'); clearFieldError('productStatus'); } else { this.classList.remove('success'); this.classList.add('error'); showFieldError('productStatus','Vui lòng chọn trạng thái'); }});
  }

  function setupSKUGeneration() {
    const nameInput = document.getElementById('productName'); if (nameInput) nameInput.addEventListener('blur', generateSKU);
  }

  document.addEventListener('DOMContentLoaded', function(){
    setupRealTimeValidation(); setupSKUGeneration();
    document.addEventListener('click', function(e){ const modal = document.getElementById('createProductModal'); if (modal && e.target === modal) closeCreateProductModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && isProductModalOpen) closeCreateProductModal(); });
  });

  window.openCreateProductModal = openCreateProductModal;
  window.closeCreateProductModal = closeCreateProductModal;
  window.submitCreateProductForm = submitCreateProductForm;
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

  function showSuccessMessage() { const el = document.querySelector('#createSensorModal #successMessage'); if (el) el.classList.add('show'); }
  function hideSuccessMessage() { const el = document.querySelector('#createSensorModal #successMessage'); if (el) el.classList.remove('show'); }

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
    const minT = document.getElementById('minThreshold'); const maxT = document.getElementById('maxThreshold');
    if (minT && maxT && minT.value && maxT.value) { const min=parseFloat(minT.value), max=parseFloat(maxT.value); if (min >= max) { showFieldError('maxThreshold','Ngưỡng tối đa phải lớn hơn ngưỡng tối thiểu'); isValid=false; } }
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
        showSuccessMessage();
        resetCreateSensorForm();
        setTimeout(() => {
          closeCreateSensorModal();
          if (typeof window.refreshSensorList === 'function') window.refreshSensorList(); else location.reload();
        }, 1500);
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

  window.openCreateSensorModal = openCreateSensorModal;
  window.closeCreateSensorModal = closeCreateSensorModal;
  window.submitCreateSensorForm = submitCreateSensorForm;
})();


