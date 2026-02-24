<?php
/**
 * Fund Requests
 * File: modules/Dashboard/views/finance-fund-requests.php
 */
$pageTitle          = 'Fund Requests';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canSubmit   = hasPermission($userPermissions, 'finance.fund_requests');
$canApprove  = hasPermission($userPermissions, 'finance.approve_funds');
$canDisburse = hasPermission($userPermissions, 'finance.disburse_funds');
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>
<script src="<?=admin_js_url('hs.theme-appearance.js')?>"></script>
<script src="<?=admin_vendor_url('hs-navbar-vertical-aside/dist/hs-navbar-vertical-aside-mini-cache.js')?>"></script>
<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title">Fund Requests</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
                    <li class="breadcrumb-item active">Fund Requests</li>
                </ol></nav>
            </div>
            <div class="col-auto d-flex gap-2">
                <?php if($isSuperAdmin??false): ?>
                <select id="sessionFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Sessions</option>
                    <option value="day">☀️ Day CEP</option>
                    <option value="weekend">🌙 Weekend CEP</option>
                </select>
                <?php endif; ?>
                <?php if($canSubmit): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newRequestModal">
                    <i class="bi bi-plus-lg me-1"></i> New Request
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pipeline Summary -->
    <div class="row g-3 mb-4" id="pipelineRow">
        <?php
        $stages = [
            ['key'=>'pending',   'label'=>'Pending',   'color'=>'warning', 'icon'=>'bi-hourglass'],
            ['key'=>'reviewing', 'label'=>'Reviewing', 'color'=>'info',    'icon'=>'bi-eye'],
            ['key'=>'approved',  'label'=>'Approved',  'color'=>'success', 'icon'=>'bi-check-circle'],
            ['key'=>'rejected',  'label'=>'Rejected',  'color'=>'danger',  'icon'=>'bi-x-circle'],
            ['key'=>'disbursed', 'label'=>'Disbursed', 'color'=>'primary', 'icon'=>'bi-cash-coin'],
        ];
        foreach($stages as $st): ?>
        <div class="col">
            <div class="card card-hover-shadow h-100 pipeline-card" data-stage="<?=$st['key']?>" style="cursor:pointer" onclick="filterStage('<?=$st['key']?>')">
                <div class="card-body text-center py-3">
                    <i class="bi <?=$st['icon']?> fs-3 text-<?=$st['color']?> mb-1"></i>
                    <div class="display-6 fw-bold text-<?=$st['color']?>" id="cnt_<?=$st['key']?>">—</div>
                    <div class="text-muted small"><?=$st['label']?></div>
                    <div class="text-muted smaller" id="amt_<?=$st['key']?>"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters Row -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select id="stageFilter" class="form-select form-select-sm" style="width:140px">
                        <option value="">All Stages</option>
                        <?php foreach($stages as $st): ?><option value="<?=$st['key']?>"><?=$st['label']?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <input type="search" id="searchBox" class="form-control form-control-sm" placeholder="Search requests…">
                </div>
                <div class="col-auto"><span class="text-muted small" id="resultCount">Loading…</span></div>
            </div>
        </div>
    </div>

    <!-- Request Cards Grid -->
    <div id="requestsGrid" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
    </div>
    <div id="paginator" class="d-flex justify-content-center mt-3"></div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Fund Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <?php if($isSuperAdmin??false): ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                    <select id="nrSession" class="form-select"><option value="">Select…</option><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option></select>
                </div>
                <?php endif; ?>
                <div class="col-12"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" id="nrTitle" class="form-control" placeholder="Brief title of the request"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Department</label><input type="text" id="nrDept" class="form-control" placeholder="e.g. Evangelism"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Amount (RWF) <span class="text-danger">*</span></label><input type="number" id="nrAmount" class="form-control" min="0" placeholder="0"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Priority</label>
                    <select id="nrPriority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">🚨 Urgent</option></select>
                </div>
                <div class="col-md-6"><label class="form-label fw-semibold">Needed By</label><input type="date" id="nrNeeded" class="form-control"></div>
                <div class="col-12"><label class="form-label fw-semibold">Description <span class="text-danger">*</span></label><textarea id="nrDesc" class="form-control" rows="3" placeholder="Detailed justification…"></textarea></div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSubmitReq" class="btn btn-primary">Submit Request</button></div>
        </div>
    </div>
</div>

<!-- Action Modal (Approve/Reject/Disburse) -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="actionModalTitle">Action</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="actionModalBody"></div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnConfirmAction" class="btn btn-primary">Confirm</button></div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE   = '<?=BASE_URL?>';
    const API    = BASE + '/api/finance';
    const IS_SA  = <?=json_encode($isSuperAdmin??false)?>;
    const MY_SES = <?=json_encode($currentUser->session_type??null)?>;
    const CAN_APPROVE  = <?=json_encode($canApprove)?>;
    const CAN_DISBURSE = <?=json_encode($canDisburse)?>;
    let currentPage = 1, pendingAction = null;

    function sess(){ return IS_SA ? (document.getElementById('sessionFilter')?.value||null) : MY_SES; }

    async function loadPipeline(){
        const s = sess();
        const url = `${API}?action=fund_pipeline` + (s?`&session=${s}`:'');
        const res = await fetch(url,{credentials:'include'});
        const d   = await res.json();
        const map = d.data || {};
        ['pending','reviewing','approved','rejected','disbursed'].forEach(k=>{
            const cnt = document.getElementById('cnt_'+k);
            const amt = document.getElementById('amt_'+k);
            if(cnt) cnt.textContent = map[k]?.cnt||0;
            if(amt && map[k]?.total_amt) amt.textContent = 'RWF '+Number(map[k].total_amt).toLocaleString();
        });
    }

    async function loadRequests(page=1){
        currentPage = page;
        const grid = document.getElementById('requestsGrid');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params = new URLSearchParams({action:'fund_requests', page, per_page:12});
        const s = sess();
        if(s) params.set('session',s);
        const stage  = document.getElementById('stageFilter').value;
        const search = document.getElementById('searchBox').value;
        if(stage)  params.set('stage',  stage);
        if(search) params.set('search', search);
        const res  = await fetch(`${API}?${params}`,{credentials:'include'});
        const data = await res.json();
        document.getElementById('resultCount').textContent = `${data.total||0} request(s)`;
        if(!data.data?.length){ grid.innerHTML='<div class="col-12 text-center text-muted py-5">No fund requests found.</div>'; renderPager(0,1); return; }
        grid.innerHTML = data.data.map(r=>renderCard(r)).join('');
        renderPager(data.total, data.pages);
    }

    const stageConfig = {
        pending:  {color:'warning', next:[{act:'mark_review',label:'Mark for Review',cls:'btn-info'}]},
        reviewing:{color:'info',    next:[{act:'approve',label:'✓ Approve',cls:'btn-success'},{act:'reject',label:'✕ Reject',cls:'btn-danger'}]},
        approved: {color:'success', next:[{act:'disburse',label:'💳 Disburse',cls:'btn-primary'}]},
        rejected: {color:'danger',  next:[]},
        disbursed:{color:'primary', next:[]},
    };
    const priColors = {low:'secondary',medium:'info',high:'warning',urgent:'danger'};

    function renderCard(r){
        const sc   = stageConfig[r.stage]||{color:'secondary',next:[]};
        const pct  = r.amount_approved ? Math.round(r.amount_approved/r.amount_requested*100) : 0;
        const flow = ['pending','reviewing','approved','disbursed'];
        const tracker = flow.map((s,i)=>`<span class="badge rounded-pill ${r.stage===s?'bg-primary':'bg-soft-secondary text-secondary'} me-1">${i+1}. ${s.charAt(0).toUpperCase()+s.slice(1)}</span>`).join('');
        const buttons = sc.next.filter(b=>{
            if((b.act==='approve'||b.act==='reject')&&!CAN_APPROVE)return false;
            if(b.act==='disburse'&&!CAN_DISBURSE)return false;
            return true;
        }).map(b=>`<button class="btn btn-xs ${b.cls}" onclick="doAction(${r.id},'${b.act}')">${b.label}</button>`).join('');
        return `<div class="col-md-6 col-xl-4">
          <div class="card h-100 border-start border-3 border-${sc.color}">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="badge bg-soft-secondary text-secondary small">${esc(r.request_number||'#'+r.id)}</span>
                  <span class="badge bg-soft-${priColors[r.priority]||'secondary'} text-${priColors[r.priority]||'secondary'} ms-1 small">${esc(r.priority)}</span>
                </div>
                <span class="badge bg-soft-${sc.color} text-${sc.color} text-capitalize">${esc(r.stage)}</span>
              </div>
              <h6 class="mb-1">${esc(r.title)}</h6>
              <p class="text-muted small mb-2">${esc(r.department||'General')} • ${esc(r.cep_session)} session</p>
              <div class="d-flex justify-content-between mb-2">
                <div><small class="text-muted">Requested</small><br><strong>RWF ${Number(r.amount_requested||0).toLocaleString()}</strong></div>
                ${r.amount_approved?`<div class="text-end"><small class="text-muted">Approved</small><br><strong class="text-success">RWF ${Number(r.amount_approved||0).toLocaleString()}</strong></div>`:''}
              </div>
              <div class="mb-2 text-muted small">${tracker}</div>
              <div class="d-flex align-items-center text-muted small mb-2">
                <i class="bi bi-person me-1"></i>${esc(r.requested_by_name||'Unknown')}
                <i class="bi bi-calendar ms-auto me-1"></i>${r.created_at?.substr(0,10)||''}
              </div>
              ${buttons?`<div class="d-flex gap-1 flex-wrap mt-2">${buttons}</div>`:''}
            </div>
          </div>
        </div>`;
    }

    function renderPager(total, pages){
        const el = document.getElementById('paginator');
        if(!el||pages<=1){el.innerHTML='';return;}
        el.innerHTML = `<ul class="pagination pagination-sm">
            <li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadReqs(${currentPage-1});return false;">‹</a></li>
            ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadReqs(${i+1});return false;">${i+1}</a></li>`).join('')}
            <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadReqs(${currentPage+1});return false;">›</a></li>
        </ul>`;
    }
    window.loadReqs = loadRequests;

    window.filterStage = function(stage){
        document.getElementById('stageFilter').value = stage;
        loadRequests(1);
        document.querySelectorAll('.pipeline-card').forEach(c=>c.classList.toggle('border-primary',c.dataset.stage===stage));
    };

    window.doAction = function(id, act){
        pendingAction = {id, act};
        const titles = {mark_review:'Mark for Review',approve:'Approve Request',reject:'Reject Request',disburse:'Disburse Funds'};
        document.getElementById('actionModalTitle').textContent = titles[act]||act;
        let body = '';
        if(act==='approve')   body = `<div class="mb-3"><label class="form-label fw-semibold">Approved Amount (RWF)</label><input type="number" id="aApproved" class="form-control" placeholder="Leave blank to approve full amount"></div>`;
        if(act==='reject')    body = `<div class="mb-3"><label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label><textarea id="aReason" class="form-control" rows="3"></textarea></div>`;
        if(act==='disburse')  body = `
            <div class="mb-3"><label class="form-label fw-semibold">Payment Method</label><select id="aMethod" class="form-select"><option value="cash">Cash</option><option value="mobile_money">Mobile Money</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option></select></div>
            <div class="mb-3"><label class="form-label fw-semibold">Reference No</label><input type="text" id="aRef" class="form-control" placeholder="Transaction reference"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Recipient Name</label><input type="text" id="aRecip" class="form-control"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Amount</label><input type="number" id="aAmt" class="form-control"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Notes</label><textarea id="aNotes" class="form-control" rows="2"></textarea></div>`;
        if(!body) body = `<p class="text-muted">Confirm this action for request #${id}?</p>`;
        document.getElementById('actionModalBody').innerHTML = body;
        new bootstrap.Modal(document.getElementById('actionModal')).show();
    };

    async function submitAction(){
        if(!pendingAction) return;
        const {id, act} = pendingAction;
        const payload = {id, action: act};
        if(act==='approve')  payload.amount_approved  = document.getElementById('aApproved')?.value||null;
        if(act==='reject')   payload.rejection_reason = document.getElementById('aReason')?.value||'';
        if(act==='disburse'){
            payload.payment_method = document.getElementById('aMethod')?.value;
            payload.reference_no   = document.getElementById('aRef')?.value;
            payload.recipient_name = document.getElementById('aRecip')?.value;
            payload.amount         = document.getElementById('aAmt')?.value;
            payload.notes          = document.getElementById('aNotes')?.value;
        }
        const res  = await fetch(`${API}?action=fund_advance`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data = await res.json();
        bootstrap.Modal.getInstance(document.getElementById('actionModal'))?.hide();
        if(data.success){ loadPipeline(); loadRequests(currentPage); showToast(`Request ${act.replace('_',' ')} successfully!`,'success'); }
        else showToast(data.message||'Action failed','danger');
    }

    async function submitNewRequest(){
        const btn = document.getElementById('btnSubmitReq');
        btn.disabled=true;
        const payload = {
            cep_session     : IS_SA ? (document.getElementById('nrSession')?.value||MY_SES) : MY_SES,
            title           : document.getElementById('nrTitle').value,
            department      : document.getElementById('nrDept').value,
            amount_requested: document.getElementById('nrAmount').value,
            priority        : document.getElementById('nrPriority').value,
            needed_by_date  : document.getElementById('nrNeeded').value,
            description     : document.getElementById('nrDesc').value,
        };
        const res  = await fetch(`${API}?action=fund_submit`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data = await res.json();
        btn.disabled=false;
        if(data.success){
            bootstrap.Modal.getInstance(document.getElementById('newRequestModal'))?.hide();
            loadPipeline(); loadRequests(1);
            showToast('Request '+data.request_number+' submitted!','success');
        } else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div'); t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`; t.style.zIndex=9999; t.textContent=msg;
        document.body.appendChild(t); setTimeout(()=>t.remove(),3500);
    }
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    let searchTimer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadPipeline(); loadRequests();
        document.getElementById('sessionFilter')?.addEventListener('change',()=>{loadPipeline();loadRequests(1);});
        document.getElementById('stageFilter')?.addEventListener('change',()=>loadRequests(1));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>loadRequests(1),350);});
        document.getElementById('btnSubmitReq')?.addEventListener('click',submitNewRequest);
        document.getElementById('btnConfirmAction')?.addEventListener('click',submitAction);
    });
})();
</script>
</body></html>