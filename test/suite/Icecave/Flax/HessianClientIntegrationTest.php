<?php
namespace Icecave\Flax;

use Exception;
use Exception;
use Icecave\Collections\Vector;
use PHPUnit_Framework_TestCase;

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
     * @group large
     * @dataProvider argumentTestData
     */
    public function testArgument($name, $argument, $output = true)
    {
        try {
            $response = self::$client->invoke($name, $argument);

            $this->assertEquals($output, $response);
        } catch (Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    public function argumentTestData()
    {
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
            // binary //
            ////////////

            array(
                'argBinary_0',
                new Binary,
            ),
            array(
                'argBinary_1',
                new Binary($this->generateData(1)),
            ),
            array(
                'argBinary_15',
                new Binary($this->generateData(15)),
            ),
            array(
                'argBinary_16',
                new Binary($this->generateData(16)),
            ),
            array(
                'argBinary_1023',
                new Binary($this->generateData(1023)),
            ),
            array(
                'argBinary_1024',
                new Binary($this->generateData(1024)),
            ),
            array(
                'argBinary_65536',
                new Binary($this->generateData(65536)),
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

        );
    }

    private function generateData($length)
    {
        $result = '';

        if ($length <= 16) {
            $result .= '012345678901234567890';
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
