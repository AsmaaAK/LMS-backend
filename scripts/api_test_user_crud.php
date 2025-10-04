<?php

function http($method, $url, $headers = [], $body = null) {
    $ch = curl_init($url);
    $hdrs = [];
    foreach ($headers as $k => $v) {
        $hdrs[] = $k . ': ' . $v;
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$status, $resp];
}

$base = 'http://127.0.0.1:8000';

// 1) Login as admin
$loginBody = json_encode(['email' => 'admin@test.local', 'password' => 'Admin@123456']);
[$st, $res] = http('POST', $base.'/api/login', [
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
], $loginBody);
$login = json_decode($res, true);
$token = $login['access_token'] ?? null;

$out = [ 'login_status' => $st, 'login' => $login ];

if (!$token) {
    echo json_encode($out, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";
    exit(1);
}

$authHeaders = [
    'Accept' => 'application/json',
    'Authorization' => 'Bearer '.$token,
    'Content-Type' => 'application/json',
];

// 2) Create user (store)
$newEmail = 'crud_'.bin2hex(random_bytes(3)).'@example.com';
$storeBody = json_encode([
    'name' => 'CRUD User',
    'email' => $newEmail,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
    'role_id' => 3, // student
]);
[$stStore, $resStore] = http('POST', $base.'/api/users', $authHeaders, $storeBody);
$store = json_decode($resStore, true);

$createdId = $store['data']['id'] ?? null;

// 3) Index users
[$stIndex, $resIndex] = http('GET', $base.'/api/users', $authHeaders);
$index = json_decode($resIndex, true);

// 4) Show created user
[$stShow, $resShow] = http('GET', $base.'/api/users/'.($createdId ?? 0), $authHeaders);
$show = json_decode($resShow, true);

// 5) Update created user
$updateBody = json_encode(['name' => 'CRUD User Updated']);
[$stUpdate, $resUpdate] = http('PUT', $base.'/api/users/'.($createdId ?? 0), $authHeaders, $updateBody);
$update = json_decode($resUpdate, true);

// 6) Delete created user
[$stDelete, $resDelete] = http('DELETE', $base.'/api/users/'.($createdId ?? 0), $authHeaders);
$delete = json_decode($resDelete, true);

echo json_encode([
    'login_status' => $st,
    'store_status' => $stStore,
    'store' => $store,
    'index_status' => $stIndex,
    'index_count' => is_array($index['data'] ?? null) ? count($index['data']) : null,
    'show_status' => $stShow,
    'show' => $show,
    'update_status' => $stUpdate,
    'update' => $update,
    'delete_status' => $stDelete,
    'delete' => $delete,
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


