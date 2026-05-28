<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

class ImapService
{
    private ?ClientManager $clientManager = null;
    private $client = null;

    public function __construct()
    {
        $this->clientManager = new ClientManager([
            'host'          => config('services.imap.host', 'imap.hostinger.com'),
            'port'          => config('services.imap.port', 993),
            'encryption'    => config('services.imap.encryption', 'ssl'),
            'validate_cert' => config('services.imap.validate_cert', true),
            'username'      => config('services.imap.username'),
            'password'      => config('services.imap.password'),
            'protocol'      => config('services.imap.protocol', 'imap'),
            'timeout'       => 30,
        ]);
    }

    /**
     * Connect to IMAP server with retry.
     */
    public function connect(int $retries = 3): bool
    {
        for ($i = 0; $i < $retries; $i++) {
            try {
                $this->client = $this->clientManager->account('default');
                $this->client->connect();
                Log::info('IMAP connected successfully');
                return true;
            } catch (\Exception $e) {
                Log::warning("IMAP connection attempt " . ($i + 1) . " failed: " . $e->getMessage());
                if ($i < $retries - 1) {
                    sleep(2);
                }
            }
        }

        Log::error('IMAP connection failed after ' . $retries . ' attempts');
        return false;
    }

    /**
     * Disconnect from IMAP server.
     */
    public function disconnect(): void
    {
        try {
            if ($this->client) {
                $this->client->disconnect();
            }
        } catch (\Exception $e) {
            Log::warning('IMAP disconnect error: ' . $e->getMessage());
        }
    }

    /**
     * Get unread emails from inbox.
     *
     * @return Message[]
     */
    public function getUnreadEmails(): array
    {
        if (!$this->client) {
            if (!$this->connect()) {
                return [];
            }
        }

        try {
            $folder = $this->client->getFolder('INBOX');
            $messages = $folder->query()->unseen()->get();

            Log::info('IMAP: Found ' . $messages->count() . ' unread emails');
            return $messages->all();
        } catch (\Exception $e) {
            Log::error('IMAP error fetching emails: ' . $e->getMessage());

            // Try reconnecting once
            if ($this->connect()) {
                try {
                    $folder = $this->client->getFolder('INBOX');
                    $messages = $folder->query()->unseen()->get();
                    return $messages->all();
                } catch (\Exception $e2) {
                    Log::error('IMAP error after reconnect: ' . $e2->getMessage());
                }
            }

            return [];
        }
    }

    /**
     * Move email to a folder. Creates folder if it doesn't exist.
     */
    public function moveEmail(Message $message, string $folderName): bool
    {
        try {
            $folder = $this->client->getFolder($folderName);

            if (!$folder) {
                // Try to create the folder
                $this->client->createFolder($folderName);
                $folder = $this->client->getFolder($folderName);
            }

            if ($folder) {
                $message->move($folder);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::warning("IMAP: Failed to move email to {$folderName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark email as seen.
     */
    public function markAsSeen(Message $message): void
    {
        try {
            $message->setFlag('Seen');
        } catch (\Exception $e) {
            Log::warning('IMAP: Failed to mark email as seen: ' . $e->getMessage());
        }
    }
}
