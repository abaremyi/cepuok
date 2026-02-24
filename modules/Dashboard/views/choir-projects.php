
<?php
// Choir Projects - redirects to projects filtered by category (or shows choir-specific projects)
/**
 * Choir Projects
 * File: modules/Dashboard/views/choir-projects.php
 */
$pageTitle          = 'Choir Projects';
$requiredPermission = 'choir.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canCreate = hasPermission($userPermissions, 'projects.create');
$canEdit   = hasPermission($userPermissions, 'projects.edit');
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
            <div class="col-sm"><h1 class="page-header-title">Choir Projects</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/choir-members')?>">Choir</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol></nav>
            </div>
            <?php if($canCreate): ?><div class="col-auto"><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cpModal"><i class="bi bi-plus-lg me-1"></i>New Choir Project</button></div><?php endif; ?>
        </div>
    </div>
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-success" id="stActive">—</div><small class="text-muted">Active</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-info" id="stPlanning">—</div><small class="text-muted">Planning</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="stDone">—</div><small class="text-muted">Completed</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-dark" id="stBudget">—</div><small class="text-muted">Total Budget</small></div></div></div>
    </div>
    <!-- Filters -->
    <div class="card mb-3"><div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <?php if($isSuperAdmin??false): ?><div class="col-auto"><select id="fltSession" class="form-select form-select-sm" style="width:140px"><option value="">All Sessions</option><option value="day">☀️ Day</option><option value="weekend">🌙 Weekend</option></select></div><?php endif; ?>
            <div class="col-auto"><select id="fltStatus" class="form-select form-select-sm" style="width:120px"><option value="">All Status</option><option value="planning">Planning</option><option value="active">Active</option><option value="on_hold">On Hold</option><option value="completed">Completed</option></select></div>
            <div class="col"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="search" id="searchBox" class="form-control" placeholder="Search choir projects…"></div></div>
        </div>
    </div></div>
    <div id="projectsGrid" class="row g-3"><div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div></div>
    <div id="paginator" class="d-flex justify-content-center mt-3"></div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>
<!-- Modal (reuse projects pattern with category preset to fundraising/evangelism etc) -->
<div class="modal fade" id="cpModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">New Choir Project</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <input type="hidden" id="cpId">
            <div class="col-12"><label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label><input type="text" id="cpTitle" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Category</label><select id="cpCategory" class="form-select"><option value="fundraising">💰 Fundraising</option><option value="evangelism">🕊️ Evangelism</option><option value="social">🤝 Social</option><option value="training">📚 Training</option><option value="infrastructure">🏗️ Infrastructure</option></select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Session</label><select id="cpSession" class="form-select"><option value="day">Day CEP</option><option value="weekend">Weekend CEP</option><option value="both">Both</option></select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Status</label><select id="cpStatus" class="form-select"><option value="planning">Planning</option><option value="active">Active</option></select></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Start Date</label><input type="date" id="cpStart" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">End Date</label><input type="date" id="cpEnd" class="form-control"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Budget (RWF)</label><input type="number" id="cpBudget" class="form-control" placeholder="0"></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea id="cpDesc" class="form-control" rows="2"></textarea></div>
        </div></div>
        <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveCP" class="btn btn-primary">Save</button></div>
    </div></div>
</div>
<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    const BASE=`<?=BASE_URL?>`,API=BASE+'/api/projects';
    const IS_SA=<?=json_encode($isSuperAdmin??false)?>,MY_SES=<?=json_encode($currentUser->session_type??null)?>;
    const CAN_EDIT=<?=json_encode($canEdit)?>;
    let currentPage=1;
    function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_SES;}
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
    const statusColors={planning:'info',active:'success',on_hold:'warning',completed:'primary',cancelled:'danger'};
    const catIcons={fundraising:'💰',evangelism:'🕊️',social:'🤝',training:'📚',infrastructure:'🏗️'};

    async function loadStats(){
        const s=sess();const params=new URLSearchParams({action:'stats'}); if(s) params.set('session',s);
        const d=(await (await fetch(`${API}?${params}`,{credentials:'include'})).json()).data||{};
        document.getElementById('stActive').textContent  = d.active||0;
        document.getElementById('stPlanning').textContent= d.planning||0;
        document.getElementById('stDone').textContent    = d.completed||0;
        document.getElementById('stBudget').textContent  = 'RWF '+Number(d.total_budget||0).toLocaleString();
    }

    async function loadProjects(page=1){
        currentPage=page;
        const grid=document.getElementById('projectsGrid');
        grid.innerHTML='<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params=new URLSearchParams({action:'list',page,per_page:9});
        const s=sess(); if(s) params.set('session',s);
        const st=document.getElementById('fltStatus').value; if(st) params.set('status',st);
        const q=document.getElementById('searchBox').value; if(q) params.set('search',q);
        const data=(await (await fetch(`${API}?${params}`,{credentials:'include'})).json());
        const list=data.data||[];
        if(!list.length){grid.innerHTML='<div class="col-12 text-center text-muted py-5">No choir projects found.</div>';renderPager(0,1);return;}
        grid.innerHTML=list.map(p=>{
            const sc=statusColors[p.status]||'secondary';
            const prog=parseInt(p.progress||0);
            return `<div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-body">
              <div class="d-flex justify-content-between mb-2"><span class="text-muted small">${esc(p.project_code||'')}</span><span class="badge bg-soft-${sc} text-${sc} text-capitalize">${esc(p.status?.replace('_',' '))}</span></div>
              <h6 class="fw-bold">${catIcons[p.category]||'📋'} ${esc(p.title)}</h6>
              ${p.lead_name?`<p class="text-muted small"><i class="bi bi-person me-1"></i>${esc(p.lead_name)}</p>`:''}
              <div class="mb-2"><div class="d-flex justify-content-between small mb-1"><span class="text-muted">Progress</span><strong>${prog}%</strong></div><div class="progress" style="height:6px"><div class="progress-bar" style="width:${prog}%"></div></div></div>
              <div class="d-flex justify-content-between small text-muted mb-3"><span>Budget: RWF ${Number(p.budget||0).toLocaleString()}</span><span>${p.tasks_done||0}/${p.task_total||0} tasks</span></div>
              <div class="d-flex gap-1">
                <a href="<?=url('admin/projects-management')?>" class="btn btn-xs btn-ghost-primary"><i class="bi bi-eye me-1"></i>View in Projects</a>
              </div>
            </div></div></div>`;
        }).join('');
        renderPager(data.total,data.pages);
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator'); if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm"><li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadP(${currentPage-1});return false;">‹</a></li>${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadP(${i+1});return false;">${i+1}</a></li>`).join('')}<li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadP(${currentPage+1});return false;">›</a></li></ul>`;
    }
    window.loadP=loadProjects;

    async function saveCP(){
        const id=document.getElementById('cpId').value;
        const payload={id:id||undefined,title:document.getElementById('cpTitle').value,category:document.getElementById('cpCategory').value,session:document.getElementById('cpSession').value,status:document.getElementById('cpStatus').value,start_date:document.getElementById('cpStart').value||null,end_date:document.getElementById('cpEnd').value||null,budget:document.getElementById('cpBudget').value||0,description:document.getElementById('cpDesc').value,progress:0};
        const action=id?'update':'create';
        const data=(await (await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})).json());
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('cpModal'))?.hide();loadProjects(currentPage);loadStats();showToast('Saved!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),3000);}
    let timer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadStats();loadProjects();
        ['fltSession','fltStatus'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>{loadProjects(1);loadStats();}));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>loadProjects(1),350);});
        document.getElementById('btnSaveCP')?.addEventListener('click',saveCP);
    });
})();
</script>
</body></html>