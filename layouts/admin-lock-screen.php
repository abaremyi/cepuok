<?php
/**
 * Session Lock Screen Overlay
 * File: layouts/admin-lock-screen.php
 *
 * Include this once inside any admin page's <body>.
 * It is hidden by default. session-lock.js controls visibility.
 *
 * When the session timeout fires:
 *   - This overlay appears (blurs the page behind it)
 *   - User enters their password to unlock → re-authenticates silently
 *   - On success the overlay hides and they continue where they were
 *   - On "Cancel / Sign out" → redirect to login page
 */
?>

<!-- ══════════════════════════════════════════════════════════════
     SESSION LOCK SCREEN  (hidden until JS triggers it)
═══════════════════════════════════════════════════════════════ -->
<div id="sessionLockOverlay" style="display:none;" aria-modal="true" role="dialog" aria-label="Session locked">
    <div class="slo-backdrop"></div>

    <div class="slo-card">
        <!-- Avatar / User info -->
        <div class="slo-avatar-wrap">
            <?php if (!empty($currentUser->photo)): ?>
                <img class="slo-avatar-img"
                     src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($currentUser->photo) ?>"
                     alt="<?= htmlspecialchars($currentUser->firstname) ?>">
            <?php else: ?>
                <div class="slo-avatar-initials">
                    <?= strtoupper(
                        substr($currentUser->firstname ?? 'U', 0, 1) .
                        substr($currentUser->lastname  ?? '',  0, 1)
                    ) ?>
                </div>
            <?php endif; ?>
            <div class="slo-lock-badge"><i class="bi bi-lock-fill"></i></div>
        </div>

        <h4 class="slo-name">
            <?= htmlspecialchars(($currentUser->firstname ?? '') . ' ' . ($currentUser->lastname ?? '')) ?>
        </h4>
        <p class="slo-email"><?= htmlspecialchars($currentUser->email ?? '') ?></p>

        <p class="slo-message">
            Your session was locked due to inactivity.<br>
            Enter your password to continue.
        </p>

        <!-- Unlock form -->
        <div class="slo-form-group">
            <div class="slo-input-wrap">
                <input type="password"
                       id="sloPasswordInput"
                       class="slo-input"
                       placeholder="Enter your password"
                       autocomplete="current-password">
                <button type="button" class="slo-eye-btn" onclick="sloTogglePassword()" title="Show/hide password">
                    <i class="bi bi-eye" id="sloEyeIcon"></i>
                </button>
            </div>
            <div id="sloError" class="slo-error" style="display:none;"></div>
        </div>

        <div class="slo-actions">
            <button id="sloUnlockBtn" class="slo-btn-unlock" onclick="sloUnlock()">
                <i class="bi bi-unlock-fill me-2"></i>
                <span id="sloUnlockLabel">Unlock &amp; Continue</span>
            </button>
            <button class="slo-btn-cancel" onclick="sloCancel()">
                <i class="bi bi-box-arrow-right me-2"></i>
                Sign in as different user
            </button>
        </div>
    </div>
</div>

<style>
/* ── Lock screen overlay ─────────────────────────────────────────────────── */
#sessionLockOverlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex !important;        /* overridden to none by JS */
    align-items: center;
    justify-content: center;
}
#sessionLockOverlay[style*="display:none"],
#sessionLockOverlay[style*="display: none"] {
    display: none !important;
}

.slo-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(10, 15, 30, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.slo-card {
    position: relative;
    z-index: 1;
    background: #fff;
    border-radius: 20px;
    padding: 40px 36px 32px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0,0,0,0.28);
    animation: sloSlideUp 0.35s cubic-bezier(.22,1,.36,1) both;
}
@keyframes sloSlideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* Avatar */
.slo-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}
.slo-avatar-img,
.slo-avatar-initials {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #e5e7ef;
    object-fit: cover;
}
.slo-avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #377dff 0%, #5b5bd6 100%);
    color: #fff;
    font-size: 28px;
    font-weight: 700;
}
.slo-lock-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #dc3545;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border: 2px solid #fff;
}

/* Text */
.slo-name   { font-size: 18px; font-weight: 700; color: #1e2022; margin: 0 0 4px; }
.slo-email  { font-size: 13px; color: #8c98a4; margin: 0 0 16px; }
.slo-message { font-size: 14px; color: #677788; margin: 0 0 24px; line-height: 1.5; }

/* Input */
.slo-form-group { margin-bottom: 20px; text-align: left; }
.slo-input-wrap { position: relative; }
.slo-input {
    width: 100%;
    padding: 12px 44px 12px 16px;
    border: 1.5px solid #d0d5dd;
    border-radius: 10px;
    font-size: 15px;
    outline: none;
    transition: border-color .2s;
    background: #f9fafc;
    box-sizing: border-box;
}
.slo-input:focus { border-color: #377dff; background: #fff; }
.slo-input.error { border-color: #dc3545; }
.slo-eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #8c98a4;
    cursor: pointer;
    padding: 4px;
    font-size: 16px;
}
.slo-error {
    color: #dc3545;
    font-size: 13px;
    margin-top: 6px;
    padding: 8px 12px;
    background: #fff5f5;
    border-radius: 6px;
    border-left: 3px solid #dc3545;
}

/* Buttons */
.slo-actions { display: flex; flex-direction: column; gap: 10px; }
.slo-btn-unlock {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #377dff 0%, #5b5bd6 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
}
.slo-btn-unlock:hover   { opacity: .9; transform: translateY(-1px); }
.slo-btn-unlock:active  { transform: translateY(0); }
.slo-btn-unlock:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.slo-btn-cancel {
    width: 100%;
    padding: 11px;
    border: 1.5px solid #d0d5dd;
    border-radius: 10px;
    background: transparent;
    color: #677788;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s, color .2s;
}
.slo-btn-cancel:hover { background: #f3f4f6; color: #1e2022; }
</style>