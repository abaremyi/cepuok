<?php
/**
 * Fund Requests — New Workflow
 * File: modules/Dashboard/views/finance-fund-requests.php
 * Route: /admin/finance-fund-requests
 *
 * Status flow: draft → to_president → rejected_by_president → to_finance → completed
 * - Sender: sees own drafts + own submitted requests with status
 * - President: sees all to_president + all to_finance + completed (NOT drafts)
 * - Accountant (disburse_funds): sees to_finance + completed
 * - Super Admin: sees everything
 */
$pageTitle          = 'Fund Requests';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

$isPresident = $isSuperAdmin || ($currentUser->role_name ?? '') === 'President';
$canDisburse = $isSuperAdmin || hasPermission($userPermissions, 'finance.disburse_funds');
$canRequest  = hasPermission($userPermissions, 'finance.fund_requests');
$defSession  = $currentUser->session_type ?? 'day';
$userId      = (int)($currentUser->user_id ?? $currentUser->id ?? 0);
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<style>
.fr-card{border-left:4px solid #e7edf3;transition:box-shadow .15s;}
.fr-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
.fr-card.stage-draft{border-left-color:#adb5bd;}
.fr-card.stage-to_president{border-left-color:#e8a838;}
.fr-card.stage-rejected_by_president{border-left-color:#de4437;}
.fr-card.stage-to_finance{border-left-color:#377dff;}
.fr-card.stage-completed{border-left-color:#00c9a7;}
.stage-badge{font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:3px 10px;border-radius:20px;}
.comment-bubble{background:#f5f8ff;border-left:3px solid #377dff;padding:10px 14px;border-radius:0 8px 8px 0;margin-bottom:8px;font-size:.85rem;}
.comment-bubble.own{background:#f0fff8;border-left-color:#00c9a7;}
</style>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <!-- Page Header -->
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col-sm">
        <h1 class="page-header-title"><i class="bi bi-send me-2 text-primary"></i>Fund Requests</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
          <li class="breadcrumb-item active">Fund Requests</li>
        </ol></nav>
      </div>
      <div class="col-auto d-flex gap-2 align-items-center flex-wrap">
        <?php if($isSuperAdmin): ?>
        <select id="sessFilter" class="form-select form-select-sm" style="width:150px" onchange="loadRequests()">
          <option value="day">☀️ Day CEP</option>
          <option value="weekend">🌙 Weekend CEP</option>
        </select>
        <?php endif; ?>
        <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Search requests…" style="width:200px" oninput="debounceSearch()">
        <?php if($canRequest): ?>
        <button class="btn btn-primary btn-sm" onclick="openNewRequest()"><i class="bi bi-plus-lg me-1"></i>New Request</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Pipeline KPIs -->
  <div class="row g-3 mb-4" id="pipelineRow">
    <?php
    $stages = [
      ['key'=>'draft',                 'label'=>'My Drafts',     'color'=>'secondary','icon'=>'bi-pencil-square'],
      ['key'=>'to_president',          'label'=>'With President','color'=>'warning',  'icon'=>'bi-hourglass-split'],
      ['key'=>'rejected_by_president', 'label'=>'Rejected',      'color'=>'danger',   'icon'=>'bi-x-circle'],
      ['key'=>'to_finance',            'label'=>'Approved',      'color'=>'primary',  'icon'=>'bi-check-circle'],
      ['key'=>'completed',             'label'=>'Disbursed',     'color'=>'success',  'icon'=>'bi-cash-stack'],
    ];
    foreach($stages as $s): ?>
    <div class="col">
      <div class="card card-hover-shadow h-100" style="cursor:pointer" onclick="filterStage('<?=$s['key']?>')">
        <div class="card-body text-center py-2">
          <i class="bi <?=$s['icon']?> fs-4 text-<?=$s['color']?>"></i>
          <div class="fw-bold fs-4 text-<?=$s['color']?>" id="pipe_<?=$s['key']?>">0</div>
          <small class="text-muted"><?=$s['label']?></small>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Stage filter tabs -->
  <div class="d-flex gap-2 mb-3 flex-wrap" id="stageTabs">
    <button class="btn btn-sm btn-primary active-tab" data-stage="" onclick="filterStage('',this)">All</button>
    <?php foreach($stages as $s): ?>
    <button class="btn btn-sm btn-outline-<?=$s['color']?>" data-stage="<?=$s['key']?>" onclick="filterStage('<?=$s['key']?>',this)"><?=$s['label']?></button>
    <?php endforeach; ?>
  </div>

  <!-- Requests list -->
  <div id="requestsContainer">
    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
  </div>

  <!-- Pagination -->
  <div id="pagination" class="d-flex justify-content-between align-items-center py-2"></div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- ── NEW REQUEST MODAL ─────────────────────────────────────── -->
<div class="modal fade" id="frModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="frModalTitle"><i class="bi bi-send me-2"></i>New Fund Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="frEditId">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Request Title <span class="text-danger">*</span></label>
            <input type="text" id="frTitle" class="form-control" placeholder="Short, descriptive title">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Priority</label>
            <select id="frPriority" class="form-select">
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Needed By</label>
            <input type="date" id="frNeededBy" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Budget Indicator (Pool) <span class="text-danger">*</span></label>
            <select id="frIndicator" class="form-select" onchange="onIndicatorChange()">
              <option value="">— Select Pool —</option>
            </select>
            <div id="frPoolBudget" class="form-text"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Activity (optional)</label>
            <select id="frActivity" class="form-select">
              <option value="">— Select Activity —</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Amount Requested (RWF) <span class="text-danger">*</span></label>
            <input type="number" id="frAmount" class="form-control" min="1" step="100" placeholder="0" oninput="checkBudget()">
            <div id="frBudgetWarn" class="form-text text-danger" style="display:none"><i class="bi bi-exclamation-triangle me-1"></i>Exceeds available pool budget</div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Description / Justification <span class="text-danger">*</span></label>
            <textarea id="frDesc" class="form-control" rows="3" placeholder="Explain the purpose and need for this fund…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="btnSaveDraft" class="btn btn-outline-secondary" onclick="saveFR('draft')"><i class="bi bi-floppy me-1"></i>Save Draft</button>
        <button id="btnSubmitFR"  class="btn btn-primary" onclick="saveFR('submit')"><i class="bi bi-send me-1"></i>Save & Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- ── DETAIL / REVIEW MODAL ──────────────────────────────────── -->
<div class="modal fade" id="frDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" id="frDetailHeader">
        <h5 class="modal-title" id="frDetailTitle">Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="frDetailBody">
        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer" id="frDetailFooter"></div>
    </div>
  </div>
</div>

<!-- ── PRESIDENT REJECT MODAL ────────────────────────────────── -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Reject Fund Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="rejectId">
        <label class="form-label fw-semibold">Rejection Reason / Comment <span class="text-danger">*</span></label>
        <textarea id="rejectReason" class="form-control" rows="4" placeholder="Explain clearly why this request is rejected…"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" onclick="submitReject()">Reject Request</button>
      </div>
    </div>
  </div>
</div>

<!-- ── DISBURSE MODAL ────────────────────────────────────────── -->
<div class="modal fade" id="disburseModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Disburse Funds</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="disburseId">
        <div class="alert alert-info border-0 mb-3" id="disburseInfo"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Amount (RWF) <span class="text-danger">*</span></label>
            <input type="number" id="disburseAmount" class="form-control" step="100">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Payment Method</label>
            <select id="disburseMethod" class="form-select">
              <option value="cash">Cash</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Reference No.</label>
            <input type="text" id="disburseRef" class="form-control" placeholder="Receipt / MoMo / Cheque no.">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Recipient Name</label>
            <input type="text" id="disburseRecip" class="form-control" placeholder="Person receiving funds">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea id="disburseNotes" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" onclick="confirmDisburse()"><i class="bi bi-check-lg me-1"></i>Confirm Disbursement</button>
      </div>
    </div>
  </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
'use strict';
const BASE        = '<?=BASE_URL?>';
const API         = BASE + '/api/finance';
const IS_SA       = <?=json_encode($isSuperAdmin)?>;
const IS_PRESIDENT= <?=json_encode($isPresident)?>;
const CAN_DISBURSE= <?=json_encode($canDisburse)?>;
const CAN_REQUEST = <?=json_encode($canRequest)?>;
const MY_ID       = <?=json_encode($userId)?>;
const MY_SESSION  = '<?=htmlspecialchars($defSession)?>';

let currentPage=1, currentStage='', currentSearch='';
let poolsForForm=[], activitiesForForm=[];

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function fmtMoney(v){return 'RWF '+Number(v||0).toLocaleString();}
function getSession(){ return IS_SA?(document.getElementById('sessFilter')?.value||MY_SESSION):MY_SESSION; }

const STAGE_META = {
  draft:                {label:'Draft',          color:'secondary',icon:'bi-pencil-square'},
  to_president:         {label:'With President', color:'warning',  icon:'bi-hourglass-split'},
  rejected_by_president:{label:'Rejected',       color:'danger',   icon:'bi-x-circle'},
  to_finance:           {label:'Approved',       color:'primary',  icon:'bi-check-circle'},
  completed:            {label:'Disbursed',      color:'success',  icon:'bi-cash-stack'},
};
const PRIORITY_COLORS={low:'success',medium:'info',high:'warning',urgent:'danger'};

// ── Pipeline & list ──────────────────────────────────────────
async function loadPipeline(){
  const res  = await fetch(`${API}?action=fund_pipeline&session=${getSession()}`,{credentials:'include'});
  const data = await res.json();
  const map  = data.data||{};
  Object.keys(STAGE_META).forEach(s=>{
    const el = document.getElementById('pipe_'+s); if(el) el.textContent = map[s]?.cnt||0;
  });
}

async function loadRequests(page=1){
  currentPage = page;
  document.getElementById('requestsContainer').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
  const params = new URLSearchParams({action:'fund_requests',session:getSession(),page,per_page:15});
  if (currentStage)  params.set('stage',  currentStage);
  if (currentSearch) params.set('search', currentSearch);
  const res  = await fetch(`${API}?${params}`,{credentials:'include'});
  const data = await res.json();
  renderList(data.data||[], data.total||0, data.pages||1);
}

function renderList(list, total, pages){
  const cont = document.getElementById('requestsContainer');
  if (!list.length) { cont.innerHTML='<div class="card"><div class="card-body text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No fund requests found.</div></div>'; renderPager(total,pages); return; }
  const prColors={low:'success',medium:'info',high:'warning',urgent:'danger'};
  cont.innerHTML = list.map(r=>{
    const sm  = STAGE_META[r.stage]||{label:r.stage,color:'secondary',icon:'bi-dot'};
    const mine= parseInt(r.requested_by)===MY_ID;
    return `<div class="card fr-card stage-${esc(r.stage)} mb-2">
      <div class="card-body py-3">
        <div class="row align-items-center">
          <div class="col-auto">
            <span class="badge bg-soft-secondary text-secondary fw-bold">${esc(r.request_number||'#')}</span>
          </div>
          <div class="col">
            <div class="fw-semibold">${esc(r.title)}</div>
            <div class="d-flex gap-2 flex-wrap mt-1">
              ${r.indicator_name?`<small class="badge bg-soft-info text-info">${esc(r.indicator_name)}</small>`:''}
              ${r.quarter?`<small class="text-muted">${esc(r.quarter)}</small>`:''}
              <small class="text-muted">By: <strong>${esc(r.requested_by_name||'—')}</strong></small>
              <small class="text-muted">${(r.submitted_at||r.created_at||'').substr(0,10)}</small>
            </div>
          </div>
          <div class="col-auto text-end">
            <div class="fw-bold text-primary">${fmtMoney(r.amount_requested)}</div>
            ${r.amount_approved?`<small class="text-success">Approved: ${fmtMoney(r.amount_approved)}</small><br>`:''}
            <span class="stage-badge bg-soft-${sm.color} text-${sm.color}"><i class="bi ${sm.icon} me-1"></i>${sm.label}</span>
            <span class="badge bg-soft-${prColors[r.priority]||'secondary'} text-${prColors[r.priority]||'secondary'} ms-1">${esc(r.priority)}</span>
          </div>
          <div class="col-auto">
            <button class="btn btn-xs btn-ghost-primary" onclick="openDetail(${r.id})"><i class="bi bi-eye me-1"></i>View</button>
            ${mine && (r.stage==='draft'||r.stage==='rejected_by_president')?`<button class="btn btn-xs btn-ghost-secondary" onclick="openEdit(${r.id})"><i class="bi bi-pencil me-1"></i>Edit</button>`:''}
            ${mine && r.stage==='draft'?`<button class="btn btn-xs btn-primary" onclick="submitDraft(${r.id})"><i class="bi bi-send me-1"></i>Submit</button>`:''}
            ${IS_PRESIDENT && r.stage==='to_president'?`
              <button class="btn btn-xs btn-success" onclick="presidApprove(${r.id},${parseFloat(r.amount_requested)})"><i class="bi bi-check me-1"></i>Approve</button>
              <button class="btn btn-xs btn-danger"  onclick="openReject(${r.id})"><i class="bi bi-x me-1"></i>Reject</button>`:''}
            ${CAN_DISBURSE && r.stage==='to_finance'?`<button class="btn btn-xs btn-success" onclick="openDisburse(${r.id},'${esc(r.title)}',${parseFloat(r.amount_approved||r.amount_requested)})"><i class="bi bi-cash me-1"></i>Disburse</button>`:''}
            ${(mine&&(r.stage==='draft'||r.stage==='rejected_by_president'))||IS_SA?`<button class="btn btn-xs btn-ghost-danger" onclick="deleteReq(${r.id})"><i class="bi bi-trash"></i></button>`:''}
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
  renderPager(total,pages);
}

function renderPager(total,pages){
  const el = document.getElementById('pagination');
  el.innerHTML = `<span class="text-muted small">${total} request(s)</span>
    <nav><ul class="pagination pagination-sm mb-0">
      <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadRequests(${currentPage-1});return false">‹</a></li>
      ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadRequests(${i+1});return false">${i+1}</a></li>`).join('')}
      <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadRequests(${currentPage+1});return false">›</a></li>
    </ul></nav>`;
}

window.filterStage = function(stage, btn){
  currentStage = stage; currentPage = 1;
  document.querySelectorAll('#stageTabs button').forEach(b=>{ b.classList.remove('active-tab'); });
  if (btn) btn.classList.add('active-tab');
  loadRequests();
};

let searchTimer;
window.debounceSearch = function(){
  clearTimeout(searchTimer);
  searchTimer = setTimeout(()=>{ currentSearch=document.getElementById('searchInput').value; currentPage=1; loadRequests(); },300);
};

// ── Form pools loading ────────────────────────────────────────
async function loadFormPools(){
  const res  = await fetch(`${API}?action=indicator_get&session=${getSession()}&year=${new Date().getFullYear()}-${new Date().getFullYear()+1}`,{credentials:'include'});
  const data = await res.json();
  poolsForForm = data.data?.pools||[];
  const sel  = document.getElementById('frIndicator');
  sel.innerHTML = '<option value="">— Select Pool —</option>';
  poolsForForm.forEach(p=>{
    const opt=document.createElement('option'); opt.value=p.id; opt.textContent=p.pool_name; opt.dataset.alloc=p.allocated_amount;
    sel.appendChild(opt);
  });
}

window.onIndicatorChange = async function(){
  const poolId = document.getElementById('frIndicator').value;
  document.getElementById('frActivity').innerHTML='<option value="">— Select Activity —</option>';
  document.getElementById('frPoolBudget').textContent='';
  document.getElementById('frBudgetWarn').style.display='none';
  if (!poolId) return;
  // Get active quarter activities for this pool
  const qRes = await fetch(`${API}?action=quarter_active&session=${getSession()}`,{credentials:'include'});
  const qData= await qRes.json();
  if (!qData.data) { document.getElementById('frPoolBudget').textContent='No approved quarter budget found.'; return; }
  const qId  = qData.data.id;
  const qFull= await fetch(`${API}?action=quarter_get&id=${qId}`,{credentials:'include'});
  const qFull2=(await qFull.json()).data;
  activitiesForForm = (qFull2?.activities||[]).filter(a=>a.pool_id==poolId && !a.is_external);
  const actSel = document.getElementById('frActivity');
  actSel.innerHTML='<option value="">— Select Activity (optional) —</option>';
  activitiesForForm.forEach(a=>{
    const rem = parseFloat(a.allocated_amount)-parseFloat(a.spent_amount);
    const opt = document.createElement('option'); opt.value=a.id; opt.textContent=`${a.activity_name} (${fmtMoney(rem)} left)`;
    actSel.appendChild(opt);
  });
  // Pool remaining
  const totRem = activitiesForForm.reduce((s,a)=>s+parseFloat(a.allocated_amount)-parseFloat(a.spent_amount),0);
  document.getElementById('frPoolBudget').textContent = `Available in this pool: ${fmtMoney(totRem)}`;
  checkBudget();
};

window.checkBudget = function(){
  const amt  = parseFloat(document.getElementById('frAmount')?.value)||0;
  const text = document.getElementById('frPoolBudget')?.textContent||'';
  const match= text.match(/RWF ([\d,]+)/);
  if (!match) return;
  const avail= parseFloat(match[1].replace(/,/g,''));
  const warn  = document.getElementById('frBudgetWarn');
  if (warn) warn.style.display = amt > avail ? '' : 'none';
};

// ── Open new / edit modal ─────────────────────────────────────
window.openNewRequest = async function(){
  document.getElementById('frEditId').value='';
  document.getElementById('frModalTitle').innerHTML='<i class="bi bi-send me-2"></i>New Fund Request';
  clearFRForm();
  await loadFormPools();
  new bootstrap.Modal(document.getElementById('frModal')).show();
};

window.openEdit = async function(id){
  const res  = await fetch(`${API}?action=fund_request_get&id=${id}`,{credentials:'include'});
  const data = await res.json();
  if (!data.data) return;
  const r = data.data;
  document.getElementById('frEditId').value    = r.id;
  document.getElementById('frModalTitle').innerHTML='<i class="bi bi-pencil me-2"></i>Edit Request';
  clearFRForm();
  await loadFormPools();
  document.getElementById('frTitle').value      = r.title||'';
  document.getElementById('frDesc').value       = r.description||'';
  document.getElementById('frAmount').value     = r.amount_requested||'';
  document.getElementById('frPriority').value   = r.priority||'medium';
  document.getElementById('frNeededBy').value   = r.needed_by_date||'';
  if (r.indicator_id) { document.getElementById('frIndicator').value=r.indicator_id; await onIndicatorChange(); }
  new bootstrap.Modal(document.getElementById('frModal')).show();
};

function clearFRForm(){
  ['frTitle','frDesc','frAmount','frNeededBy'].forEach(id=>{ const el=document.getElementById(id); if(el)el.value=''; });
  document.getElementById('frPriority').value='medium';
  document.getElementById('frIndicator').innerHTML='<option value="">— Select Pool —</option>';
  document.getElementById('frActivity').innerHTML='<option value="">—</option>';
  document.getElementById('frPoolBudget').textContent='';
  document.getElementById('frBudgetWarn').style.display='none';
}

// ── Save (draft or submit) ────────────────────────────────────
window.saveFR = async function(mode){
  const id   = document.getElementById('frEditId').value;
  const title= document.getElementById('frTitle').value.trim();
  const desc = document.getElementById('frDesc').value.trim();
  const amt  = parseFloat(document.getElementById('frAmount').value)||0;
  if (!title||!desc||!amt) { showToast('Title, description and amount are required','warning'); return; }

  const payload = {
    cep_session      : getSession(),
    title, description:desc,
    indicator_id     : parseInt(document.getElementById('frIndicator').value)||null,
    activity_id      : parseInt(document.getElementById('frActivity').value)||null,
    budget_quarter_id: null,
    amount_requested : amt,
    priority         : document.getElementById('frPriority').value,
    needed_by_date   : document.getElementById('frNeededBy').value||null,
  };

  // Get current quarter id
  if (payload.indicator_id) {
    const qRes = await fetch(`${API}?action=quarter_active&session=${getSession()}`,{credentials:'include'});
    const qD   = await qRes.json();
    if (qD.data) payload.budget_quarter_id = qD.data.id;
  }
  if (id) payload.id = parseInt(id);

  const action1 = id ? 'fund_request_update' : 'fund_request_create';
  const res1    = await fetch(`${API}?action=${action1}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const data1   = await res1.json();
  if (!data1.success) { showToast(data1.message||'Save failed','danger'); return; }

  if (mode==='submit') {
    const rid = id || data1.id;
    const res2= await fetch(`${API}?action=fund_request_submit`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:rid})});
    const d2  = await res2.json();
    if (!d2.success) { showToast(d2.message||'Submit failed','danger'); return; }
    showToast('Request submitted to President!','success');
  } else {
    showToast('Draft saved!','success');
  }
  bootstrap.Modal.getInstance(document.getElementById('frModal'))?.hide();
  loadRequests(); loadPipeline();
};

window.submitDraft = async function(id){
  const r = await Swal.fire({title:'Submit Request?',text:'This will send the request to the President for review.',icon:'question',showCancelButton:true,confirmButtonColor:'#377dff',confirmButtonText:'Submit'});
  if (!r.isConfirmed) return;
  const res  = await fetch(`${API}?action=fund_request_submit`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
  const data = await res.json();
  if (data.success) { showToast('Submitted!','success'); loadRequests(); loadPipeline(); }
  else showToast(data.message||'Failed','danger');
};

// ── Detail Modal ──────────────────────────────────────────────
window.openDetail = async function(id){
  document.getElementById('frDetailBody').innerHTML='<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
  document.getElementById('frDetailFooter').innerHTML='';
  new bootstrap.Modal(document.getElementById('frDetailModal')).show();
  const res  = await fetch(`${API}?action=fund_request_get&id=${id}`,{credentials:'include'});
  const data = await res.json();
  const r    = data.data;
  if (!r) { document.getElementById('frDetailBody').innerHTML='<p class="text-danger">Not found.</p>'; return; }
  const sm   = STAGE_META[r.stage]||{label:r.stage,color:'secondary',icon:'bi-dot'};
  document.getElementById('frDetailTitle').innerHTML=`${esc(r.request_number)} — ${esc(r.title)}`;
  document.getElementById('frDetailHeader').className=`modal-header bg-soft-${sm.color}`;

  const comments = (r.comments||[]).map(c=>`<div class="comment-bubble ${c.user_id==MY_ID?'own':''}">
    <strong>${esc(c.user_name||'—')}</strong> <span class="text-muted small">${(c.created_at||'').substr(0,16)}</span>
    <div class="mt-1">${esc(c.comment)}</div>
  </div>`).join('')||'<p class="text-muted small">No comments yet.</p>';

  const disbHtml = r.disbursement ? `<div class="alert alert-success border-0 mt-3">
    <i class="bi bi-check-circle-fill me-2"></i>Disbursed <strong>${fmtMoney(r.disbursement.amount)}</strong>
    via <strong>${esc(r.disbursement.payment_method?.replace('_',' '))}</strong>
    by <strong>${esc(r.disbursement.disbursed_by_name)}</strong>
    on ${(r.disbursement.disbursed_at||'').substr(0,10)}
    ${r.disbursement.reference_no?` (Ref: ${esc(r.disbursement.reference_no)})`:''}
  </div>` : '';

  document.getElementById('frDetailBody').innerHTML = `
    <div class="row g-3">
      <div class="col-md-6"><small class="text-muted">Requested by</small><div class="fw-semibold">${esc(r.requested_by_name||'—')}</div></div>
      <div class="col-md-3"><small class="text-muted">Amount</small><div class="fw-bold text-primary">${fmtMoney(r.amount_requested)}</div></div>
      <div class="col-md-3"><small class="text-muted">Approved</small><div class="fw-bold text-success">${r.amount_approved?fmtMoney(r.amount_approved):'—'}</div></div>
      <div class="col-md-4"><small class="text-muted">Indicator</small><div>${esc(r.indicator_name||'—')}</div></div>
      <div class="col-md-4"><small class="text-muted">Quarter</small><div>${esc(r.quarter||'—')}</div></div>
      <div class="col-md-4"><small class="text-muted">Activity</small><div>${esc(r.budget_activity_name||'—')}</div></div>
      <div class="col-md-6"><small class="text-muted">Priority</small><div><span class="badge bg-soft-${PRIORITY_COLORS[r.priority]||'secondary'} text-${PRIORITY_COLORS[r.priority]||'secondary'}">${esc(r.priority)}</span></div></div>
      <div class="col-md-6"><small class="text-muted">Needed By</small><div>${esc(r.needed_by_date||'Not specified')}</div></div>
      <div class="col-12"><small class="text-muted">Description</small><div class="border rounded p-2 mt-1 bg-light">${esc(r.description)}</div></div>
      ${r.rejection_reason?`<div class="col-12"><div class="alert alert-danger border-0"><i class="bi bi-x-circle me-2"></i><strong>Rejection reason:</strong> ${esc(r.rejection_reason)}</div></div>`:''}
      ${disbHtml}
    </div>
    <hr>
    <h6 class="fw-semibold">Comments & History</h6>
    ${comments}
    <div class="mt-3 d-flex gap-2">
      <input type="text" id="commentInput" class="form-control form-control-sm" placeholder="Add a comment…">
      <button class="btn btn-sm btn-outline-primary" onclick="addComment(${r.id})">Send</button>
    </div>`;

  // Footer actions
  const ft = document.getElementById('frDetailFooter');
  const mine = parseInt(r.requested_by) === MY_ID;
  let btns = '';
  if (mine && (r.stage==='draft'||r.stage==='rejected_by_president')) btns += `<button class="btn btn-primary" onclick="openEdit(${r.id});bootstrap.Modal.getInstance(document.getElementById('frDetailModal')).hide()"><i class="bi bi-pencil me-1"></i>Edit</button> `;
  if (mine && r.stage==='draft') btns += `<button class="btn btn-outline-primary" onclick="submitDraft(${r.id})"><i class="bi bi-send me-1"></i>Submit</button> `;
  if (IS_PRESIDENT && r.stage==='to_president') {
    btns += `<button class="btn btn-success" onclick="presidApprove(${r.id},${parseFloat(r.amount_requested)})"><i class="bi bi-check-circle me-1"></i>Approve</button> `;
    btns += `<button class="btn btn-danger" onclick="openReject(${r.id});bootstrap.Modal.getInstance(document.getElementById('frDetailModal')).hide()"><i class="bi bi-x me-1"></i>Reject</button> `;
  }
  if (CAN_DISBURSE && r.stage==='to_finance') btns += `<button class="btn btn-success" onclick="openDisburse(${r.id},'${esc(r.title)}',${parseFloat(r.amount_approved||r.amount_requested)});bootstrap.Modal.getInstance(document.getElementById('frDetailModal')).hide()"><i class="bi bi-cash me-1"></i>Disburse</button> `;
  if (r.stage==='completed') btns += `<button class="btn btn-outline-secondary" onclick="printReport(${r.id})"><i class="bi bi-printer me-1"></i>Print</button> `;
  ft.innerHTML = btns;
};

window.addComment = async function(id){
  const inp  = document.getElementById('commentInput');
  const c    = inp?.value.trim(); if(!c) return;
  const res  = await fetch(`${API}?action=fund_request_comment`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,comment:c})});
  const data = await res.json();
  if (data.success) { inp.value=''; openDetail(id); }
  else showToast(data.message||'Failed','danger');
};

// ── President actions ─────────────────────────────────────────
window.presidApprove = async function(id, requestedAmt){
  const r = await Swal.fire({
    title:'Approve Fund Request',
    html:`<div class="text-start"><label class="form-label fw-semibold">Approved Amount (RWF)</label>
      <input type="number" id="swAmtInp" class="form-control" value="${requestedAmt}" step="100" min="1">
      <div class="form-text">Leave as-is to approve full amount, or adjust.</div>
      <label class="form-label fw-semibold mt-2">Comment (optional)</label>
      <textarea id="swCmtInp" class="form-control" rows="2" placeholder="Optional approval note"></textarea></div>`,
    icon:'question', showCancelButton:true, confirmButtonColor:'#00c9a7', confirmButtonText:'Approve',
    preConfirm: ()=>({amount_approved:parseFloat(document.getElementById('swAmtInp').value)||0, comment:document.getElementById('swCmtInp').value.trim()})
  });
  if (!r.isConfirmed) return;
  const res  = await fetch(`${API}?action=fund_president_action`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,action:'approve',...r.value})});
  const data = await res.json();
  if (data.success) { showToast('Request approved! Sent to Finance.','success'); loadRequests(); loadPipeline(); bootstrap.Modal.getInstance(document.getElementById('frDetailModal'))?.hide(); }
  else showToast(data.message||'Failed','danger');
};

window.openReject = function(id){
  document.getElementById('rejectId').value=id;
  document.getElementById('rejectReason').value='';
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
};

window.submitReject = async function(){
  const id     = document.getElementById('rejectId').value;
  const reason = document.getElementById('rejectReason').value.trim();
  if (!reason) { showToast('Rejection reason is required','warning'); return; }
  const res  = await fetch(`${API}?action=fund_president_action`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,action:'reject',rejection_reason:reason})});
  const data = await res.json();
  if (data.success) {
    bootstrap.Modal.getInstance(document.getElementById('rejectModal'))?.hide();
    showToast('Request rejected','warning'); loadRequests(); loadPipeline();
  } else showToast(data.message||'Failed','danger');
};

// ── Disbursement ──────────────────────────────────────────────
window.openDisburse = function(id, title, approvedAmt){
  document.getElementById('disburseId').value = id;
  document.getElementById('disburseAmount').value = approvedAmt;
  document.getElementById('disburseInfo').innerHTML = `Disbursing for: <strong>${esc(title)}</strong> &bull; Approved: <strong>${fmtMoney(approvedAmt)}</strong>`;
  document.getElementById('disburseRef').value = '';
  document.getElementById('disburseRecip').value = '';
  document.getElementById('disburseNotes').value = '';
  new bootstrap.Modal(document.getElementById('disburseModal')).show();
};

window.confirmDisburse = async function() {
    console.log('confirmDisburse called');
    
    const id = document.getElementById('disburseId')?.value;
    if (!id) {
        showToast('No request ID found', 'danger');
        return;
    }
    
    const amount = parseFloat(document.getElementById('disburseAmount')?.value) || 0;
    if (!amount || amount <= 0) {
        showToast('Please enter a valid amount', 'warning');
        return;
    }
    
    const method = document.getElementById('disburseMethod')?.value || 'cash';
    const ref = document.getElementById('disburseRef')?.value || null;
    const recipient = document.getElementById('disburseRecip')?.value || null;
    const notes = document.getElementById('disburseNotes')?.value || null;
    
    console.log('Disbursement data:', { id, amount, method, ref, recipient, notes });
    
    // Show confirmation dialog
    const result = await Swal.fire({
        title: 'Confirm Disbursement?',
        html: `<div class="text-start">
            <p><strong>Amount:</strong> RWF ${amount.toLocaleString()}</p>
            <p><strong>Method:</strong> ${method.replace('_', ' ')}</p>
            ${recipient ? `<p><strong>Recipient:</strong> ${recipient}</p>` : ''}
            ${ref ? `<p><strong>Reference:</strong> ${ref}</p>` : ''}
            <hr>
            <p class="text-warning"><i class="bi bi-exclamation-triangle me-2"></i>This action cannot be undone!</p>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00c9a7',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Disburse',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) {
        console.log('Disbursement cancelled');
        return;
    }
    
    // Get the button and show loading state
    const modalFooter = document.querySelector('#disburseModal .modal-footer');
    const confirmBtn = modalFooter?.querySelector('.btn-success');
    const originalText = confirmBtn ? confirmBtn.innerHTML : 'Confirm Disbursement';
    
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    }
    
    try {
        const payload = {
            id: parseInt(id),
            amount: amount,
            payment_method: method,
            reference_no: ref,
            recipient_name: recipient,
            notes: notes
        };
        
        console.log('Sending disbursement payload:', payload);
        
        const response = await fetch(`${API}?action=fund_disburse`, {
            method: 'POST',
            credentials: 'include',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        // Get response text first
        const responseText = await response.text();
        console.log('Raw disbursement response:', responseText);
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON response:', responseText);
            showToast('Server returned an invalid response', 'danger');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
            return;
        }
        
        if (data.success) {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('disburseModal'));
            if (modal) modal.hide();
            
            showToast('Funds disbursed successfully!', 'success');
            
            // Refresh the data
            await loadRequests();
            await loadPipeline();
            
            // Close any open detail modal
            const detailModal = bootstrap.Modal.getInstance(document.getElementById('frDetailModal'));
            if (detailModal) detailModal.hide();
            
        } else {
            showToast(data.message || 'Failed to disburse funds', 'danger');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        }
    } catch (error) {
        console.error('Error disbursing funds:', error);
        showToast('Error: ' + error.message, 'danger');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }
};

// ── Delete ─────────────────────────────────────────────────────
window.deleteReq = async function(id){
  const r = await Swal.fire({title:'Delete Request?',icon:'warning',showCancelButton:true,confirmButtonColor:'#de4437',confirmButtonText:'Delete'});
  if (!r.isConfirmed) return;
  const res  = await fetch(`${API}?action=fund_request_delete`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
  const data = await res.json();
  if (data.success) { showToast('Deleted','success'); loadRequests(); loadPipeline(); }
  else showToast(data.message||'Failed','danger');
};

// ── Print ─────────────────────────────────────────────────────
window.printReport = function(id){ window.open(`${BASE}/admin/finance-reports?type=fund_request&id=${id}`,'_blank'); };

document.addEventListener('DOMContentLoaded',()=>{ loadPipeline(); loadRequests(); });
})();
</script>
</body></html>