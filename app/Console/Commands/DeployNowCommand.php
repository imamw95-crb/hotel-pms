<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployNowCommand extends Command
{
    protected $signature = 'deploy:now';
    protected $description = 'Manual deploy: git pull + clear cache';

    public function handle(): int
    {
        $projectDir = base_path();

        $this->info('[Deploy] Memulai...');
        $this->newLine();

        // 1. Git pull
        $this->info('[Deploy] git pull origin main...');
        exec('cd '.escapeshellarg($projectDir).' && git pull origin main 2>&1', $output, $exitCode);
        foreach ($output as $line) {
            $this->line($line);
        }
        if ($exitCode !== 0) {
            $this->error('[Deploy] Git pull gagal!');
            return 1;
        }
        $this->info('[Deploy] Git pull berhasil');
        $this->newLine();

        // 2. Clear cache
        $steps = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
        foreach ($steps as $cmd) {
            $this->info("[Deploy] php artisan $cmd...");
            $this->call($cmd);
            $this->info("[Deploy] $cmd selesai");
        }
        $this->newLine();

        $this->info('[Deploy] Selesai!');

        return 0;
    }
}
