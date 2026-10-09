
<div class="container-fluid py-4 mb-5 vl-admin-page">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Manage Hospitals</h2>
            <p class="text-muted mb-0">Operational configuration and availability controls.</p>
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
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/hospitals">Hospitals</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/cases">Cases</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/events">Event Log</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/analytics">Analytics</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- /Admin Navigation -->
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success rounded-3 shadow-sm mb-4">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Scrollable records table">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="py-3">Name & Address</th>
                        <th class="py-3">System Status</th>
                        <th class="py-3">Emergency Availability</th>
                        <th class="py-3 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hospitals as $h): ?>
                        <tr>
                            <td class="px-4 py-3 fw-bold text-dark">
                                <?= $h['id'] ?>
                            </td>
                            <td class="py-3">
                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($h['name']) ?></span>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;"><?= htmlspecialchars($h['address']) ?></small>
                            </td>
                            <td class="py-3">
                                <span class="badge <?= $h['status'] === 'active' ? 'bg-success' : 'bg-danger' ?> text-uppercase px-3 py-2 fs-6 shadow-sm">
                                    <?= htmlspecialchars($h['status']) ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="badge <?= $h['emergency_available'] ? 'bg-primary' : 'bg-warning text-dark' ?> text-uppercase px-3 py-2 fs-6 shadow-sm">
                                    <?= $h['emergency_available'] ? 'AVAILABLE' : 'OFFLINE' ?>
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <form action="/admin/hospital/update" method="POST" class="d-inline me-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                    <input type="hidden" name="field" value="status">
                                    <input type="hidden" name="value" value="<?= $h['status'] === 'active' ? 'inactive' : 'active' ?>">
                                    <button type="submit" class="btn btn-sm <?= $h['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?> fw-bold px-3" onclick="return confirm('WARNING: Changing system status affects whether hospital staff can log in and use the platform. Continue?')">
                                        <?= $h['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <form action="/admin/hospital/update" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                    <input type="hidden" name="field" value="emergency_available">
                                    <input type="hidden" name="value" value="<?= $h['emergency_available'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm <?= $h['emergency_available'] ? 'btn-outline-warning' : 'btn-outline-primary' ?> fw-bold px-3" onclick="return confirm('WARNING: Modifying Emergency Availability directly controls whether this hospital receives new AI-routed emergency cases. Proceed?')">
                                        <?= $h['emergency_available'] ? 'Set Offline' : 'Set Available' ?>
                                    </button>
                                </form>
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
        
        <?php if (count($hospitals) === 50): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Next</a>
        <?php endif; ?>
    </div>
</div>
