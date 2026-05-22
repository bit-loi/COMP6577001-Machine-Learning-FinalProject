/**
 * Churn Dashboard — Retention Actions & UI Interactions
 * 
 * Handles:
 * - Recording retention actions via AJAX
 * - Toast notifications
 * - Search debounce auto-submit
 * 
 * Expects global: window.churnConfig = { actionHandlerUrl }
 */
(function () {
    const config = window.churnConfig || {};

    // ── Action Recording ──────────────────────────────────────────
    window.recordAction = function (customerId, scoreId, actionType, btn) {
        if (btn.classList.contains('done')) return;
        btn.disabled = true;
        btn.style.opacity = '0.6';

        fetch(config.actionHandlerUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `customer_id=${encodeURIComponent(customerId)}&churn_score_id=${scoreId}&action_type=${encodeURIComponent(actionType)}&admin_note=`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.add('done');
                btn.style.opacity = '1';
                btn.disabled = false;
                showToast('✓ ' + data.message);
            } else {
                btn.disabled = false;
                btn.style.opacity = '1';
                showToast('⚠ ' + data.message, '#ef4444');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.style.opacity = '1';
            showToast('Network error', '#ef4444');
        });
    };

    // ── Toast Notification ────────────────────────────────────────
    function showToast(msg, color = '#16a34a') {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.style.background = color;
        t.style.transform = 'translateY(0)';
        t.style.opacity = '1';
        setTimeout(() => {
            t.style.transform = 'translateY(80px)';
            t.style.opacity = '0';
        }, 3000);
    }

    // ── Search Debounce ───────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let debounceTimer;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    }
})();
