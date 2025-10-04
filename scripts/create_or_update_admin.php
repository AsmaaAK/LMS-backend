<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@test.local';
$password = 'Admin@123456';

// Ensure admin role exists
$role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator role']);

$user = User::where('email', $email)->first();
if (!$user) {
    $user = User::create([
        'name' => 'System Admin',
        'email' => $email,
        'password' => Hash::make($password),
    ]);
} else {
    // Ensure password and name are updated
    $user->update([
        'name' => 'System Admin',
        'password' => Hash::make($password),
    ]);
}

// Attach admin role
if (!$user->roles()->where('name', 'admin')->exists()) {
    $user->roles()->attach($role->id);
}

echo json_encode([
    'email' => $email,
    'password' => $password,
    'roles' => $user->roles()->pluck('name'),
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


