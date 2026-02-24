<?php
/**
 * Projects Management
 * File: modules/Dashboard/views/projects-management.php
 */
$pageTitle          = 'Projects';
$requiredPermission = 'projects.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canCreate = hasPermission($userPermissions, 'projects.create');
$canEdit   = hasPermission($userPermissions, 'projects.edit');
$canTasks  = hasPermission($userPermissions, 'projects.manage_tasks');
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
            <div class="col-sm"><h1 class="page-header-title">Projects</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol></nav>
            </div>
            <?php if($canCreate): ?>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#projectModal">
                    <i class="bi bi-plus-lg me-1"></i> New Project
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php foreach([
            ['id'=>'stActive',    'label'=>'Active',     'color'=>'success'],
            ['id'=>'stPlanning',  'label'=>'Planning',   'color'=>'info'],
            ['id'=>'stCompleted', 'label'=>'Completed',  'color'=>'primary'],
            ['id'=>'stOnHold',    'label'=>'On Hold',    'color'=>'warning'],
            ['id'=>'stBudget',    'label'=>'Total Budget','color'=>'dark'],
        ] as $c): ?>
        <div class="col"><div class="card"><div class="card-body text-center">
            <div class="fs-2 fw-bold text-<?=$c['color']?>" id="<?=$c['id']?>">—</div>
            <small class="text-muted"><?=$c['label']?></small>
        </div></div></div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-3"><div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <?php if($isSuperAdmin??false): ?>
            <div class="col-auto"><select id="fltSession" class="form-select form-select-sm" style="width:140px">
                <option value="">All Sessions</option><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option>
            </select></div>
            <?php endif; ?>
            <div class="col-auto"><select id="fltStatus" class="form-select form-select-sm" style="width:120px">
                <option value="">All Status</option><option value="planning">Planning</option><option value="active">Active</option>
                <option value="on_hold">On Hold</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option>
            </select></div>
            <div class="col-auto"><select id="fltCategory" class="form-select form-select-sm" style="width:140px">
                <option value="">All Categories</option><option value="evangelism">Evangelism</option><option value="social">Social</option>
                <option value="fundraising">Fundraising</option><option value="infrastructure">Infrastructure</option><option value="training">Training</option>
            </select></div>
            <div class="col"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="searchBox" class="form-control" placeholder="Search projects…">
            </div></div>
        </div>
    </div></div>

    <!-- Projects Cards Grid -->
    <div id="projectsGrid" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
    <div id="paginator" class="d-flex justify-content-center mt-3"></div>
</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Project Create/Edit Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="pModalTitle">New Project</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="pId">
                <div class="row g-3">
                    <div class="col-12"><label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label><input type="text" id="pTitle" class="form-control" placeholder="Project title"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Category</label>
                        <select id="pCategory" class="form-select"><option value="evangelism">🕊️ Evangelism</option><option value="social">🤝 Social</option><option value="fundraising">💰 Fundraising</option><option value="infrastructure">🏗️ Infrastructure</option><option value="training">📚 Training</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="pSession" class="form-select"><option value="day">Day CEP</option><option value="weekend">Weekend CEP</option><option value="both">Both</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Status</label>
                        <select id="pStatus" class="form-select"><option value="planning">Planning</option><option value="active">Active</option><option value="on_hold">On Hold</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select>
                    </div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Start Date</label><input type="date" id="pStart" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">End Date</label><input type="date" id="pEnd" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Budget (RWF)</label><input type="number" id="pBudget" class="form-control" placeholder="0"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Project Lead (User ID)</label><input type="number" id="pLead" class="form-control" placeholder="User ID"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Progress (%)</label>
                        <input type="range" id="pProgress" class="form-range" min="0" max="100" step="5" oninput="document.getElementById('pProgressVal').textContent=this.value+'%'">
                        <small class="text-muted" id="pProgressVal">0%</small>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea id="pDesc" class="form-control" rows="3" placeholder="Project description and objectives…"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveProject" class="btn btn-primary">Save Project</button></div>
        </div>
    </div>
</div>

<!-- View Project Modal -->
<div class="modal fade" id="viewProjectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="vModalTitle">Project Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-xl-7" id="taskPanel"></div>
                    <div class="col-xl-5" id="updatePanel"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE=`<?=BASE_URL?>`,API=BASE+'/api/projects';
    const IS_SA=<?=json_encode($isSuperAdmin??false)?>,MY_SES=<?=json_encode($currentUser->session_type??null)?>;
    const CAN_EDIT=<?=json_encode($canEdit)?>,CAN_TASKS=<?=json_encode($canTasks)?>;
    let currentPage=1;
    function sess(){return IS_SA?(document.getElementById('fltSession')?.value||null):MY_SES;}
    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    const statusColors={planning:'info',active:'success',on_hold:'warning',completed:'primary',cancelled:'danger'};
    const catIcons={evangelism:'🕊️',social:'🤝',fundraising:'💰',infrastructure:'🏗️',training:'📚'};

    async function loadStats(){
        const s=sess();
        const params=new URLSearchParams({action:'stats'}); if(s) params.set('session',s);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const d=(await res.json()).data||{};
        document.getElementById('stActive').textContent    = d.active||0;
        document.getElementById('stPlanning').textContent  = d.planning||0;
        document.getElementById('stCompleted').textContent = d.completed||0;
        document.getElementById('stOnHold').textContent    = d.on_hold||0;
        document.getElementById('stBudget').textContent    = 'RWF '+Number(d.total_budget||0).toLocaleString();
    }

    async function loadProjects(page=1){
        currentPage=page;
        const grid=document.getElementById('projectsGrid');
        grid.innerHTML='<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params=new URLSearchParams({action:'list',page,per_page:9});
        const s=sess(); if(s) params.set('session',s);
        const st=document.getElementById('fltStatus').value; if(st) params.set('status',st);
        const c=document.getElementById('fltCategory').value; if(c) params.set('category',c);
        const q=document.getElementById('searchBox').value; if(q) params.set('search',q);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const list=data.data||[];
        if(!list.length){grid.innerHTML='<div class="col-12 text-center text-muted py-5">No projects found.</div>';renderPager(0,1);return;}
        grid.innerHTML=list.map(p=>{
            const sc=statusColors[p.status]||'secondary';
            const prog=parseInt(p.progress||0);
            const progCls=prog>=80?'success':prog>=50?'primary':'info';
            const spent=parseFloat(p.spent||0);const budget=parseFloat(p.budget||0);
            const budgetPct=budget?Math.round(spent/budget*100):0;
            const tasksDone=parseInt(p.tasks_done||0),taskTotal=parseInt(p.task_total||0);
            return `<div class="col-md-6 col-xl-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-muted small">${esc(p.project_code||'—')}</span>
                    <span class="badge bg-soft-${sc} text-${sc} text-capitalize">${esc(p.status?.replace('_',' '))}</span>
                  </div>
                  <h6 class="fw-bold mb-1">${catIcons[p.category]||'📋'} ${esc(p.title)}</h6>
                  ${p.lead_name?`<p class="text-muted small mb-2"><i class="bi bi-person me-1"></i>${esc(p.lead_name)}</p>`:''}
                  <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Progress</span><strong class="text-${progCls}">${prog}%</strong></div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-${progCls}" style="width:${prog}%"></div></div>
                  </div>
                  <div class="row g-2 text-center mb-3">
                    <div class="col">
                      <div class="small fw-bold">RWF ${Number(budget).toLocaleString()}</div>
                      <div class="text-muted" style="font-size:.7rem">Budget</div>
                    </div>
                    <div class="col">
                      <div class="small fw-bold text-${budgetPct>90?'danger':'success'}">RWF ${Number(spent).toLocaleString()}</div>
                      <div class="text-muted" style="font-size:.7rem">Spent (${budgetPct}%)</div>
                    </div>
                    <div class="col">
                      <div class="small fw-bold">${tasksDone}/${taskTotal}</div>
                      <div class="text-muted" style="font-size:.7rem">Tasks</div>
                    </div>
                  </div>
                  <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-ghost-primary" onclick="viewProject(${p.id})"><i class="bi bi-eye me-1"></i>View</button>
                    ${CAN_EDIT?`<button class="btn btn-xs btn-ghost-secondary" onclick="editProject(${p.id})"><i class="bi bi-pencil"></i></button>`:''}
                  </div>
                </div>
              </div>
            </div>`;
        }).join('');
        renderPager(data.total,data.pages);
    }

    function renderPager(total,pages){
        const el=document.getElementById('paginator');
        if(!el||pages<=1){el&&(el.innerHTML='');return;}
        el.innerHTML=`<ul class="pagination pagination-sm"><li class="page-item ${currentPage<=1?'disabled':''}"><a class="page-link" href="#" onclick="loadP(${currentPage-1});return false;">‹</a></li>
          ${Array.from({length:pages},(_,i)=>`<li class="page-item ${currentPage===i+1?'active':''}"><a class="page-link" href="#" onclick="loadP(${i+1});return false;">${i+1}</a></li>`).join('')}
          <li class="page-item ${currentPage>=pages?'disabled':''}"><a class="page-link" href="#" onclick="loadP(${currentPage+1});return false;">›</a></li></ul>`;
    }
    window.loadP=loadProjects;

    window.viewProject = async function(id){
        document.getElementById('vModalTitle').textContent='Loading…';
        document.getElementById('taskPanel').innerHTML='<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';
        document.getElementById('updatePanel').innerHTML='';
        new bootstrap.Modal(document.getElementById('viewProjectModal')).show();
        const res=await fetch(`${API}?action=get&id=${id}`,{credentials:'include'});
        const data=await res.json();const p=data.data;if(!p) return;
        document.getElementById('vModalTitle').textContent=p.project_code+' — '+p.title;
        const priorityColors={low:'secondary',medium:'info',high:'warning',urgent:'danger'};
        const taskStatusColors={todo:'secondary',in_progress:'primary',done:'success',blocked:'danger'};
        const tasks=p.tasks||[];
        document.getElementById('taskPanel').innerHTML=`
          <h6 class="fw-bold mb-3">Tasks <span class="badge bg-soft-secondary text-secondary">${tasks.length}</span></h6>
          ${CAN_TASKS?`<div class="input-group input-group-sm mb-3">
            <input type="text" id="newTaskTitle" class="form-control" placeholder="New task title…">
            <select id="newTaskAssigned" class="form-select form-select-sm" style="max-width:120px"><option value="">Assign to…</option></select>
            <button class="btn btn-success" onclick="addTask(${id})"><i class="bi bi-plus-lg"></i></button>
          </div>`:''}
          <div id="taskList">${tasks.length?tasks.map(t=>`<div class="d-flex align-items-center gap-2 border-bottom py-2">
            <span class="badge bg-soft-${taskStatusColors[t.status]||'secondary'} text-${taskStatusColors[t.status]||'secondary'} text-capitalize small" style="min-width:80px">${(t.status||'todo').replace('_',' ')}</span>
            <div class="flex-grow-1"><div class="small fw-semibold">${esc(t.title)}</div>${t.assigned_name?`<div class="text-muted" style="font-size:.7rem"><i class="bi bi-person me-1"></i>${esc(t.assigned_name)}</div>`:''}</div>
            <span class="badge bg-soft-${priorityColors[t.priority]||'secondary'} text-${priorityColors[t.priority]||'secondary'}">${esc(t.priority||'—')}</span>
            ${CAN_TASKS?`<select class="form-select form-select-sm" style="width:120px" onchange="updateTask(${t.id},this.value)">
              ${['todo','in_progress','done','blocked'].map(s=>`<option value="${s}" ${t.status===s?'selected':''}>${s.replace('_',' ')}</option>`).join('')}
            </select>`:''}
          </div>`).join(''):'<p class="text-muted text-center py-3">No tasks yet.</p>'}</div>`;
        const updates=p.updates||[];
        document.getElementById('updatePanel').innerHTML=`
          <h6 class="fw-bold mb-3">Updates <span class="badge bg-soft-secondary text-secondary">${updates.length}</span></h6>
          ${CAN_TASKS?`<div class="mb-3">
            <textarea id="newUpdateText" class="form-control form-control-sm mb-2" rows="2" placeholder="Progress update…"></textarea>
            <button class="btn btn-sm btn-primary w-100" onclick="addUpdate(${id})">Post Update</button>
          </div>`:''}
          <div style="max-height:350px;overflow-y:auto">${updates.map(u=>`<div class="border-start border-3 border-primary ps-3 mb-3">
            <div class="small fw-semibold">${esc(u.posted_by_name||'—')}</div>
            <div class="text-muted smaller">${u.created_at}</div>
            <p class="small mt-1 mb-0">${esc(u.update_text)}</p>
            ${u.progress?`<span class="badge bg-soft-primary text-primary mt-1">${u.progress}% progress</span>`:''}
          </div>`).join('')||'<p class="text-muted text-center py-3">No updates yet.</p>'}
          </div>`;
    };

    window.updateTask = async function(taskId, status){
        const res=await fetch(`${API}?action=task_status`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({task_id:taskId,status})});
        const data=await res.json();
        if(!data.success) showToast(data.message||'Failed','danger');
        else{showToast('Task updated','success');loadProjects(currentPage);}
    };

    window.addTask = async function(projectId){
        const title=document.getElementById('newTaskTitle').value;
        if(!title) return;
        const res=await fetch(`${API}?action=add_task`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({project_id:projectId,title,priority:'medium',status:'todo'})});
        const data=await res.json();
        if(data.success){document.getElementById('newTaskTitle').value='';viewProject(projectId);}
        else showToast(data.message||'Failed','danger');
    };

    window.addUpdate = async function(projectId){
        const text=document.getElementById('newUpdateText').value;
        if(!text) return;
        const res=await fetch(`${API}?action=add_update`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({project_id:projectId,update_text:text})});
        const data=await res.json();
        if(data.success){document.getElementById('newUpdateText').value='';viewProject(projectId);}
        else showToast(data.message||'Failed','danger');
    };

    window.editProject = async function(id){
        const res=await fetch(`${API}?action=get&id=${id}`,{credentials:'include'});
        const data=await res.json(); const p=data.data; if(!p) return;
        document.getElementById('pId').value       = p.id;
        document.getElementById('pTitle').value    = p.title;
        document.getElementById('pCategory').value = p.category;
        document.getElementById('pSession').value  = p.session;
        document.getElementById('pStatus').value   = p.status;
        document.getElementById('pStart').value    = p.start_date||'';
        document.getElementById('pEnd').value      = p.end_date||'';
        document.getElementById('pBudget').value   = p.budget||0;
        document.getElementById('pLead').value     = p.lead_user_id||'';
        document.getElementById('pProgress').value = p.progress||0;
        document.getElementById('pProgressVal').textContent = (p.progress||0)+'%';
        document.getElementById('pDesc').value     = p.description||'';
        document.getElementById('pModalTitle').textContent = 'Edit Project';
        new bootstrap.Modal(document.getElementById('projectModal')).show();
    };

    async function saveProject(){
        const id=document.getElementById('pId').value;
        const payload={
            id:id||undefined, title:document.getElementById('pTitle').value,
            category:document.getElementById('pCategory').value, session:document.getElementById('pSession').value,
            status:document.getElementById('pStatus').value, start_date:document.getElementById('pStart').value||null,
            end_date:document.getElementById('pEnd').value||null, budget:document.getElementById('pBudget').value||0,
            lead_user_id:document.getElementById('pLead').value||null, progress:document.getElementById('pProgress').value,
            description:document.getElementById('pDesc').value,
        };
        const action=id?'update':'create';
        const res=await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('projectModal'))?.hide();loadProjects(currentPage);loadStats();showToast('Project saved!','success');}
        else showToast(data.message||'Failed','danger');
    }

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    let timer;
    document.addEventListener('DOMContentLoaded',()=>{
        loadStats();loadProjects();
        ['fltSession','fltStatus','fltCategory'].forEach(id=>document.getElementById(id)?.addEventListener('change',()=>{loadProjects(1);loadStats();}));
        document.getElementById('searchBox')?.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>loadProjects(1),350);});
        document.getElementById('btnSaveProject')?.addEventListener('click',saveProject);
        document.getElementById('projectModal')?.addEventListener('hidden.bs.modal',()=>{document.getElementById('pId').value='';document.getElementById('pModalTitle').textContent='New Project';document.getElementById('pProgress').value=0;document.getElementById('pProgressVal').textContent='0%';});
    });
})();
</script>
</body></html>