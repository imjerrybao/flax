<?php
namespace Icecave\Flax;

use Exception;
use Guzzle\Common\Exception\GuzzleException;
use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use PHPUnit_Framework_TestCase;
use stdClass;

class HessianClientIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (self::$client === null) {
            $factory = new HessianClientFactory;
            self::$client = $factory->create('http://hessian.caucho.com/test/test');
        }
    }

    /**
     * @group integration
     * @group exclude-by-default
     * @group large
     * @dataProvider argumentTestData
     */
    public function testArgument($name, $argument, $skipTest = false)
    {
        if ($skipTest) {
            $this->markTestSkipped();
        }

        try {
            $response = self::$client->invoke($name, $argument);
        } catch (GuzzleException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        if ($response !== true) {
            $encoder = new Serialization\Encoder;
            $encoding = trim(
                preg_replace(
                    '/(..)/',
                    ' \1',
                    bin2hex($encoder->encode($argument))
                )
            );
            $this->fail($response . PHP_EOL . 'Flax Encoding: ' . $encoding);
        } else {
            $this->assertTrue($response);
        }
    }

    public function argumentTestData()
    {
        // Filthy hack ...
        if (!getenv('TRAVIS')) {
            return array(
                array(
                    'Integration tests skipped outside Travis CI.',
                    null,
                    true,
                )
            );
        }

        $circularReference = new stdClass;
        $circularReference->_first = 'a';
        $circularReference->_rest  = $circularReference;
        $circularReference = new Object(
            'com.caucho.hessian.test.TestCons',
            $circularReference
        );

        return array(

            array(
                'argNull',
                null,
            ),
            array(
                'argTrue',
                true,
            ),
            array(
                'argFalse',
                false,
            ),

            /////////////
            // integer //
            /////////////

            array(
                'argInt_0',
                0,
            ),
            array(
                'argInt_1',
                1,
            ),
            array(
                'argInt_47',
                47,
            ),
            array(
                'argInt_m16',
                -16,
            ),
            array(
                'argInt_0x30',
                0x30,
            ),
            array(
                'argInt_0x7ff',
                0x7ff,
            ),
            array(
                'argInt_m17',
                -17,
            ),
            array(
                'argInt_m0x800',
                -0x800,
            ),
            array(
                'argInt_0x800',
                0x800,
            ),
            array(
                'argInt_0x3ffff',
                0x3ffff,
            ),
            array(
                'argInt_m0x801',
                -0x801,
            ),
            array(
                'argInt_m0x40000',
                -0x40000,
            ),
            array(
                'argInt_0x40000',
                0x40000,
            ),
            array(
                'argInt_0x7fffffff',
                0x7fffffff,
            ),
            array(
                'argInt_m0x40001',
                -0x40001,
            ),
            array(
                'argInt_m0x80000000',
                -0x80000000,
            ),
            array(
                'argInt_m0x80000000',
                -0x80000000,
            ),

            ////////////
            // double //
            ////////////

            array(
                'argDouble_0_0',
                0.0,
            ),
            array(
                'argDouble_1_0',
                1.0,
            ),
            array(
                'argDouble_2_0',
                2.0,
            ),
            array(
                'argDouble_127_0',
                127.0,
            ),
            array(
                'argDouble_m128_0',
                -128.0,
            ),
            array(
                'argDouble_128_0',
                128.0,
            ),
            array(
                'argDouble_m129_0',
                -129.0,
            ),
            array(
                'argDouble_32767_0',
                32767.0,
            ),
            array(
                'argDouble_m32768_0',
                -32768.0,
            ),
            array(
                'argDouble_0_001',
                0.001,
            ),
            array(
                'argDouble_m0_001',
                -0.001,
            ),
            array(
                'argDouble_65_536',
                65.536,
            ),
            array(
                'argDouble_3_14159',
                3.14159,
            ),

            ///////////////
            // timestamp //
            ///////////////

            array(
                'argDate_0',
                DateTime::fromUnixTime(0),
            ),
            array(
                'argDate_1',
                new DateTime(1998, 5, 8, 9, 51, 31), // The docs state 7:51, but this is incorrect - http://massapi.com/source/resin-4.0.20/modules/hessian/src/com/caucho/hessian/test/TestHessian2Servlet.java.html
            ),
            array(
                'argDate_2',
                new DateTime(1998, 5, 8, 9, 51),
            ),

            ////////////
            // string //
            ////////////

            array(
                'argString_0',
                '',
            ),
            array(
                'argString_1',
                $this->generateString(1),
            ),
            array(
                'argString_31',
                $this->generateString(31),
            ),
            array(
                'argString_32',
                $this->generateString(32),
            ),
            array(
                'argString_1023',
                $this->generateString(1023),
            ),
            array(
                'argString_1024',
                $this->generateString(1024),
            ),
            array(
                'argString_65536',
                $this->generateString(65536),
            ),

            ////////////
            // binary //
            ////////////

            array(
                'argBinary_0',
                new Binary,
            ),
            array(
                'argBinary_1',
                new Binary($this->generateString(1)),
            ),
            array(
                'argBinary_15',
                new Binary($this->generateString(15)),
            ),
            array(
                'argBinary_16',
                new Binary($this->generateString(16)),
            ),
            array(
                'argBinary_1023',
                new Binary($this->generateString(1023)),
            ),
            array(
                'argBinary_1024',
                new Binary($this->generateString(1024)),
            ),
            array(
                'argBinary_65536',
                new Binary($this->generateString(65536)),
            ),

            ////////////
            // vector //
            ////////////

            array(
                'argUntypedFixedList_0',
                Vector::create(),
            ),
            array(
                'argUntypedFixedList_1',
                Vector::create('1'),
            ),
            array(
                'argUntypedFixedList_7',
                Vector::create('1', '2', '3', '4', '5', '6', '7'),
            ),
            array(
                'argUntypedFixedList_8',
                Vector::create('1', '2', '3', '4', '5', '6', '7', '8'),
            ),

            /////////
            // map //
            /////////

            array(
                'argUntypedMap_0',
                Map::create(),
            ),
            array(
                'argUntypedMap_1',
                Map::create(array('a', 0)),
            ),
            array(
                'argUntypedMap_2',
                Map::create(array(0, 'a'), array(1, 'b')),
            ),
            array(
                'argUntypedMap_3',
                Map::create(array(array('a'), 0)),
                version_compare(PHP_VERSION, '5.5.0') <= 0
            ),

            ////////////
            // object //
            ////////////

            array(
                'argObject_0',
                new Object('com.caucho.hessian.test.A0', new stdClass),
            ),
            array(
                'argObject_1',
                new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
            ),
            array(
                'argObject_2',
                array(
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 1)),
                )
            ),
            array(
                'argObject_2a',
                array(
                    $object = new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    $object,
                )
            ),
            array(
                'argObject_2b',
                array(
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                )
            ),
            array(
                'argObject_3',
                $circularReference
            ),
        );
    }

    private function generateString($length)
    {
        $result = '';

        if ($length <= 32) {
            $result .= '0123456789012345678901234567890123456789';
        } elseif ($length <= 1025) {
            for ($i = 0; $i < 16; ++$i) {
                $result .= intval($i / 10);
                $result .= intval($i % 10);
                $result .= " 456789012345678901234567890123456789012345678901234567890123\n";
            }
        } else {
            for ($i = 0; $i < 16 * 64; ++$i) {
                $result .= intval($i / 100);
                $result .= intval($i / 10) % 10;
                $result .= intval($i % 10);
                $result .= " 56789012345678901234567890123456789012345678901234567890123\n";
            }
        }

        return substr($result, 0, $length);
    }

    private static $client;
}
