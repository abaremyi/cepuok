<?php
/**
 * Supporters Management
 * File: modules/Dashboard/views/supporters-management.php
 */
$pageTitle          = 'Supporters';
$requiredPermission = 'supporters.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canCreate = hasPermission($userPermissions, 'supporters.create');
$canEdit   = hasPermission($userPermissions, 'supporters.edit');
$canDelete = hasPermission($userPermissions, 'supporters.delete');
$canContrib= hasPermission($userPermissions, 'supporters.contributions');
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
                <h1 class="page-header-title">Supporters</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Supporters</li>
                </ol></nav>
            </div>
            <?php if($canCreate): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#supporterModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Supporter
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="row g-3 mb-4">
        <?php
        $statCards=[
            ['id'=>'stTotal',   'label'=>'Total Supporters', 'icon'=>'bi-people-fill',    'color'=>'primary'],
            ['id'=>'stAlumni',  'label'=>'Alumni',            'icon'=>'bi-mortarboard',    'color'=>'success'],
            ['id'=>'stExternal','label'=>'External',          'icon'=>'bi-person-badge',   'color'=>'info'],
            ['id'=>'stChoir',   'label'=>'Choir',             'icon'=>'bi-music-note',     'color'=>'warning'],
            ['id'=>'stContrib', 'label'=>'Total Contributed', 'icon'=>'bi-cash-stack',     'color'=>'danger'],
        ];
        foreach($statCards as $c): ?>
        <div class="col-sm-6 col-xl"><div class="card"><div class="card-body text-center">
            <i class="bi <?=$c['icon']?> fs-3 text-<?=$c['color']?> mb-2 d-block"></i>
            <div class="fs-4 fw-bold" id="<?=$c['id']?>">—</div>
            <small class="text-muted"><?=$c['label']?></small>
        </div></div></div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto"><select id="fltType" class="form-select form-select-sm" style="width:140px">
                    <option value="">All Types</option><option value="alumni">Alumni</option>
                    <option value="external">External</option><option value="choir">Choir</option><option value="organization">Organization</option>
                </select></div>
                <div class="col-auto"><select id="fltTier" class="form-select form-select-sm" style="width:120px">
                    <option value="">All Tiers</option><option value="bronze">Bronze</option>
                    <option value="silver">Silver</option><option value="gold">Gold</option><option value="platinum">Platinum</option>
                </select></div>
                <div class="col-auto"><select id="fltStatus" class="form-select form-select-sm" style="width:120px">
                    <option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option>
                </select></div>
                <div class="col"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="searchBox" class="form-control" placeholder="Search name, org, email…">
                </div></div>
                <div class="col-auto"><span class="text-muted small" id="resultCount">Loading…</span></div>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div id="supportersGrid" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
    <div id="paginator" class="d-flex justify-content-center mt-3"></div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Add/Edit Supporter Modal -->
<div class="modal fade" id="supporterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="suppModalTitle">Add Supporter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="suppId">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label fw-semibold">Type</label>
                        <select id="suppType" class="form-select"><option value="alumni">Alumni</option><option value="external">External</option><option value="choir">Choir</option><option value="organization">Organization</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label><input type="text" id="suppFn" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label><input type="text" id="suppLn" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Organization Name</label><input type="text" id="suppOrg" class="form-control" placeholder="If organization"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" id="suppEmail" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input type="text" id="suppPhone" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Tier</label>
                        <select id="suppTier" class="form-select"><option value="bronze">🥉 Bronze</option><option value="silver">🥈 Silver</option><option value="gold">🥇 Gold</option><option value="platinum">💎 Platinum</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">CEP Session</label>
                        <select id="suppSession" class="form-select"><option value="both">Both</option><option value="day">Day CEP</option><option value="weekend">Weekend CEP</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Is Alumni?</label>
                        <select id="suppIsAlumni" class="form-select"><option value="0">No</option><option value="1">Yes</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Graduation Year</label><input type="number" id="suppGrad" class="form-control" placeholder="2020" min="2000" max="2030"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Support Area</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach(['financial'=>'💰 Financial','instruments'=>'🎸 Instruments','service'=>'🙌 Service','prayers'=>'🙏 Prayers','general'=>'📋 General'] as $v=>$l): ?>
                            <label class="badge bg-soft-secondary text-secondary p-2" style="cursor:pointer">
                                <input type="checkbox" class="supp-area" value="<?=$v?>" hidden> <?=$l?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Address</label><input type="text" id="suppAddr" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea id="suppNotes" class="form-control" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveSupp" class="btn btn-primary">Save</button></div>
        </div>
    </div>
</div>

<!-- View Contributions Modal -->
<div class="modal fade" id="contribModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="contribTitle">Contribution History</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="contribBody"></div>
            <div class="modal-footer">
                <div id="addContribForm" class="w-100 border-top pt-3" style="display:none">
                    <div class="row g-2">
                        <div class="col-md-3"><select id="cType" class="form-select form-select-sm"><option value="financial">Financial</option><option value="material">Material</option><option value="service">Service</option><option value="prayer">Prayer</option><option value="mentorship">Mentorship</option></select></div>
                        <div class="col-md-3"><input type="number" id="cAmount" class="form-control form-control-sm" placeholder="Amount (optional)"></div>
                        <div class="col-md-3"><input type="date" id="cDate" class="form-control form-control-sm" value="<?=date('Y-m-d')?>"></div>
                        <div class="col-md-3"><button class="btn btn-success btn-sm w-100" onclick="submitContrib()">Add</button></div>
                        <div class="col-12"><input type="text" id="cDesc" class="form-control form-control-sm" placeholder="Description…"></div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if($canContrib): ?><button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('addContribForm').style.display=document.getElementById('addContribForm').style.display==='none'?'block':'none'"><i class="bi bi-plus me-1"></i>Add Contribution</button><?php endif; ?>
                    <button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE   = '<?=BASE_URL?>';
    const API    = BASE + '/api/supporters';
    const CAN_EDIT   = <?=json_encode($canEdit)?>;
    const CAN_DELETE = <?=json_encode($canDelete)?>;
    let currentPage=1, contribSuppId=null;
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    async function loadStats(){
        const res=await fetch(`${API}?action=stats`,{credentials:'include'});
        const d=(await res.json()).data||{};
        document.getElementById('stTotal')   .textContent = d.total||0;
        document.getElementById('stAlumni')  .textContent = d.alumni||0;
        document.getElementById('stExternal').textContent = d.external||0;
        document.getElementById('stChoir')   .textContent = d.choir||0;
        document.getElementById('stContrib') .textContent = 'RWF '+(Number(d.total_contributions||0).toLocaleString());
    }

    async function loadSupporters(page=1){
        currentPage=page;
        const grid=document.getElementById('supportersGrid');
        grid.innerHTML='<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params=new URLSearchParams({action:'list',page,per_page:12});
        const type=document.getElementById('fltType').value; if(type) params.set('type',type);
        const tier=document.getElementById('fltTier').value; if(tier) params.set('tier',tier);
        const status=document.getElementById('fltStatus').value; if(status) params.set('status',status);
        const search=document.getElementById('searchBox').value; if(search) params.set('search',search);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        document.getElementById('resultCount').textContent=`${data.total||0} supporter(s)`;
        const list=data.data||[];
        if(!list.length){grid.innerHTML='<div class="col-12 text-center text-muted py-5">No supporters found.</div>';renderPager(0,1);return;}
        const tierIcons={bronze:'🥉',silver:'🥈',gold:'🥇',platinum:'💎'};
        const typeColors={alumni:'success',external:'info',choir:'warning',organization:'primary'};
        grid.innerHTML=list.map(s=>{
            const name = s.supporter_type==='organization'&&s.organization_name ? esc(s.organization_name) : esc(s.firstname)+' '+esc(s.lastname);
            const initials=((s.firstname?.[0]??'')+(s.lastname?.[0]??'')).toUpperCase()||'??';
            const stCls=s.status==='active'?'success':'secondary';
            return `<div class="col-md-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start mb-3">
                    <div class="avatar avatar-md avatar-soft-${typeColors[s.supporter_type]||'primary'} avatar-circle me-3 flex-shrink-0">
                      <span class="avatar-initials">${esc(initials)}</span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                      <h6 class="mb-0 text-truncate">${name}</h6>
                      <small class="text-muted">${esc(s.email||s.phone||'—')}</small>
                    </div>
                    <span class="fs-5">${tierIcons[s.tier]||'🥉'}</span>
                  </div>
                  <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge bg-soft-${typeColors[s.supporter_type]||'primary'} text-${typeColors[s.supporter_type]||'primary'} text-capitalize">${esc(s.supporter_type)}</span>
                    <span class="badge bg-soft-${stCls} text-${stCls}">${esc(s.status)}</span>
                    <span class="badge bg-soft-secondary text-secondary text-capitalize">${esc(s.cep_session)}</span>
                  </div>
                  <div class="d-flex justify-content-between text-muted small mb-3">
                    <span>${s.contribution_count||0} contribution(s)</span>
                    <strong class="text-success">RWF ${Number(s.total_contributed||0).toLocaleString()}</strong>
                  </div>
                  <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-ghost-primary" onclick="viewContribs(${s.id},'${esc(name)}')"><i class="bi bi-clock-history"></i> History</button>
                    ${CAN_EDIT?`<button class="btn btn-xs btn-ghost-secondary" onclick="editSupporter(${s.id})"><i class="bi bi-pencil"></i></button>`:''}
                    ${CAN_DELETE?`<button class="btn btn-xs btn-ghost-danger" onclick="deleteSupporter(${s.id})"><i class="bi bi-trash"></i></button>`:''}
                  </div>
                </div>
              </div>
            </div>`;
        }).join('');
        renderPager(data.total,data.pages);
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator'); if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm"><li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadSupp(${currentPage-1});return false;">‹</a></li>
          ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadSupp(${i+1});return false;">${i+1}</a></li>`).join('')}
          <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadSupp(${currentPage+1});return false;">›</a></li></ul>`;
    }
    window.loadSupp=loadSupporters;

    window.viewContribs = async function(id, name){
        contribSuppId=id;
        document.getElementById('contribTitle').textContent=`${name} — Contributions`;
        document.getElementById('contribBody').innerHTML='<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('contribModal')).show();
        const res=await fetch(`${API}?action=get&id=${id}`,{credentials:'include'});
        const data=await res.json();
        const s=data.data;
        if(!s){document.getElementById('contribBody').innerHTML='<p class="text-danger">Not found.</p>';return;}
        const list=s.contributions||[];
        const typeColors={financial:'success',material:'info',service:'primary',prayer:'warning',mentorship:'secondary'};
        document.getElementById('contribBody').innerHTML=
          `<div class="alert alert-soft-primary mb-3">Total Financial Contributions: <strong>RWF ${Number(s.total_contributed||0).toLocaleString()}</strong></div>`+
          (list.length?`<div class="table-responsive"><table class="table table-sm table-borderless">
            <thead class="thead-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th><th>By</th></tr></thead>
            <tbody>${list.map(c=>`<tr>
              <td>${c.contribution_date}</td>
              <td><span class="badge bg-soft-${typeColors[c.contribution_type]||'secondary'} text-${typeColors[c.contribution_type]||'secondary'} text-capitalize">${esc(c.contribution_type)}</span></td>
              <td>${c.amount?'RWF '+Number(c.amount).toLocaleString():'—'}</td>
              <td class="text-muted">${esc(c.description||'—')}</td>
              <td>${esc(c.recorded_by_name||'—')}</td>
            </tr>`).join('')}</tbody>
          </table></div>`:'<p class="text-muted text-center py-3">No contributions recorded yet.</p>');
    };

    window.submitContrib = async function(){
        if(!contribSuppId) return;
        const payload={
            supporter_id:contribSuppId, contribution_type:document.getElementById('cType').value,
            amount:document.getElementById('cAmount').value||null,
            description:document.getElementById('cDesc').value,
            contribution_date:document.getElementById('cDate').value,
        };
        const res=await fetch(`${API}?action=add_contribution`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){viewContribs(contribSuppId,document.getElementById('contribTitle').textContent.split('—')[0].trim());loadStats();showToast('Contribution added!','success');}
        else showToast(data.message||'Failed','danger');
    };

    window.deleteSupporter = async function(id){
        if(!confirm('Delete this supporter and all their contributions?')) return;
        const res=await fetch(`${API}?action=delete`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
        const data=await res.json();
        if(data.success){loadSupporters(currentPage);loadStats();showToast('Deleted','success');}
        else showToast(data.message||'Failed','danger');
    };

    window.editSupporter = async function(id){
        const res=await fetch(`${API}?action=get&id=${id}`,{credentials:'include'});
        const data=await res.json(); const s=data.data;
        if(!s) return;
        document.getElementById('suppId').value     = s.id;
        document.getElementById('suppType').value   = s.supporter_type;
        document.getElementById('suppFn').value     = s.firstname;
        document.getElementById('suppLn').value     = s.lastname;
        document.getElementById('suppOrg').value    = s.organization_name||'';
        document.getElementById('suppEmail').value  = s.email||'';
        document.getElementById('suppPhone').value  = s.phone||'';
        document.getElementById('suppTier').value   = s.tier||'bronze';
        document.getElementById('suppSession').value= s.cep_session||'both';
        document.getElementById('suppIsAlumni').value= s.is_alumni?'1':'0';
        document.getElementById('suppGrad').value   = s.graduation_year||'';
        document.getElementById('suppAddr').value   = s.address||'';
        document.getElementById('suppNotes').value  = s.notes||'';
        document.getElementById('suppModalTitle').textContent = 'Edit Supporter';
        new bootstrap.Modal(document.getElementById('supporterModal')).show();
    };

    async function saveSupporter(){
        const id=document.getElementById('suppId').value;
        const areas=Array.from(document.querySelectorAll('.supp-area:checked')).map(c=>c.value).join(',');
        const payload={
            id:id||undefined, supporter_type:document.getElementById('suppType').value,
            firstname:document.getElementById('suppFn').value, lastname:document.getElementById('suppLn').value,
            organization_name:document.getElementById('suppOrg').value,
            email:document.getElementById('suppEmail').value, phone:document.getElementById('suppPhone').value,
            tier:document.getElementById('suppTier').value, cep_session:document.getElementById('suppSession').value,
            is_alumni:document.getElementById('suppIsAlumni').value,
            graduation_year:document.getElementById('suppGrad').value||null,
            address:document.getElementById('suppAddr').value, notes:document.getElementById('suppNotes').value,
            support_area:areas||'general',
        };
        const action=id?'update':'create';
        const res=await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('supporterModal'))?.hide();loadSupporters(currentPage);loadStats();showToast('Supporter saved!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    let searchTimer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadStats(); loadSupporters();
        ['fltType','fltTier','fltStatus'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>loadSupporters(1)));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>loadSupporters(1),350);});
        document.getElementById('btnSaveSupp')?.addEventListener('click',saveSupporter);
        document.getElementById('supporterModal')?.addEventListener('hidden.bs.modal',()=>{document.getElementById('suppId').value='';document.getElementById('suppModalTitle').textContent='Add Supporter';});
    });
})();
</script>
</body></html>