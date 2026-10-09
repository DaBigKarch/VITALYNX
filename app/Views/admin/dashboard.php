
<div class="vl-admin-page py-3 mb-5">
    <div class="vl-admin-heading mb-4">
        <div><p class="vl-eyebrow mb-2">Platform operations</p><h1 class="h2 fw-bold text-dark mb-2">Admin Dashboard</h1><p class="text-muted mb-0">Monitor users, facilities, and emergency case activity.</p></div>
        <span class="vl-admin-heading-mark" aria-hidden="true">V</span>
    </div>

    <!-- Admin Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <nav class="nav nav-pills nav-fill bg-light p-2 flex-column flex-md-row flex-nowrap overflow-auto" style="white-space: nowrap;">
                        <a class="nav-link rounded-3 px-4 py-2 active fw-bold shadow-sm" href="/admin/dashboard">Dashboard</a>
                        <a class="nav-link rounded-3 px-4 py-2 text-dark fw-medium" href="/admin/users">Users</a>
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

    <div class="row g-3 mb-4 vl-admin-overview">
        <div class="col-6 col-xl-3"><a class="vl-admin-kpi" href="/admin/users"><span>Registered users</span><strong><?= (int)$stats['total_users'] ?></strong><small><?= (int)$stats['total_patients'] ?> patients · <?= (int)$stats['total_staff'] ?> hospital staff</small></a></div>
        <div class="col-6 col-xl-3"><a class="vl-admin-kpi" href="/admin/hospitals"><span>Active facilities</span><strong><?= (int)$stats['active_hospitals'] ?><small class="vl-admin-kpi-total"> / <?= (int)$stats['total_hospitals'] ?></small></strong><small>Availability and onboarding</small></a></div>
        <div class="col-6 col-xl-3"><a class="vl-admin-kpi" href="/admin/cases"><span>Cases in active flow</span><strong><?= (int)$stats['active_cases'] ?></strong><small><?= (int)$stats['in_care_cases'] ?> currently in care</small></a></div>
        <div class="col-6 col-xl-3"><a class="vl-admin-kpi" href="/admin/analytics"><span>Resolved cases</span><strong><?= (int)$stats['resolved_cases'] ?></strong><small><?= (int)$stats['total_cases'] ?> total emergency cases</small></a></div>
    </div>

    <div class="vl-admin-scope mb-4"><strong>Platform operations access</strong><span>Use this workspace to monitor service activity, manage facility availability, and review audit events. Clinical assessment and patient-care decisions belong to authorized hospital staff.</span></div>

    <div class="vl-admin-shortcuts mb-4">
        <a href="/admin/cases"><span>Case oversight</span><strong>Review emergency cases →</strong><small>Inspect status, assessments, and routing records.</small></a>
        <a href="/admin/hospitals"><span>Facility operations</span><strong>Manage hospitals →</strong><small>Update facility status and emergency availability.</small></a>
        <a href="/admin/events"><span>Audit trail</span><strong>Open event log →</strong><small>Review recorded platform and administrative events.</small></a>
        <a href="/admin/analytics"><span>Service performance</span><strong>View analytics →</strong><small>Explore case flow and outcome metrics.</small></a>
    </div>

    <div class="row g-4">
        <!-- Users Column -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">User Metrics</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Total Users</span>
                        <span class="fs-5 fw-bold text-dark"><?= $stats['total_users'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Patients</span>
                        <span class="fs-5 fw-bold text-dark"><?= $stats['total_patients'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Hospital Staff</span>
                        <span class="fs-5 fw-bold text-dark"><?= $stats['total_staff'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hospitals Column -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">Hospital Metrics</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Total Hospitals</span>
                        <span class="fs-5 fw-bold text-dark"><?= $stats['total_hospitals'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Active Hospitals</span>
                        <span class="fs-5 fw-bold text-success"><?= $stats['active_hospitals'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cases Column -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 bg-primary bg-opacity-10">
                    <h5 class="fw-bold mb-4 text-primary border-bottom border-primary pb-2">Emergency Cases</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Total Cases</span>
                        <span class="fs-5 fw-bold text-dark"><?= $stats['total_cases'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Resolved</span>
                        <span class="fs-5 fw-bold text-success"><?= $stats['resolved_cases'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Active Flow</span>
                        <span class="fs-5 fw-bold text-danger"><?= $stats['active_cases'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fw-bold text-uppercase">Currently In Care</span>
                        <span class="fs-5 fw-bold text-primary"><?= $stats['in_care_cases'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
