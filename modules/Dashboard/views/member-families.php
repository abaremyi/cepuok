<?php
/**
 * Spiritual Families Management
 * File: modules/Dashboard/views/member-families.php
 */
$pageTitle          = 'Spiritual Families';
$requiredPermission = 'families.view';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';
$canCreate = hasPermission($userPermissions, 'families.create');
$canEdit   = hasPermission($userPermissions, 'families.edit');
$canDelete = hasPermission($userPermissions, 'families.delete');
$canAssign = hasPermission($userPermissions, 'families.assign');
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
                <h1 class="page-header-title">Spiritual Families</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Families</li>
                </ol></nav>
            </div>
            <div class="col-auto d-flex gap-2">
                <?php if($canAssign): ?>
                <button class="btn btn-outline-primary btn-sm" onclick="openAssignModal()">
                    <i class="bi bi-person-plus me-1"></i> Assign Members
                </button>
                <?php endif; ?>
                <?php if($canCreate): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#familyModal">
                    <i class="bi bi-plus-lg me-1"></i> New Family
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-primary" id="statFamilies">—</div><small class="text-muted">Total Families</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-success" id="statAssigned">—</div><small class="text-muted">Members in Families</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-warning" id="statUnassigned">—</div><small class="text-muted">Unassigned Members</small></div></div></div>
        <div class="col-sm-3"><div class="card"><div class="card-body text-center"><div class="fs-2 fw-bold text-info" id="statDayCnt">—</div><small class="text-muted">Day CEP Families</small></div></div></div>
    </div>

    <!-- Session Tabs -->
    <ul class="nav nav-tabs mb-4" id="sessionTabs">
        <li class="nav-item"><a class="nav-link active" href="#" data-session="" onclick="switchSession(this,'');return false;">All Families</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-session="day" onclick="switchSession(this,'day');return false;">☀️ Day CEP</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-session="weekend" onclick="switchSession(this,'weekend');return false;">🌙 Weekend CEP</a></li>
    </ul>

    <!-- Families Grid -->
    <div id="familiesGrid" class="row g-3">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<!-- Family Create/Edit Modal -->
<div class="modal fade" id="familyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="familyModalTitle">New Spiritual Family</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="fmId">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label><input type="text" id="fmName" class="form-control" placeholder="e.g. Bethesda Family"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Family Code</label><input type="text" id="fmCode" class="form-control" placeholder="FAM-XXX"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                        <select id="fmSession" class="form-select"><option value="day">☀️ Day CEP</option><option value="weekend">🌙 Weekend CEP</option><option value="both">Both</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Motto</label><input type="text" id="fmMotto" class="form-control" placeholder="Family motto or verse"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Color</label><input type="color" id="fmColor" class="form-control form-control-color w-100" value="#007bff"></div>
                    <div class="col-md-3"><label class="form-label fw-semibold">Icon (Bootstrap Icon class)</label><input type="text" id="fmIcon" class="form-control" placeholder="bi bi-people" value="bi bi-people"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea id="fmDesc" class="form-control" rows="2" placeholder="About this family…"></textarea></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Family Parent (User)</label><input type="number" id="fmParent" class="form-control" placeholder="User ID"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Co-Parent (User)</label><input type="number" id="fmCoParent" class="form-control" placeholder="User ID"></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-ghost-secondary" data-bs-dismiss="modal">Cancel</button><button id="btnSaveFamily" class="btn btn-primary">Save Family</button></div>
        </div>
    </div>
</div>

<!-- View Family Members Modal -->
<div class="modal fade" id="viewFamilyModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="viewFamilyTitle">Family Members</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="input-group mb-3 w-50"><input type="text" id="memberSearch" class="form-control" placeholder="Search members…"><button class="btn btn-outline-secondary" onclick="searchFamilyMembers()"><i class="bi bi-search"></i></button></div>
                <div class="row g-2" id="familyMembersGrid"></div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Members Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Assign Members to Family</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-5"><label class="form-label fw-semibold">Target Family</label><select id="asFamilyId" class="form-select" id="asFamilySelect"><option value="">Select family…</option></select></div>
                    <div class="col-md-5"><label class="form-label fw-semibold">Search Unassigned Members</label><input type="text" id="asSearch" class="form-control" placeholder="Search…"></div>
                    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100" onclick="searchUnassigned()"><i class="bi bi-search"></i></button></div>
                </div>
                <div id="unassignedList" class="row g-2" style="max-height:300px;overflow-y:auto;"></div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><span id="selectedCount">0</span> selected</span>
                    <button id="btnAssign" class="btn btn-primary" onclick="doAssign()">Assign Selected</button>
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
    const API    = BASE + '/api/families';
    const IS_SA  = <?=json_encode($isSuperAdmin??false)?>;
    const MY_SES = <?=json_encode($currentUser->session_type??null)?>;
    const CAN_EDIT   = <?=json_encode($canEdit)?>;
    const CAN_DELETE = <?=json_encode($canDelete)?>;
    const CAN_ASSIGN = <?=json_encode($canAssign)?>;
    let currentSession = IS_SA ? '' : MY_SES;
    let viewFamilyId = null;
    let allFamilies  = [];

    function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

    async function loadStats(){
        const res  = await fetch(`${API}?action=stats`,{credentials:'include'});
        const data = await res.json();
        const list = data.data||[];
        let day=0,total=0,assigned=0,unassigned=0;
        list.forEach(r=>{
            total+=parseInt(r.total_families||0);
            if(r.cep_session==='day') day=parseInt(r.total_families||0);
            assigned   = parseInt(r.assigned_members||0);
            unassigned = parseInt(r.unassigned_members||0);
        });
        document.getElementById('statFamilies').textContent   = total;
        document.getElementById('statAssigned').textContent   = assigned;
        document.getElementById('statUnassigned').textContent = unassigned;
        document.getElementById('statDayCnt').textContent     = day;
    }

    async function loadFamilies(){
        const grid=document.getElementById('familiesGrid');
        grid.innerHTML='<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        const params=new URLSearchParams({action:'list'});
        if(currentSession) params.set('session',currentSession);
        const res  = await fetch(`${API}?${params}`,{credentials:'include'});
        const data = await res.json();
        allFamilies = data.data||[];
        if(!allFamilies.length){grid.innerHTML='<div class="col-12 text-center text-muted py-5">No families found. <a href="#" onclick="document.getElementById(\'newFamilyBtn\').click()">Create one</a>.</div>';return;}
        grid.innerHTML = allFamilies.map(f=>renderFamilyCard(f)).join('');
        // Populate assign modal family select
        const sel=document.getElementById('asFamilyId');
        if(sel){sel.innerHTML='<option value="">Select family…</option>'+allFamilies.map(f=>`<option value="${f.id}">${esc(f.family_name)} (${esc(f.cep_session)})</option>`).join('');}
    }

    function renderFamilyCard(f){
        const color   = f.color_code||'#007bff';
        const members = parseInt(f.member_count||0);
        const male    = parseInt(f.male_count||0);
        const female  = parseInt(f.female_count||0);
        const active  = parseInt(f.active_count||0);
        const sessBadge= f.cep_session==='day'?'bg-soft-warning text-warning':'bg-soft-primary text-primary';
        const initials = f.family_name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
        return `<div class="col-md-6 col-xl-4">
          <div class="card h-100 border-top border-4" style="border-top-color:${color}!important">
            <div class="card-body">
              <div class="d-flex align-items-start mb-3">
                <div class="avatar avatar-lg rounded-circle me-3 d-flex align-items-center justify-content-center text-white fw-bold fs-5"
                     style="background:${color};width:56px;height:56px;flex-shrink:0">
                  ${esc(initials)}
                </div>
                <div class="flex-grow-1 min-w-0">
                  <h5 class="mb-0 text-truncate">${esc(f.family_name)}</h5>
                  <span class="badge ${sessBadge} small">${esc(f.cep_session)}</span>
                  ${f.family_code?`<span class="badge bg-soft-secondary text-secondary ms-1 small">${esc(f.family_code)}</span>`:''}
                </div>
              </div>
              ${f.motto?`<p class="text-muted fst-italic small mb-2">"${esc(f.motto)}"</p>`:''}
              ${f.parent_name?`<p class="small mb-1 text-muted"><i class="bi bi-person-heart me-1"></i>${esc(f.parent_name)}${f.co_parent_name?` & ${esc(f.co_parent_name)}`:''}</p>`:''}
              <div class="row text-center mt-2 mb-3">
                <div class="col"><div class="fs-4 fw-bold" style="color:${color}">${members}</div><small class="text-muted">Members</small></div>
                <div class="col"><div class="fs-4 fw-bold text-primary">${male}</div><small class="text-muted">Male</small></div>
                <div class="col"><div class="fs-4 fw-bold text-danger">${female}</div><small class="text-muted">Female</small></div>
                <div class="col"><div class="fs-4 fw-bold text-success">${active}</div><small class="text-muted">Active</small></div>
              </div>
              <div class="d-flex gap-1 flex-wrap">
                <button class="btn btn-xs btn-ghost-primary" onclick="viewFamily(${f.id})"><i class="bi bi-people me-1"></i>View Members</button>
                ${CAN_EDIT?`<button class="btn btn-xs btn-ghost-secondary" onclick="editFamily(${f.id})"><i class="bi bi-pencil"></i></button>`:''}
                ${CAN_DELETE&&members===0?`<button class="btn btn-xs btn-ghost-danger" onclick="deleteFamily(${f.id})"><i class="bi bi-trash"></i></button>`:''}
              </div>
            </div>
          </div>
        </div>`;
    }

    window.switchSession = function(link, session){
        document.querySelectorAll('#sessionTabs .nav-link').forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
        currentSession = session;
        loadFamilies();
    };

    window.viewFamily = async function(id){
        viewFamilyId = id;
        const f = allFamilies.find(x=>x.id===id);
        document.getElementById('viewFamilyTitle').textContent = f?.family_name||'Family Members';
        document.getElementById('familyMembersGrid').innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('viewFamilyModal')).show();
        loadFamilyMembers(id,'');
    };

    async function loadFamilyMembers(id,search=''){
        const params=new URLSearchParams({action:'members',family_id:id});
        if(search) params.set('search',search);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const grid=document.getElementById('familyMembersGrid');
        const members=data.data||[];
        if(!members.length){grid.innerHTML='<div class="col-12 text-center text-muted py-3">No members in this family yet.</div>';return;}
        grid.innerHTML=members.map(m=>{
            const initials=((m.firstname?.[0]??'')+(m.lastname?.[0]??'')).toUpperCase();
            const avatar=m.profile_photo?`<img class="avatar-img" src="${BASE}/uploads/${esc(m.profile_photo)}" alt="">`:`<span class="avatar-initials">${esc(initials)}</span>`;
            const stCls={active:'success',pending:'warning',inactive:'secondary'}[m.status]??'secondary';
            return `<div class="col-md-4 col-lg-3">
              <div class="card card-sm text-center py-2">
                <div class="avatar avatar-md avatar-soft-primary avatar-circle mx-auto mb-2">${avatar}</div>
                <div class="fw-semibold small">${esc(m.firstname)} ${esc(m.lastname)}</div>
                <div class="text-muted smaller">${esc(m.faculty||'—')}</div>
                <span class="badge bg-soft-${stCls} text-${stCls} small">${esc(m.status)}</span>
                ${CAN_ASSIGN?`<button class="btn btn-xs btn-ghost-danger mt-1" onclick="removeMember(${m.id})" title="Remove from family"><i class="bi bi-x"></i></button>`:''}
              </div>
            </div>`;
        }).join('');
    }

    window.searchFamilyMembers = function(){
        if(viewFamilyId) loadFamilyMembers(viewFamilyId, document.getElementById('memberSearch').value);
    };

    window.removeMember = async function(memberId){
        if(!confirm('Remove this member from the family?')) return;
        const res=await fetch(`${API}?action=remove_member`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({member_id:memberId})});
        const data=await res.json();
        if(data.success){loadFamilyMembers(viewFamilyId,'');loadFamilies();showToast('Member removed from family','success');}
        else showToast(data.message||'Failed','danger');
    };

    window.editFamily = function(id){
        const f=allFamilies.find(x=>x.id===id);
        if(!f) return;
        document.getElementById('fmId').value    = f.id;
        document.getElementById('fmName').value  = f.family_name;
        document.getElementById('fmCode').value  = f.family_code||'';
        document.getElementById('fmSession').value=f.cep_session;
        document.getElementById('fmMotto').value = f.motto||'';
        document.getElementById('fmColor').value = f.color_code||'#007bff';
        document.getElementById('fmIcon').value  = f.icon_class||'bi bi-people';
        document.getElementById('fmDesc').value  = f.description||'';
        document.getElementById('fmParent').value  = f.parent_user_id||'';
        document.getElementById('fmCoParent').value= f.co_parent_user_id||'';
        document.getElementById('familyModalTitle').textContent='Edit Family';
        new bootstrap.Modal(document.getElementById('familyModal')).show();
    };

    window.deleteFamily = async function(id){
        if(!confirm('Delete this family? Members will be unassigned.')) return;
        const res=await fetch(`${API}?action=delete`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
        const data=await res.json();
        if(data.success){loadFamilies();loadStats();showToast('Family deleted','success');}
        else showToast(data.message||'Failed','danger');
    };

    async function saveFamily(){
        const id  = document.getElementById('fmId').value;
        const action = id ? 'update' : 'create';
        const payload={
            id:id||undefined, family_name:document.getElementById('fmName').value,
            family_code:document.getElementById('fmCode').value, cep_session:document.getElementById('fmSession').value,
            motto:document.getElementById('fmMotto').value, color_code:document.getElementById('fmColor').value,
            icon_class:document.getElementById('fmIcon').value, description:document.getElementById('fmDesc').value,
            parent_user_id:document.getElementById('fmParent').value||null,
            co_parent_user_id:document.getElementById('fmCoParent').value||null,
        };
        const res=await fetch(`${API}?action=${action}`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('familyModal'))?.hide();loadFamilies();loadStats();showToast('Family saved!','success');}
        else showToast(data.message||'Failed','danger');
        // reset form
        document.getElementById('fmId').value='';
        document.getElementById('familyModalTitle').textContent='New Spiritual Family';
    }

    // Assign modal
    window.openAssignModal = function(){ new bootstrap.Modal(document.getElementById('assignModal')).show(); loadFamilies(); };
    window.searchUnassigned = async function(){
        const sessF=IS_SA?(document.getElementById('asFamilyId')?.value?allFamilies.find(f=>f.id==document.getElementById('asFamilyId').value)?.cep_session:''):MY_SES;
        const s=document.getElementById('asSearch').value;
        const params=new URLSearchParams({action:'unassigned'});
        if(sessF) params.set('session',sessF); if(s) params.set('search',s);
        const res=await fetch(`${API}?${params}`,{credentials:'include'});
        const data=await res.json();
        const list=data.data||[];
        const el=document.getElementById('unassignedList');
        if(!list.length){el.innerHTML='<div class="col-12 text-center text-muted py-3">No unassigned members found.</div>';return;}
        el.innerHTML=list.map(m=>`<div class="col-md-4">
          <label class="d-flex align-items-center gap-2 card card-sm p-2 cursor-pointer" style="cursor:pointer">
            <input type="checkbox" class="assign-chk" value="${m.id}" onchange="updateCount()">
            <div>
              <div class="fw-semibold small">${esc(m.firstname)} ${esc(m.lastname)}</div>
              <div class="text-muted smaller">${esc(m.cep_session)} • ${esc(m.faculty||'—')}</div>
            </div>
          </label>
        </div>`).join('');
    };
    window.updateCount=function(){
        document.getElementById('selectedCount').textContent=document.querySelectorAll('.assign-chk:checked').length;
    };
    window.doAssign = async function(){
        const fid=parseInt(document.getElementById('asFamilyId').value);
        if(!fid){showToast('Please select a family','warning');return;}
        const ids=Array.from(document.querySelectorAll('.assign-chk:checked')).map(c=>parseInt(c.value));
        if(!ids.length){showToast('Select at least one member','warning');return;}
        const res=await fetch(`${API}?action=assign`,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({family_id:fid,member_ids:ids})});
        const data=await res.json();
        if(data.success){bootstrap.Modal.getInstance(document.getElementById('assignModal'))?.hide();loadFamilies();loadStats();showToast(`${data.assigned||ids.length} member(s) assigned!`,'success');}
        else showToast(data.message||'Failed','danger');
    };

    function showToast(msg,type='success'){
        const t=document.createElement('div');t.className=`alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;t.style.zIndex=9999;t.textContent=msg;
        document.body.appendChild(t);setTimeout(()=>t.remove(),3000);
    }

    document.addEventListener('DOMContentLoaded',()=>{
        loadFamilies(); loadStats();
        document.getElementById('familyModal').addEventListener('hidden.bs.modal',()=>{document.getElementById('fmId').value='';document.getElementById('familyModalTitle').textContent='New Spiritual Family';});
        document.getElementById('btnSaveFamily')?.addEventListener('click',saveFamily);
    });
})();
</script>
</body></html>