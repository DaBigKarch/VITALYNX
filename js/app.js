// Shared presentation enhancements. No requests, authorization, or form interception.
document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar-nav a[href], .nav-pills a[href]').forEach(function (link) {
        if (new URL(link.href, window.location.origin).pathname === currentPath) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }
    });

    document.querySelectorAll('.table-responsive').forEach(function (region) {
        const hint = document.createElement('p');
        hint.className = 'table-scroll-hint';
        hint.textContent = 'Swipe horizontally to see all columns and actions.';
        region.before(hint);
    });

    // Existing clinical forms sometimes use nearby text without a bound label.
    // Add accessible naming while preserving field names, values and submission.
    document.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(function (field) {
        if (field.hasAttribute('aria-label') || field.hasAttribute('aria-labelledby') || field.labels.length) return;
        const group = field.closest('.mb-3, .mb-4, .d-flex');
        const label = group && group.querySelector('.form-label, .vx-label, label');
        const name = label ? label.textContent.trim() : (field.getAttribute('placeholder') || field.name.replace(/_/g, ' '));
        if (name) field.setAttribute('aria-label', name);
    });
});
