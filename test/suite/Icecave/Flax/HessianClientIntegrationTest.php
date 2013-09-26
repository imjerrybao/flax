<?php
namespace Icecave\Flax;

use Exception;
use Guzzle\Common\Exception\GuzzleException;
use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use Icecave\Parity\ComparableInterface;
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
     * @dataProvider getTestData
     */
    public function testArgument($name, $argument, $skipTest = false)
    {
        if ($skipTest) {
            $this->markTestSkipped();
        }

        try {
            $result = self::$client->invoke('arg' . $name, $argument);
        } catch (GuzzleException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        if ($result !== true) {
            $encoder = new Serialization\Encoder;
            $encoding = trim(
                preg_replace(
                    '/(..)/',
                    ' \1',
                    bin2hex($encoder->encode($argument))
                )
            );
            $this->fail($result . PHP_EOL . 'Flax Encoding: ' . $encoding);
        } else {
            $this->assertTrue($result);
        }
    }

    /**
     * @group integration
     * @group exclude-by-default
     * @group large
     * @dataProvider getTestData
     */
    public function testReply($name, $output, $skipTest = false)
    {
        if ($skipTest) {
            $this->markTestSkipped();
        }

        if ($output instanceof Object) {
            $output = $output->object();
        } elseif ($output instanceof Vector) {
            $output->mapInPlace(
                function ($element) {
                    if ($element instanceof Object) {
                        return $element->object();
                    }
                    return $element;
                }
            );
        }

        try {
            $result = self::$client->invoke('reply' . $name);
        } catch (GuzzleException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        if ($output instanceof ComparableInterface && $result instanceof ComparableInterface) {
            if (0 === $output->compare($result)) {
                $this->assertTrue(true);

                return;
            }

        }

        $this->assertEquals($output, $result);
    }

    public function getTestData()
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
                'Null',
                null,
            ),
            array(
                'True',
                true,
            ),
            array(
                'False',
                false,
            ),

            /////////////
            // integer //
            /////////////

            array(
                'Int_0',
                0,
            ),
            array(
                'Int_1',
                1,
            ),
            array(
                'Int_47',
                47,
            ),
            array(
                'Int_m16',
                -16,
            ),
            array(
                'Int_0x30',
                0x30,
            ),
            array(
                'Int_0x7ff',
                0x7ff,
            ),
            array(
                'Int_m17',
                -17,
            ),
            array(
                'Int_m0x800',
                -0x800,
            ),
            array(
                'Int_0x800',
                0x800,
            ),
            array(
                'Int_0x3ffff',
                0x3ffff,
            ),
            array(
                'Int_m0x801',
                -0x801,
            ),
            array(
                'Int_m0x40000',
                -0x40000,
            ),
            array(
                'Int_0x40000',
                0x40000,
            ),
            array(
                'Int_0x7fffffff',
                0x7fffffff,
            ),
            array(
                'Int_m0x40001',
                -0x40001,
            ),
            array(
                'Int_m0x80000000',
                -0x80000000,
            ),
            array(
                'Int_m0x80000000',
                -0x80000000,
            ),

            ////////////
            // double //
            ////////////

            array(
                'Double_0_0',
                0.0,
            ),
            array(
                'Double_1_0',
                1.0,
            ),
            array(
                'Double_2_0',
                2.0,
            ),
            array(
                'Double_127_0',
                127.0,
            ),
            array(
                'Double_m128_0',
                -128.0,
            ),
            array(
                'Double_128_0',
                128.0,
            ),
            array(
                'Double_m129_0',
                -129.0,
            ),
            array(
                'Double_32767_0',
                32767.0,
            ),
            array(
                'Double_m32768_0',
                -32768.0,
            ),
            array(
                'Double_0_001',
                0.001,
            ),
            array(
                'Double_m0_001',
                -0.001,
            ),
            array(
                'Double_65_536',
                65.536,
            ),
            array(
                'Double_3_14159',
                3.14159,
            ),

            ///////////////
            // timestamp //
            ///////////////

            array(
                'Date_0',
                DateTime::fromUnixTime(0),
            ),
            array(
                'Date_1',
                new DateTime(1998, 5, 8, 9, 51, 31), // The docs state 7:51, but this is incorrect - http://massapi.com/source/resin-4.0.20/modules/hessian/src/com/caucho/hessian/test/TestHessian2Servlet.java.html
            ),
            array(
                'Date_2',
                new DateTime(1998, 5, 8, 9, 51),
            ),

            ////////////
            // string //
            ////////////

            array(
                'String_0',
                '',
            ),
            array(
                'String_1',
                $this->generateString(1),
            ),
            array(
                'String_31',
                $this->generateString(31),
            ),
            array(
                'String_32',
                $this->generateString(32),
            ),
            array(
                'String_1023',
                $this->generateString(1023),
            ),
            array(
                'String_1024',
                $this->generateString(1024),
            ),
            array(
                'String_65536',
                $this->generateString(65536),
            ),

            ////////////
            // binary //
            ////////////

            array(
                'Binary_0',
                new Binary,
            ),
            array(
                'Binary_1',
                new Binary($this->generateString(1)),
            ),
            array(
                'Binary_15',
                new Binary($this->generateString(15)),
            ),
            array(
                'Binary_16',
                new Binary($this->generateString(16)),
            ),
            array(
                'Binary_1023',
                new Binary($this->generateString(1023)),
            ),
            array(
                'Binary_1024',
                new Binary($this->generateString(1024)),
            ),
            array(
                'Binary_65536',
                new Binary($this->generateString(65536)),
            ),

            ////////////
            // vector //
            ////////////

            array(
                'UntypedFixedList_0',
                Vector::create(),
            ),
            array(
                'UntypedFixedList_1',
                Vector::create('1'),
            ),
            array(
                'UntypedFixedList_7',
                Vector::create('1', '2', '3', '4', '5', '6', '7'),
            ),
            array(
                'UntypedFixedList_8',
                Vector::create('1', '2', '3', '4', '5', '6', '7', '8'),
            ),

            /////////
            // map //
            /////////

            array(
                'UntypedMap_0',
                Map::create(),
            ),
            array(
                'UntypedMap_1',
                Map::create(array('a', 0)),
            ),
            array(
                'UntypedMap_2',
                Map::create(array(0, 'a'), array(1, 'b')),
            ),
            array(
                'UntypedMap_3',
                Map::create(array(Vector::create('a'), 0)),
                version_compare(PHP_VERSION, '5.5.0') <= 0
            ),

            ////////////
            // object //
            ////////////

            array(
                'Object_0',
                new Object('com.caucho.hessian.test.A0', new stdClass),
            ),
            array(
                'Object_1',
                new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
            ),
            array(
                'Object_2',
                Vector::create(
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 1))
                )
            ),
            array(
                'Object_2a',
                Vector::create(
                    $object = new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    $object
                )
            ),
            array(
                'Object_2b',
                Vector::create(
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0)),
                    new Object('com.caucho.hessian.test.TestObject', (object) array('_value' => 0))
                )
            ),
            array(
                'Object_3',
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
