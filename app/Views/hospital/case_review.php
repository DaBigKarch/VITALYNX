<!-- HEADER (Case identity) -->
<div class="vx-header shadow-sm vx-case-hero">
    <a href="/hospital/dashboard"><span>&larr;</span> Back to Dashboard</a>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="vx-case-identity">
            <span class="vx-case-eyebrow">Emergency case review</span>
            <h1 class="vx-header-title">Case #<?= htmlspecialchars($case['case_number']) ?></h1>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (!empty($isRouted)): ?>
                <span class="vx-header-badge bg-warning text-dark">ROUTED TO YOU</span>
            <?php else: ?>
                <span class="vx-header-badge bg-secondary text-white">GENERAL QUEUE</span>
            <?php endif; ?>
            <span class="vx-header-badge bg-light text-dark">STATUS: <?= htmlspecialchars($case['status']) ?></span>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="vx-alert vx-alert-success shadow-sm mb-4">
        <div>✅</div>
        <div><?= htmlspecialchars($_SESSION['success']) ?></div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="vx-alert vx-alert-danger shadow-sm mb-4">
        <div>❌</div>
        <div><?= htmlspecialchars($_SESSION['error']) ?></div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
    // Prepare AI Assessment variables safely
    $hasAssessment = !empty($assessment);
    $urgency = 'UNASSIGNED';
    $uColor = 'unassigned';
    
    if ($hasAssessment) {
        $rawUrgency = strtoupper(trim($assessment['urgency'] ?? ''));
        $validUrgencies = ['CRITICAL', 'HIGH', 'MODERATE', 'MEDIUM', 'LOW'];
        if (!in_array($rawUrgency, $validUrgencies, true)) $rawUrgency = 'UNASSIGNED';
        if ($rawUrgency === 'MEDIUM') $rawUrgency = 'MODERATE';
        $urgency = $rawUrgency;
        
        if ($urgency === 'CRITICAL') $uColor = 'critical';
        elseif ($urgency === 'HIGH') $uColor = 'high';
        elseif ($urgency === 'MODERATE') $uColor = 'moderate';
        elseif ($urgency === 'LOW') $uColor = 'low';
    }
    
    $isSos = false;
    foreach ($events as $e) {
        if ($e['event'] === 'SOS_ACTIVATED') {
            $isSos = true;
            break;
        }
    }
    
    // Abstract the Hospital Actions form block to render it twice (mobile inline vs desktop sidebar)
    ob_start();
?>
    <?php if ($case['status'] === 'TRIAGED'): ?>
        <form action="/hospital/case/action" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="id" value="<?= $case['id'] ?>">
            <input type="hidden" name="action" value="accept">
            <button type="submit" class="vx-action-btn vx-btn-primary py-3 mb-3">Accept Case</button>
        </form>
        
        <?php if ($isRouted): ?>
            <form action="/hospital/case/action" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= $case['id'] ?>">
                <input type="hidden" name="action" value="decline">
                <button type="submit" class="vx-action-btn vx-btn-danger" onclick="return confirm('Are you sure you want to decline this case? This action cannot be undone.');">Decline Case</button>
            </form>
        <?php endif; ?>

        <form action="/hospital/case/action" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="id" value="<?= $case['id'] ?>">
            <input type="hidden" name="action" value="request_info">
            <button type="submit" class="vx-action-btn vx-btn-secondary mt-2">Request More Info</button>
        </form>
    <?php elseif ($case['status'] === 'IN_CARE' && isset($_SESSION['staff_role']) && in_array($_SESSION['staff_role'], ['nurse', 'doctor'])): ?>
        <form action="/hospital/case/resolve" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="id" value="<?= $case['id'] ?>">
            
            <div class="mb-3">
                <label class="vx-label">Resolution Outcome</label>
                <select name="resolution_outcome" class="vx-input" required>
                    <option value="">Select outcome...</option>
                    <option value="CARE_COMPLETED">Care Completed</option>
                    <option value="TRANSFERRED">Patient Transferred</option>
                    <option value="REFERRED">Patient Referred</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="vx-label">Resolution Summary</label>
                <textarea name="resolution_summary" class="vx-input" rows="3" placeholder="Enter safe summary of resolution..." required></textarea>
            </div>
            
            <button type="submit" class="vx-action-btn vx-btn-primary py-3 mt-2" onclick="return confirm('Are you sure you want to resolve this case? This action cannot be undone.');">Resolve Case</button>
        </form>
    <?php elseif ($case['status'] === 'RESOLVED'): ?>
        <div class="vx-alert vx-alert-success justify-content-center mb-0 text-center flex-column">
            <h5 class="fw-bold mb-0">CASE RESOLVED</h5>
            <span class="small fw-medium opacity-75">No further actions can be taken.</span>
        </div>
    <?php else: ?>
        <div class="vx-alert vx-alert-info justify-content-center mb-0 text-center">
            Actions disabled for <?= htmlspecialchars($case['status']) ?> status.
        </div>
    <?php endif; ?>
<?php
    $hospitalActionsHTML = ob_get_clean();
?>

<div class="row vx-case-layout">
    <!-- LEFT / MAIN COLUMN -->
    <div class="col-lg-8">
        
        <!-- COMPACT PATIENT / CASE SUMMARY -->
        <div class="vx-card mb-4">
            <div class="vx-card-header">
                Patient & Case Details
            </div>
            <div class="vx-card-body">
                <div class="row g-3">
                    <div class="col-md-4 col-6">
                        <span class="vx-label">Patient Name</span>
                        <span class="vx-value"><?= htmlspecialchars($patient['name'] ?? 'Unknown') ?></span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="vx-label">Phone</span>
                        <span class="vx-value"><?= htmlspecialchars($patient['phone'] ?? 'Not provided') ?></span>
                    </div>
                    <div class="col-md-4 col-6">
                        <span class="vx-label">Reported At</span>
                        <span class="vx-value"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($case['created_at']))) ?></span>
                    </div>
                    <div class="col-md-12 col-6">
                        <span class="vx-label">Location</span>
                        <span class="vx-value">
                            <?php if (!empty($case['latitude']) && !empty($case['longitude'])): ?>
                                <?= htmlspecialchars($case['latitude']) ?>, <?= htmlspecialchars($case['longitude']) ?>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($isSos): ?>
                    <div class="vx-alert vx-alert-danger mt-3 mb-0 py-2">
                        <div>⚠️</div>
                        <div>Emergency SOS activated by patient.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AI TRIAGE INTELLIGENCE (HERO SECTION) -->
        <?php if ($hasAssessment): ?>
            <div class="vx-card vx-hero-ai <?= $uColor ?> mb-4">
                <div class="vx-card-header bg-white border-0 pb-0">
                    <div>AI Triage Intelligence</div>
                    <div class="vx-urgency-badge <?= $uColor ?>"><?= htmlspecialchars($urgency) ?></div>
                </div>
                <div class="vx-card-body pt-3">
                    <div class="mb-4">
                        <span class="vx-label">Assessment Summary</span>
                        <div class="fs-5 fw-medium text-dark lh-base">
                            <?= nl2br(htmlspecialchars($assessment['summary'] ?? 'N/A')) ?>
                        </div>
                    </div>
                    
                    <?php
                        $redFlagsStr = $assessment['red_flags'] ?? '[]';
                        $redFlags = json_decode($redFlagsStr, true);
                        if (!is_array($redFlags)) $redFlags = [];
                    ?>
                    <?php if (!empty($redFlags)): ?>
                    <div class="mb-4">
                        <span class="vx-label">Red Flags</span>
                        <div class="d-flex flex-wrap">
                            <?php foreach($redFlags as $flag): ?>
                                <div class="vx-red-flag">⚠️ <?= htmlspecialchars($flag) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <span class="vx-label">Recommended Action</span>
                        <div class="vx-alert vx-alert-info mb-0">
                            <div>ℹ️</div>
                            <div><?= htmlspecialchars($assessment['recommendation'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    
                    <div class="row mt-4 pt-3 border-top border-light">
                        <div class="col-12">
                            <span class="vx-label">Verification</span>
                            <div class="vx-alert vx-alert-warning mb-0 py-2 d-inline-flex">
                                <div>⚠️</div>
                                <div>HUMAN REVIEW REQUIRED</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            AI provides decision support. Hospital staff make the final clinical decision.
                        </small>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="vx-card mb-4 border-2 border-secondary">
                <div class="vx-card-header">
                    AI Triage Intelligence
                </div>
                <div class="vx-card-body text-center py-5">
                    <h5 class="fw-bold text-muted mb-2">AI Assessment Unavailable</h5>
                    <p class="text-muted mb-0 fw-medium">No structured AI triage assessment exists for this case.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- MOBILE-ONLY HOSPITAL ACTIONS (Matches prompt ordering) -->
        <div class="vx-card mb-4 d-lg-none vx-mobile-actions">
            <div class="vx-card-header bg-white text-dark">
                Hospital Actions
            </div>
            <div class="vx-card-body">
                <?= $hospitalActionsHTML ?>
            </div>
        </div>

        <?php if (in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])): ?>
            <?php require __DIR__ . '/_care_team.php'; ?>
        <?php endif; ?>

        <!-- CONVERSATION / CHAT LOG -->
        <div class="vx-card mb-4">
            <div class="vx-card-header d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#chatCollapse">
                <span>Original Emergency Conversation</span>
                <span class="text-primary" style="font-size: 0.8rem;">SHOW / HIDE ▾</span>
            </div>
            <div class="collapse show" id="chatCollapse">
                <div class="vx-chat-container">
                    <?php if (!empty($aiMessages)): ?>
                        <?php foreach ($aiMessages as $msg): ?>
                            <div class="d-flex flex-column w-100">
                                <?php if ($msg['sender'] === 'user'): ?>
                                    <div class="vx-msg-meta align-self-start ms-1">Patient</div>
                                    <div class="vx-msg vx-msg-patient">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="vx-msg-meta align-self-end me-1">VITALYNX AI</div>
                                    <div class="vx-msg vx-msg-ai">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="d-flex flex-column w-100">
                            <div class="vx-msg-meta align-self-start ms-1">Patient (Initial Description)</div>
                            <div class="vx-msg vx-msg-patient">
                                <?= nl2br(htmlspecialchars($case['description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- PATIENT-VISIBLE CARE UPDATES -->
        <?php if (in_array($case['status'], ['IN_CARE'])): ?>
        <div class="vx-card mb-4" style="border-left: 4px solid #10b981;">
            <div class="vx-card-header">
                Patient-Visible Care Updates
            </div>
            <div class="vx-card-body">
                <?php if (empty($careUpdates)): ?>
                    <p class="text-muted mb-4 fw-medium">No care updates posted yet.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <?php foreach ($careUpdates as $update): ?>
                            <div class="p-3 border rounded-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="badge bg-success text-white me-2"><?= htmlspecialchars(str_replace('_', ' ', $update['update_type'])) ?></span>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($update['author_name']) ?></span>
                                        <span class="text-muted small ms-1">(<?= htmlspecialchars(ucfirst($update['staff_role'] ?? 'Staff')) ?>)</span>
                                    </div>
                                    <span class="text-muted fw-bold" style="font-size: 0.75rem;"><?= date('M j, g:i A', strtotime($update['created_at'])) ?></span>
                                </div>
                                <div class="fs-6 text-dark fw-medium lh-base"><?= nl2br(htmlspecialchars($update['update_text'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id']) && in_array($_SESSION['staff_role'] ?? '', ['nurse', 'doctor'])): ?>
                    <form action="/hospital/case/care-update" method="POST" class="bg-white p-3 border rounded-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                        
                        <div class="mb-3">
                            <label class="vx-label">Post Care Update</label>
                            <textarea name="update_text" class="vx-input" rows="2" placeholder="e.g. A doctor has reviewed your case..." required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <select name="update_type" class="vx-input w-auto bg-light border-0 fw-bold text-success" style="min-width: 150px;">
                                <option value="CARE_STARTED">Care Started</option>
                                <option value="PATIENT_CONTACTED">Patient Contacted</option>
                                <option value="MONITORING_UPDATE">Monitoring Update</option>
                                <option value="CARE_TEAM_UPDATE">Care Team Update</option>
                                <option value="DOCTOR_UPDATE">Doctor Update</option>
                                <option value="STATUS_UPDATE">Status Update</option>
                            </select>
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2">Post Update</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- INTERNAL CLINICAL NOTES -->
        <?php if (in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])): ?>
        <div class="vx-card mb-4" style="border-left: 4px solid #3b82f6;">
            <div class="vx-card-header">
                Internal Clinical Notes
            </div>
            <div class="vx-card-body">
                <?php if (empty($clinicalNotes)): ?>
                    <p class="text-muted mb-4 fw-medium">No clinical notes added yet.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <?php foreach ($clinicalNotes as $note): ?>
                            <div class="p-3 border rounded-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="badge bg-primary text-white me-2"><?= htmlspecialchars($note['note_type']) ?></span>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($note['author_name']) ?></span>
                                        <span class="text-muted small ms-1">(<?= htmlspecialchars(ucfirst($note['staff_role'] ?? 'Staff')) ?>)</span>
                                    </div>
                                    <span class="text-muted fw-bold" style="font-size: 0.75rem;"><?= date('M j, g:i A', strtotime($note['created_at'])) ?></span>
                                </div>
                                <div class="fs-6 text-dark lh-base"><?= nl2br(htmlspecialchars($note['note_text'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="/hospital/case/note" method="POST" class="bg-white p-3 border rounded-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                        
                        <div class="mb-3">
                            <label class="vx-label">Add Note</label>
                            <textarea name="note_text" class="vx-input bg-light" rows="3" placeholder="Enter clinical handoff or review note..." required></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <select name="note_type" class="vx-input w-auto border-0 fw-bold bg-light text-primary" style="min-width: 150px;">
                                <option value="GENERAL">General Note</option>
                                <?php if (isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'nurse'): ?>
                                <option value="NURSE_HANDOFF">Nurse Handoff</option>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'doctor'): ?>
                                <option value="DOCTOR_REVIEW">Doctor Review</option>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2">Save Internal Note</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CASE TIMELINE -->
        <div class="vx-card mb-4">
            <div class="vx-card-header">
                Case Timeline
            </div>
            <div class="vx-card-body">
                <?php if (empty($events)): ?>
                    <p class="text-muted mb-0 fw-medium">No timeline events recorded yet.</p>
                <?php else: ?>
                    <ul class="vx-timeline">
                        <?php foreach (array_reverse($events) as $event): ?>
                            <?php
                                $eventName = $event['event'];
                                $color = '#3b82f6'; // default primary
                                $actorLabel = '';
                                
                                if ($eventName === 'EMERGENCY_REPORTED') { $eventName = 'Emergency reported'; }
                                elseif ($eventName === 'SOS_ACTIVATED') { $eventName = 'Emergency SOS activated'; $color = '#ef4444'; }
                                elseif ($eventName === 'AI_ASSESSMENT_COMPLETED') { $eventName = 'AI assessment completed'; $color = '#8b5cf6'; }
                                elseif ($eventName === 'HOSPITALS_MATCHED') { $eventName = 'Routed to facilities'; $color = '#f59e0b'; }
                                elseif ($eventName === 'CASE_ROUTED') { $eventName = 'Case routed to facility'; $color = '#f59e0b'; }
                                elseif ($eventName === 'HOSPITAL_NOTIFIED') { $eventName = 'Hospital notified'; $color = '#94a3b8'; }
                                elseif ($eventName === 'MORE_INFORMATION_REQUESTED') { $eventName = 'More info requested'; $color = '#f59e0b'; }
                                elseif ($eventName === 'CASE_ACCEPTED') { $eventName = 'Case accepted'; $color = '#10b981'; }
                                elseif ($eventName === 'CASE_DECLINED' || $eventName === 'HOSPITAL_DECLINED') { $eventName = 'Facility unavailable'; $color = '#94a3b8'; }
                                elseif ($eventName === 'PATIENT_MESSAGE') { $eventName = 'Patient messaged'; }
                                elseif ($eventName === 'HOSPITAL_MESSAGE') { $eventName = 'Hospital messaged'; $color = '#10b981'; }
                                elseif ($eventName === 'CASE_ASSIGNED') { $eventName = 'Care team assigned'; $color = '#3b82f6'; }
                                elseif ($eventName === 'CASE_IN_CARE') { $eventName = 'Active care started'; $color = '#10b981'; }
                                elseif ($eventName === 'CLINICAL_NOTE_ADDED') { $eventName = 'Note added'; $color = '#94a3b8'; }
                                elseif ($eventName === 'DOCTOR_REVIEW_REQUESTED') { $eventName = 'Review requested'; $color = '#f59e0b'; }
                                elseif ($eventName === 'CARE_UPDATE_POSTED') { $eventName = 'Care update posted'; $color = '#10b981'; }
                                elseif ($eventName === 'CASE_RESOLVED') { $eventName = 'Case resolved'; $color = '#10b981'; }
                                elseif ($eventName === 'CASE_FEEDBACK_SUBMITTED') { $eventName = 'Feedback submitted'; $color = '#3b82f6'; }
                                
                                if (strpos($event['actor'], 'system') !== false) $actorLabel = 'SYSTEM';
                                elseif (strpos($event['actor'], 'patient') !== false) $actorLabel = 'PATIENT';
                                elseif (strpos($event['actor'], 'hospital') !== false || strpos($event['actor'], 'user') !== false) $actorLabel = 'HOSPITAL';
                            ?>
                            <li class="vx-timeline-item">
                                <div class="d-flex align-items-start">
                                    <div class="vx-timeline-dot" style="border-color: <?= $color ?>;"></div>
                                    <div class="vx-timeline-content">
                                        <h6><?= htmlspecialchars($eventName) ?></h6>
                                        <p>
                                            <span class="fw-bold text-dark me-1"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($event['created_at']))) ?></span>
                                            <?php if ($actorLabel): ?>
                                            &bull; <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;"><?= $actorLabel ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($case['status'] === 'RESOLVED' && !empty($followup)): ?>
        <div class="vx-card mb-4" style="border-left: 4px solid #0f172a;">
            <div class="vx-card-header">
                Post-Emergency Feedback
            </div>
            <div class="vx-card-body">
                <div class="bg-light p-4 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="vx-label mb-0">Experience Rating</span>
                        <span class="badge bg-dark fs-6 px-3 py-2"><?= htmlspecialchars($followup['experience_rating']) ?> / 5</span>
                    </div>
                    <?php if (!empty($followup['feedback_text'])): ?>
                        <hr class="my-3 opacity-10">
                        <span class="vx-label">Feedback Comments</span>
                        <p class="mb-0 text-dark fw-medium lh-base"><?= nl2br(htmlspecialchars($followup['feedback_text'])) ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ($followup['needs_further_coordination']): ?>
                    <div class="vx-alert vx-alert-warning mb-0">
                        <div>⚠️</div>
                        <div>Further coordination requested by the patient.</div>
                    </div>
                <?php else: ?>
                    <div class="vx-alert vx-alert-success mb-0">
                        <div>✅</div>
                        <div>No further coordination needed.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT COLUMN / SIDEBAR -->
    <div class="col-lg-4">
        
        <!-- DESKTOP HOSPITAL ACTIONS -->
        <div class="vx-card mb-4 d-none d-lg-block vx-desktop-actions">
            <div class="vx-card-header bg-white text-dark">
                Hospital Actions
            </div>
            <div class="vx-card-body">
                <?= $hospitalActionsHTML ?>
            </div>
        </div>

        <!-- DESKTOP CARE TEAM; mobile version appears near Hospital Actions -->
        <?php if (in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])): ?>
            <div class="d-none d-lg-block">
                <?php require __DIR__ . '/_care_team.php'; ?>
            </div>
        <?php endif; ?>

        <!-- NEARBY FACILITIES -->
        <?php if (!empty($case['latitude']) && !empty($case['longitude'])): ?>
        <div class="vx-card mb-4">
            <div class="vx-card-header">
                Nearby Facilities
            </div>
            <div class="vx-card-body p-0">
                <?php if (empty($recommendedHospitals)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0 fw-medium">No suitable emergency facilities found near this location.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recommendedHospitals as $hospital): ?>
                            <div class="list-group-item p-4 border-bottom border-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($hospital['name']) ?></h6>
                                </div>
                                <div class="mb-1">
                                    <span class="badge bg-light text-secondary border px-2 py-1">Approx. <?= htmlspecialchars(number_format($hospital['distance'], 1)) ?> km</span>
                                </div>
                                <p class="text-muted small mb-0 fw-medium"><?= htmlspecialchars($hospital['address']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- PATIENT MESSAGING -->
        <?php if (in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])): ?>
        <div class="vx-card mb-4" style="border-top: 4px solid #10b981;">
            <div class="vx-card-header">
                Patient Messaging
            </div>
            <div class="vx-chat-container" style="background: #ffffff; max-height: 350px;">
                <?php if (empty($humanMessages)): ?>
                    <p class="text-center text-muted mb-0 fw-medium my-4">No messages sent yet.</p>
                <?php else: ?>
                    <?php foreach ($humanMessages as $msg): ?>
                        <div class="d-flex flex-column w-100 mb-2">
                            <?php if ($msg['sender_role'] === 'hospital'): ?>
                                <div class="vx-msg-meta align-self-end me-1 d-flex gap-2">
                                    <span><?= htmlspecialchars(date('g:i A', strtotime($msg['created_at']))) ?></span>
                                    <span>Hospital Team (You)</span>
                                </div>
                                <div class="vx-msg vx-msg-hospital mb-0">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </div>
                            <?php else: ?>
                                <div class="vx-msg-meta align-self-start ms-1 d-flex gap-2">
                                    <span>Patient</span>
                                    <span><?= htmlspecialchars(date('g:i A', strtotime($msg['created_at']))) ?></span>
                                </div>
                                <div class="vx-msg vx-msg-patient border mb-0">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="vx-card-body border-top border-light bg-light p-3">
                <?php if ($case['status'] !== 'RESOLVED'): ?>
                <form action="/hospital/case/action" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                    <input type="hidden" name="action" value="send_message">
                    <div class="d-flex flex-column gap-2">
                        <textarea name="message" class="vx-input bg-white" rows="2" placeholder="Type a message..." required></textarea>
                        <button class="vx-action-btn vx-btn-primary m-0 py-2" type="submit">Send</button>
                    </div>
                </form>
                <?php else: ?>
                    <p class="text-center text-muted mb-0 fw-medium small">Messaging disabled for resolved cases.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
