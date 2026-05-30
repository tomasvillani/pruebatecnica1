<?php
header('Content-Type: application/json');

$env = parse_ini_file(__DIR__ . '/.env');
$supabase_url = $env['SUPABASE_URL'];
$supabase_key = $env['SUPABASE_KEY'];

// Devolver historial
if(isset($_GET['action']) && $_GET['action'] === 'history'){
    $ch = curl_init($supabase_url . '/rest/v1/messages?select=user_message,ai_response,created_at&order=created_at.asc');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    echo json_encode(['history' => json_decode($response, true)]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $body = json_decode(file_get_contents('php://input'), true);
    $message = strtolower(trim($body['message'] ?? ''));

    // 1. Obtener servicios de Sanity
    $url = 'https://1wdnbdbf.api.sanity.io/v2026-05-30/data/query/production?query=*%5B_type+%3D%3D+%22service%22%5D&perspective=drafts';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $services = array_map(fn($s) => [
        'title'       => $s['title'],
        'price'       => $s['price'],
        'description' => $s['description'],
    ], $data['result']);

    // 2. Buscar servicio que coincida
    $found = null;
    foreach($services as $service){
        if(str_contains($message, strtolower($service['title']))){
            $found = $service;
            break;
        }
    }

    $reply = $found
        ? "{$found['description']} - {$found['price']}€"
        : 'No se encontró ningún servicio que coincida con tu búsqueda.';

    // 3. Guardar en Supabase
    $insert = json_encode([
        'user_message' => $body['message'],
        'ai_response'  => $reply,
    ]);

    $ch = curl_init($supabase_url . '/rest/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $insert);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Prefer: return=minimal',
    ]);
    curl_exec($ch);
    curl_close($ch);

    // 4. Devolver respuesta al JS
    echo json_encode(['reply' => $reply]);
}