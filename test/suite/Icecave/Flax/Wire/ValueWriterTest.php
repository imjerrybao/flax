<?php
namespace Icecave\Flax\Wire;

use PHPUnit_Framework_TestCase;

class ValueWriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->writer = new ValueWriter;
    }

    // /**
    //  * @dataProvider getTestValues
    //  */
    // public function testWrite($value, $expectedResult)
    // {
    //     $buffer = '...';

    //     $this->writer->write($buffer, $value);

    //     $this->assertSame('...' . $expectedResult, $buffer);
    // }

    /**
     * @dataProvider getTestValues
     */
    public function testEncode($value, $expectedResult)
    {
        $buffer = $this->writer->encode($value);

        if ($buffer !== $expectedResult) {
            echo PHP_EOL;
            echo bin2hex($expectedResult) . PHP_EOL;
            echo bin2hex($buffer) . PHP_EOL;
        }

        $this->assertSame($expectedResult, $buffer);
    }

    public function getTestValues()
    {
        $string1000 = $this->generateString(1000);
        $string2000 = $this->generateString(2000);

        $data = array(
            'null'                      => array(null, 'N'),

            'boolean - true'            => array(true,  'T'),
            'boolean - false'           => array(false, 'F'),

            'string'                    => array("",          "\x00"),
            'string - hello'            => array("hello",     "\x05hello"),
            'string - unicode'          => array("\xc3\x83",  "\x01\xc3\x83"),
            'string - long'             => array($string1000, "\x33\xe8" . $string1000),
            // 'string - chunked'          => array($string2000, '\x30\x30' . $string2000),

            'double - 1 octet zero'     => array(       0.0, "\x5b"),
            'double - 1 octet one'      => array(       1.0, "\x5c"),
            'double - 2 octet min'      => array(    -128.0, "\x5d\x80"),
            'double - 2 octet max'      => array(     127.0, "\x5d\x7f"),
            'double - 2 octet midway'   => array(     -10.0, "\x5d\xf6"),
            'double - 3 octet min'      => array(  -32768.0, "\x5e\x80\x00"),
            'double - 3 octet max'      => array(   32767.0, "\x5e\x7f\xff"),
            'double - 3 octet midway'   => array(   -1000.0, "\x5e\xfc\x18"),
            'double - 4 octet float'    => array(     12.25, "\x5f\x41\x44\x00\x00"),
            'double - 8 octet'          => array( 12.251234, "D\x40\x28\x80\xa1\xbe\x2b\x49\x5a"),

            'integer - 1 octet zero'    => array(         0, "\x90"),
            'integer - 1 octet min'     => array(       -16, "\x80"),
            'integer - 1 octet max'     => array(        47, "\xbf"),
            'integer - 1 octet midway'  => array(        -8, "\x88"),
            'integer - 2 octet min'     => array(     -2048, "\xc0\x00"),
            'integer - 2 octet max'     => array(      2047, "\xcf\xff"),
            'integer - 2 octet midway'  => array(      -256, "\xc7\x00"),
            'integer - 3 octet min'     => array(   -262144, "\xd0\x00\x00"),
            'integer - 3 octet max'     => array(    262143, "\xd7\xff\xff"),
            'integer - 3 octet midway'  => array(     -4000, "\xd3\xf0\x60"),
            'integer - 4 octet'         => array(0x10203040, "I\x10\x20\x30\x40"),
        );

        if (PHP_INT_SIZE > 4) {
            $data['integer - 8 octet'] = array(0x1020304050607080, "I\x10\x20\x30\x40\x50\x60\x70\x80");
        }

        return $data;
    }

    private function generateString($length)
    {
        $text = preg_replace(
            '/\s+/',
            ' ',
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
            quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
            consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
            proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        );

        $str = '';

        while (strlen($str) < $length) {
            $str .= $text;
        }

        return substr($str, 0, $length - 1) . '!';
    }

    // 'date'                      => array(..., ...),
    // 'date - minutes'            => array(..., ...),
    // 'string - long'             => array("\u00c3", "\x01\xc3\x83"),
    // 'list'                      => array(..., ...),
    // 'map'                       => array(..., ...),
    // 'object'                    => array(..., ...),
    // 'binary - empty'            => array("",             "\x20"),
    // 'binary - 3 octet'          => array("\x01\x02\x03", "\x23\x01\x02\x03"),
    // 'binary - multi-part'       => array(..., ...)
}
