// Admin Layout JavaScript - Common for all admin pages

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý responsive sidebar
    const toggleButton = document.querySelector('.button-menu-mobile');
    const startbar = document.querySelector('.startbar');
    const contentPage = document.querySelector('.content-page');
    const navbarCustom = document.querySelector('.navbar-custom');

    if (toggleButton && startbar && contentPage && navbarCustom) {
        toggleButton.addEventListener('click', function() {
            startbar.classList.toggle('show');

            if (window.innerWidth <= 768) {
                if (startbar.classList.contains('show')) {
                    contentPage.style.marginLeft = '0';
                    navbarCustom.style.left = '0';
                } else {
                    contentPage.style.marginLeft = '0';
                    navbarCustom.style.left = '0';
                }
            }
        });

        // Đóng sidebar khi click bên ngoài (trên mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!startbar.contains(e.target) && !toggleButton.contains(e.target)) {
                    startbar.classList.remove('show');
                    contentPage.style.marginLeft = '0';
                    navbarCustom.style.left = '0';
                }
            }
        });

        // Xử lý resize window
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                startbar.classList.remove('show');
                contentPage.style.marginLeft = '260px';
                navbarCustom.style.left = '260px';
            } else {
                contentPage.style.marginLeft = '0';
                navbarCustom.style.left = '0';
            }
        });

        // Khởi tạo vị trí ban đầu
        if (window.innerWidth > 768) {
            contentPage.style.marginLeft = '260px';
            navbarCustom.style.left = '260px';
        } else {
            contentPage.style.marginLeft = '0';
            navbarCustom.style.left = '0';
        }
    }

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

    // Xử lý collapse menu - Cải thiện để hoạt động tốt hơn
    const collapseButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
    collapseButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
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

                // Toggle icon rotation (nếu có)
                const icon = this.querySelector('i');
                if (icon) {
                    if (targetElement.classList.contains('show')) {
                        icon.style.transform = 'rotate(90deg)';
                    } else {
                        icon.style.transform = 'rotate(0deg)';
                    }
                    icon.style.transition = 'transform 0.3s ease';
                }
            }
        });
    });

    // Đảm bảo Bootstrap collapse hoạt động
    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        const collapseElements = document.querySelectorAll('.collapse');
        collapseElements.forEach(element => {
            new bootstrap.Collapse(element, {
                toggle: false
            });
        });
    }

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
});
