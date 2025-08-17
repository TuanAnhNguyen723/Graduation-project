// Admin Layout JavaScript - Common for all admin pages

document.addEventListener('DOMContentLoaded', function() {
    // ==============================================================
    // LAYOUT MANAGEMENT - QUẢN LÝ LAYOUT VÀ RESPONSIVE
    // ==============================================================
    
    // Xử lý responsive sidebar và content
    const toggleButton = document.querySelector('.button-menu-mobile');
    const startbar = document.querySelector('.startbar');
    const contentPage = document.querySelector('.content-page');
    const pageWrapper = document.querySelector('.page-wrapper');
    const navbarCustom = document.querySelector('.navbar-custom');

    // Function để cập nhật layout
    function updateLayout() {
        if (window.innerWidth > 768) {
            // Desktop layout
            if (startbar) startbar.style.transform = 'translateX(0)';
            if (contentPage) contentPage.style.marginLeft = '260px';
            if (pageWrapper) pageWrapper.style.marginLeft = '260px';
            if (navbarCustom) navbarCustom.style.left = '260px';
        } else {
            // Mobile layout
            if (startbar) startbar.style.transform = 'translateX(-100%)';
            if (contentPage) contentPage.style.marginLeft = '0';
            if (pageWrapper) pageWrapper.style.marginLeft = '0';
            if (navbarCustom) navbarCustom.style.left = '0';
        }
        
        // Đảm bảo content luôn có margin-top để tránh header
        const headerHeight = 70;
        if (contentPage) contentPage.style.marginTop = headerHeight + 'px';
        if (pageWrapper) pageWrapper.style.marginTop = headerHeight + 'px';
    }

    if (toggleButton && startbar && (contentPage || pageWrapper) && navbarCustom) {
        toggleButton.addEventListener('click', function() {
            startbar.classList.toggle('show');

            if (window.innerWidth <= 768) {
                if (startbar.classList.contains('show')) {
                    // Mobile sidebar mở
                    if (contentPage) contentPage.style.marginLeft = '0';
                    if (pageWrapper) pageWrapper.style.marginLeft = '0';
                    if (navbarCustom) navbarCustom.style.left = '0';
                } else {
                    // Mobile sidebar đóng
                    if (contentPage) contentPage.style.marginLeft = '0';
                    if (pageWrapper) pageWrapper.style.marginLeft = '0';
                    if (navbarCustom) navbarCustom.style.left = '0';
                }
            }
        });

        // Đóng sidebar khi click bên ngoài (trên mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!startbar.contains(e.target) && !toggleButton.contains(e.target)) {
                    startbar.classList.remove('show');
                    if (contentPage) contentPage.style.marginLeft = '0';
                    if (pageWrapper) pageWrapper.style.marginLeft = '0';
                    if (navbarCustom) navbarCustom.style.left = '0';
                }
            }
        });

        // Xử lý resize window
        window.addEventListener('resize', updateLayout);

        // Khởi tạo layout ban đầu
        updateLayout();
    }

    // ==============================================================
    // COLLAPSE MENU FUNCTIONALITY - XỬ LÝ MENU XỔ XUỐNG
    // ==============================================================
    
    // Xử lý collapse menu - Cải thiện để hoạt động tốt hơn
    const collapseButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
    collapseButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const target = this.getAttribute('data-bs-target');
            const targetElement = document.querySelector(target);

            if (targetElement) {
                // Toggle collapse
                if (targetElement.classList.contains('show')) {
                    targetElement.classList.remove('show');
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    targetElement.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                }

                // Không thêm icon rotation - Chỉ để CSS xử lý
                // Icon rotation sẽ được xử lý bởi CSS ::after pseudo-element
            }
        });
    });

    // Đảm bảo Bootstrap collapse hoạt động - Cải thiện
    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        const collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(element => {
            try {
                new bootstrap.Collapse(element, {
                    toggle: false
                });
            } catch (error) {
                console.log('Bootstrap Collapse initialization error:', error);
            }
        });
    } else {
        console.log('Bootstrap not loaded or Collapse not available');
        
        // Fallback: Tự xử lý collapse nếu Bootstrap không có
        const collapseButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
        collapseButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const target = this.getAttribute('data-bs-target');
                const targetElement = document.querySelector(target);

                if (targetElement) {
                    // Toggle collapse manually
                    if (targetElement.classList.contains('show')) {
                        targetElement.classList.remove('show');
                        this.setAttribute('aria-expanded', 'false');
                    } else {
                        targetElement.classList.add('show');
                        this.setAttribute('aria-expanded', 'true');
                    }

                    // Không thêm icon rotation - Chỉ để CSS xử lý
                    // Icon rotation sẽ được xử lý bởi CSS ::after pseudo-element
                }
            });
        });
    }

    // ==============================================================
    // CONTENT ENHANCEMENTS - CẢI THIỆN NỘI DUNG
    // ==============================================================
    
    // Thêm hiệu ứng hover cho các card
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }
        }, 5000);
    });

    // Smooth scroll to top
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.innerHTML = '<i class="iconoir-arrow-up"></i>';
    scrollToTopBtn.className = 'btn btn-primary rounded-circle position-fixed';
    scrollToTopBtn.style.cssText = 'bottom: 20px; right: 20px; width: 50px; height: 50px; z-index: 1000; display: none;';
    document.body.appendChild(scrollToTopBtn);

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.display = 'block';
        } else {
            scrollToTopBtn.style.display = 'none';
        }
    });

    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize popovers
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // ==============================================================
    // DEBUG AND LOGGING - KIỂM TRA VÀ GHI LOG
    // ==============================================================
    
    // Log layout information
    console.log('Layout initialized:', {
        windowWidth: window.innerWidth,
        headerHeight: 70,
        sidebarWidth: 260,
        contentPage: !!contentPage,
        pageWrapper: !!pageWrapper,
        startbar: !!startbar,
        navbarCustom: !!navbarCustom
    });
    
    // Kiểm tra layout elements
    const layoutElements = {
        '.page-wrapper': document.querySelector('.page-wrapper'),
        '.content-page': document.querySelector('.content-page'),
        '.page-content': document.querySelector('.page-content'),
        '.startbar': document.querySelector('.startbar'),
        '.navbar-custom': document.querySelector('.navbar-custom')
    };
    
    console.log('Layout elements found:', layoutElements);
    
    // Kiểm tra CSS properties
    if (contentPage) {
        const computedStyle = window.getComputedStyle(contentPage);
        console.log('Content page CSS:', {
            marginTop: computedStyle.marginTop,
            marginLeft: computedStyle.marginLeft,
            padding: computedStyle.padding
        });
    }
});

// ==============================================================
// SIMPLE DROPDOWN SUPPORT - HỖ TRỢ DROPDOWN ĐƠN GIẢN
// ==============================================================

// Đảm bảo Bootstrap dropdown hoạt động
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra xem Bootstrap có sẵn không
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        // Bootstrap đã sẵn, không cần làm gì thêm
        console.log('Bootstrap Dropdown đã sẵn sàng');
    } else {
        // Fallback nếu Bootstrap chưa load
        console.log('Đang chờ Bootstrap load...');
    }
});

// ==============================================================
// DROPDOWN NOTIFICATION HANDLING - XỬ LÝ DROPDOWN THÔNG BÁO
// ==============================================================

// Khởi tạo dropdown notification khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationDropdown();
});

// Khởi tạo lại dropdown khi window load
window.addEventListener('load', function() {
    initializeNotificationDropdown();
});

function initializeNotificationDropdown() {
    // Tìm tất cả dropdown notification
    const notificationDropdowns = document.querySelectorAll('.dropdown');
    
    notificationDropdowns.forEach(dropdown => {
        const toggleButton = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        
        if (toggleButton && dropdownMenu) {
            // Xử lý click vào toggle button
            toggleButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle dropdown
                if (dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    toggleButton.setAttribute('aria-expanded', 'false');
                } else {
                    // Đóng tất cả dropdown khác trước
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdownMenu) {
                            menu.classList.remove('show');
                            const otherToggle = menu.closest('.dropdown').querySelector('[data-bs-toggle="dropdown"]');
                            if (otherToggle) {
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                    
                    // Mở dropdown hiện tại
                    dropdownMenu.classList.add('show');
                    toggleButton.setAttribute('aria-expanded', 'true');
                }
            });
            
            // Đóng dropdown khi click bên ngoài
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    toggleButton.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Đóng dropdown khi click vào dropdown item
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Đóng dropdown sau khi click item
                    setTimeout(() => {
                        dropdownMenu.classList.remove('show');
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }, 100);
                });
            });
        }
    });
}
