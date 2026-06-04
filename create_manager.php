<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;

// Create manager user if not exists
$user = User::where('username', 'manager')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Manager User',
        'username' => 'manager',
        'email' => 'manager@hotel.com',
        'password' => bcrypt('password'),
        'role' => 'user_manager',
    ]);
    echo "Created user: {$user->name} (role: {$user->role})\n";
} else {
    echo "User already exists: {$user->name} (role: {$user->role})\n";
}

// Test permission
echo "Has manage_promo_prices: " . ($user->hasPermission('manage_promo_prices') ? 'YES' : 'NO') . "\n";
