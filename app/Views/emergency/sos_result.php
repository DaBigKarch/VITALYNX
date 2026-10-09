<div class="row justify-content-center vl-sos-result-page">
    <div class="col-md-8 col-lg-6">
        <div class="text-center mb-4">
            <h2 class="mb-1 fw-bold text-danger">EMERGENCY REQUEST RECORDED</h2>
            <p class="lead text-muted">Your emergency request has been recorded.</p>
        </div>

        <div class="card shadow border-0 rounded-3 mb-4 border-top border-danger border-5">
            <div class="card-body p-4 text-center">
                <p class="fs-5 mb-4">VITALYNX has recorded your request and notified matched facilities in the app.</p>
                <p class="small fw-semibold text-danger">This is not an emergency-service dispatch. No ambulance has been sent. Call your local emergency number directly if you need immediate help.</p>
                
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <span class="badge bg-secondary fs-6 py-2 px-3">Case: <?= htmlspecialchars($case['case_number']) ?></span>
                    <span class="status-badge status-<?= htmlspecialchars($case['status']) ?> fs-6 py-2 px-3"><?= htmlspecialchars($case['status']) ?></span>
                </div>

                <?php if (!empty($case['latitude']) && !empty($case['longitude'])): ?>
                    <div class="alert alert-success border-success border-2 fw-semibold d-flex align-items-center justify-content-center mb-0">
                        <span class="fs-5 me-2">📍</span> Emergency location captured
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning border-warning border-2 fw-semibold d-flex align-items-center justify-content-center mb-0">
                        <span class="fs-5 me-2">⚠️</span> Location unavailable
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($matchedHospitals) && !empty($matchedHospitals)): ?>
        <div class="card shadow border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold text-dark mb-0">Emergency Facilities Notified</h5>
            </div>
            <div class="list-group list-group-flush rounded-bottom">
                <?php foreach($matchedHospitals as $h): ?>
                <div class="list-group-item p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1 text-danger"><?= htmlspecialchars($h['name']) ?></h6>
                            <p class="text-muted small mb-1"><i class="me-1">📍</i> <?= htmlspecialchars($h['address']) ?></p>
                            <?php if(!empty($h['phone'])): ?>
                            <p class="text-muted small mb-0"><i class="me-1">📞</i> <?= htmlspecialchars($h['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($h['distance'])): ?>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                <?= number_format($h['distance'], 1) ?> km
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-secondary border border-secondary shadow-sm rounded-3 mb-4">
            <h6 class="fw-bold text-dark mb-1">No Facilities Matched Online</h6>
            <p class="mb-0 text-muted">Please contact local emergency services immediately.</p>
        </div>
        <?php endif; ?>

        <div class="d-grid gap-3">
            <a href="/patient/case?id=<?= $case['id'] ?>" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-5 shadow-sm">View Case Dashboard</a>
            <a href="/dashboard" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold fs-5">Return Home</a>
        </div>
    </div>
</div>
