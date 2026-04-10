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
            <div class="col-auto">
                <button class="btn btn-outline-secondary btn-sm" onclick="printSupporters()">
                    <i class="bi bi-printer me-1"></i> Print List
                </button>
                <?php if($canCreate): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#supporterModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Supporter
                </button>
                <?php endif; ?>
            </div>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Photo</label>
                        <input type="file" id="suppPhoto" class="form-control" accept="image/jpeg,image/png,image/jpg">
                        <div id="photoPreviewContainer" style="display:none; margin-top:8px;">
                            <img id="photoPreview" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #ddd;">
                        </div>
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
        contribSuppId = id;
        document.getElementById('contribTitle').textContent = `${name} — Contributions`;
        document.getElementById('contribBody').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('contribModal')).show();
        
        const res = await fetch(`${API}?action=get&id=${id}`, {credentials:'include'});
        const data = await res.json();
        const s = data.data;
        
        if(!s){
            document.getElementById('contribBody').innerHTML = '<p class="text-danger">Not found.</p>';
            return;
        }
        
        const list = s.contributions || [];
        const typeColors = {financial:'success', material:'info', service:'primary', prayer:'warning', mentorship:'secondary'};
        
        document.getElementById('contribBody').innerHTML =
            `<div class="alert alert-soft-primary mb-3">Total Financial Contributions: <strong>RWF ${Number(s.total_contributed||0).toLocaleString()}</strong></div>` +
            (list.length ? 
                `<div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Recorded By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${list.map(c => `
                                <tr>
                                    <td>${c.contribution_date}</td>
                                    <td><span class="badge bg-soft-${typeColors[c.contribution_type]||'secondary'} text-${typeColors[c.contribution_type]||'secondary'} text-capitalize">${esc(c.contribution_type)}</span></td>
                                    <td>${c.amount ? 'RWF ' + Number(c.amount).toLocaleString() : '—'}</td>
                                    <td class="text-muted">${esc(c.description || '—')}</td>
                                    <td>${esc(c.recorded_by_name || '—')}</td>
                                    <td class="text-end">
                                        <button class="btn btn-xs btn-ghost-danger" onclick="deleteContribution(${c.id}, ${id})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>` : 
                '<p class="text-muted text-center py-3">No contributions recorded yet.</p>'
            );
    };

    window.deleteContribution = function(contributionId, supporterId) {
        Swal.fire({
            title: 'Delete Contribution?',
            html: `
                <div style="text-align: left;">
                    <p class="mb-2">Are you sure you want to delete this contribution?</p>
                    <p class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i> This action cannot be undone!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Yes, Delete It',
            cancelButtonText: '<i class="bi bi-x me-1"></i> Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const res = await fetch(`${API}?action=delete_contribution`, {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            contribution_id: contributionId,
                            supporter_id: supporterId 
                        })
                    });
                    
                    const data = await res.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to delete contribution');
                    }
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                    return false;
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Refresh the contributions view
                viewContribs(supporterId, document.getElementById('contribTitle').textContent.split('—')[0].trim());
                loadStats(); // Refresh stats
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Contribution has been deleted successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
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

    window.deleteSupporter = async function(id) {
        const result = await Swal.fire({
            title: 'Delete Supporter?',
            html: `
                <div style="text-align: left;">
                    <p class="mb-2">Are you sure you want to delete this supporter?</p>
                    <p class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> All contribution history will also be permanently deleted!</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c00a37',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Yes, Delete',
            cancelButtonText: '<i class="bi bi-x me-1"></i> Cancel'
        });
        
        if (!result.isConfirmed) return;
        
        // Show loading
        Swal.fire({
            title: 'Deleting...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        try {
            const res = await fetch(`${API}?action=delete`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            
            Swal.close();
            
            if (data.success) {
                loadSupporters(currentPage);
                loadStats();
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Supporter has been deleted.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to delete supporter'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Network error. Please try again.'
            });
        }
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

    async function saveSupporter() {
        const id = document.getElementById('suppId').value;
        const areas = Array.from(document.querySelectorAll('.supp-area:checked')).map(c => c.value).join(',');
        
        // Use FormData for file upload
        const formData = new FormData();
        
        formData.append('id', id || '');
        formData.append('supporter_type', document.getElementById('suppType').value);
        formData.append('firstname', document.getElementById('suppFn').value);
        formData.append('lastname', document.getElementById('suppLn').value);
        formData.append('organization_name', document.getElementById('suppOrg').value);
        formData.append('email', document.getElementById('suppEmail').value);
        formData.append('phone', document.getElementById('suppPhone').value);
        formData.append('tier', document.getElementById('suppTier').value);
        formData.append('cep_session', document.getElementById('suppSession').value);
        formData.append('is_alumni', document.getElementById('suppIsAlumni').value);
        formData.append('graduation_year', document.getElementById('suppGrad').value || '');
        formData.append('address', document.getElementById('suppAddr').value);
        formData.append('notes', document.getElementById('suppNotes').value);
        formData.append('support_area', areas || 'general');
        
        // Add photo if selected
        const photoFile = document.getElementById('suppPhoto').files[0];
        if (photoFile) {
            formData.append('photo', photoFile);
        }
        
        const action = id ? 'update' : 'create';
        
        // Show loading
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        try {
            const res = await fetch(`${API}?action=${action}`, {
                method: 'POST',
                credentials: 'include',
                body: formData  // Don't set Content-Type header, browser will set it with boundary
            });
            
            const data = await res.json();
            Swal.close();
            
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('supporterModal'))?.hide();
                loadSupporters(currentPage);
                loadStats();
                showToast('Supporter saved!', 'success');
            } else {
                showToast(data.message || 'Failed', 'danger');
            }
        } catch (error) {
            Swal.close();
            showToast('Network error', 'danger');
        }
    }

    window.printSupporters = async function() {
        // Show loading
        Swal.fire({
            title: 'Preparing Report...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        try {
            // Get current filters
            const params = new URLSearchParams({
                action: 'list',
                per_page: 1000,
                type: document.getElementById('fltType')?.value || '',
                tier: document.getElementById('fltTier')?.value || '',
                status: document.getElementById('fltStatus')?.value || '',
                search: document.getElementById('searchBox')?.value || ''
            });
            
            const res = await fetch(`${API}?${params}`, {credentials:'include'});
            const data = await res.json();
            const supporters = data.data || [];
            
            // Get stats
            const statsRes = await fetch(`${API}?action=stats`, {credentials:'include'});
            const statsData = await statsRes.json();
            const stats = statsData.data || {};
            
            Swal.close();
            
            if (supporters.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Data',
                    text: 'No supporters found to print.'
                });
                return;
            }
            
            // Calculate totals
            const totalSupporters = supporters.length;
            const totalContributions = supporters.reduce((sum, s) => sum + (Number(s.total_contributed) || 0), 0);
            const activeCount = supporters.filter(s => s.status === 'active').length;
            const alumniCount = supporters.filter(s => s.supporter_type === 'alumni').length;
            const externalCount = supporters.filter(s => s.supporter_type === 'external').length;
            const choirCount = supporters.filter(s => s.supporter_type === 'choir').length;
            const platinumCount = supporters.filter(s => s.tier === 'platinum').length;
            
            // Create print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>CEP Supporters List</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { 
                            padding: 30px; 
                            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
                            background: #f8fafc;
                        }
                        .report-container {
                            max-width: 1200px;
                            margin: 0 auto;
                            background: white;
                            padding: 40px;
                            border-radius: 16px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                        }
                        .header { 
                            text-align: center; 
                            margin-bottom: 40px;
                            padding-bottom: 20px;
                            border-bottom: 3px solid #d96d20;
                        }
                        .header h1 { 
                            color: #0c172d; 
                            margin: 0 0 5px;
                            font-size: 32px;
                            font-weight: 700;
                        }
                        .header h3 { 
                            color: #d96d20; 
                            font-size: 18px; 
                            font-weight: 500;
                            margin: 0 0 10px;
                        }
                        .header .date {
                            color: #64748b;
                            font-size: 14px;
                        }
                        .stats-grid { 
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 20px;
                            margin-bottom: 40px;
                        }
                        .stat-card { 
                            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                            border-radius: 12px;
                            padding: 20px;
                            text-align: center;
                            border: 1px solid #e2e8f0;
                        }
                        .stat-card .value { 
                            font-size: 32px; 
                            font-weight: 700; 
                            color: #0c172d;
                            line-height: 1.2;
                        }
                        .stat-card .label { 
                            font-size: 14px; 
                            color: #64748b;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            margin-top: 5px;
                        }
                        .table-container {
                            margin-top: 30px;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            overflow: hidden;
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            background: white;
                        }
                        th { 
                            background: #0c172d; 
                            color: white; 
                            padding: 15px; 
                            text-align: left;
                            font-weight: 600;
                            font-size: 13px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }
                        td { 
                            padding: 15px; 
                            border-bottom: 1px solid #e2e8f0;
                            vertical-align: top;
                        }
                        tr:last-child td {
                            border-bottom: none;
                        }
                        .tier-badge { 
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: 600;
                            text-transform: uppercase;
                        }
                        .tier-platinum { background: linear-gradient(135deg, #e5e4e2, #d4d4d4); color: #333; }
                        .tier-gold { background: linear-gradient(135deg, #ffd700, #f7c800); color: #333; }
                        .tier-silver { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: white; }
                        .tier-bronze { background: linear-gradient(135deg, #cd7f32, #b86c1f); color: white; }
                        .type-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: 600;
                            background: #e2e8f0;
                            color: #334155;
                            text-transform: capitalize;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 12px;
                            font-weight: 600;
                        }
                        .status-active { background: #dcfce7; color: #166534; }
                        .status-inactive { background: #fee2e2; color: #991b1b; }
                        .footer { 
                            margin-top: 40px; 
                            text-align: center; 
                            color: #64748b; 
                            font-size: 12px;
                            padding-top: 20px;
                            border-top: 1px solid #e2e8f0;
                        }
                        .print-buttons {
                            text-align: center;
                            margin-top: 30px;
                        }
                        .btn {
                            padding: 10px 30px;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            cursor: pointer;
                            border: none;
                            margin: 0 10px;
                        }
                        .btn-primary {
                            background: #d96d20;
                            color: white;
                        }
                        .btn-primary:hover {
                            background: #b85c18;
                        }
                        .btn-secondary {
                            background: #64748b;
                            color: white;
                        }
                        .btn-secondary:hover {
                            background: #475569;
                        }
                        @media print {
                            .no-print { display: none; }
                            body { background: white; padding: 0; }
                            .report-container { box-shadow: none; padding: 20px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="report-container">
                        <div class="header">
                            <h1> CEP University of Kigali</h1>
                            <h3>Supporters & Contributions Report</h3>
                            <div class="date">
                                Generated: ${new Date().toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </div>
                        </div>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="value">${totalSupporters}</div>
                                <div class="label">Total Supporters</div>
                            </div>
                            <div class="stat-card">
                                <div class="value">${activeCount}</div>
                                <div class="label">Active</div>
                            </div>
                            <div class="stat-card">
                                <div class="value">RWF ${totalContributions.toLocaleString()}</div>
                                <div class="label">Total Contributed</div>
                            </div>
                            <div class="stat-card">
                                <div class="value">${platinumCount}</div>
                                <div class="label">Platinum Donors</div>
                            </div>
                        </div>
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Tier</th>
                                        <th>Contact</th>
                                        <th>Contributions</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${supporters.map(s => {
                                        const name = s.supporter_type === 'organization' && s.organization_name 
                                            ? s.organization_name 
                                            : `${s.firstname || ''} ${s.lastname || ''}`.trim();
                                        const tierClass = `tier-${s.tier || 'bronze'}`;
                                        const statusClass = s.status === 'active' ? 'status-active' : 'status-inactive';
                                        
                                        return `
                                            <tr>
                                                <td>
                                                    <strong>${escapeHtml(name)}</strong>
                                                    ${s.is_alumni ? '<br><small class="text-muted">🎓 Alumni</small>' : ''}
                                                </td>
                                                <td><span class="type-badge">${escapeHtml(s.supporter_type || '—')}</span></td>
                                                <td><span class="tier-badge ${tierClass}">${escapeHtml(s.tier || 'bronze')}</span></td>
                                                <td>
                                                    ${s.email ? `<div>📧 ${escapeHtml(s.email)}</div>` : ''}
                                                    ${s.phone ? `<div>📞 ${escapeHtml(s.phone)}</div>` : ''}
                                                </td>
                                                <td>
                                                    <strong>RWF ${Number(s.total_contributed || 0).toLocaleString()}</strong>
                                                    <br><small>${s.contribution_count || 0} contributions</small>
                                                </td>
                                                <td><span class="status-badge ${statusClass}">${escapeHtml(s.status || '—')}</span></td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="footer">
                            <div>Generated by CEP UoK Portal</div>
                            <div style="margin-top: 5px;">This is an official report</div>
                        </div>
                        
                        <div class="print-buttons no-print">
                            <button class="btn btn-primary" onclick="window.print()">
                                🖨️ Print Report
                            </button>
                            <button class="btn btn-secondary" onclick="window.close()">
                                ✕ Close
                            </button>
                        </div>
                    </div>
                    
                    <script>
                        function escapeHtml(text) {
                            if (!text) return '';
                            return String(text)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        }
                        
                        // Auto-trigger print dialog after page loads
                        window.onload = function() {
                            setTimeout(() => {
                                window.print();
                            }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            
        } catch (error) {
            console.error('Print error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate report. Please try again.'
            });
        }
    };

    // Helper function for escaping HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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