<?php

namespace Alcove\Alcove\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Alcove\Alcove\Alcove
 */
class Alcove extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Alcove\Alcove\Alcove::class;
    }
}
