
<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Manage Users</h2>
            <p class="text-muted mb-0">System User Directory (Read-Only)</p>
        </div>
        
    </div>

    <!-- Admin Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill bg-light p-2 flex-column flex-md-row flex-nowrap overflow-auto" style="white-space: nowrap;">
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/dashboard">Dashboard</a>
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/users">Users</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/hospitals">Hospitals</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/cases">Cases</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/events">Event Log</a>
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
                        <th class="px-4 py-3">ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Details</th>
                        <th class="py-3 text-end px-4">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 py-3 fw-bold text-dark">
                                <?= $user['id'] ?>
                            </td>
                            <td class="py-3 fw-medium text-dark">
                                <?= htmlspecialchars($user['name']) ?>
                            </td>
                            <td class="py-3 text-muted">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="py-3">
                                <?php
                                    $rColor = "secondary";
                                    if ($user['role'] === 'admin') $rColor = "dark";
                                    elseif ($user['role'] === 'hospital') $rColor = "primary";
                                    elseif ($user['role'] === 'patient') $rColor = "success";
                                ?>
                                <span class="badge bg-<?= $rColor ?> text-uppercase px-3 py-2 fs-6 shadow-sm border border-<?= $rColor ?>"><?= htmlspecialchars($user['role']) ?></span>
                            </td>
                            <td class="py-3 small text-muted">
                                <?php if ($user['role'] === 'hospital'): ?>
                                    <?php if (!empty($user['staff_role'])): ?>
                                        <span class="badge bg-light text-primary border border-primary text-uppercase me-1"><?= htmlspecialchars($user['staff_role']) ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <span class="text-muted small">HOSP-<?= $user['hospital_id'] ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-muted small text-end px-4">
                                <?= date('Y-m-d H:i', strtotime($user['created_at'])) ?>
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
        
        <?php if (count($users) === 50): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Next</a>
        <?php endif; ?>
    </div>
</div>
