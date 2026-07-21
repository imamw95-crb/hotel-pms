<?php
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check users with API keys
$users = App\Models\User::whereHas('tokens', function($q) {
    $q->where('name', 'api-key');
})->get();

if ($users->isEmpty()) {
    echo 'No users with api-key tokens found' . PHP_EOL;
} else {
    foreach ($users as $user) {
        echo 'User: ' . $user->name . ' (ID: ' . $user->id . ', Role: ' . $user->role . ')' . PHP_EOL;
    }
}
