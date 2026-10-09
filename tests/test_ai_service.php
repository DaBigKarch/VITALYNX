<?php
require_once __DIR__ . '/../app/Services/AIService.php';

use App\Services\AIService;

$checks = 0;
function checkResult($condition, $label) {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $checks++;
}
function apiResponse($data, $status = 200) {
    return ['status' => $status, 'body' => json_encode([
        'status' => 'completed',
        'output' => [['content' => [['type' => 'output_text', 'text' => json_encode($data)]]]]
    ])];
}
function sampleData($type = 'completion', $urgency = 'MODERATE') {
    return [
        'type' => $type,
        'message' => $type === 'message' ? 'When did the symptoms begin?' : '',
        'urgency' => $urgency,
        'confidence' => 0.72,
        'summary' => $type === 'completion' ? 'Patient reports a headache.' : '',
        'red_flags' => [],
        'recommended_action' => $type === 'completion' ? 'Seek clinical review.' : '',
        'needs_human_review' => true
    ];
}

$requestObserved = null;
$success = new AIService(['api_key' => 'test-key', 'mode' => 'openai', 'model' => 'test-model'], function ($url, $json, $key) use (&$requestObserved) {
    $requestObserved = ['url' => $url, 'payload' => json_decode($json, true), 'key' => $key];
    return apiResponse(sampleData());
});
$result = $success->processConversation('A synthetic headache report.', []);
checkResult($result['type'] === 'completion' && $result['data']['urgency'] === 'MODERATE', 'valid structured completion');
checkResult($requestObserved['url'] === 'https://api.openai.com/v1/responses', 'Responses API endpoint');
checkResult($requestObserved['payload']['model'] === 'test-model' && isset($requestObserved['payload']['text']['format']['schema']), 'configurable model and strict schema sent');
checkResult($requestObserved['payload']['store'] === false, 'provider storage disabled');

$boundedPayload = null;
$history = [];
for ($i = 0; $i < 20; $i++) $history[] = ['sender' => $i % 2 ? 'ai' : 'user', 'message' => str_repeat('x', 2500)];
$bounded = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function ($url, $json) use (&$boundedPayload) {
    $boundedPayload = json_decode($json, true);
    return apiResponse(sampleData('message'));
});
$bounded->processConversation('Synthetic report.', $history);
checkResult(count($boundedPayload['input']) === 13, 'history is limited to 12 turns plus the initial report');
$boundedTurnLengths = array_map(function ($turn) { return strlen($turn['content']); }, array_slice($boundedPayload['input'], 1));
checkResult(max($boundedTurnLengths) === 2000, 'each history turn is capped at 2,000 characters');

$messageService = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () { return apiResponse(sampleData('message')); });
$messageResult = $messageService->processConversation('Synthetic symptom report.', []);
checkResult($messageResult['type'] === 'message' && strpos($messageResult['data'], 'When did') === 0, 'valid conversational response');

$criticalCalls = 0;
$critical = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () use (&$criticalCalls) { $criticalCalls++; return apiResponse(sampleData()); });
$criticalResult = $critical->processConversation('Patient is not breathing.', []);
checkResult($criticalCalls === 0, 'critical rule returns without waiting for OpenAI');
checkResult($criticalResult['type'] === 'completion' && $criticalResult['data']['urgency'] === 'CRITICAL', 'critical deterministic override');
checkResult(strpos($criticalResult['data']['recommended_action'], 'has not dispatched') !== false, 'no false dispatch claim');
$bloodCritical = $critical->processConversation('I am coughing blood and my heart is beating very fast.', []);
checkResult($bloodCritical['type'] === 'completion' && $bloodCritical['data']['urgency'] === 'CRITICAL', 'coughing blood triggers deterministic emergency guidance');

$invalid = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () { return apiResponse(sampleData('completion', 'UNSUPPORTED')); });
$invalidResult = $invalid->processConversation('Synthetic mild symptom.', []);
checkResult(!empty($invalidResult['fallback']), 'unsupported urgency rejected and falls back');

$missingKey = new AIService(['mode' => 'openai'], function () { throw new RuntimeException('must not call'); });
$missingResult = $missingKey->processConversation('Synthetic mild symptom.', []);
checkResult(!empty($missingResult['fallback']), 'missing key uses safe fallback');

$localFallback = new AIService(['mode' => 'mock']);
$localResult = $localFallback->processConversation('Synthetic headache report.', []);
checkResult($localResult['type'] === 'message' && !empty($localResult['fallback']) && strpos($localResult['notice'], 'not an AI clinical assessment') !== false && strpos($localResult['data'], 'When did') !== false && strpos($localResult['data'], 'not an AI clinical assessment') === false, 'explicit mock mode notice is separated from the fallback question');

foreach ([401, 429, 503] as $status) {
    $service = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () use ($status) { return apiResponse([], $status); });
    $failureResult = $service->processConversation('Synthetic mild symptom.', []);
    checkResult(!empty($failureResult['fallback']), 'HTTP ' . $status . ' uses fallback');
}

$badJson = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () { return ['status' => 200, 'body' => '{bad']; });
$badJsonResult = $badJson->processConversation('Synthetic mild symptom.', []);
checkResult(!empty($badJsonResult['fallback']), 'malformed API JSON uses fallback');

$timeout = new AIService(['api_key' => 'test-key', 'mode' => 'openai'], function () { throw new RuntimeException('timeout'); });
$timeoutResult = $timeout->processConversation('Synthetic mild symptom.', []);
checkResult(!empty($timeoutResult['fallback']), 'network failure uses fallback');

echo 'AIService mock tests passed: ' . $checks . " checks\n";
