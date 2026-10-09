<div class="row justify-content-center vl-triage-page">
    <div class="col-12 col-lg-10 col-xl-9">
        <div class="vl-triage-heading d-flex align-items-center mb-3">
            <div>
                <h2 class="mb-0 fw-bold">VITALYNX AI</h2>
                <h5 class="text-muted mb-0">Guided emergency assessment</h5>
                <p class="small text-muted mb-0 mt-2">Answer what you can. This is preliminary decision support, not a diagnosis. For immediate danger, call local emergency services now.</p>
            </div>
            <span class="vl-triage-case-id ms-auto"><span>CASE</span><?= htmlspecialchars($case['case_number']) ?></span>
        </div>
        
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger shadow-sm border-0 rounded-3">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card vl-triage-chat-card shadow-sm border-0 rounded-3 mb-3 d-flex flex-column">
            <div class="vl-triage-chat-heading"><div><strong>Assessment conversation</strong><span>Your report and guidance are saved with this case.</span></div><span class="vl-triage-preliminary">Preliminary support</span></div>
            <div class="card-body p-4 overflow-auto d-flex flex-column gap-3" id="chat-container">
                <?php foreach ($messages as $msg): ?>
                    <?php if ($msg['sender'] === 'user'): ?>
                        <div class="align-self-end w-75">
                            <div class="bg-primary text-white p-3 rounded-3 rounded-3 border-bottom-0 shadow-sm">
                                <p class="mb-0 fs-5"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            </div>
                        </div>
                    <?php elseif ($msg['sender'] === 'ai'): ?>
                        <?php
                        $displayMessage = $msg['message'];
                        $legacyNotices = [
                            'OpenAI is not configured. This is a local demo prompt, not an AI clinical assessment. Contact local emergency services immediately for a life-threatening emergency. ',
                            'The AI service is temporarily unavailable. This case is saved. Please continue only if it is safe to do so; contact local emergency services immediately for a life-threatening emergency. '
                        ];
                        foreach ($legacyNotices as $legacyNotice) {
                            if (strpos($displayMessage, $legacyNotice) === 0) {
                                echo '<div class="vl-triage-notice" role="status"><strong>AI service unavailable</strong><span>' . htmlspecialchars(trim($legacyNotice)) . '</span></div>';
                                $displayMessage = substr($displayMessage, strlen($legacyNotice));
                                break;
                            }
                        }
                        ?>
                        <div class="align-self-start w-75">
                            <div class="bg-light text-dark p-3 rounded-3 rounded-3 border-bottom-0 shadow-sm border">
                                <span class="vl-assistant-label">VITALYNX GUIDANCE</span>
                                <p class="mb-0 fs-5 fw-medium ai-msg-text"><?= nl2br(htmlspecialchars($displayMessage)) ?></p>
                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-speak fw-bold px-3" aria-label="Listen to response">🔊 Listen</button>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($msg['sender'] === 'system'): ?>
                        <div class="vl-triage-notice" role="status"><strong>Service notice</strong><span><?= nl2br(htmlspecialchars($msg['message'])) ?></span></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-3">
                <div id="voiceStatus" role="status" aria-live="polite" class="small fw-bold text-muted mb-2 text-center" style="min-height: 20px;">Text chat is ready. Voice input is optional.</div>
                <form action="/emergency/triage" method="POST" id="triageForm" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($case['id']) ?>">
                    
                    <button type="button" id="btnMic" class="btn btn-outline-danger btn-lg rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;" aria-label="Start voice input" title="Voice Input">🎤</button>
                    
                    <input type="text" class="form-control form-control-lg rounded-pill px-4 bg-light flex-grow-1" id="msgInput" name="message" placeholder="Describe what you are experiencing..." aria-label="Your message" required maxlength="2000" autocomplete="off">
                    
                    <button type="submit" id="btnSend" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold flex-shrink-0">Send</button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="/dashboard" class="text-muted text-decoration-none">Return to Dashboard</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Chat Auto-Scroll ---
        var chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // --- Voice Configuration ---
        const voiceConfig = {
            language: 'en-US'
        };

        // --- Text To Speech (TTS) ---
        const synth = window.speechSynthesis;
        let currentUtterance = null;

        document.querySelectorAll('.btn-speak').forEach(btn => {
            btn.addEventListener('click', function() {
                const text = this.closest('.bg-light').querySelector('.ai-msg-text').innerText;
                
                if (synth.speaking && currentUtterance && currentUtterance.text === text) {
                    synth.cancel();
                    this.innerHTML = '🔊 Listen';
                    return;
                }
                
                document.querySelectorAll('.btn-speak').forEach(b => b.innerHTML = '🔊 Listen');
                synth.cancel();
                
                currentUtterance = new SpeechSynthesisUtterance(text);
                currentUtterance.lang = voiceConfig.language;
                
                this.innerHTML = '⏹ Stop';
                
                currentUtterance.onend = () => {
                    this.innerHTML = '🔊 Listen';
                    currentUtterance = null;
                };
                
                currentUtterance.onerror = () => {
                    this.innerHTML = '🔊 Listen';
                    currentUtterance = null;
                };
                
                synth.speak(currentUtterance);
            });
        });

        // --- Speech To Text (STT) ---
        const btnMic = document.getElementById('btnMic');
        const msgInput = document.getElementById('msgInput');
        const voiceStatus = document.getElementById('voiceStatus');
        const triageForm = document.getElementById('triageForm');
        const btnSend = document.getElementById('btnSend');

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        let isListening = false;

        if (!SpeechRecognition) {
            btnMic.disabled = true;
            btnMic.title = "Voice input unavailable on this browser";
            voiceStatus.textContent = "Text chat is ready. Voice input is not supported in this browser.";
        } else {
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = true;
            recognition.lang = voiceConfig.language;
            
            let finalTranscript = '';
            
            btnMic.addEventListener('click', () => {
                if (isListening) {
                    recognition.stop();
                    return;
                }
                
                try {
                    finalTranscript = msgInput.value ? msgInput.value + ' ' : '';
                    recognition.start();
                } catch (e) {
                    voiceStatus.textContent = "Could not start voice input. You can type your message instead.";
                }
            });
            
            recognition.onstart = () => {
                isListening = true;
                btnMic.classList.remove('btn-outline-danger');
                btnMic.classList.add('btn-danger');
                btnMic.innerHTML = '🛑';
                btnMic.setAttribute('aria-label', 'Stop voice input');
                voiceStatus.textContent = "Listening...";
                voiceStatus.classList.add('text-danger');
            };
            
            recognition.onresult = (event) => {
                let interimTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript + ' ';
                        voiceStatus.textContent = "Transcribing...";
                    } else {
                        interimTranscript += transcript;
                    }
                }
                
                msgInput.value = finalTranscript + interimTranscript;
            };
            
            recognition.onerror = (event) => {
                if (event.error === 'not-allowed') {
                    voiceStatus.textContent = "Microphone permission was denied. You can type your message instead.";
                } else if (event.error === 'no-speech') {
                    voiceStatus.textContent = "No speech detected. Tap mic to try again.";
                } else {
                    voiceStatus.textContent = "Voice input could not continue. You can type your message instead.";
                }
                voiceStatus.classList.remove('text-danger');
            };
            
            recognition.onend = () => {
                isListening = false;
                btnMic.classList.add('btn-outline-danger');
                btnMic.classList.remove('btn-danger');
                btnMic.innerHTML = '🎤';
                btnMic.setAttribute('aria-label', 'Start voice input');
                
                if (voiceStatus.textContent === "Listening..." || voiceStatus.textContent === "Transcribing...") {
                    voiceStatus.textContent = "Text chat is ready. Voice input is optional.";
                }
                voiceStatus.classList.remove('text-danger');
                
                msgInput.value = msgInput.value.trim();
            };
        }

        // --- Prevent duplicate submissions ---
        if (triageForm) {
            triageForm.addEventListener('submit', function() {
                if (isListening && recognition) {
                    recognition.stop();
                }
                btnSend.disabled = true;
                btnSend.innerHTML = 'Sending...';
                msgInput.readOnly = true;
            });
        }
    });
</script>
