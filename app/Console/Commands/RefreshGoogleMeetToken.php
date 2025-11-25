<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshGoogleMeetToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Google Meet access token.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = app(\App\Services\GoogleMeetService::class);
        try {
            $client = $service->refreshAccessToken();
            if ($client) {
                \Log::info('Google token refreshed successfully.');
                $this->info('Google token refreshed successfully.');
            }
        } catch (\Throwable $e) {
            \Log::error('Google token refresh failed: ' . $e->getMessage());
            $this->error('Google token refresh failed: ' . $e->getMessage());
        }
    }
}
