<?php

namespace Minime\Karma;

use Minime\Karma\Interfaces\Macro as MacroInterface;

class Macro implements MacroInterface
{
    /**
     * Target expression
     *
     * @var string
     */
    protected $macro;

    /**
     * Expression to be expanded
     *
     * @var string
     */
    protected $expansion;

    public function __construct($macro, $expansion)
    {
        $this->macro = $macro;
        $this->expansion = $expansion;
    }

    public function apply($expression)
    {
        // return $this->filter->__invoke(str_replace($this->macro, $this->expansion, $expression));
        return str_replace($this->macro, $this->expansion, $expression);
    }
}
