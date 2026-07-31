/**
 * Main Client Scripts
 * Handles dynamic actions like profile image preview, alerts auto-dismiss, and theme configurations.
 */

document.addEventListener('DOMContentLoaded', () => {
    const showToast = (title, text, icon = 'success') => {
        if (window.Swal) {
            Swal.fire({
                title,
                text,
                icon,
                timer: 2200,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    };

    const alertTypeToIcon = {
        success: 'success',
        danger: 'error',
        warning: 'warning',
        info: 'info'
    };

    // 1. Profile Avatar Live Preview
    const avatarInput = document.getElementById('avatar-upload');
    const avatarPreview = document.getElementById('avatar-preview');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    showToast('Invalid image', 'Please select a valid image file (PNG, JPG, JPEG).', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Auto Dismiss Flash Alerts after 5 seconds and mirror them as toasts
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        const alertText = alert.textContent.replace(/\s+/g, ' ').trim();
        const typeMatch = alert.className.match(/alert-(\w+)/);
        const type = typeMatch ? typeMatch[1] : 'info';
        if (alertText) {
            showToast('Notice', alertText, alertTypeToIcon[type] || 'info');
        }

        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // 3. Dynamic food count calculator on detail page
    const qtyInput = document.getElementById('food-qty');
    const priceDisplay = document.getElementById('food-unit-price');
    const totalDisplay = document.getElementById('food-total-price');

    if (qtyInput && priceDisplay && totalDisplay) {
        const unitPrice = parseFloat(priceDisplay.dataset.price);
        qtyInput.addEventListener('input', () => {
            let qty = parseInt(qtyInput.value);
            if (isNaN(qty) || qty < 1) qty = 1;
            totalDisplay.innerText = '$' + (unitPrice * qty).toFixed(2);
        });
    }

    // 4. Make homepage food cards fully clickable while preserving button actions
    const handleFoodCardNavigation = (event) => {
        const card = event.currentTarget;
        const interactiveElement = event.target.closest('a, button, input, select, textarea');

        if (interactiveElement) {
            return;
        }

        if (event.type === 'keydown' && !['Enter', ' '].includes(event.key)) {
            return;
        }

        if (event.type === 'keydown' && event.key === ' ') {
            event.preventDefault();
        }

        const detailUrl = card.dataset.detailUrl;
        if (detailUrl) {
            window.location.href = detailUrl;
        }
    };

    document.querySelectorAll('.food-card[data-detail-url]').forEach(card => {
        card.addEventListener('click', handleFoodCardNavigation);
        card.addEventListener('keydown', handleFoodCardNavigation);
    });

    // 5. Back to top button
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        const toggleBackToTop = () => {
            backToTop.style.display = window.scrollY > 300 ? 'flex' : 'none';
            backToTop.style.alignItems = 'center';
            backToTop.style.justifyContent = 'center';
        };

        window.addEventListener('scroll', toggleBackToTop);
        toggleBackToTop();
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 6. Cart quality stepper buttons
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            let value = parseInt(input.value, 10);
            if (Number.isNaN(value) || value < 1) value = 1;

            if (button.dataset.action === 'plus') {
                input.value = value + 1;
            } else {
                input.value = Math.max(1, value - 1);
            }
        });
    });

    // 7. Checkout loading state
    const orderForm = document.querySelector('form[data-order-form]');
    if (orderForm) {
        orderForm.addEventListener('submit', () => {
            const submitBtn = orderForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="me-2"><span class="spinner-border spinner-border-sm"></span></span>Placing Order...';
            }
        });
    }

    // 8. Show SweetAlert2 after Add to Cart (server redirects back with ?added=1)
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('added') === '1' && window.Swal) {
            Swal.fire({
                title: 'Item added to cart successfully!',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'View Cart',
                cancelButtonText: 'Continue Shopping',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = (window.BASE_URL || '/') + 'pages/customer/cart.php';
                } else {
                    // remove query param to avoid showing again
                    params.delete('added');
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.replaceState({}, document.title, newUrl);
                }
            });
        }
    } catch (e) {
        // silent
    }

    // 9. Smooth nav active transitions
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('mouseenter', () => {
            if (!link.classList.contains('active-nav')) {
                link.style.transform = 'translateY(-1px)';
            }
        });
        link.addEventListener('mouseleave', () => {
            link.style.transform = '';
        });
    });
});
