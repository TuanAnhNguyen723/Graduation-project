// Ecommerce Products Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    let products = [];
    let currentProductId = null;
    let nextProductId = 1;

    // Khởi tạo dữ liệu sản phẩm mẫu
    initializeSampleData();

    // Event listeners
    document.getElementById('addProductBtn').addEventListener('click', openAddModal);
    document.getElementById('saveProduct').addEventListener('click', saveProduct);
    document.getElementById('confirmDelete').addEventListener('click', confirmDeleteProduct);
    document.getElementById('productImage').addEventListener('change', previewImage);

    // Thêm event listeners cho các nút edit/delete trong bảng
    addTableEventListeners();

    // Hàm khởi tạo dữ liệu mẫu
    function initializeSampleData() {
        const tableRows = document.querySelectorAll('#datatable_1 tbody tr');
        tableRows.forEach((row, index) => {
            const productId = index + 1;
            const productName = row.querySelector('.product-name').textContent;
            const category = row.cells[2].textContent;
            const pics = row.cells[3].textContent;
            const price = row.cells[4].textContent.replace('$', '');
            const status = row.cells[5].querySelector('.badge').textContent.trim();
            const createdAt = row.cells[6].textContent;

            products.push({
                id: productId,
                name: productName,
                category: category,
                pics: parseInt(pics),
                price: parseFloat(price),
                status: status,
                createdAt: createdAt,
                image: row.querySelector('img').src,
                description: row.querySelector('.text-muted').textContent
            });

            // Cập nhật các nút edit/delete với product ID
            const actionCell = row.cells[7]; // Cột action
            const editLink = actionCell.querySelector('a:first-child');
            const deleteLink = actionCell.querySelector('a:last-child');
            
            if (editLink) {
                editLink.className = 'edit-product';
                editLink.setAttribute('data-product-id', productId);
                editLink.href = '#';
            }
            
            if (deleteLink) {
                deleteLink.className = 'delete-product';
                deleteLink.setAttribute('data-product-id', productId);
                deleteLink.href = '#';
            }
        });

        nextProductId = products.length + 1;
    }

    // Hàm thêm event listeners cho bảng
    function addTableEventListeners() {
        // Event delegation cho các nút edit/delete
        document.getElementById('datatable_1').addEventListener('click', function(e) {
            if (e.target.closest('.edit-product')) {
                e.preventDefault();
                const productId = parseInt(e.target.closest('.edit-product').getAttribute('data-product-id'));
                editProduct(productId);
            } else if (e.target.closest('.delete-product')) {
                e.preventDefault();
                const productId = parseInt(e.target.closest('.delete-product').getAttribute('data-product-id'));
                deleteProduct(productId);
            }
        });
    }

    // Hàm mở modal thêm sản phẩm
    function openAddModal() {
        currentProductId = null;
        document.getElementById('productModalLabel').textContent = 'Thêm Sản phẩm mới';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }

    // Hàm mở modal sửa sản phẩm
    function editProduct(productId) {
        const product = products.find(p => p.id === productId);
        if (!product) return;

        currentProductId = productId;
        document.getElementById('productModalLabel').textContent = 'Sửa Sản phẩm';
        
        // Điền dữ liệu vào form
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productPics').value = product.pics;
        document.getElementById('productDescription').value = product.description;
        document.getElementById('productStatus').value = product.status;

        // Hiển thị ảnh hiện tại
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        previewImg.src = product.image;
        preview.style.display = 'block';

        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }

    // Hàm xóa sản phẩm
    function deleteProduct(productId) {
        const product = products.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('deleteProductName').textContent = product.name;
        document.getElementById('confirmDelete').setAttribute('data-product-id', productId);
        
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    // Hàm xác nhận xóa sản phẩm
    function confirmDeleteProduct() {
        const productId = parseInt(document.getElementById('confirmDelete').getAttribute('data-product-id'));
        const productIndex = products.findIndex(p => p.id === productId);
        
        if (productIndex !== -1) {
            products.splice(productIndex, 1);
            removeProductFromTable(productId);
            
            // Đóng modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
            modal.hide();
            
            showNotification('Xóa sản phẩm thành công!', 'success');
        }
    }

    // Hàm lưu sản phẩm (thêm mới hoặc cập nhật)
    function saveProduct() {
        const form = document.getElementById('productForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const productData = {
            name: formData.get('productName'),
            category: formData.get('productCategory'),
            price: parseFloat(formData.get('productPrice')),
            pics: parseInt(formData.get('productPics')) || 0,
            description: formData.get('productDescription'),
            status: formData.get('productStatus'),
            image: 'assets/images/products/01.png', // Default image
            createdAt: new Date().toLocaleString('vi-VN')
        };

        // Xử lý upload ảnh nếu có
        const imageFile = document.getElementById('productImage').files[0];
        if (imageFile) {
            const reader = new FileReader();
            reader.onload = function(e) {
                productData.image = e.target.result;
                saveProductData(productData);
            };
            reader.readAsDataURL(imageFile);
        } else {
            saveProductData(productData);
        }
    }

    // Hàm lưu dữ liệu sản phẩm
    function saveProductData(productData) {
        if (currentProductId) {
            // Cập nhật sản phẩm
            const productIndex = products.findIndex(p => p.id === currentProductId);
            if (productIndex !== -1) {
                products[productIndex] = { ...products[productIndex], ...productData };
                updateProductInTable(currentProductId, products[productIndex]);
                showNotification('Cập nhật sản phẩm thành công!', 'success');
            }
        } else {
            // Thêm sản phẩm mới
            productData.id = nextProductId++;
            products.push(productData);
            addProductToTable(productData);
            showNotification('Thêm sản phẩm thành công!', 'success');
        }

        // Đóng modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('productModal'));
        modal.hide();
    }

    // Hàm thêm sản phẩm vào bảng
    function addProductToTable(product) {
        const tbody = document.querySelector('#datatable_1 tbody');
        const newRow = document.createElement('tr');
        
        const statusBadge = getStatusBadge(product.status);
        
        newRow.innerHTML = `
            <td style="width: 16px;">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="check" id="customCheck${product.id}">
                </div>
            </td>
            <td class="ps-0">
                <img src="${product.image}" alt="" height="40" class="rounded me-1">
                <p class="d-inline-block align-middle mb-0">
                    <a href="ecommerce-order-details.html" class="d-inline-block align-middle mb-0 product-name">${product.name}</a> 
                    <br>
                    <span class="text-muted font-13">${product.description || 'Mô tả sản phẩm'}</span> 
                </p>
            </td>
            <td>${product.category}</td>
            <td>${product.pics}</td>
            <td>$${product.price}</td>
            <td>${statusBadge}</td>
            <td>
                <span>${product.createdAt}</span>
            </td>
            <td class="text-end">                                                       
                <a href="#" class="edit-product" data-product-id="${product.id}"><i class="las la-pen text-secondary fs-18"></i></a>
                <a href="#" class="delete-product" data-product-id="${product.id}"><i class="las la-trash-alt text-secondary fs-18"></i></a>
            </td>
        `;

        tbody.appendChild(newRow);
    }

    // Hàm cập nhật sản phẩm trong bảng
    function updateProductInTable(productId, product) {
        const editLink = document.querySelector(`.edit-product[data-product-id="${productId}"]`);
        if (!editLink) return;
        
        const row = editLink.closest('tr');
        if (row) {
            const statusBadge = getStatusBadge(product.status);
            
            row.querySelector('.product-name').textContent = product.name;
            row.querySelector('.text-muted').textContent = product.description || 'Mô tả sản phẩm';
            row.cells[2].textContent = product.category;
            row.cells[3].textContent = product.pics;
            row.cells[4].textContent = `$${product.price}`;
            row.cells[5].innerHTML = statusBadge;
            row.cells[6].textContent = product.createdAt;
        }
    }

    // Hàm xóa sản phẩm khỏi bảng
    function removeProductFromTable(productId) {
        const editLink = document.querySelector(`.edit-product[data-product-id="${productId}"]`);
        if (editLink) {
            const row = editLink.closest('tr');
            if (row) {
                row.remove();
            }
        }
    }

    // Hàm tạo badge trạng thái
    function getStatusBadge(status) {
        switch(status) {
            case 'Published':
                return '<span class="badge bg-success-subtle text-success"><i class="fas fa-check me-1"></i> Published</span>';
            case 'Draft':
                return '<span class="badge bg-secondary-subtle text-secondary"><i class="fas fa-box-archive me-1"></i> Draft</span>';
            case 'Inactive':
                return '<span class="badge bg-danger-subtle text-danger"><i class="fas fa-xmark me-1"></i> Inactive</span>';
            default:
                return '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
        }
    }

    // Hàm preview ảnh
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }

    // Hàm hiển thị thông báo
    function showNotification(message, type = 'info') {
        // Tạo toast notification
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        
        const toastId = 'toast-' + Date.now();
        toastContainer.innerHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Thông báo</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        document.body.appendChild(toastContainer);
        
        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();
        
        // Tự động xóa toast sau khi ẩn
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
            toastContainer.remove();
        });
    }
});
