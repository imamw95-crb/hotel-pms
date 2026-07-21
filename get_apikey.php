<?php
require '/www/wwwroot/icon.cloudnod.my.id/vendor/autoload.php';
$app = require '/www/wwwroot/icon.cloudnod.my.id/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Token model
$tokens = DB::table('personal_access_tokens')
    ->where('name', 'api-key')
    ->select('id', 'tokenable_id', 'name', 'token', 'created_at')
    ->get();

echo "API Keys found: " . $tokens->count() . PHP_EOL;
foreach ($tokens as $token) {
    echo "Token ID: " . $token->id . PHP_EOL;
    echo "User ID: " . $token->tokenable_id . PHP_EOL;
    echo "Token (hash): " . $token->token . PHP_EOL;
    echo "Created: " . $token->created_at . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "NOTE: The raw API key is NOT stored - only the SHA256 hash is stored." . PHP_EOL;
echo "The raw key was shown only once when generated." . PHP_EOL;
echo "You need to generate a new API key if the original was lost." . PHP_EOL;
