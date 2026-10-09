
<div class="container-fluid py-4 mb-5 vl-history-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Case History</h2>
            <p class="text-muted mb-0">Review resolved and historically closed cases associated with your facility.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="/hospital/dashboard" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Back to Dashboard</a>
        </div>
    </div>

    <?php if (empty($cases)): ?>
        <div class="card border-0 shadow-sm rounded-3 py-5 text-center bg-light">
            <div class="card-body">
                <div class="display-1 text-muted mb-3 opacity-25">📁</div>
                <h4 class="fw-bold text-dark">No closed cases yet</h4>
                <p class="text-muted mb-0">Cases resolved by your facility will appear here for later review.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records table">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-muted small fw-bold">
                        <tr>
                            <th class="px-4 py-3">Case ID</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Urgency</th>
                            <th class="py-3">Assignment</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Outcome</th>
                            <th class="py-3">Follow-Up</th>
                            <th class="px-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                            <?php 
                                $uColor = 'secondary';
                                if ($case['urgency'] === 'critical') $uColor = 'danger';
                                elseif ($case['urgency'] === 'high') $uColor = 'warning';
                                elseif ($case['urgency'] === 'medium') $uColor = 'primary';
                                elseif ($case['urgency'] === 'low') $uColor = 'success';
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-bold text-dark">
                                    <?= htmlspecialchars($case['case_number']) ?>
                                </td>
                                <td class="py-3 small text-muted">
                                    <?= date('M j, Y', strtotime($case['created_at'])) ?><br>
                                    <?= date('g:i A', strtotime($case['created_at'])) ?>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($case['urgency'])): ?>
                                        <span class="urgency-badge urgency-<?= strtoupper($case['urgency']) ?>"><?= strtoupper($case['urgency']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($case['assigned_name']): ?>
                                        <span class="text-dark fw-medium"><?= htmlspecialchars($case['assigned_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php $hstatus = $case['hospital_status'] === 'DECLINED' ? 'DECLINED' : ($case['hospital_status'] === 'EXPIRED' ? 'EXPIRED' : $case['status']); ?>
                                    <span class="status-badge status-<?= $hstatus ?>"><?= htmlspecialchars($hstatus) ?></span>
                                </td>
                                <td class="py-3 small text-dark fw-medium">
                                    <?= $case['resolution_outcome'] ? htmlspecialchars(str_replace('_', ' ', $case['resolution_outcome'])) : '-' ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($case['followup_id']): ?>
                                        <?php if ($case['needs_further_coordination']): ?>
                                            <span class="badge bg-warning text-dark rounded-pill">Req. Coord.</span>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">Submitted</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="/hospital/case?id=<?= $case['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill fw-bold px-3">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
