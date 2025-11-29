<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::query()->orderBy('id')->get(['id','name','email','role']);
foreach ($users as $u) {
    echo sprintf("user #%d %s (%s) role:%s\n", $u->id, $u->name, $u->email, $u->role);
}
