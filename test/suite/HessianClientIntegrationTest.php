<?php
namespace Icecave\Flax;

use Exception;
use Guzzle\Common\Exception\GuzzleException;
use Icecave\Chrono\DateTime;
use Icecave\Collections\Map;
use Icecave\Collections\Vector;
use Icecave\Parity\Parity;
use PHPUnit_Framework_TestCase;
use stdClass;

class HessianClientIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (self::$client === null) {
            $factory = new HessianClientFactory();
            self::$client = $factory->create('http://hessian.caucho.com/test/test');
        }
    }

    /**
     * @group integration
     * @group exclude-by-default
     * @group large
     * @dataProvider argumentTestVectors
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
            $encoder = new Serialization\Encoder();
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

    public function argumentTestVectors()
    {
        return $this->prepareTestVectors($this->commonTestVectors());
    }

    /**
     * @group integration
     * @group exclude-by-default
     * @group large
     * @dataProvider replyTestVectors
     */
    public function testReply($name, $output, $skipTest = false, $compareUsingParity = true)
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

        if ($compareUsingParity) {
            $this->assertTrue(
                Parity::isEqualTo($output, $result)
            );
        } else {
            $this->assertEquals($output, $result);
        }
    }

    public function replyTestVectors()
    {
        $testVectors = [

            //////////
            // long //
            //////////

            [
                'Long_0',
                0,
            ],
            [
                'Long_1',
                1,
            ],
            [
                'Long_0x7ff',
                0x7ff,
            ],
            [
                'Long_m0x800',
                -0x800,
            ],
            [
                'Long_0x800',
                0x800,
            ],
            [
                'Long_0x3ffff',
                0x3ffff,
            ],
            [
                'Long_m0x801',
                -0x801,
            ],
            [
                'Long_m0x40000',
                -0x40000,
            ],
            [
                'Long_0x40000',
                0x40000,
            ],
            [
                'Long_0x7fffffff',
                0x7fffffff,
            ],
            [
                'Long_m0x40001',
                -0x40001,
            ],
            [
                'Long_m0x80000000',
                -0x80000000,
            ],
            [
                'Long_m0x80000000',
                -0x80000000,
            ],
        ];

        return $this->prepareTestVectors(
            array_merge(
                $this->commonTestVectors(),
                $testVectors
            )
        );
    }

    public function commonTestVectors()
    {
        $circularReference = new stdClass();
        $circularReference->_first = 'a';
        $circularReference->_rest  = $circularReference;
        $circularReference = new Object(
            'com.caucho.hessian.test.TestCons',
            $circularReference
        );

        return [

            [
                'Null',
                null,
            ],
            [
                'True',
                true,
            ],
            [
                'False',
                false,
            ],

            /////////////
            // integer //
            /////////////

            [
                'Int_0',
                0,
            ],
            [
                'Int_1',
                1,
            ],
            [
                'Int_47',
                47,
            ],
            [
                'Int_m16',
                -16,
            ],
            [
                'Int_0x30',
                0x30,
            ],
            [
                'Int_0x7ff',
                0x7ff,
            ],
            [
                'Int_m17',
                -17,
            ],
            [
                'Int_m0x800',
                -0x800,
            ],
            [
                'Int_0x800',
                0x800,
            ],
            [
                'Int_0x3ffff',
                0x3ffff,
            ],
            [
                'Int_m0x801',
                -0x801,
            ],
            [
                'Int_m0x40000',
                -0x40000,
            ],
            [
                'Int_0x40000',
                0x40000,
            ],
            [
                'Int_0x7fffffff',
                0x7fffffff,
            ],
            [
                'Int_m0x40001',
                -0x40001,
            ],
            [
                'Int_m0x80000000',
                -0x80000000,
            ],
            [
                'Int_m0x80000000',
                -0x80000000,
            ],

            ////////////
            // double //
            ////////////

            [
                'Double_0_0',
                0.0,
            ],
            [
                'Double_1_0',
                1.0,
            ],
            [
                'Double_2_0',
                2.0,
            ],
            [
                'Double_127_0',
                127.0,
            ],
            [
                'Double_m128_0',
                -128.0,
            ],
            [
                'Double_128_0',
                128.0,
            ],
            [
                'Double_m129_0',
                -129.0,
            ],
            [
                'Double_32767_0',
                32767.0,
            ],
            [
                'Double_m32768_0',
                -32768.0,
            ],
            [
                'Double_0_001',
                0.001,
            ],
            [
                'Double_m0_001',
                -0.001,
            ],
            [
                'Double_65_536',
                65.536,
            ],
            [
                'Double_3_14159',
                3.14159,
            ],

            ///////////////
            // timestamp //
            ///////////////

            [
                'Date_0',
                DateTime::fromUnixTime(0),
            ],
            [
                'Date_1',
                new DateTime(1998, 5, 8, 9, 51, 31), // The docs state 7:51, but this is incorrect - http://massapi.com/source/resin-4.0.20/modules/hessian/src/com/caucho/hessian/test/TestHessian2Servlet.java.html
            ],
            [
                'Date_2',
                new DateTime(1998, 5, 8, 9, 51),
            ],

            ////////////
            // string //
            ////////////

            [
                'String_0',
                '',
            ],
            [
                'String_1',
                $this->generateString(1),
            ],
            [
                'String_31',
                $this->generateString(31),
            ],
            [
                'String_32',
                $this->generateString(32),
            ],
            [
                'String_1023',
                $this->generateString(1023),
            ],
            [
                'String_1024',
                $this->generateString(1024),
            ],
            [
                'String_65536',
                $this->generateString(65536),
            ],

            ////////////
            // binary //
            ////////////

            [
                'Binary_0',
                new Binary(),
            ],
            [
                'Binary_1',
                new Binary($this->generateString(1)),
            ],
            [
                'Binary_15',
                new Binary($this->generateString(15)),
            ],
            [
                'Binary_16',
                new Binary($this->generateString(16)),
            ],
            [
                'Binary_1023',
                new Binary($this->generateString(1023)),
            ],
            [
                'Binary_1024',
                new Binary($this->generateString(1024)),
            ],
            [
                'Binary_65536',
                new Binary($this->generateString(65536)),
            ],

            ////////////
            // vector //
            ////////////

            [
                'UntypedFixedList_0',
                Vector::create(),
            ],
            [
                'UntypedFixedList_1',
                Vector::create('1'),
            ],
            [
                'UntypedFixedList_7',
                Vector::create('1', '2', '3', '4', '5', '6', '7'),
            ],
            [
                'UntypedFixedList_8',
                Vector::create('1', '2', '3', '4', '5', '6', '7', '8'),
            ],

            /////////
            // map //
            /////////

            [
                'UntypedMap_0',
                Map::create(),
            ],
            [
                'UntypedMap_1',
                Map::create(['a', 0]),
            ],
            [
                'UntypedMap_2',
                Map::create([0, 'a'], [1, 'b']),
            ],
            [
                'UntypedMap_3',
                Map::create([Vector::create('a'), 0]),
                version_compare(PHP_VERSION, '5.5.0') < 0
            ],

            ////////////
            // object //
            ////////////

            [
                'Object_0',
                new Object('com.caucho.hessian.test.A0', new stdClass()),
            ],
            [
                'Object_1',
                new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 0]),
            ],
            [
                'Object_2',
                Vector::create(
                    new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 0]),
                    new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 1])
                )
            ],
            [
                'Object_2a',
                Vector::create(
                    $object = new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 0]),
                    $object
                )
            ],
            [
                'Object_2b',
                Vector::create(
                    new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 0]),
                    new Object('com.caucho.hessian.test.TestObject', (object) ['_value' => 0])
                )
            ],
            [
                'Object_3',
                $circularReference,
                false,
                false
            ],
        ];
    }

    private function prepareTestVectors(array $data)
    {
        $namedData = [];

        foreach ($data as $arguments) {
            $namedData[$arguments[0]] = $arguments;
        }

        return $namedData;
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
