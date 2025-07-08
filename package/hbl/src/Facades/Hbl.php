<?php

namespace Anil\Hbl\Facades;

use Illuminate\Support\Facades\Facade;

class Hbl extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hbl';
    }
}
