<?php
header('Content-Type: application/json');

// 1. Verify Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// 2. Check if file was uploaded properly
if (!isset($_FILES['audio_data']) || $_FILES['audio_data']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Audio file upload failed or missing.']);
    exit;
}

$uploadedFile = $_FILES['audio_data'];
$selectedLanguage = $_POST['source_lang'] ?? 'zul_Latn';

// 3. Define upload storage path
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Ensure unique filename
$fileName = 'voice_' . time() . '_' . uniqid() . '.webm';
$filePath = $uploadDir . $fileName;

// 4. Move file to upload directory
if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
    echo json_encode(['success' => false, 'error' => 'Could not save uploaded audio file.']);
    exit;
}

// --- OPTIONAL: Forward file to Lelapa AI VulaBula or OpenAI Whisper API ---
/*
$apiKey = 'YOUR_LELAPA_OR_OPENAI_API_KEY';
$ch = curl_init('https://api.lelapa.ai/v1/stt/process'); // Example endpoint

$cFile = new CURLFile($filePath, 'audio/webm', $fileName);
$data = [
    'file' => $cFile,
    'language' => $selectedLanguage
];

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-CLIENT-TOKEN: ' . $apiKey,
    'Content-Type: multipart/form-data'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
$resultData = json_decode($response, true);
*/

// 5. Simulated Success Response
echo json_encode([
    'success' => true,
    'message' => 'Audio received successfully! Saved as <code>' . htmlspecialchars($fileName) . '</code>.<br>' .
                 'Language selected: <strong>' . htmlspecialchars($selectedLanguage) . '</strong>'
]);