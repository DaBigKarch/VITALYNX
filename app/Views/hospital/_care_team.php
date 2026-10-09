<div class="vx-card mb-4">
    <div class="vx-card-header">Care Team</div>
    <div class="vx-card-body">
        <?php
            $isAssigned = false;
            $assigneeName = 'Unassigned';
            if (!empty($isRouted) && !empty($isRouted['assigned_to'])) {
                $isAssigned = true;
                foreach ($hospitalStaff as $staff) {
                    if ((int)$staff['id'] === (int)$isRouted['assigned_to']) {
                        $assigneeName = $staff['name'] . ' (' . ucfirst($staff['staff_role'] ?? 'Staff') . ')';
                        break;
                    }
                }
            }
        ?>
        <div class="mb-4">
            <span class="vx-label">Primary Handler</span>
            <span class="vx-value"><?= htmlspecialchars($assigneeName) ?></span>
        </div>

        <?php if (!$isAssigned && isset($_SESSION['user_id'])): ?>
            <form action="/hospital/case/assign" method="POST" class="mb-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                <div class="d-flex flex-column gap-2">
                    <select name="assignee_id" class="vx-input bg-light" required aria-label="Assign case to clinical staff">
                        <option value="">Select doctor or nurse...</option>
                        <?php foreach ($hospitalStaff as $staff): ?>
                            <option value="<?= htmlspecialchars($staff['id']) ?>"><?= htmlspecialchars($staff['name']) ?> (<?= htmlspecialchars(ucfirst($staff['staff_role'] ?? 'Staff')) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="vx-action-btn vx-btn-primary m-0 py-2">Assign</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($case['status'] === 'ASSIGNED' && $isAssigned && isset($_SESSION['staff_role']) && in_array($_SESSION['staff_role'], ['nurse', 'doctor'], true)): ?>
            <div class="pt-3 border-top">
                <form action="/hospital/case/start-care" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                    <button type="submit" class="vx-action-btn vx-btn-primary">Start Active Care</button>
                </form>
            </div>
        <?php elseif ($case['status'] === 'IN_CARE'): ?>
            <div class="pt-3 border-top">
                <div class="vx-alert vx-alert-success justify-content-center mb-0 py-2">Active Care in Progress</div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'nurse' && empty($isRouted['requires_doctor_review'])): ?>
            <div class="mt-3 pt-3 border-top">
                <form action="/hospital/case/escalate" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                    <button type="submit" class="vx-action-btn vx-btn-danger m-0 py-2">Request Doctor Review</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($isRouted['requires_doctor_review'])): ?>
            <div class="mt-3 pt-3 border-top">
                <div class="vx-alert vx-alert-warning justify-content-center mb-0 py-2">Doctor Review Requested</div>
            </div>
        <?php endif; ?>
    </div>
</div>
