<div class="row justify-content-center align-items-stretch vl-auth-layout">
    <aside class="col-lg-5 d-none d-lg-flex vl-auth-aside">
        <div class="vl-auth-aside-content">
            <span class="vl-auth-symbol" aria-hidden="true">+</span>
            <p class="vl-eyebrow">VITALYNX / Patient account</p>
            <h2>Keep your emergency case information together.</h2>
            <p>Create an account to report an emergency, follow case updates, and communicate with the care team through the platform.</p>
            <div class="vl-auth-aside-foot"><span class="vl-auth-dot"></span> Your account starts with patient access</div>
        </div>
    </aside>
    <div class="col-md-8 col-lg-5 vl-auth">
        <div class="card shadow-sm">
            <div class="card-header vl-auth-header">
                <p class="vl-eyebrow mb-2">VITALYNX / Get started</p><h1 class="h3 mb-1">Create your account</h1><p class="small mb-0">Start with your contact details below.</p>
            </div>
            <div class="card-body">
                <form action="/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" autocomplete="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" autocomplete="tel" inputmode="tel">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <p class="text-center small text-muted mt-4 mb-0">Already registered? <a href="/login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>
