<?php
/**
 * Session Management Page
 * File: modules/Dashboard/views/session-management.php
 * Super Admin: Control portal access, session settings, committee years, handover
 */

require_once ROOT_PATH . '/helpers/AuthMiddleware.php';
require_once ROOT_PATH . '/helpers/PermissionHelper.php';
require_once ROOT_PATH . '/config/database.php';

$auth = new AuthMiddleware();
$currentUser = $auth->requireAuth([], true); // Super admin only
$userPermissions = $currentUser->permissions ?? [];
$pageTitle = 'Session Management';
$currentPage = 'session-management.php';

$db = Database::getConnection();

// Load portal sessions
$sessions = $db->query("
    SELECT cs.*, ly.year_label, ly.year_start, ly.year_end, ly.is_current AS ly_current,
           lu.firstname AS locked_by_fn, lu.lastname AS locked_by_ln
    FROM cep_sessions cs
    LEFT JOIN leadership_years ly ON cs.committee_year_id = ly.id
    LEFT JOIN users lu ON cs.locked_by = lu.id
    ORDER BY cs.session_type, cs.is_current DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Load all committee years
$committeeYears = $db->query("
    SELECT * FROM leadership_years ORDER BY year_start DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Member stats per session
$memberStats = $db->query("
    SELECT cep_session, 
           COUNT(*) as total,
           SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
           SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
    FROM members GROUP BY cep_session
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Rebuild as keyed array
$sessionStats = [];
$rows = $db->query("SELECT cep_session, COUNT(*) as total, SUM(status='active') as active, SUM(status='pending') as pending FROM members GROUP BY cep_session")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) $sessionStats[$r['cep_session']] = $r;

include LAYOUTS_PATH . '/admin-header.php';
?>
<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<script src="<?= admin_js_url('hs.theme-appearance.js') ?>"></script>
<?php include LAYOUTS_PATH . '/admin-navbar.php'; ?>
<?php include LAYOUTS_PATH . '/admin-sidebar.php'; ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <!-- Page Header -->
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col">
        <h1 class="page-header-title"><i class="bi-layers me-2"></i>Session Management</h1>
        <p class="text-muted mb-0">Control portal access, manage CEP sessions and committee transitions</p>
      </div>
    </div>
  </div>

  <!-- Alert container -->
  <div id="pageAlert" class="alert" style="display:none;" role="alert"></div>

  <!-- ====== SESSION ACCESS CONTROL ====== -->
  <div class="row mb-4">
    <?php
    $sessionDefs = [
        'day'     => ['label' => 'Day CEP', 'icon' => '☀️', 'color' => 'warning', 'badge_class' => 'bg-warning text-dark'],
        'weekend' => ['label' => 'Weekend CEP', 'icon' => '🌙', 'color' => 'primary', 'badge_class' => 'bg-primary text-white'],
    ];
    $currentSessions = [];
    foreach ($sessions as $s) {
        if ($s['is_current']) $currentSessions[$s['session_type']] = $s;
    }
    ?>

    <?php foreach ($sessionDefs as $sType => $sDef): ?>
      <?php $sess = $currentSessions[$sType] ?? null; ?>
      <div class="col-lg-6 mb-4">
        <div class="card h-100 border-<?= $sDef['color'] ?>" style="border-top: 4px solid;">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= $sDef['icon'] ?> <?= $sDef['label'] ?></h4>
            <?php if ($sess): ?>
              <?php if ($sess['portal_enabled']): ?>
                <span class="badge bg-success fs-6"><i class="bi-unlock-fill me-1"></i>Portal Active</span>
              <?php else: ?>
                <span class="badge bg-danger fs-6"><i class="bi-lock-fill me-1"></i>Portal Locked</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="badge bg-secondary">No Session Set</span>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <?php if ($sess): ?>
              <!-- Stats row -->
              <div class="row text-center mb-3">
                <div class="col-4">
                  <div class="fs-4 fw-bold text-primary"><?= $sessionStats[$sType]['total'] ?? 0 ?></div>
                  <small class="text-muted">Total Members</small>
                </div>
                <div class="col-4">
                  <div class="fs-4 fw-bold text-success"><?= $sessionStats[$sType]['active'] ?? 0 ?></div>
                  <small class="text-muted">Active</small>
                </div>
                <div class="col-4">
                  <div class="fs-4 fw-bold text-warning"><?= $sessionStats[$sType]['pending'] ?? 0 ?></div>
                  <small class="text-muted">Pending</small>
                </div>
              </div>

              <div class="mb-3">
                <table class="table table-sm table-borderless mb-0">
                  <tr>
                    <td class="text-muted ps-0" style="width:140px;">Committee Year:</td>
                    <td><strong><?= htmlspecialchars($sess['year_label'] ?? 'N/A') ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0">Session Label:</td>
                    <td><?= htmlspecialchars($sess['session_label']) ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0">Academic Year:</td>
                    <td><?= htmlspecialchars($sess['academic_year']) ?></td>
                  </tr>
                  <?php if ($sess['handover_date']): ?>
                  <tr>
                    <td class="text-muted ps-0">Handover Date:</td>
                    <td><span class="badge bg-info"><?= date('D, d M Y', strtotime($sess['handover_date'])) ?></span></td>
                  </tr>
                  <?php endif; ?>
                  <?php if (!$sess['portal_enabled'] && $sess['locked_by_fn']): ?>
                  <tr>
                    <td class="text-muted ps-0">Locked By:</td>
                    <td><?= htmlspecialchars($sess['locked_by_fn'] . ' ' . $sess['locked_by_ln']) ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0">Reason:</td>
                    <td class="text-danger"><?= htmlspecialchars($sess['portal_locked_reason'] ?? '') ?></td>
                  </tr>
                  <?php endif; ?>
                </table>
              </div>

              <!-- Portal Toggle -->
              <div class="d-flex gap-2">
                <?php if ($sess['portal_enabled']): ?>
                  <button class="btn btn-danger btn-sm flex-grow-1" onclick="lockPortal('<?= $sType ?>')">
                    <i class="bi-lock-fill me-1"></i>Lock Portal Access
                  </button>
                <?php else: ?>
                  <button class="btn btn-success btn-sm flex-grow-1" onclick="unlockPortal('<?= $sType ?>')">
                    <i class="bi-unlock-fill me-1"></i>Unlock Portal Access
                  </button>
                <?php endif; ?>
                <button class="btn btn-outline-secondary btn-sm" onclick="editSession('<?= $sType ?>',<?= $sess['id'] ?>, <?= htmlspecialchars(json_encode($sess)) ?>)">
                  <i class="bi-pencil me-1"></i>Edit
                </button>
              </div>
            <?php else: ?>
              <div class="text-center py-4 text-muted">
                <i class="bi-layers" style="font-size:48px;"></i>
                <p class="mt-2">No current session configured</p>
                <button class="btn btn-primary" onclick="createSession('<?= $sType ?>')">
                  <i class="bi-plus me-1"></i>Setup <?= $sDef['label'] ?>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ====== COMMITTEE YEARS OVERVIEW ====== -->
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi-calendar-range me-2"></i>Committee Years</h4>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#handoverModal">
        <i class="bi-box-arrow-right me-1"></i>Initiate Handover
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-borderless table-hover table-align-middle mb-0">
        <thead class="thead-light">
          <tr>
            <th>Committee Year</th>
            <th>Period</th>
            <th>Sessions</th>
            <th>Status</th>
            <th>Handover</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($committeeYears as $cy): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($cy['year_label']) ?></strong>
              </td>
              <td><?= $cy['year_start'] ?> – <?= $cy['year_end'] ?></td>
              <td>
                <?php if ($cy['has_dual_sessions']): ?>
                  <span class="badge bg-warning text-dark me-1">Day</span>
                  <span class="badge bg-primary">Weekend</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Single</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($cy['is_current']): ?>
                  <span class="badge bg-success"><i class="bi-check-circle me-1"></i>Current</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Past</span>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $ho = $db->prepare("SELECT * FROM committee_handovers WHERE outgoing_year_id = ? LIMIT 1");
                  $ho->execute([$cy['id']]);
                  $handover = $ho->fetch(PDO::FETCH_ASSOC);
                  if ($handover):
                    $hoStatus = $handover['status'] === 'completed' ? 'bg-success' : 'bg-warning text-dark';
                ?>
                  <span class="badge <?= $hoStatus ?>"><?= ucfirst($handover['status']) ?></span>
                  <small class="text-muted d-block"><?= date('d M Y', strtotime($handover['handover_date'])) ?></small>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>/admin/leadership-management?year=<?= $cy['id'] ?>" class="btn btn-outline-primary btn-xs">
                  <i class="bi-people me-1"></i>View Committee
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ====== QUICK ACTIONS ====== -->
  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card card-hover-shadow h-100">
        <div class="card-body text-center py-4">
          <div class="mb-3" style="font-size:48px;">👥</div>
          <h5>Manage Member Sessions</h5>
          <p class="text-muted small">Move members between Day and Weekend sessions</p>
          <a href="<?= BASE_URL ?>/admin/membership-management" class="btn btn-outline-primary btn-sm">
            Go to Members
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card card-hover-shadow h-100">
        <div class="card-body text-center py-4">
          <div class="mb-3" style="font-size:48px;">🏛️</div>
          <h5>Committee Handover</h5>
          <p class="text-muted small">Transfer responsibilities and records to incoming committee</p>
          <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#handoverModal">
            Start Handover
          </button>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card card-hover-shadow h-100">
        <div class="card-body text-center py-4">
          <div class="mb-3" style="font-size:48px;">📋</div>
          <h5>Audit Logs</h5>
          <p class="text-muted small">Review all portal activity and access logs</p>
          <a href="<?= BASE_URL ?>/admin/audit-logs" class="btn btn-outline-secondary btn-sm">
            View Logs
          </a>
        </div>
      </div>
    </div>
  </div>

</div><!-- .content -->
</main>

<!-- ====== MODALS ====== -->

<!-- Lock Portal Modal -->
<div class="modal fade" id="lockPortalModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi-lock-fill me-2 text-danger"></i>Lock Portal Access</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="bi-exclamation-triangle me-2"></i>
          <strong>Warning:</strong> Locking portal access will prevent all leaders in this session from accessing the portal until you unlock it.
        </div>
        <input type="hidden" id="lockSessionType">
        <div class="mb-3">
          <label class="form-label fw-bold">Reason for locking <span class="text-danger">*</span></label>
          <textarea id="lockReason" class="form-control" rows="3" placeholder="e.g. Committee handover in progress, System maintenance..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" onclick="confirmLock()">
          <i class="bi-lock-fill me-1"></i>Lock Portal
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit/Create Session Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sessionModalTitle">Session Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sessionEditType">
        <input type="hidden" id="sessionEditId">
        <div class="mb-3">
          <label class="form-label fw-semibold">Session Label <span class="text-danger">*</span></label>
          <input type="text" id="sessionLabel" class="form-control" placeholder="e.g. Day CEP 2026-2027">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
          <input type="text" id="sessionAcYear" class="form-control" placeholder="e.g. 2026-2027">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Committee Year</label>
          <select id="sessionCommitteeYear" class="form-select">
            <option value="">Select committee year...</option>
            <?php foreach ($committeeYears as $cy): ?>
              <option value="<?= $cy['id'] ?>"><?= htmlspecialchars($cy['year_label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Expected Handover Date</label>
          <input type="date" id="sessionHandoverDate" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="saveSession()">
          <i class="bi-save me-1"></i>Save Session
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Handover Modal -->
<div class="modal fade" id="handoverModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="bi-box-arrow-right me-2"></i>Initiate Committee Handover</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="bi-info-circle me-2"></i>
          This will formally transfer leadership responsibilities from the outgoing committee to the incoming one.
          Make sure all financial records are reconciled before proceeding.
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">CEP Session</label>
            <select id="hoSession" class="form-select">
              <option value="day">☀️ Day CEP</option>
              <option value="weekend">🌙 Weekend CEP</option>
              <option value="both">Both Sessions</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Handover Date <span class="text-danger">*</span></label>
            <input type="date" id="hoDate" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Outgoing Committee</label>
            <select id="hoOutgoing" class="form-select">
              <?php foreach ($committeeYears as $cy): ?>
                <option value="<?= $cy['id'] ?>" <?= $cy['is_current'] ? 'selected' : '' ?>><?= htmlspecialchars($cy['year_label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Incoming Committee</label>
            <select id="hoIncoming" class="form-select">
              <option value="">Select incoming committee...</option>
              <?php foreach ($committeeYears as $cy): ?>
                <option value="<?= $cy['id'] ?>"><?= htmlspecialchars($cy['year_label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Financial Balance Handed Over (RWF)</label>
            <input type="number" id="hoBalance" class="form-control" placeholder="0.00" step="0.01">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Executive Summary</label>
            <textarea id="hoSummary" class="form-control" rows="4" placeholder="Brief summary of achievements, activities, and financial position..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Pending Issues</label>
            <textarea id="hoPending" class="form-control" rows="3" placeholder="Any unresolved issues, pending approvals, outstanding matters..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Recommendations to Incoming Committee</label>
            <textarea id="hoRecommendations" class="form-control" rows="3" placeholder="Advice and recommendations for the new committee..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning" onclick="submitHandover()">
          <i class="bi-box-arrow-right me-1"></i>Complete Handover
        </button>
      </div>
    </div>
  </div>
</div>

<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
<?php include LAYOUTS_PATH . '/admin-scripts.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';

function showAlert(type, msg) {
  const el = document.getElementById('pageAlert');
  el.className = `alert alert-${type}`;
  el.innerHTML = `<i class="bi-${type==='success'?'check-circle':'exclamation-triangle'} me-2"></i>${msg}`;
  el.style.display = 'block';
  setTimeout(() => el.style.display = 'none', 5000);
}

// ---- Lock Portal ----
function lockPortal(sessionType) {
  document.getElementById('lockSessionType').value = sessionType;
  document.getElementById('lockReason').value = '';
  new bootstrap.Modal(document.getElementById('lockPortalModal')).show();
}

function confirmLock() {
  const reason = document.getElementById('lockReason').value.trim();
  const sType = document.getElementById('lockSessionType').value;
  if (!reason) { alert('Please provide a reason for locking the portal.'); return; }

  $.ajax({
    url: `${BASE_URL}/api/membership?action=toggleSession`,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ session_type: sType, enabled: false, reason }),
    success: function(res) {
      bootstrap.Modal.getInstance(document.getElementById('lockPortalModal')).hide();
      if (res.success) { showAlert('success', `${sType === 'day' ? 'Day' : 'Weekend'} CEP portal locked.`); setTimeout(() => location.reload(), 1500); }
      else showAlert('danger', res.message);
    }
  });
}

// ---- Unlock Portal ----
function unlockPortal(sessionType) {
  Swal.fire({
    title: 'Unlock Portal?',
    text: `This will restore portal access for ${sessionType === 'day' ? 'Day' : 'Weekend'} CEP leaders.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    confirmButtonText: 'Yes, Unlock'
  }).then(r => {
    if (r.isConfirmed) {
      $.ajax({
        url: `${BASE_URL}/api/membership?action=toggleSession`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ session_type: sessionType, enabled: true }),
        success: function(res) {
          if (res.success) { showAlert('success', 'Portal unlocked!'); setTimeout(() => location.reload(), 1500); }
          else showAlert('danger', res.message);
        }
      });
    }
  });
}

// ---- Edit / Create Session ----
function editSession(type, id, data) {
  document.getElementById('sessionEditType').value = type;
  document.getElementById('sessionEditId').value = id;
  document.getElementById('sessionModalTitle').textContent = `Edit ${type === 'day' ? 'Day' : 'Weekend'} CEP Session`;
  document.getElementById('sessionLabel').value = data.session_label || '';
  document.getElementById('sessionAcYear').value = data.academic_year || '';
  document.getElementById('sessionCommitteeYear').value = data.committee_year_id || '';
  document.getElementById('sessionHandoverDate').value = data.handover_date || '';
  new bootstrap.Modal(document.getElementById('sessionModal')).show();
}

function createSession(type) {
  document.getElementById('sessionEditType').value = type;
  document.getElementById('sessionEditId').value = '';
  document.getElementById('sessionModalTitle').textContent = `Setup ${type === 'day' ? 'Day' : 'Weekend'} CEP Session`;
  document.getElementById('sessionLabel').value = '';
  document.getElementById('sessionAcYear').value = '';
  document.getElementById('sessionCommitteeYear').value = '';
  document.getElementById('sessionHandoverDate').value = '';
  new bootstrap.Modal(document.getElementById('sessionModal')).show();
}

function saveSession() {
  const label = document.getElementById('sessionLabel').value.trim();
  const acYear = document.getElementById('sessionAcYear').value.trim();
  if (!label || !acYear) { alert('Session label and academic year are required.'); return; }

  $.ajax({
    url: `${BASE_URL}/api/membership?action=saveSession`,
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      id: document.getElementById('sessionEditId').value,
      session_type: document.getElementById('sessionEditType').value,
      session_label: label,
      academic_year: acYear,
      committee_year_id: document.getElementById('sessionCommitteeYear').value,
      handover_date: document.getElementById('sessionHandoverDate').value,
    }),
    success: function(res) {
      bootstrap.Modal.getInstance(document.getElementById('sessionModal')).hide();
      if (res.success) { showAlert('success', 'Session saved!'); setTimeout(() => location.reload(), 1200); }
      else showAlert('danger', res.message);
    }
  });
}

// ---- Handover ----
function submitHandover() {
  const date = document.getElementById('hoDate').value;
  const outgoing = document.getElementById('hoOutgoing').value;
  const summary = document.getElementById('hoSummary').value.trim();
  if (!date || !outgoing || !summary) { alert('Handover date, outgoing committee, and summary are required.'); return; }

  Swal.fire({
    title: 'Confirm Handover?',
    html: 'This action will formally mark the committee handover as complete. <strong>This cannot be undone.</strong>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ffc107',
    confirmButtonText: 'Yes, Complete Handover'
  }).then(r => {
    if (r.isConfirmed) {
      $.ajax({
        url: `${BASE_URL}/api/membership?action=submitHandover`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
          cep_session: document.getElementById('hoSession').value,
          handover_date: date,
          outgoing_year_id: outgoing,
          incoming_year_id: document.getElementById('hoIncoming').value,
          financial_balance: document.getElementById('hoBalance').value,
          handover_summary: summary,
          pending_issues: document.getElementById('hoPending').value,
          recommendations: document.getElementById('hoRecommendations').value,
        }),
        success: function(res) {
          bootstrap.Modal.getInstance(document.getElementById('handoverModal')).hide();
          if (res.success) {
            Swal.fire('Handover Complete!', 'The committee handover has been recorded.', 'success')
              .then(() => location.reload());
          } else {
            showAlert('danger', res.message);
          }
        }
      });
    }
  });
}
</script>
</body>
</html>