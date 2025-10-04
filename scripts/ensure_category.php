<?php

use App\Models\Category;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cat = Category::firstOrCreate(
    ['name' => 'General'],
    ['slug' => 'general', 'description' => 'Default category']
);

echo json_encode(['category_id' => $cat->id, 'name' => $cat->name], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


