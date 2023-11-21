<?php

namespace App\Services\Facades;
use Illuminate\Support\Facades\Facade;

class DeliveryEvent extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'App\Services\DeliveryEvent';
    }
}