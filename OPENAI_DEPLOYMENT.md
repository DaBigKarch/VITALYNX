# OpenAI triage deployment

VITALYNX keeps its existing `/emergency/triage` conversation, `ai_messages` history, `ai_assessments` records, and case workflow. When `AI_MODE=openai` and an API key is configured, `AIService` sends the bounded recent conversation to the OpenAI Responses API with a strict JSON Schema response format. The response is validated in PHP before it can be persisted. API/network/format errors use the existing local fallback. Deterministic critical symptom rules bypass the API and immediately produce a critical assessment.

## Server settings

Configure these in the server-side `.env` beside `index.php` (or the hosting provider's private environment configuration). Keep the file out of public access and do not put secrets in JavaScript or in a distributable ZIP.

```ini
AI_MODE=openai
OPENAI_API_KEY=your_secret_key
OPENAI_MODEL=gpt-4o-mini
```

`OPENAI_BASE_URL` is optional and defaults to `https://api.openai.com/v1`. Legacy `AI_API_KEY` and `AI_MODEL` names remain accepted. `AI_MODE=mock` explicitly selects the local fallback. If no API key is configured, VITALYNX also uses that fallback.

The host must permit outbound HTTPS requests using PHP cURL or HTTPS URL streams (`allow_url_fopen`) and valid TLS certificates. cURL uses a five-second connection timeout and a 20-second total timeout; the stream compatibility path applies a 20-second timeout. No Composer package, worker, shell function, or database migration is required.

## Safety and data handling

- The key is read from server-side configuration and sent only in the server-to-server Authorization header.
- Only the initial emergency description (up to 5,000 characters) and up to 12 recent user/assistant turns (each capped at 2,000 characters) are sent. Account records, profile fields, location, and unrelated records are not added. Patient-entered text is sent as needed for triage and may itself contain identifying details; ask patients to omit names and contact details when they are not needed.
- OpenAI request storage is disabled (`store: false`). Avoid entering unnecessary identifying details in the emergency description.
- Model output remains advisory. It is schema-checked and validated for required fields, urgency, confidence range, and data types. It cannot resolve a case.
- Critical rules are checked locally before any network request and cannot be downgraded. Critical recommendations tell the patient to contact local emergency services; VITALYNX does not dispatch them.
- Missing keys, HTTP errors (including authentication and rate limits), timeouts, malformed output, and unavailable cURL fall back safely. Logs contain only a generic error category, never a key, provider body, or conversation.

## Verification

Run `php tests/test_ai_service.php` from this project directory. It uses injected mock HTTP responses and makes no live API call. A successful mock test does not verify the production key, account access, model availability, or InfinityFree outbound network access.

To enable live use, set a valid key and `AI_MODE=openai` in the server's existing private `.env`, then submit a synthetic report and confirm its saved assessment is visible to the authorized patient and routed hospital staff. Never use real patient data for this check.
