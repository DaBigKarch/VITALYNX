
<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Inspect Case: <?= htmlspecialchars($case['case_number']) ?></h2>
            <p class="text-muted mb-0">Operational Oversight View (Read-Only)</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="/admin/cases" class="btn btn-outline-secondary px-4 fw-bold">Back to Cases</a>
        </div>
    </div>
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Case Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="vl-timeline">
                        <?php foreach ($events as $event): ?>
                            <li class="vl-timeline-item">
                                <div class="d-flex align-items-start">
                                    <div class="vl-timeline-dot bg-dark"></div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars(str_replace('_', ' ', $event['event_type'])) ?></h6>
                                            <small class="text-muted fw-medium"><?= date('M j, Y H:i:s', strtotime($event['created_at'])) ?></small>
                                        </div>
                                        <?php if ($event['actor_id']): ?>
                                            <p class="text-muted small mb-0">Actor: <?= htmlspecialchars($event['actor_id']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <?php if ($assessment): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark">Emergency Triage Support</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold">Urgency</small>
                            <div class="mt-1"><span class="urgency-badge urgency-<?= strtoupper($assessment['urgency']) ?>"><?= strtoupper($assessment['urgency']) ?></span></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold">Review status</small>
                            <p class="fs-6 fw-semibold text-dark">Clinical review required</p>
                        </div>
                    </div>
                    <small class="text-muted text-uppercase fw-bold">Summary</small>
                    <p class="text-dark"><?= nl2br(htmlspecialchars($assessment['summary'])) ?></p>
                    <p class="small text-muted mb-0">AI-assisted decision support only. This is not a diagnosis; qualified healthcare staff make final clinical decisions.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Metadata</h5>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold">Status</small>
                        <div class="mt-1"><span class="status-badge status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars($case['status']) ?></span></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold">Patient ID</small>
                        <p class="fs-5 fw-bold text-dark"><?= htmlspecialchars($case['user_id']) ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold">Location</small>
                        <p class="text-dark mb-0">Lat: <?= htmlspecialchars($case['latitude'] ?? 'N/A') ?><br>Lng: <?= htmlspecialchars($case['longitude'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($routedHospitals)): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-dark">Hospital Routing</h6>
                </div>
                <div class="list-group list-group-flush rounded-3">
                    <?php foreach ($routedHospitals as $rh): ?>
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark">Hospital ID: <?= htmlspecialchars($rh['hospital_id']) ?></span>
                                <span class="status-badge status-<?= htmlspecialchars($rh['status']) ?> fs-6"><?= htmlspecialchars($rh['status']) ?></span>
                            </div>
                            <?php if ($rh['assigned_to']): ?>
                                <small class="text-muted d-block mt-1">Assigned to ID: <?= htmlspecialchars($rh['assigned_to']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
