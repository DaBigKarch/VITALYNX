<?php
$patientCaseCounts = ['active' => 0, 'assessment' => 0, 'in_care' => 0, 'resolved' => 0];
foreach ($cases as $patientCase) {
    if ($patientCase['status'] === 'RESOLVED') $patientCaseCounts['resolved']++;
    else $patientCaseCounts['active']++;
    if ($patientCase['status'] === 'REPORTED') $patientCaseCounts['assessment']++;
    if ($patientCase['status'] === 'IN_CARE') $patientCaseCounts['in_care']++;
}
?>
<div class="vl-page-heading vx-dashboard-heading">
    <div><p class="vl-eyebrow mb-2">Patient workspace</p><h1 class="h2 mb-2">Your care dashboard</h1><p class="text-muted mb-0">Report an emergency, follow your case status, and review updates from your care team.</p></div>
    <a href="/patient/history" class="btn btn-outline-primary fw-bold rounded-pill px-4">View case history</a>
</div>
<div class="row g-3 mt-2 mb-4 vl-stats">
    <div class="col-6 col-lg-3"><div class="vl-patient-metric"><span>Active cases</span><strong><?= (int)$patientCaseCounts['active'] ?></strong><small>Cases still in progress</small></div></div>
    <div class="col-6 col-lg-3"><div class="vl-patient-metric"><span>Assessment needed</span><strong><?= (int)$patientCaseCounts['assessment'] ?></strong><small>Continue your triage chat</small></div></div>
    <div class="col-6 col-lg-3"><div class="vl-patient-metric"><span>In care</span><strong><?= (int)$patientCaseCounts['in_care'] ?></strong><small>Care team is updating your case</small></div></div>
    <div class="col-6 col-lg-3"><div class="vl-patient-metric"><span>Unread updates</span><strong><?= (int)count($notifications) ?></strong><small>Messages and case changes</small></div></div>
</div>
<div class="row g-4 mt-2">
    <!-- Right Sidebar on Desktop / Top on Mobile: Emergency Actions -->
    <div class="col-lg-4 order-1 order-lg-2">
        <a href="/emergency/report" class="btn btn-danger btn-lg w-100 mb-3 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h4l2-9 5 18 5-9h4"/></svg>
            REPORT EMERGENCY
        </a>

        <div class="vl-card-emergency">
            <h5 class="text-danger fw-bold mb-2">EMERGENCY SOS</h5>
            <p class="small text-danger mb-2 fw-medium">Press and hold to send an emergency request to matched facilities through VITALYNX.</p>
            <p class="small text-dark mb-4">This does not contact emergency services or dispatch an ambulance. For immediate danger, call your local emergency number now.</p>
            
            <form id="sosForm" action="/emergency/sos" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="latitude" id="sos_latitude" value="">
                <input type="hidden" name="longitude" id="sos_longitude" value="">
                
                <button type="button" id="btnSos" class="vl-btn-sos" aria-describedby="sosHelp">
                    HOLD TO<br>SEND REQUEST
                </button>
                <span id="sosHelp" class="visually-hidden">Press and hold for 2.5 seconds to send an emergency request.</span>
            </form>
            <div id="sosStatus" role="status" aria-live="polite" class="mt-3 fw-bold text-danger small" style="min-height: 24px;"></div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btnSos = document.getElementById('btnSos');
                const sosForm = document.getElementById('sosForm');
                const sosStatus = document.getElementById('sosStatus');
                const latInput = document.getElementById('sos_latitude');
                const lngInput = document.getElementById('sos_longitude');
                
                let holdTimer;
                let isHolding = false;
                const holdDuration = 2500; // 2.5 seconds
                
                function startHold(e) {
                    if (e.type === 'touchstart') e.preventDefault();
                    if (isHolding) return;
                    isHolding = true;
                    
                    btnSos.classList.add('active');
                    btnSos.innerHTML = 'KEEP<br>HOLDING...';
                    sosStatus.textContent = '';
                    
                    holdTimer = setTimeout(() => {
                        btnSos.innerHTML = 'ACTIVATED';
                        btnSos.style.backgroundColor = '#1e293b'; // dark slate
                        btnSos.style.borderColor = '#334155';
                    sosStatus.textContent = 'Sending your request to VITALYNX...';
                        executeSos();
                    }, holdDuration);
                }
                
                function cancelHold() {
                    if (!isHolding) return;
                    isHolding = false;
                    clearTimeout(holdTimer);
                    
                    if (btnSos.innerHTML !== 'ACTIVATED') {
                        btnSos.classList.remove('active');
                        btnSos.innerHTML = 'HOLD TO<br>SEND REQUEST';
                        sosStatus.textContent = 'Hold cancelled';
                        setTimeout(() => { if (!isHolding) sosStatus.textContent = ''; }, 2000);
                    }
                }
                
                function executeSos() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            pos => { latInput.value = pos.coords.latitude; lngInput.value = pos.coords.longitude; sosForm.submit(); },
                            err => { sosForm.submit(); },
                            { timeout: 5000 }
                        );
                    } else {
                        sosForm.submit();
                    }
                }
                
                btnSos.addEventListener('mousedown', startHold);
                btnSos.addEventListener('touchstart', startHold, {passive: false});
                btnSos.addEventListener('keydown', e => {
                    if ((e.key === ' ' || e.key === 'Enter') && !e.repeat) {
                        e.preventDefault();
                        startHold(e);
                    }
                });
                btnSos.addEventListener('keyup', e => {
                    if (e.key === ' ' || e.key === 'Enter') cancelHold();
                });
                btnSos.addEventListener('blur', cancelHold);
                window.addEventListener('mouseup', cancelHold);
                window.addEventListener('touchend', cancelHold);
                btnSos.addEventListener('mouseleave', cancelHold);
                btnSos.addEventListener('touchcancel', cancelHold);
            });
        </script>
        
    </div>

    <!-- Left Main Column -->
    <div class="col-lg-8 order-2 order-lg-1">
        
        <?php if (!empty($notifications)): ?>
            <div class="mb-4">
                <h5 class="fw-bold mb-3 text-dark">Recent Notifications</h5>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($notifications as $n): ?>
                        <div class="card border-start border-primary border-4 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-light text-primary border border-primary fw-bold text-uppercase"><?= htmlspecialchars($n['type']) ?></span>
                                    <small class="text-muted fw-medium"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></small>
                                </div>
                                <p class="mb-2 text-dark fw-medium"><?= htmlspecialchars($n['message']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if ($n['case_id']): ?>
                                        <a href="/patient/case?id=<?= $n['case_id'] ?>" class="btn btn-sm btn-outline-primary fw-bold px-3">View Case</a>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>
                                    <form action="/notifications/read" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                                        <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none fw-medium p-0">Dismiss</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><h5 class="fw-bold text-dark m-0">Your cases in progress</h5><small class="text-muted">Open a case to continue assessment or view care-team updates.</small></div>
                <a href="/patient/history" class="btn btn-sm btn-link text-decoration-none">View All History</a>
            </div>
            
            <?php if (empty($cases)): ?>
                <div class="card shadow-sm border-0 bg-light text-center py-5">
                    <div class="card-body">
                        <h5 class="fw-bold text-muted mb-2">No Active Cases</h5>
                        <p class="text-muted mb-0">You currently have no active emergency cases.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($cases as $case): ?>
                        <div class="card vl-patient-case-card shadow-sm border-0 position-relative overflow-hidden">
                            <div class="card-body position-relative z-1 p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <span class="badge bg-dark fw-bold px-2 py-1"><?= htmlspecialchars($case['case_number']) ?></span>
                                        <span class="status-badge status-<?= htmlspecialchars($case['status']) ?>"><?= htmlspecialchars($case['status']) ?></span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1"><?= !empty($case['summary']) ? htmlspecialchars($case['summary']) : ($case['status'] === 'REPORTED' ? 'Assessment awaiting completion' : 'Care team coordination') ?></h6>
                                    <?php if (!empty($case['urgency'])): ?><p class="text-muted small mb-1">Preliminary urgency: <?= htmlspecialchars(strtolower($case['urgency'])) ?></p><?php endif; ?>
                                    <p class="text-muted small mb-0 fw-medium">
                                        Initiated: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($case['created_at']))) ?>
                                    </p>
                                </div>
                                <div class="d-flex flex-column align-items-md-end gap-2">
                                    <?php if (!empty($case['urgency'])): ?>
                                        <span class="urgency-badge urgency-<?= strtoupper($case['urgency']) ?>"><?= strtoupper($case['urgency']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($case['status'] === 'REPORTED'): ?>
                                        <a href="/emergency/triage?id=<?= (int)$case['id'] ?>" class="btn btn-primary fw-bold px-4">Continue Assessment</a>
                                    <?php else: ?>
                                        <a href="/patient/case?id=<?= (int)$case['id'] ?>" class="btn btn-primary fw-bold px-4">Track Progress</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>
