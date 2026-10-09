<?php 

function formatSeconds($seconds) {
    if ($seconds === null) return '<span class="text-muted">Insufficient data</span>';
    if ($seconds < 60) return $seconds . ' sec';
    $min = floor($seconds / 60);
    $remSec = $seconds % 60;
    if ($min < 60) return $min . ' min ' . $remSec . ' sec';
    $hr = floor($min / 60);
    $remMin = $min % 60;
    return $hr . ' hr ' . $remMin . ' min';
}

$core = $metrics['core'];
$urgency = $metrics['urgency'];
$sos = $metrics['sos'];
$routing = $metrics['routing'];
$followup = $metrics['followup'];
$resolution = $metrics['resolution'];
$timing = $metrics['timing'];
?>

<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark mb-1">Operational Analytics</h2>
            <p class="text-muted mb-0">Aggregate System Metrics (Read-Only)</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <div class="btn-group shadow-sm bg-white border rounded-3 p-1">
                <a href="?range=today" class="btn btn-sm rounded-2 <?= $range === 'today' ? 'btn-primary fw-bold' : 'btn-white text-muted fw-medium border-0' ?>">Today</a>
                <a href="?range=7d" class="btn btn-sm rounded-2 <?= $range === '7d' ? 'btn-primary fw-bold' : 'btn-white text-muted fw-medium border-0' ?>">7 Days</a>
                <a href="?range=30d" class="btn btn-sm rounded-2 <?= $range === '30d' ? 'btn-primary fw-bold' : 'btn-white text-muted fw-medium border-0' ?>">30 Days</a>
                <a href="?range=90d" class="btn btn-sm rounded-2 <?= $range === '90d' ? 'btn-primary fw-bold' : 'btn-white text-muted fw-medium border-0' ?>">90 Days</a>
                <a href="?range=all" class="btn btn-sm rounded-2 <?= $range === 'all' ? 'btn-primary fw-bold' : 'btn-white text-muted fw-medium border-0' ?>">All Time</a>
            </div>
            
        </div>
    </div>

    <!-- Admin Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill bg-light p-2 flex-column flex-md-row flex-nowrap overflow-auto" style="white-space: nowrap;">
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/dashboard">Dashboard</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/users">Users</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/hospitals">Hospitals</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/cases">Cases</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/events">Event Log</a>
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/analytics">Analytics</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Admin Navigation -->

    <!-- Row 1: System Overview & SOS -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">System Overview</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0 border-end">
                            <h2 class="display-5 fw-bold text-dark mb-0"><?= $core['total'] ?></h2>
                            <span class="text-muted text-uppercase small fw-bold">Total Cases</span>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0 border-end">
                            <h2 class="display-5 fw-bold text-primary mb-0"><?= $core['total'] - $core['RESOLVED'] ?></h2>
                            <span class="text-muted text-uppercase small fw-bold">Active Cases</span>
                        </div>
                        <div class="col-md-4">
                            <h2 class="display-5 fw-bold text-success mb-0"><?= $core['RESOLVED'] ?></h2>
                            <span class="text-muted text-uppercase small fw-bold">Resolved Cases</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-danger border-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-danger">SOS Activations</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Total Activations</span>
                        <span class="fs-5 fw-bold text-dark"><?= $sos['total'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Active SOS Cases</span>
                        <span class="fs-5 fw-bold text-danger"><?= $sos['active'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fw-bold text-uppercase">Resolved SOS</span>
                        <span class="fs-5 fw-bold text-success"><?= $sos['resolved'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Pipeline & Urgency -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Case Pipeline</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Reported (Not Triaged)</span>
                        <span class="fw-bold text-dark"><?= $core['REPORTED'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Triaged (Waiting Match/Accept)</span>
                        <span class="fw-bold text-dark"><?= $core['TRIAGED'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Accepted (Waiting Assignment)</span>
                        <span class="fw-bold text-dark"><?= $core['ACCEPTED'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Assigned (Waiting Care Start)</span>
                        <span class="fw-bold text-dark"><?= $core['ASSIGNED'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">In Care</span>
                        <span class="fw-bold text-dark"><?= $core['IN_CARE'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="text-muted fw-medium">Resolved</span>
                        <span class="fw-bold text-success"><?= $core['RESOLVED'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Urgency Distribution</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="urgency-badge urgency-CRITICAL w-25 text-center">Critical</span>
                        <span class="fs-5 fw-bold text-dark"><?= $urgency['critical'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="urgency-badge urgency-HIGH w-25 text-center">High</span>
                        <span class="fs-5 fw-bold text-dark"><?= $urgency['high'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="urgency-badge urgency-MODERATE w-25 text-center">Moderate</span>
                        <span class="fs-5 fw-bold text-dark"><?= $urgency['moderate'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="urgency-badge urgency-LOW w-25 text-center">Low</span>
                        <span class="fs-5 fw-bold text-dark"><?= $urgency['low'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Timing & Routing -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-info border-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Response Timing (Averages)</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-medium">Report &rarr; Triage</span>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['report_triage']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-medium">Triage &rarr; Acceptance</span>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['triage_accept']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-medium">Acceptance &rarr; Assignment</span>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['accept_assign']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-medium">Assignment &rarr; Care</span>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['assign_care']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-medium">Care &rarr; Resolution</span>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['care_resolve']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 pt-3 mt-1 bg-light rounded-3 px-3 border-0">
                            <strong class="text-dark">Total: Report &rarr; Resolution</strong>
                            <span class="fw-bold text-dark"><?= formatSeconds($timing['report_resolve']) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Hospital Routing</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Routing Matches Generated</span>
                        <span class="fw-bold text-dark"><?= $routing['matched'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted fw-medium">Cases Accepted</span>
                        <span class="fw-bold text-success"><?= $routing['accepted'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="text-muted fw-medium">Cases Declined</span>
                        <span class="fw-bold text-danger"><?= $routing['declined'] ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Resolution Outcomes</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($resolution)): ?>
                        <p class="text-muted mb-0 small">No resolved cases yet.</p>
                    <?php else: ?>
                        <?php foreach ($resolution as $outcome => $cnt): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted fw-medium"><?= htmlspecialchars(str_replace('_', ' ', $outcome)) ?></span>
                                <span class="fw-bold text-dark"><?= $cnt ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Follow Up -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-primary bg-opacity-10 border-start border-primary border-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary">Patient-Reported Coordination Experience</h5>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0 border-end border-primary border-opacity-25">
                            <h3 class="fw-bold text-dark mb-1"><?= $followup['total'] ?></h3>
                            <span class="text-muted text-uppercase small fw-bold">Follow-Ups Submitted</span>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0 border-end border-primary border-opacity-25">
                            <h3 class="fw-bold text-dark mb-1">
                                <?= $followup['avg_rating'] !== null ? $followup['avg_rating'] . ' / 5' : '<span class="fs-6 text-muted">No data</span>' ?>
                            </h3>
                            <span class="text-muted text-uppercase small fw-bold">Average Rating</span>
                        </div>
                        <div class="col-md-4">
                            <h3 class="fw-bold text-dark mb-1"><?= $followup['coordination_requests'] ?></h3>
                            <span class="text-muted text-uppercase small fw-bold">Further Coordination Requested</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
