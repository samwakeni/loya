<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice Prompt Recorder</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4 text-center">
                <h4 class="mb-3">Ask in Your Language</h4>
                
                <!-- Status Message -->
                <p id="status-text" class="text-muted small">Click start to record your vocal prompt.</p>

                <!-- Recording Controls -->
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <button id="btn-start" class="btn btn-danger">
                        🎤 Start Recording
                    </button>
                    <button id="btn-stop" class="btn btn-secondary" disabled>
                        ⏹️ Stop & Send
                    </button>
                </div>

                <!-- Language Selection -->
                <div class="mb-3 text-start">
                    <label for="language-select" class="form-label small fw-bold">Spoken Language:</label>
                    <select id="language-select" class="form-select form-select-sm">
                        <option value="zul_Latn">isiZulu</option>
                        <option value="xho_Latn">isiXhosa</option>
                        <option value="sot_Latn">Sesotho</option>
                        <option value="afr_Latn">Afrikaans</option>
                        <option value="eng_Latn">English (SA)</option>
                    </select>
                </div>

                <!-- Spinner -->
                <div id="loading-spinner" class="spinner-border text-primary mx-auto my-2 d-none" role="status">
                    <span class="visually-hidden">Processing...</span>
                </div>

                <!-- Output Area -->
                <div id="response-box" class="alert alert-info d-none text-start mt-3">
                    <strong>Transcript / Result:</strong>
                    <div id="response-text" class="mt-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
let mediaRecorder;
let audioChunks = [];

$('#btn-start').on('click', async function () {
    audioChunks = []; // Reset recorded chunks
    
    try {
        // Request microphone access
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);

        // Capture data as audio records
        mediaRecorder.ondataavailable = event => {
            if (event.data.size > 0) {
                audioChunks.push(event.data);
            }
        };

        // UI state changes
        mediaRecorder.start();
        $('#status-text').text("Recording... Speak now.").addClass("text-danger fw-bold");
        $('#btn-start').prop('disabled', true);
        $('#btn-stop').prop('disabled', false);

    } catch (err) {
        alert("Microphone permission denied or not supported by browser.");
        console.error(err);
    }
});

$('#btn-stop').on('click', function () {
    if (!mediaRecorder) return;

    mediaRecorder.stop();
    $('#status-text').text("Processing audio...").removeClass("text-danger fw-bold");
    $('#btn-start').prop('disabled', false);
    $('#btn-stop').prop('disabled', true);

    // Trigger upload once recording stream closes
    mediaRecorder.onstop = function () {
        // Create an audio Blob (webm/wav format depending on browser)
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });

        // Prepare FormData payload
        const formData = new FormData();
        formData.append('audio_data', audioBlob, 'voice_prompt.webm');
        formData.append('source_lang', $('#language-select').val());

        // Show spinner
        $('#loading-spinner').removeClass('d-none');
        $('#response-box').addClass('d-none');

        // Execute jQuery AJAX Request
        $.ajax({
            url: 'voice.php',
            type: 'POST',
            data: formData,
            contentType: false, // Required for binary multipart upload
            processData: false, // Prevents jQuery from converting FormData to string
            success: function (response) {
                $('#loading-spinner').addClass('d-none');
                $('#response-box').removeClass('d-none');

                if (response.success) {
                    $('#response-text').html(response.message);
                } else {
                    $('#response-text').html('<span class="text-danger">Error: ' + response.error + '</span>');
                }
            },
            error: function (xhr, status, error) {
                $('#loading-spinner').addClass('d-none');
                $('#response-box').removeClass('d-none');
                $('#response-text').html('<span class="text-danger">Server connection error.</span>');
            }
        });
    };
});
</script>
</body>
</html>