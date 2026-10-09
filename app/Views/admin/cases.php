
<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Emergency Cases</h2>
            <p class="text-muted mb-0">System-wide operational case oversight.</p>
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
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/cases">Cases</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/events">Event Log</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/analytics">Analytics</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Admin Navigation -->

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Case Filters</h6>
            <div class="d-flex gap-2">
                <a href="/admin/cases" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-secondary' ?> px-3 fw-bold">All</a>
                <a href="/admin/cases?status=REPORTED" class="btn btn-sm <?= $statusFilter === 'REPORTED' ? 'btn-primary' : 'btn-outline-secondary' ?> px-3 fw-bold">Reported</a>
                <a href="/admin/cases?status=IN_CARE" class="btn btn-sm <?= $statusFilter === 'IN_CARE' ? 'btn-primary' : 'btn-outline-secondary' ?> px-3 fw-bold">In Care</a>
                <a href="/admin/cases?status=RESOLVED" class="btn btn-sm <?= $statusFilter === 'RESOLVED' ? 'btn-primary' : 'btn-outline-secondary' ?> px-3 fw-bold">Resolved</a>
            </div>
        </div>
        <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records table">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="px-4 py-3">Case Number</th>
                        <th class="py-3">Created</th>
                        <th class="py-3">Urgency</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Resolution</th>
                        <th class="py-3 text-end px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><div class="display-6 opacity-25 mb-2">📁</div><span class="fw-bold">No cases found matching the criteria.</span></td></tr>
                    <?php else: ?>
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
                                    <?= date('M j, Y H:i', strtotime($case['created_at'])) ?>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($case['urgency'])): ?>
                                        <span class="urgency-badge urgency-<?= strtoupper($case['urgency']) ?>"><?= strtoupper($case['urgency']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <span class="status-badge status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $case['status'])) ?></span>
                                </td>
                                <td class="py-3 small text-muted">
                                    <?= $case['resolved_at'] ? date('M j, H:i', strtotime($case['resolved_at'])) : '-' ?>
                                </td>
                                <td class="py-3 text-end px-4">
                                    <a href="/admin/case?id=<?= $case['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill fw-bold px-3">Inspect</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="d-flex justify-content-between mt-4">
        <?php 
            $qStatus = $statusFilter ? "&status=" . urlencode($statusFilter) : "";
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $qStatus ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Previous</a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
        
        <?php if (count($cases) === 50): ?>
            <a href="?page=<?= $page + 1 ?><?= $qStatus ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Next</a>
        <?php endif; ?>
    </div>
</div>
