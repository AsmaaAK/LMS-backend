<?php

use App\Models\Role;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = [
    ['name' => 'admin', 'description' => 'Administrator role'],
    ['name' => 'manager', 'description' => 'Manager role'],
    ['name' => 'teacher', 'description' => 'Teacher role'],
    ['name' => 'student', 'description' => 'Student role'],
];

$created = [];
foreach ($roles as $r) {
    $role = Role::firstOrCreate(['name' => $r['name']], ['description' => $r['description']]);
    $created[] = ['id' => $role->id, 'name' => $role->name];
}

echo json_encode(['roles' => $created], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


