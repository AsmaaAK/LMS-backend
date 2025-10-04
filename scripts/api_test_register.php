<?php

$email = 'user'.bin2hex(random_bytes(4)).'@example.com';
$payload = [
    'name' => 'New User',
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
];

$ch = curl_init('http://127.0.0.1:8000/api/register');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$err = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Persist last user info for follow-up login test
@file_put_contents(__DIR__.'/.last_user.json', json_encode([
    'email' => $email,
    'password' => 'password1234',
    'register_response' => json_decode($response, true) ?? $response,
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

echo json_encode([
    'status' => $status,
    'email' => $email,
    'raw' => json_decode($response, true) ?? $response,
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


