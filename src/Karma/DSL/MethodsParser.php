<?php

namespace Minime\Karma\DSL;

use Minime\Karma\Macro;
use Minime\Karma\Exceptions\MethodsParserException;

class MethodsParser extends Parser
{
    protected $methodsregexp;
    protected $validateregexp;
    protected $allowedregexp;
    protected $deniedregexp;

    /**
     * List of allowed tokens
     *
     * @var array
     */
    protected static $tokens = [
        'methods' => [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'OPTIONS',
            'LINK',
            'UNLINK',
            'HEAD',
        ],
        'operators' => [
            'not' => '^',
            'any' => '*',
        ],
        'delimiter' => '|'
    ];

    public function __construct()
    {
        $tokens = static::$tokens;
        $this->methodsregexp  = '(' . implode('|', $tokens['methods']) . ')';
        $this->allowedregexp  = "#(?<=^|\\{$tokens['delimiter']}){$this->methodsregexp}#";
        $this->deniedregexp   = "#(?<=\\{$tokens['operators']['not']}){$this->methodsregexp}#";
        $this->validateregexp = "#^(\s)*((\^{0,1}){$this->methodsregexp}((\s)*(\||$)(\s)*))+$#";
        $this->pushMacro( new Macro($tokens['operators']['any'], implode($tokens['delimiter'], $tokens['methods'])) );
    }

    /**
     * Parses a given dsl and returns a raw tree of HTTP methods
     *
     * @param  string $dsl raw dsl string
     * @return array
     */
    public function parse($dsl)
    {
        $dsl = $this->applyMacros($dsl);
        $this->validate($dsl);
        $dsl = $this->sanitize($dsl);

        preg_match_all($this->allowedregexp, $dsl, $allowed);
        $allow = $allowed[0];

        preg_match_all($this->deniedregexp, $dsl, $denyed);
        $deny = $denyed[0];

        if ($deny AND !$allow) {
            $allow = static::$tokens['methods'];
        }

        return $this->reduce($allow, $deny);
    }

    /**
     * Validates a given dsl
     *
     * @param  string          $dsl raw dsl string
     * @throws ParserException If dsl is invalid
     */
    protected function validate($dsl)
    {
        if (!preg_match($this->validateregexp, $dsl)) {
            throw new MethodsParserException("Invalid HTTP methods configuration found in \"{$dsl}\"");
        }
    }

    /**
     * Discards denied methods and reduces parsed tree to unique values
     *
     * @param  array $allow list of allowed methods
     * @param  array $deny  list of denied methods
     * @return array reduced tree
     */
    protected function reduce(array $allow, array $deny)
    {
        return array_values(array_unique(array_diff( $allow, $deny )));
    }

    /**
     * Sanitizes DSL prior to parsing
     *
     * @param  string $dsl raw dsl string
     * @return string sanitized dsl string
     */
    protected function sanitize($dsl)
    {
        return preg_replace('#\s#', '', $dsl); // wipe whitespace
    }
}
