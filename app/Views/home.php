<section class="vl-landing-hero">
    <div class="vl-landing-copy">
        <div class="vl-landing-kicker"><span class="vl-kicker-mark" aria-hidden="true"></span> EMERGENCY CASE COORDINATION</div>
        <h1>Emergency information, organized for the people responding.</h1>
        <p class="vl-landing-lead">VITALYNX guides patients through a structured report, keeps assessment details with the case, and gives authorized care teams a shared place to review updates.</p>
        <div class="vl-landing-actions">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register" class="btn vl-btn-landing-primary">Start a patient report <span aria-hidden="true">→</span></a>
                <a href="/login" class="vl-btn-landing-secondary">Already registered? Sign in</a>
            <?php else: ?>
                <a href="/dashboard" class="btn vl-btn-landing-primary">Open your dashboard <span aria-hidden="true">→</span></a>
            <?php endif; ?>
        </div>
        <div class="vl-landing-safety"><span aria-hidden="true">!</span><p><strong>For immediate danger, contact local emergency services.</strong><br>VITALYNX does not dispatch ambulances. AI support is preliminary and does not replace clinical judgment.</p></div>
    </div>

    <div class="vl-landing-visual" aria-label="VITALYNX emergency case workflow">
        <div class="vl-visual-topline"><span>ONE CONNECTED WORKFLOW</span><span class="vl-visual-status"><i></i> CARE COORDINATION</span></div>
        <div class="vl-visual-path">
            <div class="vl-visual-rail" aria-hidden="true"><span></span><span></span><span></span></div>
            <article class="vl-visual-step">
                <span class="vl-visual-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s-7-4.4-7-11a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 6.6-7 11-7 11Z"/><path d="M5 12h4l2-3 2 6 2-3h4"/></svg></span>
                <div><small>01 / PATIENT</small><h2>Emergency report</h2><p>Symptoms and location, when available.</p></div>
                <span class="vl-step-check" aria-hidden="true">01</span>
            </article>
            <article class="vl-visual-step">
                <span class="vl-visual-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 4.5 6v5.5c0 4.5 3.2 7.7 7.5 9.5 4.3-1.8 7.5-5 7.5-9.5V6L12 3Z"/><path d="M8 12h2l1.3-2.5L13 15l1.3-3H16"/></svg></span>
                <div><small>02 / TRIAGE SUPPORT</small><h2>Guided assessment</h2><p>Relevant details are gathered for staff review.</p></div>
                <span class="vl-step-check" aria-hidden="true">02</span>
            </article>
            <article class="vl-visual-step">
                <span class="vl-visual-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M8 10h1m6 0h1m-8 3h1m6 0h1"/></svg></span>
                <div><small>03 / CARE TEAM</small><h2>Facility review</h2><p>Authorized staff review and coordinate next steps.</p></div>
                <span class="vl-step-check" aria-hidden="true">03</span>
            </article>
        </div>
        <div class="vl-visual-footer"><span class="vl-footer-line"></span><span>Clinical decisions stay with qualified staff.</span></div>
    </div>
</section>

<section id="how-it-works" class="vl-landing-capabilities" aria-labelledby="landing-capabilities-title">
    <div class="vl-capabilities-heading">
        <div><span class="vl-section-kicker">ONE WORKSPACE, THREE HANDOFFS</span><h2 id="landing-capabilities-title">The important details stay with the case.</h2></div>
        <p>Patients, facility staff, and administrators each get tools for their part of the coordination process.</p>
    </div>
    <div class="vl-capability-grid">
        <article class="vl-capability-card"><span class="vl-capability-index">01</span><div><h3>Report clearly</h3><p>Patients can describe an emergency and answer guided triage questions.</p></div><span class="vl-capability-arrow" aria-hidden="true">↗</span></article>
        <article class="vl-capability-card"><span class="vl-capability-index">02</span><div><h3>Review together</h3><p>Routed cases, clinical notes, and staff assignments stay linked to the case.</p></div><span class="vl-capability-arrow" aria-hidden="true">↗</span></article>
        <article class="vl-capability-card"><span class="vl-capability-index">03</span><div><h3>Keep people informed</h3><p>Patients can follow progress and read messages from their care team.</p></div><span class="vl-capability-arrow" aria-hidden="true">↗</span></article>
    </div>
</section>

<footer class="vl-landing-emergency"><span class="vl-emergency-icon" aria-hidden="true">!</span><p><strong>Medical emergency?</strong> Call your local emergency number now. VITALYNX is a case coordination platform and cannot send emergency services.</p></footer>
