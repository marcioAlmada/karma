<?php

namespace Minime\Karma\DSL;

/**
 * MethodsParserTest
 *
 * @group DSL
 */
class MethodsParserTest extends \PHPUnit_Framework_TestCase
{

    protected static $parser;

    public static function setUpBeforeClass()
    {
        static::$parser  = new MethodsParser;
    }

    /**
     * @test
     */
    public function testConstruct() {
    	new MethodsParser();
    }

    /**
     * @test
     * @dataProvider expressionProvider
     */
    public function parse($dsl, array $allow)
    {
        $this->assertSame($allow, static::$parser->parse($dsl));
    }

    public function expressionProvider()
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'LINK', 'UNLINK', 'HEAD'];

        $engine = function (array $allow, array $deny = []) {
            return array_values(array_unique(array_diff($allow, $deny)));
        };

        return [
            ['GET',             ['GET']],
            ['GET|POST|PUT',    ['GET', 'POST', 'PUT']],

            ['^GET',            $engine($methods, ['GET'])],
            ['^GET|^POST|^PUT', $engine($methods, ['GET', 'POST', 'PUT'])],

            ['GET|^POST',       $engine(['GET'])],

            ['*',               $engine($methods)],
            ['*|*',             $engine($methods)],

            ['*|GET',           $engine($methods)],
            ['GET|*',           $engine($methods)],
            ['GET|*|POST|*',    $engine($methods)],

            ['*|^GET',          $engine($methods, ['GET'])],
            ['*|^GET|^POST',    $engine($methods, ['GET', 'POST'])],

            ['GET|*|^POST',     $engine($methods, ['POST'])],

            // with space
            
            ['  GET  ',             ['GET']],
            ['GET |    POST | PUT', ['GET', 'POST', 'PUT']],
            [' ^GET',               $engine($methods, ['GET'])],
            ["^GET |\t^POST| ^PUT", $engine($methods, ['GET', 'POST', 'PUT'])],
            ['GET|^POST  ',         $engine(['GET'])],
            [' * ',                 $engine($methods)],
            ['* | *',               $engine($methods)],
        ];
    }

    /**
     * @test
     * @dataProvider invalidExpressionProvider
     * @expectedException Minime\Karma\Exceptions\MethodsParserException
     */
    public function validate($dsl)
    {
        static::$parser->parse($dsl);
    }

    public function invalidExpressionProvider()
    {
        return [
            [''],
            ['  GET  || POST'],
            ['G ET'],
            ['^ POST']
        ];
    }

}
