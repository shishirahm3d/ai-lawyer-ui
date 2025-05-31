<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$settingsFile = '../settings.json';

// Handle GET request - load settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        echo json_encode(['success' => true, 'settings' => $settings]);
    } else {
        // Default settings
        $defaultSettings = [
            'model' => 'hf.co/shishirahm3d/ai-lawyer-bd-1-8b-instruct-bnb-4bit-GGUF:Q4_K_M',
            'apiUrl' => 'https://3acc-34-143-176-29.ngrok-free.app/api/chat',
            'temperature' => 0.8,
            'systemPrompt' => 'You are an AI Lawyer trained specifically on Bangladeshi law. You assist users by answering legal questions, providing accurate, simple, and understandable legal explanations. You must interpret user queries carefully, refer to relevant laws, and explain legal terms in plain language. If a query is in Bangla, respond in Bangla. If it is in English, respond in English. Avoid giving personal opinions, and always stay factual, respectful, and clear. If you are unsure about a law, politely indicate that the query may need consultation with a licensed legal professional.'
        ];
        echo json_encode(['success' => true, 'settings' => $defaultSettings]);
    }
    exit;
}

// Handle POST request - save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['model']) || !isset($input['apiUrl']) || !isset($input['systemPrompt'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Required settings missing']);
        exit;
    }
    
    $settings = [
        'model' => $input['model'],
        'apiUrl' => $input['apiUrl'],
        'temperature' => floatval($input['temperature']),
        'systemPrompt' => $input['systemPrompt']
    ];
    
    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT)) !== false) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
    }
    exit;
}

// Handle other methods
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
?>
