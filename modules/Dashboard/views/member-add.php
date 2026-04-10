<?php
/**
 * Add New Member (Admin)
 * File: modules/Dashboard/views/member-add.php
 */
$pageTitle          = 'Add New Member';
$requiredPermission = 'membership.create';
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// Pre-load membership types and talents for the form selects
require_once ROOT_PATH . '/modules/Membership/models/MembershipModel.php';
$model = new MembershipModel();

// Get membership types
$membershipTypes = $model->getMembershipTypes();
if (empty($membershipTypes)) {
    $membershipTypes = [
        ['id' => 1, 'type_name' => 'Current Student & CEP Member'],
        ['id' => 2, 'type_name' => 'POST CEPiens (Alumni)'],
        ['id' => 3, 'type_name' => 'Frequent Visitor'],
        ['id' => 4, 'type_name' => 'Donor/Partner']
    ];
}

// Get talents - ensure it's always an array
$talentsData = $model->getTalents();
$talents = [];

// Handle different possible formats from getTalents()
if (!empty($talentsData)) {
    if (isset($talentsData[0]['talent_name'])) {
        // Already in correct format
        $talents = $talentsData;
    } else {
        // Might be grouped by category
        foreach ($talentsData as $category => $categoryTalents) {
            if (is_array($categoryTalents)) {
                foreach ($categoryTalents as $talent) {
                    if (isset($talent['id']) && isset($talent['talent_name'])) {
                        $talents[] = $talent;
                    }
                }
            }
        }
    }
}

// If still empty, provide default talents
if (empty($talents)) {
    $talents = [
        ['id' => 1, 'talent_name' => 'Singing'],
        ['id' => 2, 'talent_name' => 'Playing Instrument'],
        ['id' => 3, 'talent_name' => 'Worship Leading'],
        ['id' => 4, 'talent_name' => 'Photography'],
        ['id' => 5, 'talent_name' => 'Videography'],
        ['id' => 6, 'talent_name' => 'Graphic Design'],
        ['id' => 7, 'talent_name' => 'Social Media'],
        ['id' => 8, 'talent_name' => 'Public Speaking'],
        ['id' => 9, 'talent_name' => 'Event Planning'],
        ['id' => 10, 'talent_name' => 'Teaching'],
        ['id' => 11, 'talent_name' => 'Evangelism'],
        ['id' => 12, 'talent_name' => 'Prayer Ministry'],
        ['id' => 13, 'talent_name' => 'Hospitality'],
        ['id' => 14, 'talent_name' => 'Counseling']
    ];
}
?>
<?php include LAYOUTS_PATH . '/admin-header.php'; ?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include LAYOUTS_PATH . '/admin-lock-screen.php'; ?>
<script>(function(){var el=document.getElementById('sessionLockOverlay');if(el)el.dataset.email=<?=json_encode($currentUser->email??'')?>;})();</script>

<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title"><i class="bi bi-person-plus me-2"></i>Add New Member</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb breadcrumb-no-gutter">
                    <!-- <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li> -->
                    <li class="breadcrumb-item"><a href="<?=url('admin/membership-management')?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Add New Members </li>
                </ol></nav>
            </div>
            <div class="col-auto">
                <a href="<?=url('admin/membership-management')?>" class="btn btn-ghost-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Members
                </a>
            </div>
        </div>
    </div>

    <!-- Alert area -->
    <div id="alertArea"></div>

    <div class="row g-4">

        <!-- ── LEFT: Main Form ──────────────────────────────────────── -->
        <div class="col-xl-8">

            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-header-title"><i class="bi bi-person me-2"></i>Personal Information</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="fFirstname" class="form-control" placeholder="e.g. Jean">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="fLastname" class="form-control" placeholder="e.g. Mutabazi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" id="fEmail" class="form-control" placeholder="jean@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                            <input type="tel" id="fPhone" class="form-control" placeholder="+250 7XX XXX XXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select id="fGender" class="form-select">
                                <option value="">Select…</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" id="fDOB" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Year Joined CEP <span class="text-danger">*</span></label>
                            <select id="fYearJoined" class="form-select">
                                <?php for($y=date('Y');$y>=2010;$y--): ?>
                                <option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" id="fAddress" class="form-control" placeholder="e.g. KG 123 St, Kigali">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-header-title"><i class="bi bi-mortarboard me-2"></i>Academic Information</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Faculty / School</label>
                            <input type="text" id="fFaculty" class="form-control" placeholder="e.g. Faculty of Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Program / Course</label>
                            <input type="text" id="fProgram" class="form-control" placeholder="e.g. Computer Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select id="fAcademicYear" class="form-select">
                                <option value="">Select…</option>
                                <option>Year 1</option><option>Year 2</option>
                                <option>Year 3</option><option>Year 4</option>
                                <option>Year 5</option><option>Graduate</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faith Information -->
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-header-title"><i class="bi bi-heart me-2"></i>Faith &amp; Church</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Church Name</label>
                            <input type="text" id="fChurch" class="form-control" placeholder="e.g. PCEA Remera">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Born Again?</label>
                            <select id="fBornAgain" class="form-select">
                                <option value="Prefer not to say">Prefer not to say</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Baptized?</label>
                            <select id="fBaptized" class="form-select">
                                <option value="Prefer not to say">Prefer not to say</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bio / Notes</label>
                            <textarea id="fBio" class="form-control" rows="3" placeholder="Optional: any notes about this member…"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Talents -->
            <?php if(!empty($talents)): ?>
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-header-title"><i class="bi bi-stars me-2"></i>Talents</h4></div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach($talents as $t): ?>
                        <div class="col-md-3 col-sm-4 col-6">
                            <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                                <input type="checkbox" class="talent-chk form-check-input" value="<?=(int)$t['id']?>">
                                <span><?=htmlspecialchars($t['talent_name']??$t['name']??'')?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ── RIGHT: Membership Settings ──────────────────────────── -->
        <div class="col-xl-4">

            <div class="card mb-4 sticky-top" style="top:80px">
                <div class="card-header"><h4 class="card-header-title"><i class="bi bi-gear me-2"></i>Membership Settings</h4></div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">CEP Session <span class="text-danger">*</span></label>
                        <select id="fSession" class="form-select">
                            <?php if($isSuperAdmin??false): ?>
                            <option value="">Select…</option>
                            <option value="day">☀️ Day CEP</option>
                            <option value="weekend">🌙 Weekend CEP</option>
                            <?php else: ?>
                            <option value="<?=htmlspecialchars($currentUser->session_type??'day')?>">
                                <?=($currentUser->session_type??'day')==='weekend'?'🌙 Weekend CEP':'☀️ Day CEP'?>
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Membership Type</label>
                        <select id="fMemberType" class="form-select">
                            <?php foreach($membershipTypes as $mt): ?>
                            <option value="<?=(int)$mt['id']?>"><?=htmlspecialchars($mt['type_name']??$mt['name']??'')?></option>
                            <?php endforeach; ?>
                            <?php if(empty($membershipTypes)): ?>
                            <option value="1">Regular Member</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Initial Status</label>
                        <select id="fStatus" class="form-select">
                            <option value="active" selected>✅ Active (approved immediately)</option>
                            <option value="pending">⏳ Pending (needs review)</option>
                        </select>
                        <div class="form-text">Active members are fully approved. Pending members appear in the applications queue.</div>
                    </div>

                    <!-- Save buttons -->
                    <div class="d-grid gap-2">
                        <button id="btnSave" class="btn btn-primary btn-lg" onclick="saveMember()">
                            <i class="bi bi-person-check me-1"></i> Add Member
                        </button>
                        <a href="<?=url('admin/membership-management')?>" class="btn btn-ghost-secondary">
                            Cancel
                        </a>
                    </div>

                    <!-- Quick summary preview -->
                    <hr>
                    <div class="text-muted small">
                        <p class="mb-1"><strong>What happens next:</strong></p>
                        <ul class="ps-3 mb-0">
                            <li>A membership number (CEP-D/W-YEAR-XXXX) is auto-generated</li>
                            <li>Active members can be managed immediately in Membership Management</li>
                            <li>Pending members appear in the Applications pipeline</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>
<script>
(function(){
    'use strict';
    const BASE = '<?=BASE_URL?>';
    const API  = BASE + '/api/membership';

    function val(id){ return (document.getElementById(id)||{}).value || ''; }
    function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

    function showAlert(msg, type){
        const el = document.getElementById('alertArea');
        el.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${esc(msg)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        el.scrollIntoView({behavior:'smooth', block:'nearest'});
    }

    window.saveMember = async function(){
        const btn = document.getElementById('btnSave');

        // Client-side validation
        const errors = [];
        if (!val('fFirstname').trim())   errors.push('First name is required');
        if (!val('fLastname').trim())    errors.push('Last name is required');
        if (!val('fEmail').trim())       errors.push('Email is required');
        if (!val('fPhone').trim())       errors.push('Phone is required');
        if (!val('fGender'))             errors.push('Gender is required');
        if (!val('fSession'))            errors.push('CEP Session is required');
        if (!val('fYearJoined'))         errors.push('Year joined is required');

        if (errors.length) {
            showAlert(errors.join(' | '), 'danger');
            return;
        }

        const talents = Array.from(document.querySelectorAll('.talent-chk:checked')).map(c=>parseInt(c.value));

        const payload = {
            firstname          : val('fFirstname').trim(),
            lastname           : val('fLastname').trim(),
            email              : val('fEmail').trim(),
            phone              : val('fPhone').trim(),
            gender             : val('fGender'),
            date_of_birth      : val('fDOB') || null,
            address            : val('fAddress') || null,
            year_joined_cep    : val('fYearJoined'),
            cep_session        : val('fSession'),
            faculty            : val('fFaculty') || null,
            program            : val('fProgram') || null,
            academic_year      : val('fAcademicYear') || null,
            church_name        : val('fChurch') || null,
            is_born_again      : val('fBornAgain'),
            is_baptized        : val('fBaptized'),
            bio                : val('fBio') || null,
            membership_type_id : parseInt(val('fMemberType')) || 1,
            status             : val('fStatus'),
            talents            : talents,
        };

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

        try {
            const res  = await fetch(`${API}?action=adminCreate`, {
                method      : 'POST',
                credentials : 'include',
                headers     : {'Content-Type':'application/json'},
                body        : JSON.stringify(payload),
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Member Added Successfully!',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>${esc(payload.firstname)} ${esc(payload.lastname)}</strong> has been added to the membership.</p>
                            <p class="mb-0"><strong>Membership Number:</strong> <span class="badge bg-primary fs-6 p-2">${esc(data.membership_number)}</span></p>
                            <p class="mt-2 mb-0"><strong>Status:</strong> <span class="badge bg-${payload.status === 'active' ? 'success' : 'warning'}">${payload.status}</span></p>
                        </div>
                    `,
                    confirmButtonText: 'View Members List',
                    confirmButtonColor: '#7d37ff',
                    showCancelButton: true,
                    cancelButtonText: 'Add Another Member',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = BASE + '/admin/membership-management';
                    } else {
                        resetForm();
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-person-check me-1"></i> Add Member';
                        showAlert('Member added successfully! You can add another member.', 'success');
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Add Member',
                    text: data.message || 'Please check the details and try again.',
                    confirmButtonColor: '#dc3545'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-person-check me-1"></i> Add Member';
            }
        } catch(e) {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Please check your connection and try again.',
                confirmButtonColor: '#dc3545'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check me-1"></i> Add Member';
        }
    };

    window.resetForm = function() {
        document.getElementById('fFirstname').value = '';
        document.getElementById('fLastname').value = '';
        document.getElementById('fEmail').value = '';
        document.getElementById('fPhone').value = '';
        document.getElementById('fGender').value = '';
        document.getElementById('fDOB').value = '';
        document.getElementById('fAddress').value = '';
        document.getElementById('fYearJoined').value = '<?= date('Y') ?>';
        document.getElementById('fSession').value = '<?= $currentUser->session_type ?? 'day' ?>';
        document.getElementById('fFaculty').value = '';
        document.getElementById('fProgram').value = '';
        document.getElementById('fAcademicYear').value = '';
        document.getElementById('fChurch').value = '';
        document.getElementById('fBornAgain').value = 'Prefer not to say';
        document.getElementById('fBaptized').value = 'Prefer not to say';
        document.getElementById('fBio').value = '';
        document.getElementById('fMemberType').value = '1';
        document.getElementById('fStatus').value = 'active';
        document.querySelectorAll('.talent-chk').forEach(c => c.checked = false);
        
        showAlert('Form has been reset.', 'info');
    };

    // Allow pressing Enter on any input to submit
    document.addEventListener('keydown', function(e){
        if(e.key==='Enter' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT'){
            e.preventDefault();
            saveMember();
        }
    });
})();
</script>
</body></html>