<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = \App\Models\User::find(6);
$fields = ['google_drive_email','google_access_token','google_refresh_token','google_expires_at'];
foreach ($fields as $f){ echo $f.': '.(is_null($u->$f)?'NULL':(is_object($u->$f)?$u->$f->toDateTimeString():substr($u->$f,0,40)))."\n"; }
