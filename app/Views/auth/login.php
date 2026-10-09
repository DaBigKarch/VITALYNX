<div class="row justify-content-center align-items-stretch vl-auth-layout">
    <aside class="col-lg-5 d-none d-lg-flex vl-auth-aside">
        <div class="vl-auth-aside-content">
            <span class="vl-auth-symbol" aria-hidden="true">+</span>
            <p class="vl-eyebrow">VITALYNX / Care coordination</p>
            <h2>Stay connected to your case.</h2>
            <p>Sign in to review case status, receive care-team updates, and continue your conversation.</p>
            <div class="vl-auth-aside-foot"><span class="vl-auth-dot"></span> Secure access for patients and care teams</div>
        </div>
    </aside>
    <div class="col-md-8 col-lg-5 vl-auth">
        <div class="card shadow-sm">
            <div class="card-header vl-auth-header">
                <p class="vl-eyebrow mb-2">VITALYNX / Welcome back</p><h1 class="h3 mb-1">Sign in to your account</h1><p class="small mb-0">Access your dashboard and case updates.</p>
            </div>
            <div class="card-body">
                <form action="/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-center small text-muted mt-4 mb-0">New to VITALYNX? <a href="/register">Create an account</a></p>
            </div>
        </div>
    </div>
</div>
