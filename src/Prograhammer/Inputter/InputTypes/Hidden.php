<?php namespace Prograhammer\Inputter\InputTypes;

use Prograhammer\Inputter\InputTypeInterface;

class Hidden extends Text implements InputTypeInterface
{

    protected $type = "hidden";

}