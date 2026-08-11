/**
 * Admin Dashboard CRUD Utilities
 * Provides toast notifications, confirmation dialogs, and shared AJAX handlers
 * for all admin CRUD operations without page reloads.
 */

// ============================================
// TOAST NOTIFICATION SYSTEM
// ============================================

(function initAdminToasts() {
    let toastContainer = document.getElementById('admin-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'admin-toast-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1080';
        document.body.appendChild(toastContainer);
    }

    window.showAdminToast = function (message, type = 'success') {
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `admin-toast ${type} show`;
        toast.textContent = message;
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toastContainer.appendChild(toast);

        window.setTimeout(function () {
            toast.classList.remove('show');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }, 2200);
    };
})();

// ============================================
// CONFIRMATION DIALOG SYSTEM
// ============================================

window.showAdminConfirm = function (title, message, confirmText = 'Delete', cancelText = 'Cancel') {
    return new Promise(function (resolve) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-sm">
                <div class="modal-content border-0">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">${cancelText}</button>
                        <button type="button" class="btn btn-danger btn-sm" id="confirm-action">${confirmText}</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);

        modal.querySelector('#confirm-action').addEventListener('click', function () {
            bsModal.hide();
            resolve(true);
        });

        modal.addEventListener('hidden.bs.modal', function () {
            modal.remove();
            resolve(false);
        });

        bsModal.show();
    });
};

// ============================================
// AJAX CRUD OPERATIONS
// ============================================

window.AdminCRUD = {
    /**
     * Perform a generic AJAX request for CRUD operations
     * @param {string} endpoint - The AJAX endpoint URL
     * @param {Object} data - Form data to send
     * @param {Object} options - Additional options (method, headers, etc.)
     * @returns {Promise}
     */
    request: function (endpoint, data = {}, options = {}) {
        const method = options.method || 'POST';
        const headers = options.headers || {};

        let body = null;
        if (method === 'POST' || method === 'PUT') {
            if (data instanceof FormData) {
                body = data;
            } else {
                body = new URLSearchParams();
                Object.keys(data).forEach(function (key) {
                    body.append(key, data[key]);
                });
                headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            }
        }

        const fetchOptions = {
            method: method,
            headers: headers
        };

        if (body) {
            fetchOptions.body = body;
        }

        return fetch(endpoint, fetchOptions)
            .then(function (response) {
                return response.json().then(function (data) {
                    return { response, data };
                });
            });
    },

    /**
     * Create a new item via AJAX
     * @param {string} endpoint - AJAX endpoint
     * @param {Object|FormData} formData - Data to send
     * @param {Object} options - Additional options
     * @returns {Promise}
     */
    create: function (endpoint, formData, options = {}) {
        return this.request(endpoint, formData, Object.assign({ method: 'POST' }, options))
            .then(function (result) {
                if (result.data && result.data.success) {
                    if (options.onSuccess) {
                        options.onSuccess(result.data);
                    }
                    showAdminToast(result.data.message || 'Item created successfully.', 'success');
                    return result.data;
                } else {
                    const errorMsg = result.data?.message || 'Unable to create item.';
                    if (options.onError) {
                        options.onError(result.data);
                    }
                    showAdminToast(errorMsg, 'error');
                    throw new Error(errorMsg);
                }
            })
            .catch(function (error) {
                const errorMsg = options.errorMessage || 'An error occurred.';
                if (options.onError) {
                    options.onError({ success: false, message: errorMsg });
                }
                showAdminToast(errorMsg, 'error');
                throw error;
            });
    },

    /**
     * Update an item via AJAX
     * @param {string} endpoint - AJAX endpoint
     * @param {Object|FormData} formData - Data to send
     * @param {Object} options - Additional options
     * @returns {Promise}
     */
    update: function (endpoint, formData, options = {}) {
        return this.request(endpoint, formData, Object.assign({ method: 'POST' }, options))
            .then(function (result) {
                if (result.data && result.data.success) {
                    if (options.onSuccess) {
                        options.onSuccess(result.data);
                    }
                    showAdminToast(result.data.message || 'Item updated successfully.', 'success');
                    return result.data;
                } else {
                    const errorMsg = result.data?.message || 'Unable to update item.';
                    if (options.onError) {
                        options.onError(result.data);
                    }
                    showAdminToast(errorMsg, 'error');
                    throw new Error(errorMsg);
                }
            })
            .catch(function (error) {
                const errorMsg = options.errorMessage || 'An error occurred.';
                if (options.onError) {
                    options.onError({ success: false, message: errorMsg });
                }
                showAdminToast(errorMsg, 'error');
                throw error;
            });
    },

    /**
     * Delete an item via AJAX with confirmation
     * @param {string} endpoint - AJAX endpoint
     * @param {Object} data - Data to send (usually item ID)
     * @param {Object} options - Additional options (title, message, etc.)
     * @returns {Promise}
     */
    delete: async function (endpoint, data, options = {}) {
        const confirmed = await showAdminConfirm(
            options.title || 'Confirm Delete',
            options.message || 'Are you sure? This action cannot be undone.',
            'Delete',
            'Cancel'
        );

        if (!confirmed) return;

        return this.request(endpoint, data, Object.assign({ method: 'POST' }, options))
            .then(function (result) {
                if (result.data && result.data.success) {
                    if (options.onSuccess) {
                        options.onSuccess(result.data);
                    }
                    showAdminToast(result.data.message || 'Item deleted successfully.', 'success');
                    return result.data;
                } else {
                    const errorMsg = result.data?.message || 'Unable to delete item.';
                    if (options.onError) {
                        options.onError(result.data);
                    }
                    showAdminToast(errorMsg, 'error');
                    throw new Error(errorMsg);
                }
            })
            .catch(function (error) {
                const errorMsg = options.errorMessage || 'An error occurred.';
                if (options.onError) {
                    options.onError({ success: false, message: errorMsg });
                }
                showAdminToast(errorMsg, 'error');
                throw error;
            });
    }
};

// ============================================
// FORM DISABLE/ENABLE UTILITIES
// ============================================

window.disableFormControls = function (formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    const controls = form.querySelectorAll('input, select, textarea, button:not([data-bs-dismiss])');
    controls.forEach(function (control) {
        control.disabled = true;
    });
};

window.enableFormControls = function (formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    const controls = form.querySelectorAll('input, select, textarea, button:not([data-bs-dismiss])');
    controls.forEach(function (control) {
        control.disabled = false;
    });
};
