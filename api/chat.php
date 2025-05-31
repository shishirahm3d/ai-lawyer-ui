<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log the request for debugging
error_log("Chat API called with method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
error_log("Input received: " . print_r($input, true));

if (!$input || !isset($input['messages'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input - messages not provided']);
    exit;
}

$messages = $input['messages'];
$parameters = $input['parameters'] ?? [
    'temperature' => 0.8,
    'top_k' => 40,
    'repeat_penalty' => 1.1,
    'min_p_sampling' => 0.05,
    'top_p' => 0.95
];

// Update chat.php to use the settings from the request

// Replace the Ollama API configuration section with:
// Get model and API URL from request or use defaults
$model = $input['model'] ?? "hf.co/shishirahm3d/ai-lawyer-bd-1-8b-instruct-bnb-4bit-GGUF:Q4_K_M";
$ollama_url = $input['apiUrl'] ?? "https://6042-35-233-247-35.ngrok-free.app/api/chat";

error_log("Using model: " . $model);
error_log("Using API URL: " . $ollama_url);

// Prepare the request data
$requestData = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => $parameters['temperature'],
    'top_k' => $parameters['top_k'],
    'repeat_penalty' => $parameters['repeat_penalty'],
    'min_p_sampling' => $parameters['min_p_sampling'],
    'top_p' => $parameters['top_p'],
    'stream' => true
];

error_log("Sending request to Ollama: " . $ollama_url);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ollama_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'ngrok-skip-browser-warning: true'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

error_log("cURL response code: " . $httpCode);
error_log("cURL error: " . $error);

if ($error) {
    echo json_encode(['success' => false, 'error' => 'cURL error: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Ollama API HTTP error: ' . $httpCode]);
    exit;
}

// Process the streaming response
$lines = explode("\n", $response);
$fullResponse = '';

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    $data = json_decode($line, true);
    if ($data && isset($data['message']['content'])) {
        $fullResponse .= $data['message']['content'];
    }
    
    if ($data && isset($data['done']) && $data['done']) {
        break;
    }
}

if (empty($fullResponse)) {
    echo json_encode(['success' => false, 'error' => 'No response received from AI model']);
    exit;
}

echo json_encode([
    'success' => true,
    'response' => $fullResponse
]);
?>
