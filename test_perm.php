<?php

use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$request = Request::capture();
$kernel->handle($request);

$user = User::where('username', 'fo')->first();
echo "User: {$user->name} (role: {$user->role})\n";
echo 'Has manage_promo_prices: '.($user->hasPermission('manage_promo_prices') ? 'YES' : 'NO')."\n";
echo 'Has view_rooms: '.($user->hasPermission('view_rooms') ? 'YES' : 'NO')."\n";
echo 'Has view_room_types: '.($user->hasPermission('view_room_types') ? 'YES' : 'NO')."\n";
