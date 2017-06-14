<?php

namespace WhiteSunrise\Guardian\Facades;

use Illuminate\Support\Facades\Facade;

class GuardianFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'guardian';
    }
}
