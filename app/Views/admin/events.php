
<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">System Events Log</h2>
            <p class="text-muted mb-0">Immutable global audit trail of case operations.</p>
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
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/events">Event Log</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/analytics">Analytics</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Admin Navigation -->

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records table">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="px-4 py-3">Event ID</th>
                        <th class="py-3">Timestamp</th>
                        <th class="py-3">Case ID</th>
                        <th class="py-3">Event Type</th>
                        <th class="py-3 px-4 text-end">Actor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><div class="display-6 opacity-25 mb-2">📋</div><span class="fw-bold">Audit log is empty.</span></td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td class="px-4 py-3 text-muted small">
                                    <?= $event['id'] ?>
                                </td>
                                <td class="py-3 small text-muted">
                                    <?= date('Y-m-d H:i:s', strtotime($event['created_at'])) ?>
                                </td>
                                <td class="py-3 fw-bold text-dark">
                                    <a href="/admin/case?id=<?= $event['case_id'] ?>" class="text-decoration-none">Case #<?= $event['case_id'] ?></a>
                                </td>
                                <td class="py-3">
                                    <?php 
                                        $eType = $event['event_type'];
                                        $eColor = 'secondary';
                                        if (strpos($eType, 'REPORTED') !== false || strpos($eType, 'SOS') !== false) $eColor = 'danger';
                                        elseif (strpos($eType, 'RESOLVED') !== false || strpos($eType, 'ACCEPTED') !== false || strpos($eType, 'COMPLETED') !== false) $eColor = 'success';
                                        elseif (strpos($eType, 'ASSIGNED') !== false || strpos($eType, 'ROUTED') !== false) $eColor = 'primary';
                                        elseif (strpos($eType, 'DECLINED') !== false || strpos($eType, 'EXPIRED') !== false) $eColor = 'dark';
                                    ?>
                                    <span class="badge bg-<?= $eColor ?> text-uppercase px-3 py-2 fs-6 shadow-sm">
                                        <?= htmlspecialchars(str_replace('_', ' ', $event['event_type'])) ?>
                                    </span>
                                </td>
                                <td class="py-3 small text-muted text-end px-4">
                                    <?= htmlspecialchars($event['actor_id'] ?? 'System') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
        
        <?php if (count($events) === 100): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Next</a>
        <?php endif; ?>
    </div>
</div>
