<div class="row justify-content-center vl-assessment-page">
    <div class="col-md-8 col-lg-6">
        
        <div class="text-center mb-4">
            <h2 class="mb-1 fw-bold text-dark">VITALYNX AI</h2>
            <h4 class="text-muted">Emergency Triage Support Complete</h4>
            <span class="badge bg-secondary mt-2 fs-6">Case: <?= htmlspecialchars($case['case_number']) ?></span>
        </div>

        <?php
            $urgency = strtoupper($assessment['urgency'] ?? 'UNASSIGNED');
            if ($urgency === 'MEDIUM') $urgency = 'MODERATE';
            
            $uColor = 'secondary';
            if ($urgency === 'CRITICAL') $uColor = 'danger';
            elseif ($urgency === 'HIGH') $uColor = 'warning';
            elseif ($urgency === 'MODERATE') $uColor = 'primary';
            elseif ($urgency === 'LOW') $uColor = 'success';
        ?>

        <div class="card shadow border-0 rounded-3 mb-4 border-top border-<?= $uColor ?> border-5">
            <div class="card-body p-4">
                <?php if (($assessment['model'] ?? '') === 'vitalynx-safe-fallback-v1'): ?>
                <div class="alert alert-warning" role="status">The AI service was unavailable, so this preliminary assessment used the local safety fallback. A qualified healthcare professional must review it. Contact local emergency services directly for an emergency.</div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-muted">Urgency Level</h5>
                    <span class="urgency-badge urgency-<?= htmlspecialchars($urgency) ?> fs-5 px-3 py-2 text-uppercase" aria-label="Urgency: <?= htmlspecialchars($urgency) ?>">
        <?= $urgency === 'CRITICAL' ? '⚠️ ' : '' ?><?= htmlspecialchars($urgency) ?>
    </span>
                </div>
                
                <div class="mb-4">
                    <h5 class="fw-bold text-muted mb-2">Summary</h5>
                    <p class="fs-5 mb-0"><?= nl2br(htmlspecialchars($assessment['summary'])) ?></p>
                </div>
                
                <?php
                    $redFlags = json_decode($assessment['red_flags'] ?? '[]', true);
                ?>
                <?php if (!empty($redFlags)): ?>
                <div class="mb-4">
                    <h5 class="fw-bold text-muted mb-2">Warning Signs Detected</h5>
                    <ul class="list-group list-group-flush rounded-3 border">
                        <?php foreach($redFlags as $flag): ?>
                            <li class="list-group-item bg-light text-danger fw-medium"><i class="me-2">⚠️</i> <?= htmlspecialchars(ucfirst($flag)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <h5 class="fw-bold text-muted mb-2">Recommended Action</h5>
                    <div class="alert alert-info border-info border-2 fw-semibold mb-0 fs-5">
                        <?= htmlspecialchars($assessment['recommendation']) ?>
                    </div>
                </div>
                
                <div class="pt-3 border-top">
                    <small class="text-muted">Preliminary decision support only. This is not a diagnosis and requires review by a qualified healthcare professional.</small>
                </div>
                
            </div>
        </div>

        <?php if (isset($matchedHospitals) && !empty($matchedHospitals)): ?>
        <div class="card shadow border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold text-dark mb-0">Recommended Facilities</h5>
            </div>
            <div class="list-group list-group-flush rounded-bottom">
                <?php foreach($matchedHospitals as $h): ?>
                <div class="list-group-item p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($h['name']) ?></h6>
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
            <h6 class="fw-bold text-dark mb-1">No Suitable Facilities Found Online</h6>
            <p class="mb-0 text-muted">We could not match you with an active emergency facility in your immediate area. Please contact local emergency services directly or proceed to the nearest open hospital.</p>
        </div>
        <?php endif; ?>

        <div class="alert alert-warning border border-warning shadow-sm rounded-3 mb-4">
            <h6 class="fw-bold text-dark">Human Review Required</h6>
            <p class="mb-0 text-muted small">This assessment is AI-assisted and requires review by a qualified healthcare professional.</p>
        </div>

        <div class="alert alert-secondary border-0 rounded-3 mb-4 text-center">
            <p class="mb-2 fw-bold text-dark small">VITALYNX provides AI-assisted emergency triage support and does not replace emergency services or qualified healthcare professionals.</p>
            <?php if ($urgency === 'CRITICAL' || $urgency === 'HIGH'): ?>
                <p class="mb-0 fw-bold text-danger text-uppercase">Seek appropriate emergency medical care immediately.</p>
            <?php endif; ?>
        </div>

        <div class="d-grid mt-4">
            <a href="/dashboard" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold">Return to Dashboard</a>
        </div>

    </div>
</div>
