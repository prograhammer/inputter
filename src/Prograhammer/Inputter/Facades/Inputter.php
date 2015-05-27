<?php namespace Prograhammer\Inputter\Facades;

use Illuminate\Support\Facades\Facade;

class Inputter extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return '\Prograhammer\Inputter\Inputter'; }

}