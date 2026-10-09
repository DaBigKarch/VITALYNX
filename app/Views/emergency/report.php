<div class="row justify-content-center vl-report">
    <div class="col-md-10 col-lg-8">
        <p class="vl-eyebrow mb-2">PATIENT WORKSPACE <span aria-hidden="true">/</span> EMERGENCY REPORT</p>
        <h1 class="h2 mb-3 fw-bold">Report an Emergency</h1>
        <p class="lead mb-4 text-muted">Share the key details so your case can be assessed and reviewed by an appropriate care team.</p>
        
        <div class="alert alert-warning border-warning border-2 fw-semibold d-flex align-items-center mb-4 rounded-3 py-3 shadow-sm">
            <span class="fs-4 me-3">⚠️</span>
            <div><strong>Immediate danger?</strong> If this is immediately life-threatening, contact your local emergency services now. <a class="d-block mt-2 fw-bold" href="#emergency-sos">Go to Emergency SOS below &darr;</a></div>
        </div>

        <ol class="vl-report-steps" aria-label="What happens after you submit">
            <li class="is-current"><span>01</span><div><strong>Report</strong><small>Share the situation</small></div></li>
            <li><span>02</span><div><strong>Assess</strong><small>Answer guided questions</small></div></li>
            <li><span>03</span><div><strong>Review</strong><small>Care team reviews the case</small></div></li>
        </ol>
        
        <div class="card shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <form id="reportForm" action="/emergency/report" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold fs-5 mb-3">What is happening?</label>
                        <textarea 
                            class="form-control form-control-lg rounded-3 bg-light" 
                            id="description" 
                            name="description" 
                            rows="5" 
                            maxlength="5000"
                            placeholder="Describe the symptoms, who needs help, and when they started..."
                            aria-describedby="descriptionHint" required><?= htmlspecialchars($description ?? '') ?></textarea>
                        <p id="descriptionHint" class="form-text mb-0">Describe the symptoms, who needs help, and when the problem started.</p>
                    </div>

                    <div class="mb-4">
                        <div class="card bg-light border-0 rounded-3">
                            <div class="card-body p-3 d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center">
                                <div>
                                    <h6 class="fw-bold mb-1">Emergency Location</h6>
                                    <small class="text-muted" id="locationStatusText" role="status" aria-live="polite">Optional. Location can help VITALYNX identify nearby facilities.</small>
                                </div>
                                <div>
                                    <button type="button" id="btnShareLocation" class="btn btn-outline-primary fw-bold rounded-pill px-3 py-2">
                                        Share my current location
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" id="latitude" value="">
                        <input type="hidden" name="longitude" id="longitude" value="">
                    </div>

                    <script>
                        document.getElementById('btnShareLocation').addEventListener('click', function() {
                            const btn = this;
                            const statusText = document.getElementById('locationStatusText');
                            const latInput = document.getElementById('latitude');
                            const lngInput = document.getElementById('longitude');

                            if (!navigator.geolocation) {
                                statusText.textContent = "Location is not supported by your browser.";
                                statusText.className = "text-danger small";
                                return;
                            }

                            btn.disabled = true;
                            btn.textContent = "Locating...";

                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    latInput.value = position.coords.latitude;
                                    lngInput.value = position.coords.longitude;
                                    
                                    statusText.innerHTML = "<span class='text-success fw-bold'>Location captured ✓</span>";
                                    btn.textContent = "Location Captured";
                                    btn.className = "btn btn-success fw-bold rounded-pill px-3 py-2";
                                },
                                function(error) {
                                    latInput.value = "";
                                    lngInput.value = "";
                                    btn.disabled = false;
                                    btn.textContent = "Retry Location";
                                    
                                    switch(error.code) {
                                        case error.PERMISSION_DENIED:
                                            statusText.textContent = "Location access was denied.";
                                            break;
                                        case error.POSITION_UNAVAILABLE:
                                            statusText.textContent = "Location information is unavailable.";
                                            break;
                                        case error.TIMEOUT:
                                            statusText.textContent = "The request to get user location timed out.";
                                            break;
                                        default:
                                            statusText.textContent = "An unknown error occurred.";
                                            break;
                                    }
                                    statusText.className = "text-danger small";
                                },
                                {
                                    enableHighAccuracy: true,
                                    timeout: 10000,
                                    maximumAge: 0
                                }
                            );
                        });
                    </script>
                    
                    <div class="d-grid gap-3">
                        <button type="submit" id="btnSubmitReport" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-5 shadow-sm">Start Assessment</button>
                    </div>
                </form>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportForm = document.getElementById('reportForm');
        const btnSubmit = document.getElementById('btnSubmitReport');
        if(reportForm && btnSubmit) {
            reportForm.addEventListener('submit', function() {
                // Ensure required description is filled
                const desc = document.getElementById('description').value.trim();
                if(desc.length > 0) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = 'Starting Assessment... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                }
            });
        }
    });
</script>
            </div>
        </div>
        
        <div id="emergency-sos" class="vl-card-emergency mt-4">
            <h5 class="text-danger fw-bold mb-3">EMERGENCY SOS</h5>
            <p class="small text-danger mb-4 fw-medium">Press and hold to submit an emergency request in VITALYNX.</p>
            <p class="small text-dark border border-danger rounded-3 p-3 bg-white">This records an SOS request in VITALYNX and may notify matched facilities in the app. It does not call emergency services or dispatch an ambulance. Call your local emergency number now for immediate danger.</p>
            <form id="sosForm" action="/emergency/sos" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="latitude" id="sos_latitude" value="">
                <input type="hidden" name="longitude" id="sos_longitude" value="">
                
                <button type="button" id="btnSos" class="vl-btn-sos">
                    HOLD TO<br>SEND REQUEST
                </button>
            </form>
            <div id="sosStatus" role="status" aria-live="polite" class="mt-3 fw-bold text-danger" style="min-height: 24px;"></div>
        </div>
    </div>
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
                
                btnSos.style.backgroundColor = '#1e293b'; btnSos.style.borderColor = '#334155';
                sosStatus.textContent = 'Sending your in-app emergency request...';
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
                    function(position) {
                        latInput.value = position.coords.latitude;
                        lngInput.value = position.coords.longitude;
                        sosForm.submit();
                    },
                    function() { sosForm.submit(); },
                    { timeout: 5000 }
                );
            } else {
                sosForm.submit();
            }
        }
        
        btnSos.addEventListener('mousedown', startHold);
        btnSos.addEventListener('touchstart', startHold, {passive: false});
        window.addEventListener('mouseup', cancelHold);
        window.addEventListener('touchend', cancelHold);
        btnSos.addEventListener('mouseleave', cancelHold);
        btnSos.addEventListener('touchcancel', cancelHold);
    });
</script>
