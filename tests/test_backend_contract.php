<?php
$root = dirname(__DIR__);
$schema = file_get_contents($root . '/database/schema.sql');
$hospitalView = file_get_contents($root . '/app/Views/hospital/dashboard.php');
$hospitalController = file_get_contents($root . '/app/Controllers/Web/HospitalController.php');
$patientView = file_get_contents($root . '/app/Views/patient/case_view.php');
$patientController = file_get_contents($root . '/app/Controllers/Web/PatientController.php');
$patientCaseView = file_get_contents($root . '/app/Views/patient/case_view.php');
$analytics = file_get_contents($root . '/app/Models/AnalyticsModel.php');
$caseEvent = file_get_contents($root . '/app/Models/CaseEvent.php');
$hospitalModel = file_get_contents($root . '/app/Models/CaseHospital.php');
$layout = file_get_contents($root . '/app/Views/layouts/main.php');
$style = file_get_contents($root . '/css/style.css');
$hospitalCaseView = file_get_contents($root . '/app/Views/hospital/case_review.php');
$adminCaseView = file_get_contents($root . '/app/Views/admin/case_view.php');

$checks = 0;
function assertContract($condition, $description) {
    global $checks;
    if (!$condition) throw new RuntimeException('Contract failed: ' . $description);
    $checks++;
}

$requiredTables = ['hospitals', 'users', 'emergency_cases', 'ai_assessments', 'ai_messages', 'case_messages', 'case_hospitals', 'case_events', 'notifications', 'care_updates', 'clinical_notes', 'case_followups', 'admin_audit_logs', 'api_tokens'];
preg_match_all('/CREATE TABLE IF NOT EXISTS ([a-z_]+) \((.*?)\) ENGINE=/si', $schema, $tableMatches, PREG_SET_ORDER);
$tables = [];
foreach ($tableMatches as $match) $tables[$match[1]] = $match[2];
foreach ($requiredTables as $table) assertContract(isset($tables[$table]), 'schema defines ' . $table);

foreach (['users' => ['staff_role', 'hospital_id'], 'emergency_cases' => ['resolved_at', 'resolved_by', 'resolution_outcome', 'resolution_summary'], 'case_hospitals' => ['assigned_to', 'requires_doctor_review'], 'case_events' => ['event']] as $table => $columns) {
    foreach ($columns as $column) assertContract(preg_match('/^\s*' . preg_quote($column, '/') . '\s/m', $tables[$table]) === 1, $table . '.' . $column . ' exists');
}
assertContract(strpos($tables['emergency_cases'], "'ASSIGNED'") !== false, 'case state supports assigned workflow');
assertContract(strpos($tables['case_hospitals'], "'MATCHED'") !== false && strpos($tables['case_hospitals'], 'UNIQUE KEY uq_case_hospital') !== false, 'hospital routing statuses and upsert key agree');
assertContract(strpos($analytics, 'event_type') === false && strpos($analytics, 'case_events WHERE event =') !== false, 'analytics uses the persisted event column');
assertContract(strpos($caseEvent, 'case_events (case_id, event, actor)') !== false, 'event model persists the schema event field');

assertContract(strpos($hospitalController, 'getTriagedCases()') === false && strpos($hospitalView, 'Available Triaged Cases Queue') === false, 'hospital dashboard does not display globally triaged cases');
assertContract(strpos($hospitalController, 'isClinicalStaffForHospital($assigneeId, $_SESSION[\'hospital_id\'])') !== false, 'assignment handler validates facility staff');
assertContract(strpos($hospitalController, 'getDashboardStatsForHospital($_SESSION[\'hospital_id\'])') !== false && strpos($hospitalModel, 'WHERE ch.hospital_id = ?') !== false, 'hospital dashboard stats are scoped to routed facility cases');
assertContract(strpos($patientView, 'name="needs_coordination"') !== false && strpos($patientController, "INPUT_POST, 'needs_coordination'") !== false, 'follow-up coordination field matches');
assertContract(strpos($patientView, 'name="feedback_notes"') !== false && strpos($patientController, "\$_POST['feedback_notes']") !== false, 'follow-up comments field matches');
assertContract(strpos($patientController, 'notifyHospital(') !== false && strpos($patientController, 'createForHospital(') === false, 'follow-up uses the available notification method');
assertContract(strpos($patientCaseView, "\$assessment['urgency'] ?? 'LOW'") === false && strpos($patientCaseView, 'Continue triage conversation') !== false, 'pending patient assessment is not mislabeled LOW and offers a continuation link');
assertContract(strpos($layout, "\$role === 'patient'") !== false && strpos($layout, "\$role === 'hospital'") !== false && strpos($layout, "\$role === 'admin'") !== false, 'shared navigation adapts to each authenticated role');
assertContract(strpos($style, 'prefers-reduced-motion') !== false && strpos($style, ':focus-visible') !== false, 'refreshed UI includes reduced-motion and keyboard focus treatments');
assertContract(strpos($hospitalCaseView, 'AI Confidence') === false && strpos($hospitalCaseView, 'HUMAN REVIEW REQUIRED') !== false, 'hospital review does not foreground AI confidence and retains human review');
assertContract(strpos($adminCaseView, 'confidence_score') === false && strpos($adminCaseView, 'Clinical review required') !== false, 'admin case view avoids an overconfident score and states review requirement');
assertContract(strpos($hospitalView, 'after patient triage is completed') !== false && strpos($hospitalView, 'You\'re up to date') !== false, 'empty hospital queue explains when cases appear');

echo 'Backend contract checks passed: ' . $checks . " assertions\n";
