<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/stock', 'GET', ['draw'=>1, 'columns'=>[['data'=>'branch']], 'start'=>0, 'length'=>10]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$response = $kernel->handle($request);
echo $response->getContent();
