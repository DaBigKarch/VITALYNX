<div class="row justify-content-center vl-patient-case">
    <div class="col-md-8 col-lg-6">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="/dashboard" class="text-decoration-none text-muted fw-bold">&larr; Back to Dashboard</a>
        </div>

        <div class="text-center mb-4">
            <h2 class="mb-1 fw-bold text-dark">VITALYNX</h2>
            <h4 class="text-muted">Emergency Case Status</h4>
            <span class="badge bg-secondary mt-2 fs-6">Case: <?= htmlspecialchars($case['case_number']) ?></span>
            <div class="mt-2">
            <?php
                $statusMsg = 'Unknown status';
                $sColor = 'secondary';
                if ($case['status'] === 'REPORTED') {
                    $statusMsg = 'Emergency received';
                    $sColor = 'info text-dark';
                } elseif ($case['status'] === 'TRIAGED') {
                    $statusMsg = 'Emergency assessed';
                    $sColor = 'warning text-dark';
                } elseif ($case['status'] === 'ACCEPTED') {
                    $statusMsg = 'Receiving facility accepted the case';
                    $sColor = 'success';
                } elseif ($case['status'] === 'IN_CARE') {
                    $statusMsg = 'Patient in care';
                    $sColor = 'primary';
                } elseif ($case['status'] === 'RESOLVED') {
                    $statusMsg = 'Case resolved';
                    $sColor = 'dark';
                }
            ?>
            <span class="badge bg-<?= $sColor ?> fs-5 px-3 py-2 border border-<?= $sColor ?> shadow-sm"><?= htmlspecialchars($statusMsg) ?></span>
            </div>
            
            <?php if (!empty($case['latitude']) && !empty($case['longitude'])): ?>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2">
                        <i class="me-1">📍</i> Emergency location captured
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($case['status'] === 'RESOLVED'): ?>
            <div class="card bg-success bg-opacity-10 border-0 border-start border-success border-5 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold text-success mb-2">CASE RESOLVED</h4>
                    <p class="mb-3 text-dark">This emergency case has been marked as resolved by the care team. Active monitoring has concluded.</p>
                    
                    <div class="bg-white p-3 rounded-3 shadow-sm border mb-0">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <small class="text-muted fw-bold text-uppercase d-block mb-1">Outcome</small>
                                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars(str_replace('_', ' ', $case['resolution_outcome'] ?? 'COMPLETED')) ?></span>
                            </div>
                            <div class="col-md-8 pt-3 pt-md-0">
                                <small class="text-muted fw-bold text-uppercase d-block mb-1">Resolution Summary</small>
                                <span class="text-dark"><?= nl2br(htmlspecialchars($case['resolution_summary'] ?? 'Case resolved.')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($case['status'] === 'RESOLVED'): ?>
            <div class="card shadow-sm border-0 rounded-3 mb-4 border-start border-primary border-5">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Post-Emergency Follow-Up</h5>
                    <?php if ($followup): ?>
                        <div class="alert alert-success border-success border-2 shadow-sm rounded-3">
                            <h6 class="fw-bold text-success mb-2">Feedback Received ✓</h6>
                            <p class="mb-0 small">Thank you for providing your follow-up.</p>
                            <?php if (!empty($followup['experience_rating'])): ?>
                                <p class="mb-0 small">Your VITALYNX emergency coordination experience was rated <strong><?= htmlspecialchars($followup['experience_rating']) ?>/5</strong>.</p>
                            <?php endif; ?>
                            <?php if ($followup['needs_further_coordination']): ?>
                                <hr>
                                <?php if (!empty($followup['hospital_id'])): ?>
                                    <p class="mb-0 small fw-bold">Your request was shared with the receiving facility. Contact local emergency services directly if you need urgent help.</p>
                                <?php else: ?>
                                    <p class="mb-0 small fw-bold">Your request was saved, but no receiving facility is linked to this case. Contact local emergency services directly if you need urgent help.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 border-top border-primary border-4 rounded-3">
                            <div class="card-body p-4 bg-white">
                                <h6 class="fw-bold text-dark mb-3">Case Resolution & Feedback</h6>
                                <form id="followupForm" action="/patient/case/follow-up" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                                    
                                    <div class="mb-4 bg-light p-3 rounded-3 border">
                                        <label class="form-label fw-bold text-dark d-block">1. Operational Coordination</label>
                                        <p class="small text-muted mb-2">Do you require any further coordination from VITALYNX regarding this emergency?</p>
                                        <label for="needs_coordination" class="visually-hidden">Further coordination required</label>
                                        <select id="needs_coordination" name="needs_coordination" class="form-select w-auto" required>
                                            <option value="">-- Select --</option>
                                            <option value="1">Yes, further coordination needed</option>
                                            <option value="0">No, coordination complete</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark d-block">2. Experience Feedback</label>
                                        <p class="small text-muted mb-2">How would you rate your coordination experience with VITALYNX? (Optional)</p>
                                        <select name="experience_rating" class="form-select w-auto">
                                            <option value="">-- Select Rating --</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Adequate</option>
                                            <option value="2">2 - Poor</option>
                                            <option value="1">1 - Very Poor</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-dark d-block">3. Additional Comments</label>
                                        <textarea name="feedback_notes" class="form-control" rows="3" placeholder="Provide any additional feedback about the system..."></textarea>
                                    </div>

                                    <button type="submit" id="btnSubmitFollowup" class="btn btn-primary fw-bold px-4 py-2">Submit Feedback</button>
                                </form>
                                <script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        const form = document.getElementById("followupForm");
                                        if(form) {
                                            form.addEventListener("submit", function() {
                                                const btn = document.getElementById("btnSubmitFollowup");
                                                btn.disabled = true;
                                                btn.innerHTML = "Submitting...";
                                            });
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($assessment) && !empty($assessment['urgency'])): ?>
            <?php
                $urgency = strtoupper($assessment['urgency']);
                $uColor = 'secondary';
                if ($urgency === 'CRITICAL') $uColor = 'danger';
                elseif ($urgency === 'HIGH') $uColor = 'warning';
                elseif ($urgency === 'MODERATE') $uColor = 'primary';
                elseif ($urgency === 'LOW') $uColor = 'success';
            ?>
            <div class="card shadow border-0 rounded-3 mb-4 border-top border-<?= $uColor ?> border-5">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-muted">Preliminary urgency</h5>
                        <span class="badge bg-<?= $uColor ?> fs-5 px-3 py-2"><?= htmlspecialchars($urgency) ?></span>
                    </div>
                    <div class="mb-4">
                        <h5 class="fw-bold text-muted mb-2">Assessment summary</h5>
                        <p class="fs-6 mb-0"><?= nl2br(htmlspecialchars($assessment['summary'] ?? '')) ?></p>
                    </div>
                    <?php if (!empty($assessment['recommendation'])): ?>
                        <div>
                            <h5 class="fw-bold text-muted mb-2">Recommended next step</h5>
                            <div class="alert alert-info border-info border-2 fw-semibold mb-0 fs-6"><?= htmlspecialchars($assessment['recommendation']) ?></div>
                        </div>
                    <?php endif; ?>
                    <p class="small text-muted mt-3 mb-0">AI-assisted decision support only. This is not a diagnosis; qualified healthcare staff make clinical decisions.</p>
                </div>
            </div>
        <?php elseif ($case['status'] === 'REPORTED'): ?>
            <div class="card vl-assessment-pending mb-4">
                <div class="card-body p-4">
                    <span class="vl-pending-label">TRIAGE IN PROGRESS</span>
                    <h3 class="h5 mt-2 mb-2">Your emergency report is saved</h3>
                    <p class="text-muted mb-3">Continue the triage conversation to provide details for the preliminary assessment. No urgency level has been assigned yet.</p>
                    <a class="btn btn-primary" href="/emergency/triage?id=<?= (int)$case['id'] ?>">Continue triage conversation <span aria-hidden="true">→</span></a>
                    <p class="small text-danger fw-semibold mt-3 mb-0">If this may be life-threatening, contact local emergency services now. Do not wait for the online assessment.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card vl-assessment-pending mb-4">
                <div class="card-body p-4">
                    <span class="vl-pending-label">ASSESSMENT UNAVAILABLE</span>
                    <h3 class="h5 mt-2 mb-2">No triage result is available</h3>
                    <p class="text-muted mb-0">This case remains saved. Contact local emergency services directly if urgent help is needed.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($aiMessages)): ?>
        <section class="card vl-ai-transcript mb-4" aria-labelledby="triage-conversation-title">
            <div class="card-header">
                <div><h2 id="triage-conversation-title" class="h6 mb-1">Triage conversation</h2><p class="small text-muted mb-0">Your initial exchange with VITALYNX triage support.</p></div>
                <?php if ($case['status'] === 'REPORTED'): ?><a class="btn btn-sm btn-outline-primary" href="/emergency/triage?id=<?= (int)$case['id'] ?>">Continue</a><?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <?php foreach ($aiMessages as $aiMessage): ?>
                    <?php if ($aiMessage['sender'] === 'system'): ?>
                        <p class="vl-ai-system-message mb-0"><?= nl2br(htmlspecialchars($aiMessage['message'])) ?></p>
                    <?php else: ?>
                        <div class="vl-ai-message <?= $aiMessage['sender'] === 'user' ? 'vl-ai-message-user' : 'vl-ai-message-assistant' ?>">
                            <span><?= $aiMessage['sender'] === 'user' ? 'You' : 'VITALYNX triage support' ?></span>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($aiMessage['message'])) ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($careUpdates)): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-success border-5">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0 text-success">Care Team Updates</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($careUpdates as $update): ?>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="badge bg-success text-white border me-2"><?= htmlspecialchars(str_replace('_', ' ', $update['update_type'])) ?></span>
                                </div>
                                <span class="text-muted small"><?= date('M j, g:i A', strtotime($update['created_at'])) ?></span>
                            </div>
                            <div class="fs-5 text-dark fw-medium"><?= nl2br(htmlspecialchars($update['update_text'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow border-0 rounded-3 mb-4">
            <div class="card-header bg-light border-0 py-3 px-4 rounded-3">
                <h5 class="fw-bold mb-0 text-dark">Case Timeline</h5>
            </div>
            <div class="card-body p-4 bg-white rounded-3">
                <?php if (empty($events)): ?>
                    <p class="text-muted mb-0">No timeline events recorded yet.</p>
                <?php else: ?>
                    <ul class="vl-timeline">
                        
                        <?php foreach (array_reverse($events) as $event): ?>
                            <?php
                                $eventName = $event['event'];
                                $color = 'primary';
                                $actorLabel = '';
                                
                                if ($eventName === 'EMERGENCY_REPORTED') { $eventName = 'Emergency reported'; $color = 'primary'; }
                                elseif ($eventName === 'SOS_ACTIVATED') { $eventName = 'Emergency SOS activated'; $color = 'danger'; }
                                elseif ($eventName === 'AI_ASSESSMENT_COMPLETED') { $eventName = 'AI assessment completed'; $color = 'info'; }
                                elseif ($eventName === 'HOSPITALS_MATCHED') { $eventName = 'Emergency case routed to nearby emergency facilities'; $color = 'warning'; }
                                elseif ($eventName === 'CASE_ROUTED') { $eventName = 'Case routed to facility'; $color = 'warning'; }
                                elseif ($eventName === 'HOSPITAL_NOTIFIED') { $eventName = 'Hospital facility notified'; $color = 'secondary'; }
                                elseif ($eventName === 'MORE_INFORMATION_REQUESTED') { $eventName = 'Hospital requested more information'; $color = 'warning'; }
                                elseif ($eventName === 'CASE_ACCEPTED') { $eventName = 'Hospital accepted the case'; $color = 'success'; }
                                elseif ($eventName === 'CASE_DECLINED' || $eventName === 'HOSPITAL_DECLINED') { $eventName = 'A matched facility was unavailable'; $color = 'secondary'; }
                                elseif ($eventName === 'PATIENT_MESSAGE') { $eventName = 'Patient sent a message'; $color = 'primary'; }
                                elseif ($eventName === 'HOSPITAL_MESSAGE') { $eventName = 'Message received from hospital'; $color = 'success'; }
                                elseif ($eventName === 'CASE_ASSIGNED') { $eventName = 'Care team assigned'; $color = 'info'; }
                                elseif ($eventName === 'CASE_IN_CARE') { $eventName = 'Care team started active care'; $color = 'success'; }
                                elseif ($eventName === 'CLINICAL_NOTE_ADDED') { $eventName = 'Clinical team is reviewing your case'; $color = 'secondary'; }
                                elseif ($eventName === 'DOCTOR_REVIEW_REQUESTED') { $eventName = 'Doctor review requested'; $color = 'warning'; }
                                elseif ($eventName === 'CARE_UPDATE_POSTED') { $eventName = 'Care team provided an update'; $color = 'success'; }
                                elseif ($eventName === 'CASE_RESOLVED') { $eventName = 'Case resolved by the care team'; $color = 'success'; }
                                elseif ($eventName === 'CASE_FEEDBACK_SUBMITTED') { $eventName = 'Patient feedback submitted'; $color = 'info'; }
                                
                                if (strpos($event['actor'], 'system') !== false) $actorLabel = 'System';
                                elseif (strpos($event['actor'], 'patient') !== false) $actorLabel = 'Patient';
                                elseif (strpos($event['actor'], 'hospital') !== false || strpos($event['actor'], 'user') !== false) $actorLabel = 'Hospital Team';
                            ?>
                            <li class="vl-timeline-item">
                                <div class="d-flex align-items-start">
                                    <div class="vl-timeline-dot bg-<?= $color ?>"></div>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($eventName) ?></h6>
                                        <p class="text-muted small mb-0">
                                            <?= htmlspecialchars(date('M j, Y g:i A', strtotime($event['created_at']))) ?>
                                            <?php if ($actorLabel): ?>
                                            &bull; <span class="badge bg-light text-dark border"><?= $actorLabel ?></span>
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

        <div class="card shadow border-0 rounded-3 mb-4">
            <div class="card-header bg-light border-0 py-3 px-4 rounded-3">
                <h5 class="fw-bold mb-0 text-dark">Recommended Emergency Facilities</h5>
            </div>
            <div class="card-body p-4 bg-white rounded-3">
                <?php if (empty($case['latitude']) || empty($case['longitude'])): ?>
                    <p class="text-muted mb-3">Emergency location unavailable.</p>
                    <div class="alert alert-info border-info mb-0">
                        Share your location during case creation to receive nearby facility recommendations.
                    </div>
                <?php elseif (empty($recommendedHospitals)): ?>
                    <p class="text-muted mb-0">No suitable emergency facilities found near your location.</p>
                <?php else: ?>
                    <p class="text-muted small mb-3">VITALYNX recommends these facilities based on proximity and reported emergency availability.</p>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recommendedHospitals as $hospital): ?>
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($hospital['name']) ?></h6>
                                    <span class="badge bg-primary rounded-pill">Approx. <?= htmlspecialchars(number_format($hospital['distance'], 1)) ?> km</span>
                                </div>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($hospital['address']) ?></p>
                                <?php if (!empty($hospital['phone'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary mt-1" href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $hospital['phone'])) ?>">Call facility: <?= htmlspecialchars($hospital['phone']) ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])): ?>
        <div class="card shadow border-0 rounded-3 mb-4 border-primary">
            <div class="card-header bg-primary text-white border-0 py-3 px-4 rounded-3">
                <h5 class="fw-bold mb-0">Hospital Communication</h5>
            </div>
            <div class="card-body p-4 bg-light overflow-auto" style="max-height: 400px;">
                <?php if (empty($messages)): ?>
                    <p class="text-center text-muted mb-0 fw-medium">No messages sent yet.</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-3 d-flex flex-column <?= $msg['sender_role'] === 'patient' ? 'align-items-end' : 'align-items-start' ?>">
                            <div class="d-flex align-items-baseline mb-1 gap-2">
                                <span class="fw-bold <?= $msg['sender_role'] === 'patient' ? 'text-primary' : 'text-success' ?>"><?= $msg['sender_role'] === 'patient' ? 'You' : 'Hospital Team' ?></span>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars(date('g:i A', strtotime($msg['created_at']))) ?></small>
                            </div>
                            <div class="p-3 rounded-3 shadow-sm <?= $msg['sender_role'] === 'patient' ? 'bg-primary text-white border-primary' : 'bg-white text-dark border' ?>" style="max-width: 85%;">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white border-0 p-4 rounded-3">
                <?php if ($case['status'] !== 'RESOLVED'): ?>
                <form action="/patient/case/message" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                    <div class="d-flex gap-2 w-100">
                        <input type="text" name="message" class="form-control form-control-lg" placeholder="Type a message..." aria-label="Type a message" required>
                        <button class="btn btn-primary btn-lg px-4 fw-bold" type="submit">Send Message</button>
                    </div>
                </form>
                <?php else: ?>
                    <p class="text-center text-muted mb-0 small">Messaging is disabled for resolved cases.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
