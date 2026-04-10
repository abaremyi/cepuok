<?php
/**
 * Edit Member (Admin)
 * File: modules/Dashboard/views/member-edit.php
 * Route: /admin/member-edit?id={member_id}
 */
$pageTitle = 'Edit Member';
$requiredPermission = ['membership.edit', 'membership.create'];
require_once dirname(__DIR__, 3) . '/helpers/admin-base.php';

// Check if user has permission
$canEditMember =  hasAnyPermission($userPermissions, ['membership.edit', 'membership.create']);

if (!$canEditMember) {
    header('Location: ' . url('admin/membership-management') . '?error=You do not have permission to edit members');
    exit;
}

// Check if member ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . url('admin/membership-management') . '?error=Invalid member ID');
    exit;
}

$memberId = (int)$_GET['id'];

// Load member data
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';
$mc = new MembershipController();
$memberResult = $mc->getMember($memberId);

if (!$memberResult['success']) {
    header('Location: ' . url('admin/membership-management') . '?error=Member not found');
    exit;
}

$member = $memberResult['data'];

// Pre-load membership types and talents
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

// Get talents
$talentsData = $model->getTalents();
$allTalents = [];

if (!empty($talentsData)) {
    if (isset($talentsData[0]['talent_name'])) {
        $allTalents = $talentsData;
    } else {
        foreach ($talentsData as $category => $categoryTalents) {
            if (is_array($categoryTalents)) {
                foreach ($categoryTalents as $talent) {
                    if (isset($talent['id']) && isset($talent['talent_name'])) {
                        $allTalents[] = $talent;
                    }
                }
            }
        }
    }
}

// Get member's existing talents
$memberTalents = [];
if (!empty($member['talents'])) {
    foreach ($member['talents'] as $talent) {
        $memberTalents[] = $talent['id'];
    }
}

// Get families for dropdown
$familiesResult = $mc->getFamilies($member['cep_session'] ?? null);
$families = $familiesResult['data'] ?? [];

// Get churches list
require_once ROOT_PATH . '/modules/Membership/models/MembershipModel.php';
$churches = [];
try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT id, church_name FROM churches WHERE is_active = 1 ORDER BY church_name");
    $churches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error loading churches: " . $e->getMessage());
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
                <h1 class="page-header-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Member: 
                    <?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?=url('admin/membership-management')?>">Members</a></li>
                        <li class="breadcrumb-item active">Edit Member</li>
                    </ol>
                </nav>
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

    <!-- Member ID for JS -->
    <input type="hidden" id="memberId" value="<?= $memberId ?>">

    <div class="row g-4">

        <!-- LEFT: Main Form -->
        <div class="col-xl-8">

            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-header-title"><i class="bi bi-person me-2"></i>Personal Information</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="fFirstname" class="form-control" 
                                   value="<?= htmlspecialchars($member['firstname']) ?>" placeholder="e.g. Jean">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="fLastname" class="form-control" 
                                   value="<?= htmlspecialchars($member['lastname']) ?>" placeholder="e.g. Mutabazi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" id="fEmail" class="form-control" 
                                   value="<?= htmlspecialchars($member['email']) ?>" placeholder="jean@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                            <input type="tel" id="fPhone" class="form-control" 
                                   value="<?= htmlspecialchars($member['phone']) ?>" placeholder="+250 7XX XXX XXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select id="fGender" class="form-select">
                                <option value="">Select…</option>
                                <option value="Male" <?= $member['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $member['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" id="fDOB" class="form-control" 
                                   value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Year Joined CEP <span class="text-danger">*</span></label>
                            <select id="fYearJoined" class="form-select">
                                <?php for($y=date('Y');$y>=2010;$y--): ?>
                                <option value="<?=$y?>" <?= ($member['year_joined_cep'] ?? date('Y')) == $y ? 'selected' : '' ?>>
                                    <?=$y?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" id="fAddress" class="form-control" 
                                   value="<?= htmlspecialchars($member['address'] ?? '') ?>" 
                                   placeholder="e.g. KG 123 St, Kigali">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-header-title"><i class="bi bi-mortarboard me-2"></i>Academic Information</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Faculty / School</label>
                            <input type="text" id="fFaculty" class="form-control" 
                                   value="<?= htmlspecialchars($member['faculty'] ?? '') ?>" 
                                   placeholder="e.g. Faculty of Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Program / Course</label>
                            <input type="text" id="fProgram" class="form-control" 
                                   value="<?= htmlspecialchars($member['program'] ?? '') ?>" 
                                   placeholder="e.g. Computer Science">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select id="fAcademicYear" class="form-select">
                                <option value="">Select…</option>
                                <option value="Year 1" <?= ($member['academic_year'] ?? '') == 'Year 1' ? 'selected' : '' ?>>Year 1</option>
                                <option value="Year 2" <?= ($member['academic_year'] ?? '') == 'Year 2' ? 'selected' : '' ?>>Year 2</option>
                                <option value="Year 3" <?= ($member['academic_year'] ?? '') == 'Year 3' ? 'selected' : '' ?>>Year 3</option>
                                <option value="Year 4" <?= ($member['academic_year'] ?? '') == 'Year 4' ? 'selected' : '' ?>>Year 4</option>
                                <option value="Year 5" <?= ($member['academic_year'] ?? '') == 'Year 5' ? 'selected' : '' ?>>Year 5</option>
                                <option value="Graduate" <?= ($member['academic_year'] ?? '') == 'Graduate' ? 'selected' : '' ?>>Graduate</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faith Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-header-title"><i class="bi bi-heart me-2"></i>Faith &amp; Church</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Church</label>
                            <select id="fChurch" class="form-select">
                                <option value="">Select Church</option>
                                <?php foreach ($churches as $church): ?>
                                <option value="<?= htmlspecialchars($church['church_name']) ?>" 
                                    <?= ($member['church_name'] ?? '') == $church['church_name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($church['church_name']) ?>
                                </option>
                                <?php endforeach; ?>
                                <option value="other">Other (specify below)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Other Church Name</label>
                            <input type="text" id="fOtherChurch" class="form-control" 
                                   value="<?= htmlspecialchars($member['other_church_name'] ?? '') ?>" 
                                   placeholder="If church is Other">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Born Again?</label>
                            <select id="fBornAgain" class="form-select">
                                <option value="Prefer not to say" <?= ($member['is_born_again'] ?? 'Prefer not to say') == 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                                <option value="Yes" <?= ($member['is_born_again'] ?? '') == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= ($member['is_born_again'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Baptized?</label>
                            <select id="fBaptized" class="form-select">
                                <option value="Prefer not to say" <?= ($member['is_baptized'] ?? 'Prefer not to say') == 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                                <option value="Yes" <?= ($member['is_baptized'] ?? '') == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= ($member['is_baptized'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bio / Notes</label>
                            <textarea id="fBio" class="form-control" rows="3" 
                                      placeholder="Optional: any notes about this member…"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Talents -->
            <?php if(!empty($allTalents)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-header-title"><i class="bi bi-stars me-2"></i>Talents & Gifts</h4>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach($allTalents as $talent): ?>
                        <div class="col-md-3 col-sm-4 col-6">
                            <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                                <input type="checkbox" class="talent-chk form-check-input" 
                                       value="<?= (int)$talent['id'] ?>"
                                       <?= in_array($talent['id'], $memberTalents) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($talent['talent_name'] ?? $talent['name'] ?? '') ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT: Membership Settings -->
        <div class="col-xl-4">

            <div class="card mb-4 sticky-top" style="top:80px">
                <div class="card-header">
                    <h4 class="card-header-title"><i class="bi bi-gear me-2"></i>Membership Settings</h4>
                </div>
                <div class="card-body">

                    <!-- Membership Number (read-only) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Membership Number</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($member['membership_number'] ?? 'Not assigned') ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">CEP Session <span class="text-danger">*</span></label>
                        <select id="fSession" class="form-select" <?= !$isSuperAdmin ? 'disabled' : '' ?>>
                            <?php if($isSuperAdmin): ?>
                            <option value="day" <?= ($member['cep_session'] ?? '') == 'day' ? 'selected' : '' ?>>☀️ Day CEP</option>
                            <option value="weekend" <?= ($member['cep_session'] ?? '') == 'weekend' ? 'selected' : '' ?>>🌙 Weekend CEP</option>
                            <?php else: ?>
                            <option value="<?= htmlspecialchars($member['cep_session'] ?? 'day') ?>">
                                <?= ($member['cep_session'] ?? 'day') == 'weekend' ? '🌙 Weekend CEP' : '☀️ Day CEP' ?>
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Spiritual Family</label>
                        <select id="fFamily" class="form-select">
                            <option value="">No family assigned</option>
                            <?php foreach ($families as $family): ?>
                            <option value="<?= $family['id'] ?>" 
                                <?= ($member['family_id'] ?? '') == $family['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($family['family_name']) ?> 
                                (<?= $family['member_count'] ?? 0 ?> members)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Membership Type</label>
                        <select id="fMemberType" class="form-select">
                            <?php foreach($membershipTypes as $mt): ?>
                            <option value="<?= (int)$mt['id'] ?>" 
                                <?= ($member['membership_type_id'] ?? '') == $mt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mt['type_name'] ?? $mt['name'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="fStatus" class="form-select">
                            <option value="active" <?= ($member['status'] ?? '') == 'active' ? 'selected' : '' ?>>✅ Active</option>
                            <option value="pending" <?= ($member['status'] ?? '') == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                            <option value="inactive" <?= ($member['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>❌ Inactive</option>
                            <option value="suspended" <?= ($member['status'] ?? '') == 'suspended' ? 'selected' : '' ?>>⛔ Suspended</option>
                        </select>
                    </div>

                    <!-- Save buttons -->
                    <div class="d-grid gap-2">
                        <button id="btnUpdate" class="btn btn-primary btn-lg" onclick="updateMember()">
                            <i class="bi bi-check-circle me-1"></i> Update Member
                        </button>
                        <?php if ($isSuperAdmin || hasPermission($userPermissions, 'membership.delete')): ?>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteMember()">
                            <i class="bi bi-trash me-1"></i> Delete Member
                        </button>
                        <?php endif; ?>
                        <a href="<?=url('admin/membership-management')?>" class="btn btn-ghost-secondary">
                            Cancel
                        </a>
                    </div>

                    <!-- Member info -->
                    <hr>
                    <div class="text-muted small">
                        <p class="mb-1"><strong>Member Information:</strong></p>
                        <ul class="ps-3 mb-0">
                            <li>Registered: <?= date('M d, Y', strtotime($member['created_at'] ?? 'now')) ?></li>
                            <?php if (!empty($member['approved_at'])): ?>
                            <li>Approved: <?= date('M d, Y', strtotime($member['approved_at'])) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($member['approved_by_firstname'])): ?>
                            <li>Approved by: <?= htmlspecialchars($member['approved_by_firstname'] . ' ' . ($member['approved_by_lastname'] ?? '')) ?></li>
                            <?php endif; ?>
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
    const BASE = '<?= BASE_URL ?>';
    const API = BASE + '/api/membership';
    const memberId = document.getElementById('memberId').value;

    function val(id) { 
        const el = document.getElementById(id);
        return el ? el.value : ''; 
    }
    
    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showAlert(msg, type) {
        const el = document.getElementById('alertArea');
        el.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${esc(msg)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    window.updateMember = async function() {
        const btn = document.getElementById('btnUpdate');

        // Client-side validation
        const errors = [];
        if (!val('fFirstname').trim()) errors.push('First name is required');
        if (!val('fLastname').trim()) errors.push('Last name is required');
        if (!val('fEmail').trim()) errors.push('Email is required');
        if (!val('fPhone').trim()) errors.push('Phone is required');
        if (!val('fGender')) errors.push('Gender is required');
        if (!val('fYearJoined')) errors.push('Year joined is required');

        if (errors.length) {
            showAlert(errors.join(' | '), 'danger');
            return;
        }

        const talents = Array.from(document.querySelectorAll('.talent-chk:checked'))
            .map(c => parseInt(c.value));

        const payload = {
            firstname: val('fFirstname').trim(),
            lastname: val('fLastname').trim(),
            email: val('fEmail').trim(),
            phone: val('fPhone').trim(),
            gender: val('fGender'),
            date_of_birth: val('fDOB') || null,
            address: val('fAddress') || null,
            year_joined_cep: val('fYearJoined'),
            cep_session: val('fSession'),
            faculty: val('fFaculty') || null,
            program: val('fProgram') || null,
            academic_year: val('fAcademicYear') || null,
            church_name: val('fChurch') === 'other' ? val('fOtherChurch') : val('fChurch'),
            other_church_name: val('fChurch') === 'other' ? val('fOtherChurch') : null,
            is_born_again: val('fBornAgain'),
            is_baptized: val('fBaptized'),
            bio: val('fBio') || null,
            membership_type_id: parseInt(val('fMemberType')) || 1,
            family_id: val('fFamily') || null,
            status: val('fStatus'),
            talents: talents
        };

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating…';

        try {
            const res = await fetch(`${API}?action=update&id=${memberId}`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Member Updated Successfully!',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>${esc(payload.firstname)} ${esc(payload.lastname)}</strong> has been updated.</p>
                            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-${payload.status === 'active' ? 'success' : payload.status === 'pending' ? 'warning' : 'secondary'}">${payload.status}</span></p>
                        </div>
                    `,
                    confirmButtonText: 'View Members List',
                    confirmButtonColor: '#377dff',
                    showCancelButton: true,
                    cancelButtonText: 'Stay Here',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = BASE + '/admin/membership-management';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Update Member';
                        showAlert('Member updated successfully!', 'success');
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Update Member',
                    text: data.message || 'Please check the details and try again.',
                    confirmButtonColor: '#dc3545'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Update Member';
            }
        } catch (e) {
            console.error('Update error:', e);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Please check your connection and try again.',
                confirmButtonColor: '#dc3545'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Update Member';
        }
    };

    window.deleteMember = function() {
        Swal.fire({
            title: 'Delete Member?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`${API}?action=delete&id=${memberId}`, {
                        method: 'DELETE',
                        credentials: 'include'
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Member has been deleted.',
                            timer: 2000
                        }).then(() => {
                            window.location.href = BASE + '/admin/membership-management';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete member.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please check your connection.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }
        });
    };

    // Handle church selection
    document.getElementById('fChurch')?.addEventListener('change', function() {
        const otherField = document.getElementById('fOtherChurch');
        if (this.value === 'other') {
            otherField.style.display = 'block';
            otherField.required = true;
        } else {
            otherField.style.display = 'none';
            otherField.required = false;
        }
    });

    // Initialize other church field visibility
    (function() {
        const churchSelect = document.getElementById('fChurch');
        const otherField = document.getElementById('fOtherChurch');
        if (churchSelect && otherField) {
            if (churchSelect.value === 'other' || (otherField.value && otherField.value.trim() !== '')) {
                otherField.style.display = 'block';
            } else {
                otherField.style.display = 'none';
            }
        }
    })();

})();
</script>
</body>
</html>