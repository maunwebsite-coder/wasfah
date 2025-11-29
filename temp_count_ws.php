<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$count = \App\Models\Workshop::query()
    ->where('user_id', 8)
    ->where(function($query){
        $query->whereNotNull('recording_url')
            ->orWhereNotNull('meeting_code')
            ->orWhereNotNull('meeting_link');
    })
    ->count();
echo "count: $count\n";
