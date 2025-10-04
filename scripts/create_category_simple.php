<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a basic category without relying on SoftDeletes model scopes
$name = 'General';
$slug = 'general';

$exists = DB::table('categories')->where('name', $name)->first();
if ($exists) {
    $id = $exists->id;
} else {
    $id = DB::table('categories')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'description' => 'Default category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

@file_put_contents(__DIR__.'/.last_category.json', json_encode(['id' => $id, 'name' => $name], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
echo json_encode(['category_id' => $id, 'name' => $name], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


