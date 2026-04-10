<?php
/**
 * Finance — Budget Indicators
 * File: modules/Dashboard/views/finance-budget-indicators.php
 * Route: /admin/finance-budget-indicators
 *
 * President & Super Admin only.
 * One indicator set per session per academic year.
 * Pools must sum to 100% of the base balance.
 */
$pageTitle          = 'Budget Indicators';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

$canManage  = $isSuperAdmin || hasPermission($userPermissions, 'finance.manage_indicators');
$isPresident= $isSuperAdmin || ($currentUser->role_name ?? '') === 'President';
$defYear    = date('Y') . '-' . (date('Y') + 1);
$defSession = $currentUser->session_type ?? 'day';
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<style>
.pool-row { background:#f9fafb; border:1px solid #e7edf3; border-radius:8px; padding:12px 14px; margin-bottom:8px; }
.pool-color-dot { width:14px; height:14px; border-radius:50%; display:inline-block; flex-shrink:0; }
.ind-status-badge { font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; padding:3px 10px; border-radius:20px; }
.pct-total-bar { height:10px; border-radius:6px; background:#e7edf3; overflow:hidden; }
.pct-total-fill { height:100%; background:#377dff; transition:width .3s; border-radius:6px; }
</style>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm">
        <h1 class="page-header-title"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Budget Indicators</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
          <li class="breadcrumb-item active">Budget Indicators</li>
        </ol></nav>
      </div>
      <div class="col-auto d-flex gap-2 align-items-center">
        <?php if($isSuperAdmin): ?>
        <select id="sessFilter" class="form-select form-select-sm" style="width:150px" onchange="loadIndicator()">
          <option value="day">☀️ Day CEP</option>
          <option value="weekend">🌙 Weekend CEP</option>
        </select>
        <?php endif; ?>
        <select id="yearFilter" class="form-select form-select-sm" style="width:130px" onchange="loadIndicator()">
          <?php for($y=date('Y');$y<=date('Y')+1;$y++): $lbl="$y-".($y+1); ?>
          <option value="<?=$lbl?>" <?=$lbl===$defYear?'selected':''?>><?=$lbl?></option>
          <?php endfor; ?>
        </select>
        <?php if($canManage): ?>
        <button id="btnCreateInd" class="btn btn-primary btn-sm" onclick="openCreateModal()" style="display:none">
          <i class="bi bi-plus-lg me-1"></i> Create Indicators
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Loading -->
  <div id="indLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

  <!-- No indicator state -->
  <div id="indEmpty" style="display:none">
    <div class="card"><div class="card-body text-center py-5">
      <i class="bi bi-pie-chart fs-1 text-muted"></i>
      <h4 class="text-muted mt-3">No Budget Indicators Set</h4>
      <p class="text-muted">No budget indicators have been defined for the selected session and year.</p>
      <?php if($canManage): ?>
      <button class="btn btn-primary" onclick="openCreateModal()"><i class="bi bi-plus-lg me-1"></i>Create Budget Indicators</button>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- Indicator Display -->
  <div id="indDisplay" style="display:none">

    <!-- Indicator Header Card -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-lg avatar-soft-primary avatar-circle">
                <span class="avatar-initials fs-4"><i class="bi bi-pie-chart-fill"></i></span>
              </div>
              <div>
                <h4 class="mb-1" id="indTitle">—</h4>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                  <span id="indStatus" class="ind-status-badge"></span>
                  <span class="text-muted small">Base Balance: <strong id="indBalance">—</strong></span>
                  <span class="text-muted small">Lock Date: <strong id="indLockDate">—</strong></span>
                  <span class="text-muted small">Created by: <strong id="indCreatedBy">—</strong></span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-auto d-flex gap-2">
            <div id="indActions"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pools Grid -->
    <div class="row g-3 mb-4" id="poolsGrid"></div>

    <!-- Pools Distribution Chart -->
    <div class="row g-3 mb-4">
      <div class="col-md-5">
        <div class="card h-100">
          <div class="card-header"><h5 class="card-header-title">Pool Distribution</h5></div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <canvas id="poolChart" style="max-height:250px"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-header-title">Quarterly Budgets</h5>
            <?php if($canManage): ?>
            <button class="btn btn-sm btn-primary" id="btnNewQuarter" onclick="goToBudget()" style="display:none">
              <i class="bi bi-plus-lg me-1"></i>New Quarter Budget
            </button>
            <?php endif; ?>
          </div>
          <div class="card-body" id="quartersPanel">
            <p class="text-muted text-center py-3">Confirm indicators to unlock quarterly budgets.</p>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- CREATE / EDIT INDICATOR MODAL -->
<div class="modal fade" id="indModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white" style="padding-bottom: 15px;">
        <h5 class="modal-title" id="indModalTitle"><i class="bi bi-pie-chart-fill me-2"></i>Create Budget Indicators</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editIndId">

        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <label class="form-label fw-semibold">CEP Session <span class="text-danger">*</span></label>
            <select id="iSession" class="form-select">
              <option value="day">☀️ Day CEP</option>
              <option value="weekend">🌙 Weekend CEP</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
            <input type="text" id="iYear" class="form-control" placeholder="2026-2027">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Base Balance (RWF) <span class="text-danger">*</span></label>
            <input type="number" id="iBalance" class="form-control" min="0" step="100" placeholder="200000">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">President Edit Lock Date</label>
            <input type="date" id="iLockDate" class="form-control">
            <div class="form-text">After this date only Super Admin can modify.</div>
          </div>
        </div>

        <h6 class="fw-semibold mb-3">Budget Pools <span class="text-muted small fw-normal">(must total exactly 100%)</span>
          <span class="ms-3 text-muted small">Total: <strong id="pctTotalDisplay" class="text-danger">0%</strong></span>
        </h6>
        <div class="pct-total-bar mb-3"><div id="pctTotalBar" class="pct-total-fill" style="width:0%"></div></div>

        <div id="poolsEditor"></div>

        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addPoolRow()">
          <i class="bi bi-plus-circle me-1"></i>Add Pool
        </button>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSaveInd" class="btn btn-primary" onclick="saveIndicator()">
          <i class="bi bi-check-lg me-1"></i>Save Indicators
        </button>
      </div>
    </div>
  </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
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
let currentInd = null;
let poolChart = null;
let poolColors = ['#377dff','#00c9a7','#e8a838','#ed4c78','#6f42c1','#20c997','#fd7e14','#6c757d'];

const DEFAULT_POOLS = [
    {pool_name:'Evangelism',        pool_slug:'evangelism',     pool_type:'department', color:'#377dff', percentage:30},
    {pool_name:'Social Affairs',    pool_slug:'social_affairs', pool_type:'department', color:'#00c9a7', percentage:25},
    {pool_name:'Choir',             pool_slug:'choir',          pool_type:'department', color:'#e8a838', percentage:20},
    {pool_name:'Internal Processes',pool_slug:'internal',       pool_type:'internal',   color:'#ed4c78', percentage:15},
    {pool_name:'Reserve Pool',      pool_slug:'reserve',        pool_type:'reserve',    color:'#6f42c1', percentage:10},
];

// Global functions that need to be accessible from onclick
window.openCreateModal = function() {
    console.log('openCreateModal called');
    
    const editIndId = document.getElementById('editIndId');
    if (editIndId) editIndId.value = '';
    
    const modalTitle = document.getElementById('indModalTitle');
    if (modalTitle) modalTitle.innerHTML = '<i class="bi bi-plus-lg me-2"></i>Create Budget Indicators';
    
    const sessionEl = document.getElementById('iSession');
    if (sessionEl) sessionEl.value = getSession();
    
    const yearEl = document.getElementById('iYear');
    if (yearEl) yearEl.value = getYear() || (new Date().getFullYear() + '-' + (new Date().getFullYear() + 1));
    
    const balanceEl = document.getElementById('iBalance');
    if (balanceEl) balanceEl.value = '';
    
    const lockDateEl = document.getElementById('iLockDate');
    if (lockDateEl) lockDateEl.value = '';
    
    const poolsEditor = document.getElementById('poolsEditor');
    if (poolsEditor) {
        poolsEditor.innerHTML = '';
        DEFAULT_POOLS.forEach(p => addPoolRow(p));
    }
    
    updatePctTotal();
    
    const modal = new bootstrap.Modal(document.getElementById('indModal'));
    modal.show();
};

window.openEditModal = async function(id) {
    console.log('openEditModal called with id:', id);
    
    document.getElementById('editIndId').value = id;
    document.getElementById('indModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Budget Indicators';
    
    const poolsEditor = document.getElementById('poolsEditor');
    poolsEditor.innerHTML = '';
    
    try {
        const res = await fetch(`${BASE_URL}/api/finance?action=indicator_get_by_id&id=${id}`, {
            credentials: 'include'
        });
        const data = await res.json();
        
        if (!data.data) {
            showToast('Indicator not found', 'danger');
            return;
        }
        
        const ind = data.data;
        document.getElementById('iSession').value = ind.cep_session;
        document.getElementById('iYear').value = ind.academic_year;
        document.getElementById('iBalance').value = ind.base_balance;
        document.getElementById('iLockDate').value = ind.lock_date || '';
        
        (ind.pools || []).forEach(p => addPoolRow(p));
        updatePctTotal();
        
        const modal = new bootstrap.Modal(document.getElementById('indModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error loading indicator for edit:', error);
        showToast('Error loading indicator: ' + error.message, 'danger');
    }
};

window.saveIndicator = async function() {
    const btn = document.getElementById('btnSaveInd');
    const id = document.getElementById('editIndId').value;
    
    const pcts = [...document.querySelectorAll('.pool-pct')].map(i => parseFloat(i.value) || 0);
    const total = pcts.reduce((a, b) => a + b, 0);
    
    if (Math.abs(total - 100) > 0.01) {
        showToast(`Pools must total 100%. Current: ${total.toFixed(2)}%`, 'warning');
        return;
    }
    
    const pools = [...document.querySelectorAll('.pool-editor-row')].map(row => ({
        pool_name: row.querySelector('.pool-name').value.trim(),
        pool_slug: row.querySelector('.pool-slug').value.trim(),
        pool_type: row.querySelector('.pool-type').value,
        percentage: parseFloat(row.querySelector('.pool-pct').value) || 0,
        color: row.querySelector('.pool-color').value,
    }));
    
    if (pools.some(p => !p.pool_name)) {
        showToast('All pools must have a name', 'warning');
        return;
    }
    
    const payload = {
        cep_session: document.getElementById('iSession').value,
        academic_year: document.getElementById('iYear').value.trim(),
        base_balance: parseFloat(document.getElementById('iBalance').value) || 0,
        lock_date: document.getElementById('iLockDate').value || null,
        pools,
    };
    
    if (id) payload.id = parseInt(id);
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    
    const action = id ? 'indicator_update' : 'indicator_create';
    
    try {
        const res = await fetch(`${BASE_URL}/api/finance?action=${action}`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('indModal')).hide();
            showToast('Budget indicators saved!', 'success');
            loadIndicator();
        } else {
            showToast(data.message || 'Save failed', 'danger');
        }
    } catch (error) {
        console.error('Error saving indicator:', error);
        showToast('Error: ' + error.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Indicators';
    }
};

window.confirmIndicator = async function(id) {
    const r = await Swal.fire({
        title: 'Confirm Budget Indicators?',
        text: 'Once confirmed, quarterly budgets can be created. You can still edit pools until the lock date.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00c9a7',
        confirmButtonText: 'Yes, Confirm'
    });
    
    if (!r.isConfirmed) return;
    
    try {
        const res = await fetch(`${BASE_URL}/api/finance?action=indicator_confirm`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        
        if (data.success) {
            showToast('Indicators confirmed!', 'success');
            loadIndicator();
        } else {
            showToast(data.message || 'Failed', 'danger');
        }
    } catch (error) {
        console.error('Error confirming indicator:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.deleteIndicator = async function(id) {
    console.log('Delete indicator called with id:', id);
    
    const result = await Swal.fire({
        title: 'Delete Budget Indicators?',
        text: 'All related pools will be deleted. Approved quarterly budgets will block deletion.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) {
        console.log('Delete cancelled by user');
        return;
    }
    
    console.log('User confirmed delete, sending request...');
    
    try {
        const response = await fetch(`${BASE_URL}/api/finance?action=indicator_delete`, {
            method: 'POST',
            credentials: 'include',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        // First get the response text
        const responseText = await response.text();
        console.log('Raw delete response:', responseText);
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON response:', responseText);
            showToast('Server returned an invalid response', 'danger');
            return;
        }
        
        if (data.success) {
            showToast('Budget indicators deleted successfully!', 'success');
            // Reload the page to reflect changes
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete indicators', 'danger');
        }
    } catch (error) {
        console.error('Error deleting indicator:', error);
        showToast('Error: ' + error.message, 'danger');
    }
};

window.addPoolRow = function(data = {}) {
    const idx = document.querySelectorAll('.pool-editor-row').length;
    const color = data.color || poolColors[idx % poolColors.length];
    const types = ['department', 'internal', 'reserve', 'other'];
    
    const div = document.createElement('div');
    div.className = 'pool-row pool-editor-row d-flex align-items-center gap-2 flex-wrap';
    div.innerHTML = `
        <input type="color" value="${color}" class="form-control form-control-color pool-color" style="width:40px;height:36px;padding:2px" title="Color" onchange="updatePctTotal()">
        <input type="text" class="form-control pool-name" style="width:170px" placeholder="Pool name *" value="${esc(data.pool_name || '')}">
        <input type="text" class="form-control pool-slug" style="width:130px" placeholder="slug (auto)" value="${esc(data.pool_slug || '')}">
        <select class="form-select pool-type" style="width:130px">
            ${types.map(t => `<option value="${t}"${(data.pool_type || 'department') === t ? ' selected' : ''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('')}
        </select>
        <div class="input-group" style="width:120px">
            <input type="number" class="form-control pool-pct text-end" min="0" max="100" step="0.01" placeholder="%" value="${data.percentage || ''}" oninput="updatePctTotal()">
            <span class="input-group-text">%</span>
        </div>
        <span class="text-muted small pool-alloc-display">= RWF 0</span>
        <button type="button" class="btn btn-xs btn-ghost-danger ms-auto" onclick="this.closest('.pool-editor-row').remove();updatePctTotal()"><i class="bi bi-trash"></i></button>`;
    
    document.getElementById('poolsEditor').appendChild(div);
    
    const nameInput = div.querySelector('.pool-name');
    const slugInput = div.querySelector('.pool-slug');
    
    nameInput.addEventListener('input', e => {
        if (!slugInput.value || slugInput.dataset.manual !== '1') {
            slugInput.value = e.target.value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
        }
    });
    
    slugInput.addEventListener('input', e => {
        e.target.dataset.manual = '1';
    });
    
    updatePctTotal();
};

window.updatePctTotal = function() {
    const pcts = [...document.querySelectorAll('.pool-pct')].map(i => parseFloat(i.value) || 0);
    const total = pcts.reduce((a, b) => a + b, 0);
    const base = parseFloat(document.getElementById('iBalance')?.value || 0);
    
    const totalDisplay = document.getElementById('pctTotalDisplay');
    if (totalDisplay) {
        totalDisplay.textContent = total.toFixed(2) + '%';
        totalDisplay.className = Math.abs(total - 100) < 0.01 ? 'text-success fw-bold' : 'text-danger fw-bold';
    }
    
    const totalBar = document.getElementById('pctTotalBar');
    if (totalBar) {
        totalBar.style.width = Math.min(100, total) + '%';
        totalBar.style.background = Math.abs(total - 100) < 0.01 ? '#00c9a7' : '#377dff';
    }
    
    // Update alloc display per row
    document.querySelectorAll('.pool-editor-row').forEach(row => {
        const pct = parseFloat(row.querySelector('.pool-pct').value) || 0;
        const allocDisplay = row.querySelector('.pool-alloc-display');
        if (allocDisplay) {
            allocDisplay.textContent = '= ' + fmtMoney(Math.round(base * pct / 100));
        }
    });
};

window.goToBudget = function() {
    if (!currentInd) return;
    window.location.href = `${BASE_URL}/admin/finance-budget?indicator_id=${currentInd.id}`;
};

// Helper functions
function getSession() {
    if (typeof IS_SA !== 'undefined' && IS_SA) {
        const sessEl = document.getElementById('sessFilter');
        return sessEl ? sessEl.value : DEF_SESSION;
    }
    return typeof DEF_SESSION !== 'undefined' ? DEF_SESSION : 'day';
}

function getYear() {
    const yearEl = document.getElementById('yearFilter');
    return yearEl ? yearEl.value : '';
}

async function loadIndicator() {
    console.log('Loading indicator...');
    
    document.getElementById('indLoading').style.display = 'block';
    document.getElementById('indEmpty').style.display = 'none';
    document.getElementById('indDisplay').style.display = 'none';
    
    const btnCreate = document.getElementById('btnCreateInd');
    if (btnCreate) btnCreate.style.display = 'none';
    
    try {
        const session = getSession();
        const year = getYear();
        console.log('Fetching with:', { session, year });
        
        const res = await fetch(`${BASE_URL}/api/finance?action=indicator_get&session=${session}&year=${year}`, {
            credentials: 'include'
        });
        const data = await res.json();
        console.log('API Response:', data);
        
        document.getElementById('indLoading').style.display = 'none';
        
        if (!data.data) {
            console.log('No indicator found - showing empty state');
            document.getElementById('indEmpty').style.display = 'block';
            if (typeof CAN_MANAGE !== 'undefined' && CAN_MANAGE && btnCreate) {
                btnCreate.style.display = 'inline-block';
            }
            return;
        }
        
        currentInd = data.data;
        renderIndicator(currentInd);
        
    } catch (error) {
        console.error('Error loading indicator:', error);
        document.getElementById('indLoading').style.display = 'none';
        if (typeof showToast === 'function') {
            showToast('Error loading indicator: ' + error.message, 'danger');
        }
    }
}

function renderIndicator(ind) {
    console.log('Rendering indicator:', ind);
    document.getElementById('indDisplay').style.display = 'block';
    
    document.getElementById('indTitle').textContent = `${ind.cep_session.toUpperCase()} — ${ind.academic_year}`;
    document.getElementById('indBalance').textContent = fmtMoney(ind.base_balance);
    document.getElementById('indLockDate').textContent = ind.lock_date || 'No limit';
    document.getElementById('indCreatedBy').textContent = ind.created_by_name || '—';
    
    const statusColors = { draft: 'warning', confirmed: 'success', locked: 'danger' };
    const sc = statusColors[ind.status] || 'secondary';
    const statusEl = document.getElementById('indStatus');
    statusEl.className = `ind-status-badge bg-soft-${sc} text-${sc}`;
    statusEl.textContent = ind.status.charAt(0).toUpperCase() + ind.status.slice(1);
    
    // Action buttons
    const actDiv = document.getElementById('indActions');
    actDiv.innerHTML = '';
    
    const canEdit = (typeof IS_SA !== 'undefined' && IS_SA) || 
                    (typeof IS_PRESIDENT !== 'undefined' && IS_PRESIDENT && ind.status !== 'locked' && (!ind.lock_date || new Date(ind.lock_date) >= new Date()));
    
    if (canEdit && typeof CAN_MANAGE !== 'undefined' && CAN_MANAGE) {
        actDiv.innerHTML += `<button class="btn btn-outline-secondary btn-sm" onclick="openEditModal(${ind.id})"><i class="bi bi-pencil me-1"></i>Edit</button> `;
    }
    if (typeof IS_PRESIDENT !== 'undefined' && IS_PRESIDENT && ind.status === 'draft') {
        actDiv.innerHTML += `<button class="btn btn-success btn-sm" onclick="confirmIndicator(${ind.id})"><i class="bi bi-check-circle me-1"></i>Confirm Indicators</button> `;
    }
    if (typeof IS_SA !== 'undefined' && IS_SA) {
        actDiv.innerHTML += `<button class="btn btn-outline-danger btn-sm" onclick="deleteIndicator(${ind.id})"><i class="bi bi-trash me-1"></i>Delete</button>`;
    }
    
    // Pools grid
    const pools = ind.pools || [];
    document.getElementById('poolsGrid').innerHTML = pools.map(p => {
        const pct = parseFloat(p.percentage);
        return `<div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="pool-color-dot" style="background:${esc(p.color)};width:18px;height:18px;border-radius:4px;"></div>
                        <span class="fw-bold">${esc(p.pool_name)}</span>
                        <span class="badge bg-soft-secondary text-secondary ms-auto">${esc(p.pool_type)}</span>
                    </div>
                    <div class="row text-center g-2 mb-2">
                        <div class="col-6"><div class="fw-bold fs-4 text-primary">${pct}%</div><small class="text-muted">Allocation</small></div>
                        <div class="col-6"><div class="fw-bold fs-4 text-success">${fmtMoney(p.allocated_amount)}</div><small class="text-muted">RWF Amount</small></div>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar" style="width:${pct}%;background:${esc(p.color)}"></div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
    
    renderPoolChart(pools);
    loadQuarters(ind.id, ind.status);
    
    // Show new quarter button if confirmed/locked
    const btnQ = document.getElementById('btnNewQuarter');
    if (btnQ) {
        btnQ.style.display = (ind.status !== 'draft' && typeof CAN_MANAGE !== 'undefined' && CAN_MANAGE) ? '' : 'none';
    }
}

function renderPoolChart(pools) {
    const ctx = document.getElementById('poolChart').getContext('2d');
    if (poolChart) poolChart.destroy();
    if (pools.length === 0) return;
    
    poolChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: pools.map(p => p.pool_name),
            datasets: [{
                data: pools.map(p => parseFloat(p.percentage)),
                backgroundColor: pools.map(p => p.color),
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 } }
                }
            }
        }
    });
}

async function loadQuarters(indicatorId, indStatus) {
    const panel = document.getElementById('quartersPanel');
    if (indStatus === 'draft') {
        panel.innerHTML = '<p class="text-muted text-center py-3">Confirm indicators to unlock quarterly budgets.</p>';
        return;
    }
    
    panel.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    
    try {
        const res = await fetch(`${BASE_URL}/api/finance?action=quarters_list&indicator_id=${indicatorId}`, {
            credentials: 'include'
        });
        const data = await res.json();
        const list = data.data || [];
        const stColors = { draft: 'warning', suspended: 'danger', approved: 'success' };
        
        panel.innerHTML = list.length
            ? list.map(q => `<div class="d-flex align-items-center gap-2 py-2 border-bottom">
                <span class="badge bg-soft-secondary text-secondary">${esc(q.quarter)}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${esc(q.budget_name)}</div>
                    <small class="text-muted">RWF ${Number(q.line_allocated || 0).toLocaleString()} allocated &bull; ${Number(q.line_spent || 0).toLocaleString()} spent</small>
                    ${q.status === 'draft' ? `<span class="badge bg-warning text-dark ms-2" title="Expires in ${14 - parseInt(q.days_in_draft || 0)} days">Draft (${14 - parseInt(q.days_in_draft || 0)}d left)</span>` : ''}
                    ${q.status === 'suspended' ? `<span class="badge bg-danger ms-2">Suspended</span>` : ''}
                </div>
                <span class="badge bg-soft-${stColors[q.status] || 'secondary'} text-${stColors[q.status] || 'secondary'}">${esc(q.status)}</span>
                <a href="${BASE_URL}/admin/finance-budget?id=${q.id}" class="btn btn-xs btn-ghost-primary">Open</a>
            </div>`).join('')
            + (typeof CAN_MANAGE !== 'undefined' && CAN_MANAGE ? `<div class="pt-3"><a href="${BASE_URL}/admin/finance-budget?indicator_id=${indicatorId}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Add Quarter</a></div>` : '')
            : `<p class="text-muted text-center py-3">No quarterly budgets yet.</p>`
            + (typeof CAN_MANAGE !== 'undefined' && CAN_MANAGE ? `<a href="${BASE_URL}/admin/finance-budget?indicator_id=${indicatorId}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Add First Quarter</a>` : '');
    } catch (error) {
        console.error('Error loading quarters:', error);
        panel.innerHTML = '<p class="text-danger text-center">Error loading quarters</p>';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // Make sure BASE_URL is defined
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?= BASE_URL ?>';
    }
    
    // Define constants from PHP
    window.CAN_MANAGE = <?= json_encode($canManage) ?>;
    window.IS_SA = <?= json_encode($isSuperAdmin) ?>;
    window.IS_PRESIDENT = <?= json_encode($isPresident) ?>;
    window.DEF_SESSION = '<?= htmlspecialchars($defSession) ?>';
    
    // Add input listener for balance
    const balanceInput = document.getElementById('iBalance');
    if (balanceInput) {
        balanceInput.addEventListener('input', updatePctTotal);
    }
    
    // Load indicator data
    loadIndicator();
});
</script>
</body></html>