
<div class="container py-4 mb-5 vl-history-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <p class="vl-eyebrow mb-2">PATIENT WORKSPACE / RECORDS</p>
            <h1 class="fw-bold text-dark mb-1">Your case history</h1>
            <p class="text-muted mb-0">Review previous reports, assessment status, and care outcomes.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="/dashboard" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Back to Active Cases</a>
        </div>
    </div>

    <?php if (empty($cases)): ?>
        <div class="card border-0 shadow-sm rounded-3 py-5 text-center bg-light">
            <div class="card-body">
                <span class="vl-empty-mark mb-3" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s-7-4.4-7-11a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 6.6-7 11-7 11Z"/><path d="M5 12h4l2-3 2 6 2-3h4"/></svg></span>
                <h4 class="fw-bold text-dark">Your case history is empty</h4>
                <p class="text-muted">When you submit an emergency report, it will be listed here.</p>
                <a href="/emergency/report" class="btn btn-primary">Start an emergency report</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($cases as $case): ?>
                <?php 
                    $uColor = 'secondary';
                    if ($case['urgency'] === 'critical') $uColor = 'danger';
                    elseif ($case['urgency'] === 'high') $uColor = 'warning';
                    elseif ($case['urgency'] === 'medium') $uColor = 'primary';
                    elseif ($case['urgency'] === 'low') $uColor = 'success';
                ?>
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($case['case_number']) ?></h5>
                                        <span class="status-badge status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $case['status'])) ?></span>
                                        <?php if (!empty($case['urgency'])): ?>
                                            <span class="urgency-badge urgency-<?= strtoupper($case['urgency']) ?>"><?= strtoupper($case['urgency']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <strong>Reported:</strong> <?= date('M j, Y, g:i A', strtotime($case['created_at'])) ?>
                                        <?php if ($case['resolved_at']): ?>
                                            <br><strong>Resolved:</strong> <?= date('M j, Y, g:i A', strtotime($case['resolved_at'])) ?>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if ($case['resolution_outcome']): ?>
                                        <p class="mb-1 text-dark"><strong>Outcome:</strong> <?= htmlspecialchars(str_replace('_', ' ', $case['resolution_outcome'])) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($case['hospital_names']): ?>
                                        <p class="mb-1 text-dark small"><strong>Hospital(s):</strong> <?= htmlspecialchars($case['hospital_names']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($case['followup_id']): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill mt-2">
                                            Feedback Submitted
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <?php if ($case['status'] === 'REPORTED'): ?>
                                        <a href="/emergency/triage?id=<?= (int)$case['id'] ?>" class="btn btn-primary rounded-pill px-4 fw-bold">Continue Assessment</a>
                                    <?php else: ?>
                                        <a href="/patient/case?id=<?= (int)$case['id'] ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View Case Details</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Previous</a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
            
            <?php if (count($cases) === 20): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
