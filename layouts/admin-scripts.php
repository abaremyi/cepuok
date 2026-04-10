<?php
/**
 * Admin Scripts Layout — SHARED (all admin pages)
 * File: layouts/admin-scripts.php
 */
?>

<!-- Vendor / Theme JS -->
<script src="<?= admin_js_url('vendor.min.js') ?>"></script>
<script src="<?= admin_js_url('theme.min.js') ?>"></script>
<script src="<?= admin_js_url('toastr.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /* Globals every page needs */
    const BASE_URL     = '<?= BASE_URL ?>';
    const userPhoto    = '<?= $userPhoto    ?? '' ?>';
    const userInitials = '<?= $userInitials ?? '' ?>';
    const userFullName = '<?= $userFullName ?? '' ?>';

    /* Toastr defaults */
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton   : true,
            progressBar   : true,
            positionClass : 'toast-top-right',
            timeOut       : 5000
        };
    }

    /* Global SweetAlert wrapper */
    window.showAlert = function(type, title, message, callback) {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            confirmButtonColor: '#ff37b2'
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    };

    window.showConfirm = function(title, text, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && callback && typeof callback === 'function') {
                callback();
            }
        });
    };

    window.showToast = function (msg, type) {
        type = type || 'success';
        if (typeof toastr !== 'undefined') {
            if      (type === 'success')              toastr.success(msg);
            else if (type === 'danger' || type === 'error') toastr.error(msg);
            else if (type === 'warning')              toastr.warning(msg);
            else                                      toastr.info(msg);
        } else {
            const el = document.createElement('div');
            el.className = 'alert alert-' + type + ' position-fixed bottom-0 end-0 m-3 shadow';
            el.style.zIndex = 9999;
            el.textContent  = msg;
            document.body.appendChild(el);
            setTimeout(function () { el.remove(); }, 4000);
        }
    };

    /* Shared helper: getCookie */
    window.getCookie = function (name) {
        const v = '; ' + document.cookie;
        const p = v.split('; ' + name + '=');
        if (p.length === 2) return p.pop().split(';').shift();
        return '';
    };

    /* DOMContentLoaded — ONLY safe, page-agnostic code here */
    document.addEventListener('DOMContentLoaded', function () {
        /* Running clock — only rendered on dashboard */
        const timeEl = document.getElementById('currentTime');
        if (timeEl) {
            const tick = function () {
                timeEl.textContent = new Date().toLocaleTimeString('en-US', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
            };
            tick();
            setInterval(tick, 1000);
        }

        /* Initialize tooltips */
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    /* Global AJAX error handler */
    window.handleAjaxError = function(xhr) {
        let message = 'An error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                message = response.message || message;
            } catch(e) {}
        }
        showAlert('error', 'Error!', message);
    };
</script>

<!-- Session Lock -->
<script src="<?= js_url('admin/session-lock.js') ?>"></script>

<!-- HS Theme Component Bootstrap -->
<script>
    (function () {
        window.addEventListener('load', function () {
            if (window.HSSideNav) new HSSideNav('.js-navbar-vertical-aside').init();
            if (window.HSBsDropdown) HSBsDropdown.init();
            if (typeof HSCore !== 'undefined') {
                try { HSCore.components.HSTomSelect  && HSCore.components.HSTomSelect.init('.js-select');   } catch(e){}
                try { HSCore.components.HSClipboard  && HSCore.components.HSClipboard.init('.js-clipboard');} catch(e){}
                try { HSCore.components.HSDropzone   && HSCore.components.HSDropzone.init('.js-dropzone');  } catch(e){}
                try {
                    if (HSCore.components.HSDatatables) {
                        document.querySelectorAll('.js-datatable').forEach(function (el) {
                            HSCore.components.HSDatatables.init(el);
                        });
                    }
                } catch(e){}
            }
        });
    })();
</script>