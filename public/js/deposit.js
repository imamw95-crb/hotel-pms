/**
 * ============================================
 * HOTEL PMS — Deposit Kartu (Card Deposit) JS
 * Handles operations for deposit module (page-based + AJAX modals)
 * Depends on: app.js (Toast, Modal, FormHandler)
 * ============================================
 */

var Deposit = (function() {
    'use strict';

    // ── Open create deposit in modal ──
    function openCreateModal(reservationId) {
        var url = window._depositCreateUrl || '/deposits/create';
        if (reservationId) {
            url += '?reservation_id=' + reservationId;
        }
        if (typeof Modal !== 'undefined' && Modal.open) {
            Modal.open(url, { size: 'max-w-2xl' });
        } else {
            window.location.href = url;
        }
    }

    // ── Open show/detail deposit in modal ──
    function openShowModal(id) {
        var url = (window._depositShowUrlTemplate || '/deposits/__ID__').replace('__ID__', id);
        if (typeof Modal !== 'undefined' && Modal.open) {
            Modal.open(url, { size: 'max-w-3xl' });
        } else {
            window.location.href = url;
        }
    }

    // ── Return deposit via AJAX ──
    function returnDeposit(id) {
        if (!confirm('Yakin ingin menandai deposit ini sebagai dikembalikan?')) return;
        var url = (window._depositReturnUrlTemplate || '/deposits/__ID__/return').replace('__ID__', id);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        if (data.message) Toast.success(data.message);
                        refreshTable();
                        if (typeof Modal !== 'undefined') Modal.close();
                    } else {
                        if (data.message) Toast.error(data.message);
                    }
                } catch(e) {
                    Toast.error('Response tidak valid');
                }
            } else {
                Toast.error('Terjadi kesalahan server');
            }
        };
        xhr.onerror = function() {
            Toast.error('Koneksi gagal. Silakan coba lagi.');
        };
        xhr.send();
    }

    // ── Refresh the deposit table ──
    function refreshTable() {
        var container = document.getElementById('depositTableContainer');
        if (!container) {
            // Fallback: reload the page
            window.location.reload();
            return;
        }
        // Get current query params
        var currentUrl = window.location.href;
        var sep = currentUrl.indexOf('?') !== -1 ? '&' : '?';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', currentUrl + sep + '_ajax_table=1', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.table) {
                        container.innerHTML = data.table;
                        // Re-init delete forms if any
                        if (typeof initDeleteForms === 'function') initDeleteForms();
                    }
                } catch(e) {
                    window.location.reload();
                }
            } else {
                window.location.reload();
            }
        };
        xhr.onerror = function() {
            window.location.reload();
        };
        xhr.send();
    }

    // ── Apply filters from the modal-index view ──
    function applyFilters() {
        var dateFrom = document.getElementById('filterDateFrom')?.value || '';
        var dateTo = document.getElementById('filterDateTo')?.value || '';
        var search = document.getElementById('filterSearch')?.value || '';

        var params = [];
        if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
        if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
        if (search) params.push('search=' + encodeURIComponent(search));

        var url = (window._depositIndexUrl || '/deposits') + (params.length ? '?' + params.join('&') : '');

        // If modal is open, load into modal; else redirect
        var overlay = document.getElementById('modalOverlay');
        if (overlay && !overlay.classList.contains('hidden') && typeof Modal !== 'undefined') {
            Modal.open(url, { size: 'max-w-6xl' });
        } else {
            window.location.href = url;
        }
    }

    // ── Reset filters ──
    function resetFilters() {
        var overlay = document.getElementById('modalOverlay');
        if (overlay && !overlay.classList.contains('hidden') && typeof Modal !== 'undefined') {
            Modal.open(window._depositIndexUrl || '/deposits', { size: 'max-w-6xl' });
        } else {
            window.location.href = window._depositIndexUrl || '/deposits';
        }
    }

    // ── Public API ──
    return {
        openCreateModal: openCreateModal,
        openShowModal: openShowModal,
        returnDeposit: returnDeposit,
        returnDepositFromDetail: returnDeposit, // alias used in modal-show
        refreshTable: refreshTable,
        applyFilters: applyFilters,
        resetFilters: resetFilters
    };
})();

// ── Expose globally ──
window.Deposit = Deposit;