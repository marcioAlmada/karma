<?php

namespace Minime\Karma\DSL;

use Minime\Karma\Interfaces\Macro;

abstract class Parser
{
    /**
     * List of parser macros
     *
     * @var array
     */
    protected $macros = [];

    public function pushMacro(Macro $macro)
    {
        $this->macros[] = $macro;
    }

    public function applyMacros($expression)
    {
        array_walk($this->macros, function ($macro) use (&$expression) {
            $expression = $macro->apply($expression);
        });
        
        return $expression;
    }

}
