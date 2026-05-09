<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use EloquentFilter\ServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    ServiceProvider::class,
];
