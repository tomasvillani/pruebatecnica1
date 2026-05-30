<?php
$env = parse_ini_file(__DIR__ . '/.env');
$supabaseKey = $env['SUPABASE_KEY'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'];

    $services = getServicesFromSanity();
    $msg = strtolower($userMessage);

    $match = null;
    foreach($services as $service){
        if(isset($service['title'])){
            $title = strtolower($service['title']);
            // busca si alguna palabra del mensaje aparece en el título
            $words = explode(' ', $msg);
            foreach($words as $word){
                if(strlen($word) > 2 && str_contains($title, $word)){
                    $match = $service;
                    break 2; // sale de ambos foreach
                }
            }
        }
    }

    $aiResponse = $match
        ? $match['description'] . ' - ' . $match['price'] . '€'
        : 'No he encontrado un servicio relacionado';

    guardarEnSupabase($userMessage, $aiResponse, $supabaseKey); // <-- pasa la key

    header('Content-Type: application/json');
    echo json_encode(['response' => $aiResponse]);
}

function getServicesFromSanity() {
    $url = 'https://1wdnbdbf.api.sanity.io/v2026-05-30/data/query/production?query=*[_type==%22service%22]&perspective=drafts';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);
    return $data['result'] ?? [];
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $supabaseUrl = 'https://gvwudezsomphbkfkcqri.supabase.co';

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

function guardarEnSupabase($userMessage, $aiResponse, $supabaseKey) {
    $supabaseUrl = 'https://gvwudezsomphbkfkcqri.supabase.co';

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

    error_log("Supabase HTTP code: " . $httpCode);
    error_log("Supabase response: " . $result);
}