<?php

namespace UptimeKuma\LaravelApi\Facades;

use Illuminate\Support\Facades\Facade;

class UptimeKuma extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'uptime-kuma';
    }
}
