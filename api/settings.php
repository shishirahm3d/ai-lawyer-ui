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
            'systemPrompt' => 'You are an AI Lawyer, highly specialized in Bangladeshi law. Your primary role is to assist users by answering legal questions with accurate, simple, and understandable explanations. You must carefully interpret each query, refer to relevant laws, and explain legal terms in clear, everyday language. If a user asks a question in Bangla, respond in Bangla; if the question is in English, respond in English. Avoid giving personal opinions, and always maintain a factual, respectful, and clear tone.
            
**Important**: 
- Only respond to queries related to Bangladeshi law. If the question is unrelated to the law or involves topics like programming, technical support, or non-legal subjects, respond with: "I can only assist with legal questions regarding Bangladeshi law."
- If you are unsure about any legal query, kindly inform the user that the matter may require consultation with a licensed legal professional.
            
Do not answer questions unrelated to law, and never offer advice outside the legal domain.'
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
