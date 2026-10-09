<div class="vl-page-heading vx-dashboard-heading">
    <div>
        <p class="vl-eyebrow mb-2">Hospital workspace</p>
        <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars(['doctor' => 'Doctor Dashboard', 'nurse' => 'Nurse Dashboard'][$_SESSION['staff_role'] ?? ''] ?? 'Hospital Dashboard') ?></h1>
        <p class="text-muted mb-0"><?= ($_SESSION['staff_role'] ?? '') === 'doctor' ? 'Prioritize clinical review requests, assess routed cases, and guide care decisions.' : (($_SESSION['staff_role'] ?? '') === 'nurse' ? 'Review routed cases, manage your assigned workload, and record care updates.' : 'Review incoming cases and coordinate care at your facility.') ?></p>
    </div>
    <a href="/hospital/history" class="btn btn-outline-secondary fw-bold rounded-pill px-4">Case History</a>
</div>

<div class="vl-hospital-brief mb-4"><span class="vl-hospital-brief-mark" aria-hidden="true">i</span><div><strong>Facility-scoped workspace</strong><p class="mb-0">This queue includes cases routed to your facility. Patient triage is preliminary decision support; authorized clinicians retain clinical decisions.</p></div></div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success rounded-3 shadow-sm">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger rounded-3 shadow-sm">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row mb-4 vl-stats">
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-danger text-white border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75">Triaged</h6>
                <h2 class="mb-0 fw-bold display-5"><?= $stats['TRIAGED'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-warning text-dark border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75">Accepted</h6>
                <h2 class="mb-0 fw-bold display-5"><?= $stats['ACCEPTED'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-primary text-white border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75">In Care</h6>
                <h2 class="mb-0 fw-bold display-5"><?= $stats['IN_CARE'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-success text-white border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <h6 class="text-uppercase fw-bold opacity-75">Resolved</h6>
                <h2 class="mb-0 fw-bold display-5"><?= $stats['RESOLVED'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Notifications Section -->
    <?php if (!empty($notifications)): ?>
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-danger bg-opacity-10 border-0 pt-3 pb-2 px-4 rounded-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-danger mb-0"><span class="fs-5 me-2">🔔</span> Action Required: Notifications</h5>
                <form action="/notifications/read" method="POST" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="mark_all" value="1">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Clear All</button>
                </form>
            </div>
            <div class="list-group list-group-flush rounded-3">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item px-4 py-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-dark rounded-pill"><?= htmlspecialchars($n['type']) ?></span>
                            <small class="text-muted"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></small>
                        </div>
                        <p class="mb-2 text-dark fw-bold fs-5"><?= htmlspecialchars($n['message']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($n['case_id']): ?>
                                <a href="/hospital/case?id=<?= $n['case_id'] ?>" class="btn btn-sm btn-outline-dark fw-bold px-3">Review Case</a>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            <form action="/notifications/read" method="POST" class="m-0">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                                <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none p-0">Mark as Read</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Routed Cases Section -->
    
<?php
$assignedToMe = [];
$doctorReview = [];
$activeCases = [];
$newCases = [];
$doctorReviewCount = 0;
$assignedCount = 0;
$awaitingActionCount = 0;

foreach ($routedCases as $rc) {
    if ($rc['case_status'] === 'RESOLVED' || $rc['status'] === 'DECLINED' || $rc['status'] === 'EXPIRED') continue;

    if ($rc['requires_doctor_review'] == 1) $doctorReviewCount++;
    if (isset($_SESSION['user_id']) && (int)$rc['assigned_to'] === (int)$_SESSION['user_id']) $assignedCount++;
    if (in_array($rc['status'], ['MATCHED', 'NOTIFIED', 'VIEWED'], true)) $awaitingActionCount++;

    // Keep a case in only one actionable section to reduce duplicate queue cards.
    if ($rc['requires_doctor_review'] == 1 && isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'doctor') {
        $doctorReview[] = $rc;
    } elseif (isset($_SESSION['user_id']) && (int)$rc['assigned_to'] === (int)$_SESSION['user_id']) {
        $assignedToMe[] = $rc;
    } elseif (in_array($rc['case_status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'], true)) {
        $activeCases[] = $rc;
    } elseif (in_array($rc['status'], ['MATCHED', 'NOTIFIED', 'VIEWED'], true)) {
        $newCases[] = $rc;
    }
}
?>

    <div class="row g-3 mb-4 vl-queue-summary">
        <div class="col-md-4"><div class="vl-queue-summary-card"><span>Awaiting facility response</span><strong><?= (int)$awaitingActionCount ?></strong><small>New routed requests</small></div></div>
        <div class="col-md-4"><div class="vl-queue-summary-card"><span>Assigned to you</span><strong><?= (int)$assignedCount ?></strong><small>Your active workload</small></div></div>
        <div class="col-md-4"><div class="vl-queue-summary-card"><span>Doctor review flagged</span><strong><?= (int)$doctorReviewCount ?></strong><small>Clinical review requests</small></div></div>
    </div>
    
    <?php if (!empty($newCases)): ?>
        <h5 class="fw-bold mb-3 text-dark">New Routed Cases</h5>
        <div class="row g-4 mb-4">
            <?php foreach ($newCases as $rc): ?>
                <?php renderRoutedCaseCard($rc, 'bg-warning', 'New Request'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($assignedToMe)): ?>
        <h5 class="fw-bold mb-3 text-primary">Assigned to Me</h5>
        <div class="row g-4 mb-4">
            <?php foreach ($assignedToMe as $rc): ?>
                <?php renderRoutedCaseCard($rc, 'bg-primary', 'Assigned', true); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($doctorReview)): ?>
        <h5 class="fw-bold mb-3 text-danger">Doctor Review Requests</h5>
        <div class="row g-4 mb-4">
            <?php foreach ($doctorReview as $rc): ?>
                <?php renderRoutedCaseCard($rc, 'bg-danger', 'Review Needed', true); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($activeCases)): ?>
        <h5 class="fw-bold mb-3 text-success">Active Cases (<?= htmlspecialchars($_SESSION['user_name'] ?? 'Hospital') ?>)</h5>
        <div class="row g-4 mb-4">
            <?php foreach ($activeCases as $rc): ?>
                <?php renderRoutedCaseCard($rc, 'bg-success', 'Active', true); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($newCases) && empty($assignedToMe) && empty($doctorReview) && empty($activeCases)): ?>
        <div class="vl-empty-state mb-5" role="status">
            <span class="vl-empty-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20h16M6 20V6l6-3 6 3v14M9 11h1m4 0h1m-6 3h1m4 0h1"/></svg></span>
            <div>
                <p class="vl-eyebrow mb-2">CASE QUEUE</p>
                <h2>You're up to date</h2>
                <p>No cases are currently routed to this facility. Cases appear here after patient triage is completed and a facility match is made.</p>
                <a href="/hospital/history" class="btn btn-outline-primary">Review case history <span aria-hidden="true">→</span></a>
            </div>
        </div>
    <?php endif; ?>

<?php
function renderRoutedCaseCard($rc, $badgeClass, $badgeText, $isActive = false) {
    $cId = htmlspecialchars(strtoupper(substr(md5($rc['case_id']), 0, 13)));
    $urgency = htmlspecialchars(ucfirst(strtolower($rc['urgency'] ?? 'Unknown')));
    $dist = htmlspecialchars(number_format($rc['distance'], 1));
    $time = htmlspecialchars(date('g:i A', strtotime($rc['notified_at'])));
    $caseId = $rc['case_id'];
    $borderColor = str_replace('bg-', 'border-', $badgeClass);
    
    echo <<<HTML
    <div class="col-md-6 col-lg-4">
        <div class="card vx-routed-case-card h-100 border-0 shadow-sm rounded-3 border-start border-4 $borderColor">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-dark mb-2 fs-6">CASE-{$cId}</span>
                        <h5 class="card-title fw-bold text-dark mb-0"><span class="urgency-badge urgency-{$urgency}">{$urgency}</span></h5>
                    </div>
                    <span class="badge $badgeClass text-white rounded-pill px-3 py-2">{$badgeText}</span>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-light text-secondary border border-secondary rounded-pill me-2">
                        Approx. {$dist} km
                    </span>
                    <span class="badge bg-light text-secondary border border-secondary rounded-pill">
                        {$time}
                    </span>
                </div>
                <a href="/hospital/case?id={$caseId}" class="btn btn-primary w-100 fw-bold">Review Case</a>
            </div>
        </div>
    </div>
HTML;
}
?>

</div>
