<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'VITALYNX') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=20261009-ui6">
</head>
<body class="<?= isset($_SESSION['user_id']) ? 'vl-authenticated' : 'vl-public' ?>">
    <a class="vl-skip-link" href="#main-content">Skip to main content</a>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark vl-topbar">
        <div class="container-fluid vl-topbar-inner">
            <a class="navbar-brand d-inline-flex align-items-center gap-2" href="/"><span class="vl-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 4v16M4 12h16"/></svg></span><span>VITALYNX</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav vl-primary-nav me-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php $role = $_SESSION['user_role'] ?? ''; $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
                        <?php
                        $workspaceLinks = $role === 'admin'
                                ? [['Platform', '/admin/dashboard'], ['Cases', '/admin/cases'], ['Hospitals', '/admin/hospitals'], ['Users', '/admin/users'], ['Analytics', '/admin/analytics'], ['Events', '/admin/events']]
                                : ($role === 'hospital'
                                    ? [['Case queue', '/hospital/dashboard'], ['History', '/hospital/history']]
                                    : [['Overview', '/dashboard'], ['Report emergency', '/emergency/report'], ['Case history', '/patient/history']]);
                            $workspaceIconPaths = [
                                '/dashboard' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
                                '/emergency/report' => 'M3 12h4l2-7 4 14 3-7h5',
                                '/patient/history' => 'M4 5v5h5M5 10a8 8 0 1 1-1 4',
                                '/hospital/dashboard' => 'M3 21h18M5 21V7l7-4 7 4v14M9 11h1m4 0h1m-5 4h1m4 0h1',
                                '/hospital/history' => 'M4 5h16v15H4zM8 3v4m8-4v4M7 11h10m-10 4h7',
                                '/admin/dashboard' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
                                '/admin/cases' => 'M6 4h12v17H6zM9 4V2h6v2M9 9h6m-6 4h6m-6 4h4',
                                '/admin/hospitals' => 'M3 21h18M5 21V7l7-4 7 4v14M9 11h1m4 0h1m-5 4h1m4 0h1',
                                '/admin/users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8m7-7a4 4 0 0 1 0 8m2 3h2a3 3 0 0 1 3 3v1',
                                '/admin/analytics' => 'M3 3v18h18M8 16l4-5 4 3 5-7',
                                '/admin/events' => 'M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'
                            ];
                        ?>
                        <?php foreach ($workspaceLinks as $navItem): ?>
                            <?php
                                $navActive = $currentPath === $navItem[1] || strpos($currentPath, $navItem[1] . '/') === 0;
                                if ($role === 'admin' && $navItem[1] === '/admin/cases' && strpos($currentPath, '/admin/case') === 0) $navActive = true;
                                if ($role === 'hospital' && $navItem[1] === '/hospital/dashboard' && strpos($currentPath, '/hospital/case') === 0) $navActive = true;
                                if ($role === 'patient' && $navItem[1] === '/patient/history' && strpos($currentPath, '/patient/case') === 0) $navActive = true;
                                if ($role === 'patient' && $navItem[1] === '/emergency/report' && in_array($currentPath, ['/emergency/triage', '/emergency/sos', '/emergency/sos/result'], true)) $navActive = true;
                            ?>
                            <li class="nav-item"><a class="nav-link <?= $navActive ? 'active' : '' ?>" href="<?= htmlspecialchars($navItem[1]) ?>" <?= $navActive ? 'aria-current="page"' : '' ?>><span class="vl-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="<?= htmlspecialchars($workspaceIconPaths[$navItem[1]] ?? 'M4 4h16v16H4z') ?>"/></svg></span><span><?= htmlspecialchars($navItem[0]) ?></span></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/#how-it-works">How it works</a></li>
                    <?php endif; ?>
                </ul>
                <div class="vl-account-actions">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="vl-account-name"><span class="vl-account-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1))) ?></span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?></span>
                        <a class="vl-signout" href="/logout">Sign out</a>
                    <?php else: ?>
                        <a class="vl-login-link" href="/login">Sign in</a>
                        <a class="vl-register-link" href="/register">Create account <span aria-hidden="true">→</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main id="main-content" class="container vl-main" tabindex="-1">
        <?php if(isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php require_once __DIR__ . '/../' . $view . '.php'; ?>
    </main>
    
    <footer class="vl-footer py-4 text-center">
        <div class="container">
            <span class="text-muted">VITALYNX &copy; <?= date('Y') ?> - AI-Assisted Emergency Response</span>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/app.js?v=20261009"></script>
</body>
</html>
