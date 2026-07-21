<?php
// Fix: Add missing create_reservation permission
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Permission;

$perm = Permission::firstOrCreate(
    ['slug' => 'create_reservation'],
    [
        'name' => 'Create Reservation',
        'group' => 'reservation',
        'description' => 'Dapat membuat reservasi via API/website',
    ]
);

if ($perm->wasRecentlyCreated) {
    echo "Created create_reservation permission (ID: {$perm->id})\n";
} else {
    echo "create_reservation already exists (ID: {$perm->id})\n";
}

// Assign to admin role
DB::table('role_permission')->updateOrInsert(
    ['role' => 'admin', 'permission_id' => $perm->id],
    ['created_at' => now(), 'updated_at' => now()]
);
echo "Assigned to admin\n";

// Assign to frontoffice role
DB::table('role_permission')->updateOrInsert(
    ['role' => 'frontoffice', 'permission_id' => $perm->id],
    ['created_at' => now(), 'updated_at' => now()]
);
echo "Assigned to frontoffice\n";

echo "ALL DONE\n";
