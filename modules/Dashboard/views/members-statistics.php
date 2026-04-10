<?php
/**
 * Members Statistics Dashboard
 * File: modules/Dashboard/views/members-statistics.php
 */

$pageTitle          = 'Members Statistics';
$requiredPermission = 'membership.view';
$currentPage        = 'members-statistics.php';

require_once get_helper('admin-base');
require_once ROOT_PATH . '/modules/Membership/controllers/MembershipController.php';

$mc           = new MembershipController();
$isSuperAdmin = !empty($currentUser->is_super_admin);
$sessionCtx   = $isSuperAdmin ? null : ($currentUser->session_type ?? null);

// ── Server-side data ──────────────────────────────────────────────────────────
$stats       = $mc->getStatistics($sessionCtx)['data']     ?? [];
$families    = $mc->getFamilies($sessionCtx)['data']        ?? [];
$types       = $mc->getMembershipTypes()['data']            ?? [];

// ── Direct DB queries (all in one block) ─────────────────────────────────────
$facultyStat = [];
$growthRows  = [];
$monthRows   = [];
$talentRows  = [];
$typeRows    = [];
$recentRows  = [];
$faithRows   = ['born_again_yes' => 0, 'baptized_yes' => 0];

try {
    $db    = Database::getConnection();
    $sessQ = $sessionCtx ? "AND cep_session = " . $db->quote($sessionCtx) : "";

    // Faculty distribution
    $facWhere    = $sessionCtx
        ? "WHERE cep_session = " . $db->quote($sessionCtx) . " AND faculty IS NOT NULL"
        : "WHERE faculty IS NOT NULL";
    $facultyStat = $db->query(
        "SELECT faculty, COUNT(*) as count FROM members $facWhere GROUP BY faculty ORDER BY count DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Growth by year joined
    $growthRows = $db->query(
        "SELECT year_joined_cep AS yr, COUNT(*) AS cnt
         FROM members WHERE year_joined_cep IS NOT NULL $sessQ
         GROUP BY year_joined_cep ORDER BY year_joined_cep ASC LIMIT 15"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Registrations per month (last 12 months)
    $monthRows = $db->query(
        "SELECT DATE_FORMAT(created_at,'%Y-%m') AS mo, COUNT(*) AS cnt
         FROM members WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) $sessQ
         GROUP BY mo ORDER BY mo ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Top talents
    $talentRows = $db->query(
        "SELECT tg.talent_name, tg.category, COUNT(*) AS cnt
         FROM member_talents mt
         JOIN talents_gifts tg ON tg.id = mt.talent_id
         JOIN members m ON m.id = mt.member_id
         WHERE m.status = 'active' $sessQ
         GROUP BY tg.id ORDER BY cnt DESC LIMIT 10"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Membership type breakdown
    $typeRows = $db->query(
        "SELECT mt.type_name, COUNT(m.id) AS cnt
         FROM membership_types mt
         LEFT JOIN members m ON m.membership_type_id = mt.id AND m.status = 'active' $sessQ
         GROUP BY mt.id ORDER BY cnt DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Born-again / Baptised
    $faithRows = $db->query(
        "SELECT
            SUM(CASE WHEN is_born_again='yes' THEN 1 ELSE 0 END) AS born_again_yes,
            SUM(CASE WHEN is_baptized='yes'   THEN 1 ELSE 0 END) AS baptized_yes
         FROM members WHERE status='active' $sessQ"
    )->fetch(PDO::FETCH_ASSOC) ?: ['born_again_yes' => 0, 'baptized_yes' => 0];

    // Recent registrations
    $sessWhere  = $sessionCtx ? "WHERE cep_session = " . $db->quote($sessionCtx) : "";
    $recentRows = $db->query(
        "SELECT firstname, lastname, faculty, cep_session, status, created_at, profile_photo
         FROM members $sessWhere ORDER BY created_at DESC LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Non-fatal — page will render with empty charts
    error_log('members-statistics DB error: ' . $e->getMessage());
}

// Helpers
$total     = (int)($stats['total']     ?? 0);
$active    = (int)($stats['active']    ?? 0);
$pending   = (int)($stats['pending']   ?? 0);
$inactive  = (int)($stats['inactive']  ?? 0);
$suspended = (int)($stats['suspended'] ?? 0);
$male      = (int)($stats['male']      ?? 0);
$female    = (int)($stats['female']    ?? 0);
$dayS      = (int)($stats['day_session']     ?? 0);
$weekS     = (int)($stats['weekend_session'] ?? 0);
$new30     = (int)($stats['new_30_days']     ?? 0);
$activeRate = $total ? round($active / $total * 100) : 0;

function pct($n, $d) { return $d ? round($n / $d * 100, 1) : 0; }
?>
<?php include get_layout('admin-header'); ?>

<body class="has-navbar-vertical-aside navbar-vertical-aside-show-xl footer-offset">
<?php include get_layout('admin-lock-screen'); ?>
<script>(function(){ var el=document.getElementById('sessionLockOverlay'); if(el) el.dataset.email=<?= json_encode($currentUser->email ?? '') ?>; })();</script>
<?php include get_layout('admin-navbar'); ?>
<?php include get_layout('admin-sidebar'); ?>

<main id="content" role="main" class="main">
<div class="content container-fluid">

  <!-- ── Page Header ──────────────────────────────────────────────── -->
  <div class="page-header">
    <div class="row align-items-center">
      <div class="col">
        <h1 class="page-header-title">
          <i class="bi-bar-chart-line me-2"></i>Members Statistics
        </h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-no-gutter">
            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Members Statistics</li>
          </ol>
        </nav>
      </div>
      <div class="col-auto d-flex gap-2">
        <?php if (!$isSuperAdmin && $sessionCtx): ?>
          <span class="badge bg-soft-<?= $sessionCtx === 'day' ? 'warning text-warning' : 'primary text-primary' ?> px-3 py-2 fs-6">
            <?= $sessionCtx === 'day' ? '☀️ Day CEP' : '🌘 Weekend CEP' ?>
          </span>
        <?php endif; ?>
        <a href="<?= url('admin/reports-members') ?>" class="btn btn-outline-primary btn-sm">
          <i class="bi-file-earmark-text me-1"></i>Full Report
        </a>
        <a href="<?= url('admin/reports-members-custom') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="bi-sliders me-1"></i>Custom Report
        </a>
      </div>
    </div>
  </div>

  <!-- ── Row 1: Key Metric Cards ──────────────────────────────────── -->
  <div class="row g-3 mb-4">

    <!-- Total -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-people-fill text-primary" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-dark"><?= number_format($total) ?></div>
          <div class="text-muted small mt-1">Total Members</div>
        </div>
      </div>
    </div>

    <!-- Active -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm" style="border-top:3px solid #198754!important;">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-check-circle-fill text-success" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-success"><?= number_format($active) ?></div>
          <div class="text-muted small mt-1">Active <span class="badge bg-soft-success text-success"><?= $activeRate ?>%</span></div>
        </div>
      </div>
    </div>

    <!-- Pending -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-clock-fill text-warning" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-warning"><?= number_format($pending) ?></div>
          <div class="text-muted small mt-1">Pending</div>
        </div>
      </div>
    </div>

    <!-- New this month -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-person-plus-fill text-info" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-info"><?= number_format($new30) ?></div>
          <div class="text-muted small mt-1">New (30 days)</div>
        </div>
      </div>
    </div>

    <!-- Male -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-gender-male text-primary" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-primary"><?= number_format($male) ?></div>
          <div class="text-muted small mt-1">Male <span class="text-muted"><?= pct($male, $total) ?>%</span></div>
        </div>
      </div>
    </div>

    <!-- Female -->
    <div class="col-6 col-sm-4 col-xl-2">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center py-4">
          <div class="mb-2"><i class="bi-gender-female text-danger" style="font-size:28px;"></i></div>
          <div class="display-6 fw-bold text-danger"><?= number_format($female) ?></div>
          <div class="text-muted small mt-1">Female <span class="text-muted"><?= pct($female, $total) ?>%</span></div>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Row 2: Charts ────────────────────────────────────────────── -->
  <div class="row g-3 mb-4">

    <!-- Status doughnut -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-pie-chart-fill text-primary me-2"></i>Membership Status</h5>
        </div>
        <div class="card-body d-flex flex-column align-items-center">
          <div style="position:relative;width:200px;height:200px;">
            <canvas id="statusChart"></canvas>
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
              <div class="fw-bold fs-4"><?= $total ?></div>
              <div class="text-muted" style="font-size:11px;">Total</div>
            </div>
          </div>
          <div class="row g-2 mt-3 w-100">
            <?php
            $statusItems = [
              ['Active',    $active,    'success'],
              ['Pending',   $pending,   'warning'],
              ['Inactive',  $inactive,  'secondary'],
              ['Suspended', $suspended, 'danger'],
            ];
            foreach ($statusItems as [$label, $val, $color]): ?>
            <div class="col-6">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-soft-<?= $color ?> text-<?= $color ?>" style="width:10px;height:10px;padding:0;border-radius:50%;display:inline-block;"></span>
                <span class="small text-muted"><?= $label ?></span>
                <span class="ms-auto fw-semibold small"><?= $val ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Gender + Session side by side -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-gender-ambiguous text-info me-2"></i>Gender & Session</h5>
        </div>
        <div class="card-body">
          <!-- Gender bar -->
          <p class="text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing:.5px;">Gender Split</p>
          <div class="mb-1 d-flex justify-content-between small">
            <span>👨 Male</span><span class="fw-semibold"><?= $male ?> (<?= pct($male,$total) ?>%)</span>
          </div>
          <div class="progress mb-3" style="height:10px;border-radius:6px;">
            <div class="progress-bar bg-primary" style="width:<?= pct($male,$total) ?>%"></div>
            <div class="progress-bar bg-danger" style="width:<?= pct($female,$total) ?>%"></div>
          </div>
          <div class="d-flex justify-content-between small mb-4">
            <span>👩 Female</span><span class="fw-semibold"><?= $female ?> (<?= pct($female,$total) ?>%)</span>
          </div>

          <?php if ($isSuperAdmin): ?>
          <!-- Session bar (super-admin only) -->
          <p class="text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing:.5px;">Session Split</p>
          <div class="mb-1 d-flex justify-content-between small">
            <span>☀️ Day</span><span class="fw-semibold"><?= $dayS ?> (<?= pct($dayS,$total) ?>%)</span>
          </div>
          <div class="progress mb-3" style="height:10px;border-radius:6px;">
            <div class="progress-bar bg-warning" style="width:<?= pct($dayS,$total) ?>%"></div>
            <div class="progress-bar bg-indigo" style="width:<?= pct($weekS,$total) ?>%;background:#6610f2;"></div>
          </div>
          <div class="d-flex justify-content-between small">
            <span>🌘 Weekend</span><span class="fw-semibold"><?= $weekS ?> (<?= pct($weekS,$total) ?>%)</span>
          </div>
          <?php endif; ?>

          <!-- Faith indicators -->
          <hr class="my-3">
          <p class="text-muted small mb-2 fw-semibold text-uppercase" style="letter-spacing:.5px;">Faith (Active Members)</p>
          <div class="d-flex gap-3">
            <div class="text-center flex-fill">
              <div class="fw-bold fs-5 text-success"><?= $faithRows['born_again_yes'] ?? 0 ?></div>
              <div class="text-muted" style="font-size:11px;">Born Again</div>
            </div>
            <div class="vr"></div>
            <div class="text-center flex-fill">
              <div class="fw-bold fs-5 text-primary"><?= $faithRows['baptized_yes'] ?? 0 ?></div>
              <div class="text-muted" style="font-size:11px;">Baptised</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Registrations by month (bar) -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-calendar3 text-success me-2"></i>Registrations (12 mo.)</h5>
        </div>
        <div class="card-body">
          <canvas id="monthChart" style="height:220px;"></canvas>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Row 3: Faculty + Growth ──────────────────────────────────── -->
  <div class="row g-3 mb-4">

    <!-- Faculty horizontal bars -->
    <div class="col-md-5">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-mortarboard-fill text-warning me-2"></i>Faculty Distribution</h5>
        </div>
        <div class="card-body">
          <?php if (empty($facultyStat)): ?>
            <p class="text-muted text-center py-4">No faculty data available.</p>
          <?php else:
            $maxFac = max(array_column($facultyStat, 'count'));
            foreach ($facultyStat as $f):
              $pct = $maxFac ? round($f['count']/$maxFac*100) : 0;
              $colors = ['Information Technology'=>'primary','Law'=>'warning','Finance'=>'success','Accounting'=>'info','Economics'=>'danger','Education'=>'secondary','Procurement'=>'dark','Graduate School'=>'indigo'];
              $c = $colors[$f['faculty']] ?? 'primary';
          ?>
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1">
              <span class="small"><?= htmlspecialchars($f['faculty']) ?></span>
              <span class="small fw-semibold"><?= $f['count'] ?></span>
            </div>
            <div class="progress" style="height:8px;border-radius:4px;">
              <div class="progress-bar bg-<?= $c ?>" style="width:<?= $pct ?>%;"></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <!-- Growth by year (line chart) -->
    <div class="col-md-7">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0 d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="bi-graph-up-arrow text-primary me-2"></i>Growth by Year Joined</h5>
          <span class="badge bg-soft-primary text-primary"><?= count($growthRows) ?> years</span>
        </div>
        <div class="card-body">
          <canvas id="growthChart" style="height:220px;"></canvas>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Row 4: Member Type + Families + Talents ──────────────────── -->
  <div class="row g-3 mb-4">

    <!-- Member Type doughnut -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-tag-fill text-info me-2"></i>Member Types</h5>
        </div>
        <div class="card-body d-flex flex-column align-items-center">
          <canvas id="typeChart" style="width:180px;height:180px;max-width:180px;"></canvas>
          <div class="mt-3 w-100">
            <?php foreach ($typeRows as $tr): ?>
            <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
              <span class="small"><?= htmlspecialchars($tr['type_name']) ?></span>
              <span class="badge bg-soft-secondary text-secondary"><?= $tr['cnt'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Families overview -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="bi-house-heart-fill me-2" style="color:#e84393;"></i>Spiritual Families</h5>
          <a href="<?= url('admin/member-families') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            <?php if (empty($families)): ?>
              <li class="list-group-item text-muted text-center py-4">No families yet.</li>
            <?php else: foreach (array_slice($families, 0, 8) as $fam): ?>
            <li class="list-group-item d-flex align-items-center gap-2 py-2">
              <span class="rounded-circle d-inline-block flex-shrink-0"
                    style="width:12px;height:12px;background:<?= htmlspecialchars($fam['family_color'] ?? '#377dff') ?>;"></span>
              <span class="small flex-grow-1"><?= htmlspecialchars($fam['family_name']) ?></span>
              <span class="badge bg-soft-secondary text-secondary"><?= $fam['member_count'] ?></span>
              <?php if (!$isSuperAdmin): ?>
              <?php else: ?>
              <span class="badge bg-soft-<?= $fam['cep_session']==='day'?'warning':'primary' ?> text-<?= $fam['cep_session']==='day'?'warning':'primary' ?>" style="font-size:10px;">
                <?= $fam['cep_session'] === 'day' ? '☀️' : '🌘' ?>
              </span>
              <?php endif; ?>
            </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- Top Talents -->
    <div class="col-md-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0">
          <h5 class="card-title mb-0"><i class="bi-star-fill text-warning me-2"></i>Top Talents & Gifts</h5>
        </div>
        <div class="card-body">
          <?php if (empty($talentRows)): ?>
            <p class="text-muted text-center py-4">No talent data available.</p>
          <?php else:
            $maxT = max(array_column($talentRows, 'cnt'));
            foreach ($talentRows as $t):
              $pctT = $maxT ? round($t['cnt']/$maxT*100) : 0;
          ?>
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1">
              <span class="small"><?= htmlspecialchars($t['talent_name']) ?></span>
              <span class="small fw-semibold"><?= $t['cnt'] ?></span>
            </div>
            <div class="progress" style="height:7px;border-radius:4px;">
              <div class="progress-bar bg-warning" style="width:<?= $pctT ?>%;"></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Row 5: Recent Registrations ─────────────────────────────── -->
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="bi-person-badge text-primary me-2"></i>Recent Registrations</h5>
          <a href="<?= url('admin/membership-management') ?>" class="btn btn-xs btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
          <table class="table table-borderless table-thead-bordered table-align-middle card-table mb-0">
            <thead class="thead-light">
              <tr>
                <th>Member</th>
                <th>Session</th>
                <th>Faculty</th>
                <th>Status</th>
                <th>Registered</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentRows)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No members yet.</td></tr>
              <?php else: foreach ($recentRows as $r):
                $ini = strtoupper(($r['firstname'][0]??'').($r['lastname'][0]??''));
                $sc  = ['active'=>'success','pending'=>'warning','inactive'=>'secondary','suspended'=>'danger'][$r['status']] ?? 'secondary';
              ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($r['profile_photo']): ?>
                      <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($r['profile_photo']) ?>"
                           style="width:32px;height:32px;border-radius:50%;object-fit:cover;"
                           onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                      <div class="avatar avatar-sm avatar-soft-primary avatar-circle" style="display:none">
                        <span class="avatar-initials"><?= $ini ?></span>
                      </div>
                    <?php else: ?>
                      <div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                        <span class="avatar-initials"><?= $ini ?></span>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div class="fw-semibold"><?= htmlspecialchars($r['firstname'].' '.$r['lastname']) ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if ($r['cep_session'] === 'day'): ?>
                    <span class="badge bg-soft-warning text-warning">☀️ Day</span>
                  <?php else: ?>
                    <span class="badge bg-soft-primary text-primary">🌘 Weekend</span>
                  <?php endif; ?>
                </td>
                <td><span class="small"><?= htmlspecialchars($r['faculty'] ?? '—') ?></span></td>
                <td><span class="badge bg-soft-<?= $sc ?> text-<?= $sc ?>"><?= strtoupper($r['status']) ?></span></td>
                <td><span class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div><!-- /container -->
<?php include LAYOUTS_PATH . '/admin-footer.php'; ?>
</main>

<?php include get_layout('admin-scripts'); ?>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
(function () {
    'use strict';

    // Shared palette
    const COLORS = {
        success  : '#198754',
        warning  : '#ffc107',
        secondary: '#6c757d',
        danger   : '#dc3545',
        primary  : '#377dff',
        info     : '#0dcaf0',
        indigo   : '#6610f2',
    };

    // ── 1. Status doughnut ──────────────────────────────────────────
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Inactive', 'Suspended'],
            datasets: [{
                data: [<?= $active ?>, <?= $pending ?>, <?= $inactive ?>, <?= $suspended ?>],
                backgroundColor: [COLORS.success, COLORS.warning, COLORS.secondary, COLORS.danger],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            cutout: '72%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed
                    }
                }
            }
        }
    });

    // ── 2. Registrations by month (bar) ────────────────────────────
    const monthData = <?= json_encode(array_values($monthRows)) ?>;
    const monthLabels = monthData.map(r => {
        const [y,m] = r.mo.split('-');
        return new Date(y, m-1).toLocaleDateString('en-GB', {month:'short', year:'2-digit'});
    });
    new Chart(document.getElementById('monthChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Registrations',
                data: monthData.map(r => r.cnt),
                backgroundColor: 'rgba(55,125,255,.7)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // ── 3. Growth by year (line) ────────────────────────────────────
    const growthData = <?= json_encode(array_values($growthRows)) ?>;
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthData.map(r => r.yr),
            datasets: [{
                label: 'Members Joined',
                data: growthData.map(r => r.cnt),
                borderColor: COLORS.primary,
                backgroundColor: 'rgba(55,125,255,.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: COLORS.primary,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // ── 4. Member Type doughnut ────────────────────────────────────
    const typeData  = <?= json_encode(array_values($typeRows)) ?>;
    const typeColors = ['#377dff','#198754','#ffc107','#dc3545','#6610f2','#0dcaf0','#fd7e14'];
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: typeData.map(r => r.type_name),
            datasets: [{
                data: typeData.map(r => r.cnt),
                backgroundColor: typeColors.slice(0, typeData.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 5,
            }]
        },
        options: {
            cutout: '60%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } }
            }
        }
    });

})();
</script>
</body>