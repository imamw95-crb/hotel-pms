<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:key-generate
                            {--user= : ID atau email user yang akan diberi API key}
                            {--name=reservation : Nama/tujuan API key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API Key untuk akses API reservation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userInput = $this->option('user');
        $keyName = $this->option('name');

        // Cari user
        if ($userInput) {
            $user = is_numeric($userInput)
                ? User::find($userInput)
                : User::where('email', $userInput)->first();
        } else {
            // Default: user pertama (owner/admin)
            $user = User::whereIn('role', ['owner', 'admin'])->first();
        }

        if (!$user) {
            $this->error('User tidak ditemukan!');
            return 1;
        }

        // Generate API key
        $apiKey = Str::random(48);

        // Hapus token lama dengan nama yang sama
        $user->tokens()->where('name', $keyName)->delete();

        // Simpan token baru (hashed)
        $user->tokens()->create([
            'name' => $keyName,
            'token' => hash('sha256', $apiKey),
            'abilities' => ['*'],
        ]);

        $this->info('========================================');
        $this->info('  API Key berhasil dibuat!');
        $this->info('========================================');
        $this->info("  User  : {$user->name} ({$user->email})");
        $this->info("  Role  : {$user->role}");
        $this->info("  Name  : {$keyName}");
        $this->info("  Key   : {$apiKey}");
        $this->info('========================================');
        $this->warn('  SIMPAN KEY INI! Key tidak bisa ditampilkan lagi.');
        $this->info('========================================');
        $this->info('');
        $this->info('Penggunaan:');
        $this->info('  Header: X-API-Key: ' . $apiKey);
        $this->info('  Query : ?api_key=' . $apiKey);

        return 0;
    }
}
