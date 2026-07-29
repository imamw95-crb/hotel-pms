<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Scheduler Heartbeat — writes a timestamp every minute.
 *
 * If this stops updating, the Laravel scheduler (cron) is not running.
 */
class SchedulerHeartbeatCommand extends Command
{
    protected $signature = 'scheduler:heartbeat';

    protected $description = 'Write scheduler heartbeat timestamp to log file';

    public function handle(): int
    {
        $heartbeatFile = storage_path('logs/scheduler-heartbeat.log');
        $dir = dirname($heartbeatFile);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Append current timestamp
        file_put_contents(
            $heartbeatFile,
            now()->format('Y-m-d H:i:s').PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        // Keep only last ~100 lines
        $lines = file($heartbeatFile);
        if (count($lines) > 100) {
            file_put_contents($heartbeatFile, implode('', array_slice($lines, -100)));
        }

        return self::SUCCESS;
    }
}
