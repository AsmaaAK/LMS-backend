<?php

use App\Models\User;
use App\Models\Role;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find or create admin role
$role = Role::firstOrCreate(['name' => 'admin'], [
    'description' => 'Administrator role'
]);

// Get last created user
$user = User::orderByDesc('id')->first();
if (!$user) {
    echo "No users found.\n";
    exit(1);
}

// Attach role if not already attached
if (!$user->roles()->where('name', 'admin')->exists()) {
    $user->roles()->attach($role->id);
}

echo json_encode([
    'user_id' => $user->id,
    'email' => $user->email,
    'roles' => $user->roles()->pluck('name'),
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";
