/**
 * ============================================
 * HOTEL PMS — Async Form Handler
 * Handles all forms with data-ajax="true"
 * Depends on: app.js (Toast, FormHandler, Modal)
 * ============================================
 */

function initAsyncForms() {
    var forms = document.querySelectorAll('form[data-ajax="true"]');
    for (var i = 0; i < forms.length; i++) {
        var form = forms[i];
        if (form.dataset.asyncInitialized) continue;
        form.dataset.asyncInitialized = 'true';

        (function(f) {
            f.addEventListener('submit', function(e) {
                e.preventDefault();
                if (typeof FormHandler !== 'undefined' && FormHandler.submit) {
                    FormHandler.submit(f, {
                        onSuccess: function(data) {
                            if (f.closest('#modalContainer')) {
                                Modal.close();
                            }
                            // Auto-refresh page after actions that change room state (checkout, checkin, etc.)
                            if (f.dataset.refresh === 'true' && data && data.success) {
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else {
                                    window.location.reload();
                                }
                            }
                        }
                    });
                }
            });
        })(form);
    }
}

document.addEventListener('DOMContentLoaded', initAsyncForms);
