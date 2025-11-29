<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$workshops = \App\Models\Workshop::query()
    ->orderBy('user_id')
    ->orderByDesc('id')
    ->get(['id','title','user_id','meeting_code','meeting_link','recording_url','is_online','start_date']);
foreach ($workshops as $ws) {
    echo sprintf("#%d user:%d online:%s code:%s link:%s rec:%s start:%s title:%s\n",
        $ws->id,
        $ws->user_id,
        $ws->is_online ? 'Y' : 'N',
        $ws->meeting_code ?: '-',$ws->meeting_link ?: '-',$ws->recording_url ?: '-',
        $ws->start_date ? $ws->start_date->toDateTimeString() : '-',
        $ws->title);
}
