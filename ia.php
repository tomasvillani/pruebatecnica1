<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'];

    $aiResponse = llamarIA($userMessage);
    guardarEnSupabase($userMessage, $aiResponse);

    header('Content-Type: application/json');
    echo json_encode(['response' => $aiResponse]);
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $supabaseUrl = 'https://gvwudezsomphbkfkcqri.supabase.co';
    $supabaseKey = getenv('SUPABASE_KEY');

    $ch = curl_init("$supabaseUrl/rest/v1/messages?order=created_at.asc");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
        ]
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/json');
    echo $result;
}

function llamarIA($mensaje) {
    if(str_contains($mensaje, 'sofá')){
        return 'Servicio recomendado: limpieza de sofá (60€ aprox)';
    } elseif(str_contains($mensaje, 'alfombra')){
        return 'Servicio recomendado: limpieza de alfombra (50€ aprox)';
    } elseif(str_contains($mensaje, 'coche')){
        return 'Servicio recomendado: limpieza de coche (70€ aprox)';
    } else {
        return 'Lo siento, no tengo un servicio específico para eso. ¿Podrías ser más específico?';
    }
}

function guardarEnSupabase($userMessage, $aiResponse) {
    $supabaseUrl = 'https://gvwudezsomphbkfkcqri.supabase.co';
    $supabaseKey = getenv('SUPABASE_KEY');

    $datos = json_encode([
        'user_message' => $userMessage,
        'ai_response'  => $aiResponse
    ]);

    $ch = curl_init("$supabaseUrl/rest/v1/messages");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
            'Prefer: return=minimal'
        ],
        CURLOPT_POSTFIELDS => $datos
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Muestra en el log de PHP qué ha pasado
    error_log("Supabase HTTP code: " . $httpCode);
    error_log("Supabase response: " . $result);
}