/**
 * Session Lock Manager
 * File: js/admin/session-lock.js
 *
 * Replaces admin-session-timeout.js + session-config.js with a single,
 * cohesive module.
 *
 * Behaviour:
 *   - Tracks user activity (click, keydown, scroll, mousemove)
 *   - Shows a countdown toast 5 minutes before timeout
 *   - On timeout: shows the lock-screen overlay (NOT a page redirect)
 *   - Unlock: re-authenticates via /api/auth?action=login, re-issues cookie
 *   - Cancel: redirects to login page
 *   - Heartbeat every 5 min so the server-side JWT stays fresh
 *   - Silent token validation every 1 min
 */

(function () {
    'use strict';

    
    const BASE_URL = '<?= BASE_URL ?>';

    /* ── Configuration ──────────────────────────────────────────────────── */
    const CFG = {
        timeoutMs:   30 * 60 * 1000,   // 30 min inactivity → lock
        warnMs:       5 * 60 * 1000,   // show warning 5 min before lock
        heartbeatMs:  5 * 60 * 1000,   // re-issue JWT every 5 min
        validateMs:       60 * 1000,   // silent token check every 1 min
        loginUrl:    BASE_URL + '/membership',       // redirect on cancel
        authApi:     BASE_URL + '/api/auth',
    };

    /* ── State ──────────────────────────────────────────────────────────── */
    let activityTimer = null;
    let warnTimer     = null;
    let warnToastEl   = null;
    let warnCountdownInterval = null;
    let warnSecondsLeft = 0;
    let isLocked = false;

    /* ── Activity tracking ──────────────────────────────────────────────── */
    function resetTimers() {
        if (isLocked) return;            // don't reset while locked

        clearTimeout(activityTimer);
        clearTimeout(warnTimer);
        dismissWarnToast();

        // Warning fires 5 min before timeout
        warnTimer = setTimeout(showWarnToast, CFG.timeoutMs - CFG.warnMs);

        // Lock fires at timeout
        activityTimer = setTimeout(lockScreen, CFG.timeoutMs);
    }

    ['click', 'keydown', 'scroll', 'mousemove', 'touchstart'].forEach(evt =>
        document.addEventListener(evt, resetTimers, { passive: true })
    );

    /* ── Warning toast ──────────────────────────────────────────────────── */
    function showWarnToast() {
        if (isLocked) return;

        warnSecondsLeft = Math.floor(CFG.warnMs / 1000);   // 300

        if (!warnToastEl) {
            warnToastEl = document.createElement('div');
            warnToastEl.id = 'sloWarnToast';
            warnToastEl.innerHTML = `
                <div class="slwt-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="slwt-body">
                    <strong>Session expiring soon</strong>
                    <span id="sloWarnCountdown">5:00</span>
                </div>
                <button class="slwt-stay" onclick="window.__sloExtend()">Stay logged in</button>
            `;
            document.body.appendChild(warnToastEl);

            // Inject toast styles once
            if (!document.getElementById('sloWarnToastStyle')) {
                const s = document.createElement('style');
                s.id = 'sloWarnToastStyle';
                s.textContent = `
                    #sloWarnToast {
                        position: fixed; bottom: 24px; right: 24px; z-index: 99998;
                        display: flex; align-items: center; gap: 12px;
                        background: #1e2022; color: #fff;
                        padding: 14px 18px; border-radius: 12px;
                        box-shadow: 0 8px 32px rgba(0,0,0,.35);
                        font-size: 14px; min-width: 300px;
                        animation: sloSlideUp .3s cubic-bezier(.22,1,.36,1) both;
                    }
                    .slwt-icon { font-size: 22px; color: #f5a623; }
                    .slwt-body { flex: 1; display: flex; flex-direction: column; gap: 2px; }
                    #sloWarnCountdown { font-size: 18px; font-weight: 700; color: #f5a623; }
                    .slwt-stay {
                        background: #377dff; color: #fff; border: none;
                        padding: 8px 14px; border-radius: 8px; font-size: 13px;
                        font-weight: 600; cursor: pointer; white-space: nowrap;
                    }
                    .slwt-stay:hover { background: #2b6de0; }
                `;
                document.head.appendChild(s);
            }
        }

        warnToastEl.style.display = 'flex';

        // Tick every second
        clearInterval(warnCountdownInterval);
        warnCountdownInterval = setInterval(() => {
            warnSecondsLeft--;
            const m = Math.floor(warnSecondsLeft / 60);
            const s = String(warnSecondsLeft % 60).padStart(2, '0');
            const el = document.getElementById('sloWarnCountdown');
            if (el) el.textContent = `${m}:${s}`;
            if (warnSecondsLeft <= 0) clearInterval(warnCountdownInterval);
        }, 1000);
    }

    function dismissWarnToast() {
        clearInterval(warnCountdownInterval);
        if (warnToastEl) warnToastEl.style.display = 'none';
    }

    /* Exposed so inline onclick on the toast can call it */
    window.__sloExtend = function () {
        extendSession();
        resetTimers();
    };

    /* ── Lock screen ────────────────────────────────────────────────────── */
    function lockScreen() {
        isLocked = true;
        dismissWarnToast();
        clearTimeout(activityTimer);
        clearTimeout(warnTimer);

        const overlay = document.getElementById('sessionLockOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            // Focus the password input
            setTimeout(() => {
                const inp = document.getElementById('sloPasswordInput');
                if (inp) inp.focus();
            }, 350);
        } else {
            // Fallback: if partial wasn't included, go to login
            window.location.href = CFG.loginUrl;
        }
    }

    /* ── Unlock logic (called by the lock-screen partial's button) ───────── */
    window.sloUnlock = async function () {
        const inp = document.getElementById('sloPasswordInput');
        const errEl = document.getElementById('sloError');
        const btn  = document.getElementById('sloUnlockBtn');
        const lbl  = document.getElementById('sloUnlockLabel');

        if (!inp) return;

        const password = inp.value.trim();
        if (!password) {
            sloShowError('Please enter your password.');
            return;
        }

        // Grab stored email from the page data attribute (set in admin-base.php)
        const email = document.getElementById('sessionLockOverlay')?.dataset.email || '';
        if (!email) {
            // No email stored → full re-login
            window.location.href = CFG.loginUrl;
            return;
        }

        // Loading state
        btn.disabled = true;
        lbl.textContent = 'Verifying…';
        if (errEl) errEl.style.display = 'none';
        inp.classList.remove('error');

        try {
            const res = await fetch(`${CFG.authApi}?action=login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ identifier: email, password }),
            });
            const data = await res.json();

            if (data.success) {
                // Unlocked  ✓
                isLocked = false;
                inp.value = '';
                document.getElementById('sessionLockOverlay').style.display = 'none';
                lbl.textContent = 'Unlock & Continue';
                btn.disabled = false;
                resetTimers();
            } else {
                sloShowError(data.message || 'Incorrect password. Please try again.');
                inp.classList.add('error');
                lbl.textContent = 'Unlock & Continue';
                btn.disabled = false;
                inp.focus();
            }
        } catch (err) {
            sloShowError('Connection error. Please check your network.');
            lbl.textContent = 'Unlock & Continue';
            btn.disabled = false;
        }
    };

    window.sloCancel = function () {
        window.location.href = CFG.loginUrl;
    };

    window.sloTogglePassword = function () {
        const inp  = document.getElementById('sloPasswordInput');
        const icon = document.getElementById('sloEyeIcon');
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye';
        }
    };

    // Allow pressing Enter inside the lock screen to unlock
    document.addEventListener('keydown', function (e) {
        if (isLocked && e.key === 'Enter') {
            const overlay = document.getElementById('sessionLockOverlay');
            if (overlay && overlay.style.display !== 'none') {
                window.sloUnlock();
            }
        }
    });

    function sloShowError(msg) {
        const el = document.getElementById('sloError');
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
    }

    /* ── Heartbeat (refresh JWT without user action) ────────────────────── */
    async function extendSession() {
        try {
            const res = await fetch(`${CFG.authApi}?action=heartbeat`, {
                method: 'POST',
                credentials: 'include',
            });
            const data = await res.json();
            if (!data.success) console.warn('[SLO] Heartbeat failed:', data.message);
        } catch (e) {
            console.warn('[SLO] Heartbeat error:', e);
        }
    }

    setInterval(extendSession, CFG.heartbeatMs);

    /* ── Silent token validation ─────────────────────────────────────────── */
    setInterval(async function () {
        if (isLocked) return;
        try {
            const res  = await fetch(`${CFG.authApi}?action=validate`, { credentials: 'include' });
            const data = await res.json();
            if (!data.success) lockScreen();
        } catch (e) { /* network blip, ignore */ }
    }, CFG.validateMs);

    /* ── Bootstrap ──────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', resetTimers);

})();