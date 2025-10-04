<?php

$stateFile = __DIR__.'/.last_user.json';
if (!is_file($stateFile)) {
    fwrite(STDERR, "No .last_user.json found. Run scripts/api_test_register.php first.\n");
    exit(1);
}

$state = json_decode(file_get_contents($stateFile), true);
$email = $state['email'] ?? null;
$password = $state['password'] ?? null;
if (!$email || !$password) {
    fwrite(STDERR, "State file missing email/password.\n");
    exit(1);
}

// Login
$loginPayload = [ 'email' => $email, 'password' => $password ];
$ch = curl_init('http://127.0.0.1:8000/api/login');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($loginPayload, JSON_UNESCAPED_SLASHES),
    CURLOPT_RETURNTRANSFER => true,
]);
$loginResponse = curl_exec($ch);
$loginStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$login = json_decode($loginResponse, true);
$token = $login['access_token'] ?? null;

// /api/user
$meResponse = null;
$meStatus = null;
if ($token) {
    $ch = curl_init('http://127.0.0.1:8000/api/user');
    curl_setopt_array($ch, [
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer '.$token,
        ],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $meResponse = curl_exec($ch);
    $meStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

echo json_encode([
    'register_email' => $email,
    'login_status' => $loginStatus,
    'login_raw' => $login ?: $loginResponse,
    'me_status' => $meStatus,
    'me_raw' => json_decode($meResponse, true) ?? $meResponse,
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";



