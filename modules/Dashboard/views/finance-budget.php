<?php
/**
 * Finance — Quarterly Budget Management
 * File: modules/Dashboard/views/finance-budget.php
 * Route: /admin/finance-budget
 *
 * Manages Q1/Q2/Q3 budgets linked to budget indicators.
 * ?indicator_id=X  → creates new quarter for that indicator
 * ?id=X            → opens/edits existing quarter
 */
$pageTitle          = 'Budget Management';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

$canManage  = $isSuperAdmin || hasPermission($userPermissions, 'finance.manage_budget');
$isPresident= $isSuperAdmin || ($currentUser->role_name ?? '') === 'President';
$defSession = $currentUser->session_type ?? 'day';
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<style>
.act-row{background:#f9fafb;border:1px solid #e7edf3;border-radius:8px;padding:10px 12px;margin-bottom:6px;}
.pool-section-header{background:var(--pool-color,#377dff);color:#fff;padding:8px 14px;border-radius:8px 8px 0 0;margin-top:16px;}
.status-timeline{display:flex;gap:0;align-items:stretch;}
.st-step{flex:1;text-align:center;padding:10px 6px;border:1px solid #e7edf3;font-size:.78rem;position:relative;}
.st-step.active{background:#377dff;color:#fff;border-color:#377dff;font-weight:700;}
.st-step.done{background:#00c9a7;color:#fff;border-color:#00c9a7;}
.st-step.warn{background:#e8a838;color:#fff;border-color:#e8a838;}
.st-step:first-child{border-radius:8px 0 0 8px;}
.st-step:last-child{border-radius:0 8px 8px 0;}
</style>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm">
        <h1 class="page-header-title"><i class="bi bi-calendar3-range me-2 text-primary"></i>Quarterly Budget</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
          <li class="breadcrumb-item"><a href="<?=url('admin/finance-budget-indicators')?>">Indicators</a></li>
          <li class="breadcrumb-item active" id="bcLabel">Budget</li>
        </ol></nav>
      </div>
      <div class="col-auto d-flex gap-2" id="pageActions"></div>
    </div>
  </div>

  <div id="mainLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
  <div id="mainContent" style="display:none"></div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
// Define all functions in global scope first
function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
}

function fmtMoney(v) {
    return 'RWF ' + Number(v || 0).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

// Global variables
let indicatorData = null;
let quarterData = null;

// Constants from PHP
const BASE = '<?=BASE_URL?>';
const API = BASE + '/api/finance';
const CAN_MANAGE = <?=json_encode($canManage)?>;
const IS_SA = <?=json_encode($isSuperAdmin)?>;
const IS_PRESIDENT = <?=json_encode($isPresident)?>;
const DEF_SESSION = '<?=htmlspecialchars($defSession)?>';

// Get URL parameters
const params = new URLSearchParams(location.search);
const indicatorId = parseInt(params.get('indicator_id')) || 0;
const quarterId = parseInt(params.get('id')) || 0;

// ========== GLOBAL FUNCTIONS ==========

window.approveQuarter = async function(id) {
    const r = await Swal.fire({
        title: 'Approve this Budget?',
        text: 'Once approved, it cannot be edited. Only Super Admin can delete it.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00c9a7',
        confirmButtonText: 'Approve'
    });
    if (!r.isConfirmed) return;
    
    try {
        const res = await fetch(`${API}?action=quarter_approve`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Budget approved!', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Failed', 'danger');
        }
    } catch (error) {
        console.error('Error approving quarter:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.reactivateQuarter = async function(id) {
    try {
        const res = await fetch(`${API}?action=quarter_reactivate`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Budget reactivated!', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Failed', 'danger');
        }
    } catch (error) {
        console.error('Error reactivating quarter:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.deleteQuarter = async function(id) {
    const r = await Swal.fire({
        title: 'Delete Budget?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#de4437',
        confirmButtonText: 'Delete'
    });
    if (!r.isConfirmed) return;
    
    try {
        const res = await fetch(`${API}?action=quarter_delete`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Deleted', 'success');
            setTimeout(() => location.href = `${BASE}/admin/finance-budget`, 800);
        } else {
            showToast(data.message || 'Failed', 'danger');
        }
    } catch (error) {
        console.error('Error deleting quarter:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.toggleEditMode = function() {
    const sec = document.getElementById('editFormSection');
    if (!sec) return;
    sec.style.display = sec.style.display === 'none' ? '' : 'none';
    if (sec.style.display !== 'none') {
        // (re)build edit activities from current quarterData
        buildEditActivities(indicatorData?.pools || [], quarterData?.activities || []);
        sec.scrollIntoView({ behavior: 'smooth' });
    }
};

window.addActRow = function(poolId) {
    const cont = document.getElementById('acts_' + poolId);
    if (cont) cont.insertAdjacentHTML('beforeend', actRowHtml(poolId));
};

window.addNewActRow = function(poolId) {
    document.getElementById('new_acts_' + poolId)?.insertAdjacentHTML('beforeend', actRowHtml(poolId));
};

window.saveQuarterEdit = async function(id) {
    const acts = [...document.querySelectorAll('.edit-act-row')].map(row => ({
        pool_id: parseInt(row.dataset.pool),
        activity_name: row.querySelector('.act-name').value.trim(),
        allocated_amount: parseFloat(row.querySelector('.act-amount').value) || 0,
        is_external: row.querySelector('.act-external').checked ? 1 : 0,
        notes: row.querySelector('.act-notes').value.trim() || null,
    })).filter(a => a.activity_name);

    const payload = {
        id,
        budget_name: document.getElementById('editBudgetName').value.trim(),
        notes: document.getElementById('editNotes').value.trim(),
        activities: acts
    };
    
    try {
        const res = await fetch(`${API}?action=quarter_update`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast('Budget updated!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Save failed', 'danger');
        }
    } catch (error) {
        console.error('Error saving quarter edit:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.saveNewQuarter = async function() {
    const name = document.getElementById('newBudgetName')?.value.trim();
    const q = document.getElementById('newQuarter')?.value;
    
    if (!name || !q) {
        showToast('Budget name and quarter are required', 'warning');
        return;
    }

    const acts = [...document.querySelectorAll('.edit-act-row')].map(row => ({
        pool_id: parseInt(row.dataset.pool),
        activity_name: row.querySelector('.act-name').value.trim(),
        allocated_amount: parseFloat(row.querySelector('.act-amount').value) || 0,
        is_external: row.querySelector('.act-external').checked ? 1 : 0,
        notes: row.querySelector('.act-notes').value.trim() || null,
    })).filter(a => a.activity_name);

    const payload = {
        indicator_id: indicatorData.id,
        cep_session: indicatorData.cep_session,
        academic_year: indicatorData.academic_year,
        quarter: q,
        budget_name: name,
        notes: document.getElementById('newNotes')?.value.trim() || null,
        activities: acts,
    };
    
    try {
        const res = await fetch(`${API}?action=quarter_create`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast('Budget created!', 'success');
            setTimeout(() => location.href = `${BASE}/admin/finance-budget?id=${data.id}`, 800);
        } else {
            showToast(data.message || 'Create failed', 'danger');
        }
    } catch (error) {
        console.error('Error saving new quarter:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

// ========== HELPER FUNCTIONS ==========

function actRowHtml(poolId, data = {}) {
    return `<div class="act-row d-flex align-items-center gap-2 flex-wrap edit-act-row" data-pool="${poolId}">
        <input type="text" class="form-control act-name" style="min-width:200px;flex:1" placeholder="Activity name *" value="${esc(data.activity_name || '')}">
        <div class="input-group" style="width:160px">
            <span class="input-group-text">RWF</span>
            <input type="number" class="form-control act-amount" min="0" step="100" placeholder="Amount" value="${data.allocated_amount || ''}">
        </div>
        <div class="form-check form-switch ms-1" title="External (Family/Choir own funds, tracking only)">
            <input class="form-check-input act-external" type="checkbox" ${data.is_external ? 'checked' : ''}>
            <label class="form-check-label small">External</label>
        </div>
        <input type="text" class="form-control act-notes" style="width:170px" placeholder="Notes" value="${esc(data.notes || '')}">
        <button type="button" class="btn btn-xs btn-ghost-danger" onclick="this.closest('.edit-act-row').remove()"><i class="bi bi-trash"></i></button>
    </div>`;
}

function buildEditActivities(pools, existingActs) {
    const container = document.getElementById('editActivitiesContainer');
    if (!container) return;
    
    container.innerHTML = pools.map(p => {
        const pActs = existingActs.filter(a => a.pool_id == p.id);
        return `<div class="mb-3">
            <div class="pool-section-header" style="--pool-color:${esc(p.color)}">
                <strong>${esc(p.pool_name)}</strong> &bull; Pool Budget: ${fmtMoney(p.allocated_amount)}
            </div>
            <div class="border border-top-0 rounded-bottom p-3">
                <div id="acts_${p.id}">
                    ${pActs.map(a => actRowHtml(p.id, a)).join('')}
                </div>
                <button type="button" class="btn btn-xs btn-outline-secondary mt-2" onclick="addActRow(${p.id})">
                    <i class="bi bi-plus-circle me-1"></i>Add Activity
                </button>
            </div>
        </div>`;
    }).join('');
}

// ========== LOADING FUNCTIONS ==========

async function loadExistingQuarter(id) {
    try {
        const res = await fetch(`${API}?action=quarter_get&id=${id}`, { credentials: 'include' });
        const data = await res.json();
        quarterData = data.data;
        
        if (!quarterData) {
            document.getElementById('mainContent').innerHTML = '<div class="alert alert-danger">Budget not found.</div>';
            return;
        }
        
        // Load indicator for pool info
        const iRes = await fetch(`${API}?action=indicator_get_by_id&id=${quarterData.indicator_id}`, { credentials: 'include' });
        indicatorData = (await iRes.json()).data;
        renderQuarterView();
    } catch (error) {
        console.error('Error loading quarter:', error);
        document.getElementById('mainContent').innerHTML = '<div class="alert alert-danger">Error loading budget.</div>';
    }
}

async function loadIndicatorAndShowForm(indId) {
    try {
        const res = await fetch(`${API}?action=indicator_get_by_id&id=${indId}`, { credentials: 'include' });
        indicatorData = (await res.json()).data;
        
        if (!indicatorData || indicatorData.status === 'draft') {
            document.getElementById('mainContent').innerHTML = '<div class="alert alert-warning">Budget indicators must be confirmed before creating quarterly budgets.</div>';
            return;
        }
        
        const defQ = params.get('quarter') || 'Q1';
        renderCreateForm(defQ);
    } catch (error) {
        console.error('Error loading indicator:', error);
        document.getElementById('mainContent').innerHTML = '<div class="alert alert-danger">Error loading indicator.</div>';
    }
}

async function showAllQuarters() {
    // Get indicator for session/current year then list quarters
    const year = new Date().getFullYear();
    try {
        const res = await fetch(`${API}?action=indicator_get&session=${DEF_SESSION}&year=${year}-${year + 1}`, { credentials: 'include' });
        const data = await res.json();
        const mc = document.getElementById('mainContent');
        
        if (!data.data) {
            mc.innerHTML = `<div class="card"><div class="card-body text-center py-5">
                <i class="bi bi-calendar3 fs-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Budget Indicators Found</h4>
                <p class="text-muted">Budget indicators must be created and confirmed before quarterly budgets can be managed.</p>
                <a href="${BASE}/admin/finance-budget-indicators" class="btn btn-primary">Go to Budget Indicators</a>
            </div></div>`;
            return;
        }
        
        indicatorData = data.data;
        const qRes = await fetch(`${API}?action=quarters_list&indicator_id=${indicatorData.id}`, { credentials: 'include' });
        const qData = await qRes.json();
        const quarters = qData.data || [];

        const stColors = { draft: 'warning', suspended: 'danger', approved: 'success' };
        const daysLeft = q => Math.max(0, 14 - parseInt(q.days_in_draft || 0));

        mc.innerHTML = `
            <div class="alert alert-info border-0 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>Budget indicators for <strong>${esc(indicatorData.cep_session.toUpperCase())} — ${esc(indicatorData.academic_year)}</strong>
                        &bull; Base Balance: <strong>${fmtMoney(indicatorData.base_balance)}</strong>
                        &bull; Status: <span class="fw-bold">${esc(indicatorData.status)}</span>
                    </div>
                    <a href="${BASE}/admin/finance-budget-indicators" class="btn btn-warning btn-xs ms-auto">Manage Indicators</a>
                </div>
            </div>

            ${['Q1', 'Q2', 'Q3'].map(q => {
                const existing = quarters.find(qr => qr.quarter === q);
                if (existing) {
                    const sc = stColors[existing.status] || 'secondary';
                    const pct = existing.total_allocated > 0 ? Math.min(100, Math.round(existing.line_spent / existing.total_allocated * 100)) : 0;
                    return `<div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-soft-${sc} avatar-circle"><span class="avatar-initials fw-bold">${q}</span></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${esc(existing.budget_name)}</div>
                                    <div class="d-flex gap-3 mt-1 flex-wrap">
                                        <small class="text-muted">Allocated: <strong>${fmtMoney(existing.line_allocated)}</strong></small>
                                        <small class="text-muted">Spent: <strong>${fmtMoney(existing.line_spent)}</strong></small>
                                        ${existing.status === 'draft' ? `<small class="text-warning fw-semibold">${daysLeft(existing)} day(s) to approve</small>` : ''}
                                    </div>
                                    <div class="progress mt-2" style="height:5px"><div class="progress-bar bg-${sc}" style="width:${pct}%"></div></div>
                                </div>
                                <span class="badge bg-soft-${sc} text-${sc}">${esc(existing.status)}</span>
                                <div class="d-flex gap-1">
                                    <a href="${BASE}/admin/finance-budget?id=${existing.id}" class="btn btn-sm btn-primary">Open</a>
                                    ${IS_PRESIDENT && existing.status === 'draft' ? `<button class="btn btn-sm btn-success" onclick="approveQuarter(${existing.id})"><i class="bi bi-check-circle me-1"></i>Approve</button>` : ''}
                                    ${IS_SA && existing.status === 'suspended' ? `<button class="btn btn-sm btn-warning" onclick="reactivateQuarter(${existing.id})">Reactivate</button>` : ''}
                                    ${IS_SA ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteQuarter(${existing.id})"><i class="bi bi-trash"></i></button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>`;
                } else {
                    return `<div class="card mb-3" style="border-style:dashed">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-soft-secondary avatar-circle"><span class="avatar-initials fw-bold">${q}</span></div>
                                <div class="flex-grow-1"><div class="text-muted">Quarter ${q} — Not yet created</div></div>
                                ${CAN_MANAGE && indicatorData.status !== 'draft' ? `<a href="${BASE}/admin/finance-budget?indicator_id=${indicatorData.id}&quarter=${q}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Create ${q} Budget</a>` : ''}
                            </div>
                        </div>
                    </div>`;
                }
            }).join('')}`;

        if (CAN_MANAGE && indicatorData.status !== 'draft') {
            document.getElementById('pageActions').innerHTML = `<a href="${BASE}/admin/finance-budget-indicators" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pie-chart me-1"></i>Indicators</a>`;
        }
    } catch (error) {
        console.error('Error loading quarters:', error);
        document.getElementById('mainContent').innerHTML = '<div class="alert alert-danger">Error loading data.</div>';
    }
}

function renderQuarterView() {
    const q = quarterData;
    const ind = indicatorData;
    const st = { draft: 'warning', suspended: 'danger', approved: 'success' }[q.status] || 'secondary';
    const pools = ind?.pools || [];
    const acts = q.activities || [];

    document.getElementById('bcLabel').textContent = `${q.quarter} — ${q.budget_name}`;

    // Actions
    const pa = document.getElementById('pageActions');
    pa.innerHTML = `<a href="${BASE}/admin/finance-budget" class="btn btn-ghost-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>`;
    if (IS_PRESIDENT && q.status === 'draft') pa.innerHTML += ` <button class="btn btn-success btn-sm" onclick="approveQuarter(${q.id})"><i class="bi bi-check-circle me-1"></i>Approve Budget</button>`;
    if (IS_SA && q.status === 'suspended') pa.innerHTML += ` <button class="btn btn-warning btn-sm" onclick="reactivateQuarter(${q.id})"><i class="bi bi-arrow-clockwise me-1"></i>Reactivate</button>`;
    if (q.status !== 'approved' && CAN_MANAGE) pa.innerHTML += ` <button class="btn btn-primary btn-sm" onclick="toggleEditMode()"><i class="bi bi-pencil me-1"></i>Edit</button>`;
    if (IS_SA) pa.innerHTML += ` <button class="btn btn-outline-danger btn-sm" onclick="deleteQuarter(${q.id})"><i class="bi bi-trash me-1"></i>Delete</button>`;

    // Status timeline
    const steps = [{ label: 'Draft', key: 'draft' }, { label: 'Suspended', key: 'suspended' }, { label: 'Approved', key: 'approved' }];
    const timeline = steps.map(s => {
        const cls = q.status === s.key ? 'active' : (['approved'].indexOf(q.status) > -1 && s.key === 'approved' ? 'done' : q.status === 'suspended' && s.key === 'suspended' ? 'warn' : '');
        return `<div class="st-step ${cls}"><i class="bi bi-${s.key === 'draft' ? 'pencil' : s.key === 'suspended' ? 'pause-circle' : 'check-circle'} me-1"></i>${s.label}</div>`;
    }).join('');

    // Activities grouped by pool
    const byPool = {};
    acts.forEach(a => { if (!byPool[a.pool_id]) byPool[a.pool_id] = []; byPool[a.pool_id].push(a); });

    const poolBlocks = pools.map(p => {
        const pActs = byPool[p.id] || [];
        const totAlloc = pActs.filter(a => !a.is_external).reduce((s, a) => s + parseFloat(a.allocated_amount), 0);
        const totSpent = pActs.reduce((s, a) => s + parseFloat(a.spent_amount), 0);
        const pct = totAlloc > 0 ? Math.min(100, Math.round(totSpent / totAlloc * 100)) : 0;
        
        return `<div class="mb-4">
            <div class="pool-section-header d-flex justify-content-between align-items-center" style="--pool-color:${esc(p.color)}">
                <span class="fw-bold">${esc(p.pool_name)}</span>
                <span class="small">Pool: ${fmtMoney(p.allocated_amount)} &bull; Budget: ${fmtMoney(totAlloc)}</span>
            </div>
            <div class="border border-top-0 rounded-bottom p-3">
                ${!pActs.length ? '<p class="text-muted mb-0">No activities planned for this pool.</p>' :
                pActs.map(a => `<div class="act-row d-flex align-items-center gap-2">
                    ${a.is_external ? '<span class="badge bg-soft-info text-info" title="External/tracked only">Ext</span>' : '<span style="width:28px;display:inline-block;"></span>'}
                    <span class="flex-grow-1 fw-semibold">${esc(a.activity_name)}</span>
                    <span class="text-muted small">${fmtMoney(a.allocated_amount)} allocated</span>
                    <span class="text-danger small">${fmtMoney(a.spent_amount)} spent</span>
                    ${a.notes ? `<span class="text-muted small" title="${esc(a.notes)}"><i class="bi bi-info-circle"></i></span>` : ''}
                </div>`).join('')}
                ${pActs.filter(a => !a.is_external).length ? `<div class="d-flex justify-content-between mt-2 pt-2 border-top small text-muted">
                    <span>Total Allocated: ${fmtMoney(totAlloc)}</span>
                    <span>Total Spent: ${fmtMoney(totSpent)}</span>
                    <span>Utilisation: <strong class="${pct >= 90 ? 'text-danger' : pct >= 60 ? 'text-warning' : 'text-success'}">${pct}%</strong></span>
                </div><div class="progress mt-1" style="height:5px"><div class="progress-bar ${pct >= 90 ? 'bg-danger' : pct >= 60 ? 'bg-warning' : 'bg-success'}" style="width:${pct}%"></div></div>` : ''}
            </div>
        </div>`;
    }).join('');

    const totAlloc = acts.filter(a => !a.is_external).reduce((s, a) => s + parseFloat(a.allocated_amount), 0);
    const totSpent = acts.reduce((s, a) => s + parseFloat(a.spent_amount), 0);

    document.getElementById('mainContent').innerHTML = `
        <!-- Summary -->
        <div class="row g-3 mb-4">
            ${[
                { label: 'Total Allocated', val: fmtMoney(totAlloc), color: 'primary', icon: 'bi-pie-chart' },
                { label: 'Total Spent', val: fmtMoney(totSpent), color: 'danger', icon: 'bi-cash-stack' },
                { label: 'Remaining', val: fmtMoney(totAlloc - totSpent), color: 'success', icon: 'bi-wallet2' },
                { label: 'Status', val: `<span class="badge bg-soft-${st} text-${st} fs-6">${q.status}</span>`, color: st, icon: 'bi-flag' },
            ].map(c => `<div class="col-sm-3"><div class="card"><div class="card-body text-center">
                <i class="bi ${c.icon} fs-3 text-${c.color} d-block mb-1"></i>
                <div class="fw-bold fs-4">${c.val}</div><small class="text-muted">${c.label}</small>
            </div></div></div>`).join('')}
        </div>

        <!-- Status timeline -->
        <div class="card mb-4"><div class="card-body py-2">
            <div class="status-timeline">${timeline}</div>
            ${q.status === 'draft' ? `<div class="text-center mt-2"><small class="text-warning"><i class="bi bi-clock me-1"></i>Draft expires in <strong>${Math.max(0, 14 - parseInt(q.days_in_draft || 0))}</strong> day(s). President must approve before then.</small></div>` : ''}
            ${q.status === 'suspended' ? `<div class="text-center mt-2"><small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>This budget was not approved within 2 weeks and was suspended. Super Admin can reactivate it.</small></div>` : ''}
        </div></div>

        <!-- Meta info -->
        <div class="card mb-4"><div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><small class="text-muted d-block">Budget Name</small><strong>${esc(q.budget_name)}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">Quarter</small><strong>${esc(q.quarter)}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">Session</small><strong>${esc(q.cep_session)}</strong></div>
                <div class="col-md-3"><small class="text-muted d-block">Created by</small><strong>${esc(q.created_by_name || '—')}</strong></div>
                <div class="col-md-2"><small class="text-muted d-block">Created</small><strong>${(q.created_at || '').substr(0, 10)}</strong></div>
                ${q.approved_by_name ? `<div class="col-md-3"><small class="text-muted d-block">Approved by</small><strong>${esc(q.approved_by_name)}</strong> on ${(q.approved_at || '').substr(0, 10)}</div>` : ''}
                ${q.notes ? `<div class="col-12"><small class="text-muted d-block">Notes</small>${esc(q.notes)}</div>` : ''}
            </div>
        </div></div>

        <!-- Activities by pool -->
        <div class="card"><div class="card-body">${poolBlocks || '<p class="text-muted text-center py-3">No activities added yet.</p>'}</div></div>

        <!-- Edit form (hidden by default) -->
        <div id="editFormSection" style="display:none" class="mt-4">
            <div class="card"><div class="card-header bg-primary text-white"><h5 class="card-header-title text-white">Edit Budget Activities</h5></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label fw-semibold">Budget Name</label>
                    <input type="text" id="editBudgetName" class="form-control" value="${esc(q.budget_name)}"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Notes</label>
                    <textarea id="editNotes" class="form-control" rows="2">${esc(q.notes || '')}</textarea></div>
                <div id="editActivitiesContainer"></div>
                <button class="btn btn-success mt-3" onclick="saveQuarterEdit(${q.id})"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                <button class="btn btn-ghost-secondary mt-3 ms-2" onclick="toggleEditMode()">Cancel</button>
            </div></div>
        </div>`;

    if (q.status !== 'approved') buildEditActivities(pools, acts);
}

function renderCreateForm(defQ = 'Q1') {
    const pools = indicatorData?.pools || [];
    document.getElementById('bcLabel').textContent = `New ${defQ} Budget`;
    document.getElementById('pageActions').innerHTML = `<a href="${BASE}/admin/finance-budget" class="btn btn-ghost-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>`;

    document.getElementById('mainContent').innerHTML = `
        <div class="card">
            <div class="card-header bg-primary text-white"><h5 class="card-header-title text-white">Create Quarterly Budget</h5></div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Budget Name <span class="text-danger">*</span></label>
                        <input type="text" id="newBudgetName" class="form-control" placeholder="e.g. Q1 Budget 2026">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Quarter <span class="text-danger">*</span></label>
                        <select id="newQuarter" class="form-select">
                            <option value="Q1" ${defQ === 'Q1' ? 'selected' : ''}>Q1 — Trimester 1</option>
                            <option value="Q2" ${defQ === 'Q2' ? 'selected' : ''}>Q2 — Trimester 2</option>
                            <option value="Q3" ${defQ === 'Q3' ? 'selected' : ''}>Q3 — Trimester 3</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Notes</label>
                        <input type="text" id="newNotes" class="form-control" placeholder="Optional notes">
                    </div>
                </div>

                <div class="alert alert-info border-0 mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    After creation, the President has <strong>2 weeks</strong> to approve this budget. If not approved, it will be auto-suspended.
                </div>

                <div id="createActivitiesContainer">
                    ${pools.map(p => `
                        <div class="mb-3">
                            <div class="pool-section-header" style="--pool-color:${esc(p.color)}">
                                <strong>${esc(p.pool_name)}</strong> &bull; Available: ${fmtMoney(p.allocated_amount)}
                                <span class="float-end small">Type: ${esc(p.pool_type)}</span>
                            </div>
                            <div class="border border-top-0 rounded-bottom p-3">
                                <div id="new_acts_${p.id}"></div>
                                <button type="button" class="btn btn-xs btn-outline-secondary mt-2" onclick="addNewActRow(${p.id})">
                                    <i class="bi bi-plus-circle me-1"></i>Add Activity to ${esc(p.pool_name)}
                                </button>
                            </div>
                        </div>`).join('')}
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" onclick="saveNewQuarter()"><i class="bi bi-check-lg me-1"></i>Create Budget</button>
                    <a href="${BASE}/admin/finance-budget" class="btn btn-ghost-secondary">Cancel</a>
                </div>
            </div>
        </div>`;
}

// ========== INITIALIZATION ==========

document.addEventListener('DOMContentLoaded', async () => {
    console.log('DOM loaded, initializing...');
    
    if (quarterId) {
        await loadExistingQuarter(quarterId);
    } else if (indicatorId) {
        await loadIndicatorAndShowForm(indicatorId);
    } else {
        await showAllQuarters();
    }
    
    document.getElementById('mainLoading').style.display = 'none';
    document.getElementById('mainContent').style.display = '';
});
</script>
</body></html>