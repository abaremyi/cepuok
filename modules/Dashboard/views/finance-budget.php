<?php
/**
 * Budget Management
 * File: modules/Dashboard/views/finance-budget.php
 */
$pageTitle          = 'Budget Management';
$requiredPermission = 'finance.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canManage = hasPermission($userPermissions, 'finance.manage_budget');
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
                <h1 class="page-header-title">Budget Management</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/finance-dashboard')?>">Finance</a></li>
                    <li class="breadcrumb-item active">Budgets</li>
                </ol></nav>
            </div>
            <?php if($canManage): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newBudgetModal">
                    <i class="bi bi-plus-lg me-1"></i> New Budget
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <?php if($isSuperAdmin??false): ?>
                <div class="col-auto">
                    <select id="fltSession" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Sessions</option>
                        <option value="day">☀️ Day CEP</option>
                        <option value="weekend">🌙 Weekend CEP</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-auto">
                    <select id="fltStatus" class="form-select form-select-sm" style="width:130px">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col"><span class="text-muted small" id="resultCount">Loading…</span></div>
            </div>
        </div>
    </div>

    <div id="budgetsGrid" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- New Budget Modal -->
<div class="modal fade" id="newBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Create Budget</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><label class="form-label fw-semibold">Budget Name <span class="text-danger">*</span></label><input type="text" id="bName" class="form-control" placeholder="e.g. Q1 2026 Budget"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="bSession" class="form-select">
                            <?php if($isSuperAdmin??false): ?><option value="">Select…</option><?php endif; ?>
                            <option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Academic Year</label><input type="text" id="bYear" class="form-control" value="<?=date('Y').'-'.(date('Y')+1)?>" placeholder="2026-2027"></div>
                    <div class="col-md-2"><label class="form-label fw-semibold">Status</label>
                        <select id="bStatus" class="form-select"><option value="draft">Draft</option><option value="submitted">Submit Now</option></select>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea id="bNotes" class="form-control" rows="2"></textarea></div>
                </div>
                <hr><h6 class="mb-3">Budget Lines <button type="button" class="btn btn-xs btn-outline-primary ms-2" onclick="addLine()"><i class="bi bi-plus"></i> Add Line</button></h6>
                <div class="table-responsive"><table class="table table-sm" id="linesTable">
                    <thead class="thead-light"><tr><th>Department</th><th>Line Item</th><th>Amount (RWF)</th><th></th></tr></thead>
                    <tbody id="linesTbody"></tbody>
                </table></div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted small">Total allocated: <strong id="lineTotal">RWF 0</strong></span>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnCreateBudget" class="btn btn-primary">Create Budget</button></div>
        </div>
    </div>
</div>

<!-- View Budget Modal -->
<div class="modal fade" id="viewBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="viewTitle">Budget Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewBody"><div class="text-center py-3"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer" id="viewFooter"></div>
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
    const CAN_MANAGE = <?=json_encode($canManage)?>;

    function sess(){ return IS_SA ? (document.getElementById('fltSession')?.value||null) : MY_SES; }
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    async function loadBudgets(){
        const grid = document.getElementById('budgetsGrid');
        grid.innerHTML='<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params = new URLSearchParams({action:'budget_list'});
        const s = sess(); if(s) params.set('session',s);
        const st = document.getElementById('fltStatus')?.value; if(st) params.set('status',st);
        const res  = await fetch(`${API}?${params}`,{credentials:'include'});
        const data = await res.json();
        const list = data.data||[];
        document.getElementById('resultCount').textContent = `${list.length} budget(s)`;
        if(!list.length){grid.innerHTML='<div class="col-12 text-center text-muted py-5">No budgets found. Create your first budget!</div>';return;}
        const stColors={draft:'secondary',submitted:'warning',approved:'success',rejected:'danger'};
        grid.innerHTML = list.map(b=>{
            const spent = parseFloat(b.line_spent||0);
            const total = parseFloat(b.total_amount||0);
            const pct   = total>0 ? Math.min(100,Math.round(spent/total*100)) : 0;
            const cls   = pct>=90?'bg-danger':pct>=60?'bg-warning':'bg-success';
            return `<div class="col-md-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-soft-${stColors[b.status]||'secondary'} text-${stColors[b.status]||'secondary'} text-capitalize">${esc(b.status)}</span>
                    <span class="badge bg-soft-${b.cep_session==='day'?'warning':'primary'} text-${b.cep_session==='day'?'warning':'primary'}">${esc(b.cep_session)}</span>
                  </div>
                  <h5 class="mb-1">${esc(b.budget_name)}</h5>
                  <p class="text-muted small mb-3">${esc(b.academic_year)} • Created by ${esc(b.created_by_name||'System')}</p>
                  <div class="row text-center mb-3">
                    <div class="col"><div class="fw-bold">RWF ${Number(total).toLocaleString()}</div><small class="text-muted">Budget</small></div>
                    <div class="col"><div class="fw-bold text-success">RWF ${Number(b.line_allocated||0).toLocaleString()}</div><small class="text-muted">Allocated</small></div>
                    <div class="col"><div class="fw-bold text-danger">RWF ${Number(spent).toLocaleString()}</div><small class="text-muted">Spent</small></div>
                  </div>
                  <div class="progress mb-2" style="height:6px"><div class="progress-bar ${cls}" style="width:${pct}%"></div></div>
                  <div class="d-flex justify-content-between">
                    <small class="text-muted">${pct}% utilized</small>
                    <button class="btn btn-xs btn-ghost-primary" onclick="viewBudget(${b.id})"><i class="bi bi-eye"></i> View</button>
                    ${b.status==='submitted'&&CAN_MANAGE?`<button class="btn btn-xs btn-success" onclick="approveBudget(${b.id})">✓ Approve</button>`:''}
                  </div>
                </div>
              </div>
            </div>`;
        }).join('');
    }

    window.viewBudget = async function(id){
        document.getElementById('viewBody').innerHTML='<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('viewBudgetModal')).show();
        const res  = await fetch(`${API}?action=budget_get&id=${id}`,{credentials:'include'});
        const data = await res.json();
        const b    = data.data;
        if(!b){document.getElementById('viewBody').innerHTML='<p class="text-danger">Budget not found.</p>';return;}
        document.getElementById('viewTitle').textContent = b.budget_name;
        const lines = (b.lines||[]).map(l=>`<tr>
          <td>${esc(l.department)}</td><td>${esc(l.line_item)}</td>
          <td>RWF ${Number(l.allocated_amount||0).toLocaleString()}</td>
          <td>RWF ${Number(l.spent_amount||0).toLocaleString()}</td>
          <td>${l.allocated_amount>0?Math.round(l.spent_amount/l.allocated_amount*100)+'%':'—'}</td>
        </tr>`).join('');
        document.getElementById('viewBody').innerHTML=`
          <div class="row mb-3">
            <div class="col-6"><strong>Session:</strong> ${esc(b.cep_session)} • <strong>Year:</strong> ${esc(b.academic_year)}</div>
            <div class="col-6 text-end"><strong>Total Budget:</strong> RWF ${Number(b.total_amount||0).toLocaleString()}</div>
          </div>
          ${b.notes?`<p class="text-muted small mb-3">${esc(b.notes)}</p>`:''}
          <div class="table-responsive"><table class="table table-sm table-bordered">
            <thead class="thead-light"><tr><th>Department</th><th>Line Item</th><th>Allocated</th><th>Spent</th><th>%</th></tr></thead>
            <tbody>${lines||'<tr><td colspan="5" class="text-muted text-center">No line items.</td></tr>'}</tbody>
          </table></div>`;
        const footer = document.getElementById('viewFooter');
        footer.innerHTML = b.status==='submitted'&&CAN_MANAGE
            ? `<button class="btn btn-success" onclick="approveBudget(${id})">✓ Approve Budget</button>`
            : '';
    };

    window.approveBudget = async function(id){
        if(!confirm('Approve this budget?')) return;
        const res  = await fetch(`${API}?action=budget_approve`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
        const data = await res.json();
        bootstrap.Modal.getInstance(document.getElementById('viewBudgetModal'))?.hide();
        if(data.success){loadBudgets();showToast('Budget approved!','success');}
        else showToast(data.message||'Failed','danger');
    };

    // Budget line items
    let lineCount=0;
    window.addLine = function(){
        lineCount++;
        const tr=document.createElement('tr');
        tr.innerHTML=`<td><input type="text" class="form-control form-control-sm line-dept" placeholder="Department"></td>
          <td><input type="text" class="form-control form-control-sm line-item" placeholder="Line item"></td>
          <td><input type="number" class="form-control form-control-sm line-amt" min="0" placeholder="0" oninput="updateLineTotal()"></td>
          <td><button type="button" class="btn btn-xs btn-ghost-danger" onclick="this.closest('tr').remove();updateLineTotal()"><i class="bi bi-trash"></i></button></td>`;
        document.getElementById('linesTbody').appendChild(tr);
    };
    window.updateLineTotal = function(){
        const total = Array.from(document.querySelectorAll('.line-amt')).reduce((s,i)=>s+parseFloat(i.value||0),0);
        document.getElementById('lineTotal').textContent = 'RWF '+total.toLocaleString();
    };

    async function createBudget(){
        const btn=document.getElementById('btnCreateBudget'); btn.disabled=true;
        const lines=Array.from(document.getElementById('linesTbody').rows).map(r=>({
            department:r.querySelector('.line-dept').value,
            line_item:r.querySelector('.line-item').value,
            allocated_amount:parseFloat(r.querySelector('.line-amt').value||0),
        })).filter(l=>l.department&&l.line_item);
        const total=lines.reduce((s,l)=>s+l.allocated_amount,0);
        const payload={
            budget_name:document.getElementById('bName').value,
            cep_session:document.getElementById('bSession').value,
            academic_year:document.getElementById('bYear').value,
            total_amount:total||parseFloat(document.getElementById('bName').value)||0,
            status:document.getElementById('bStatus').value,
            notes:document.getElementById('bNotes').value,
            lines
        };
        const res=await fetch(`${API}?action=budget_create`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json(); btn.disabled=false;
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('newBudgetModal'))?.hide();loadBudgets();showToast('Budget created!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3500);
    }

    document.addEventListener('DOMContentLoaded',()=>{
        loadBudgets(); addLine();
        ['fltSession','fltStatus'].forEach(id=>document.getElementById(id)?.addEventListener('change',loadBudgets));
        document.getElementById('btnCreateBudget')?.addEventListener('click',createBudget);
    });
})();
</script>
</body></html>