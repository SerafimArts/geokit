<?php

namespace Geokit\Tests;

use Geokit\LatLng;
use Geokit\Tests\Fixtures\ThirdPartyLatLng;
use PHPUnit\Framework\TestCase;

class LngLatTest extends TestCase
{
    public function testConstructorShouldAcceptStringsAsArguments()
    {
        $LatLng = new LatLng('2.5678', '1.1234');

        $this->assertSame(1.1234, $LatLng->getLongitude());
        $this->assertSame(2.5678, $LatLng->getLatitude());
    }

    public function testConstructorShouldAcceptFloatsAsArguments()
    {
        $LatLng = new LatLng(2.5678, 1.1234);

        $this->assertSame(1.1234, $LatLng->getLongitude());
        $this->assertSame(2.5678, $LatLng->getLatitude());
    }

    public function testConstructorShouldNormalizeLatLng()
    {
        $LatLng = new LatLng(91, 181);

        $this->assertEquals(-179, $LatLng->getLongitude());
        $this->assertEquals(90, $LatLng->getLatitude());
    }

    public function testConstructorShouldAcceptLocalizedFloatsAsArguments()
    {
        $currentLocale = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'deu_deu');

        $latitude = floatval('1.1234');
        $longitude = floatval('2.5678');

        $LatLng = new LatLng($latitude, $longitude);

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());

        setlocale(LC_NUMERIC, $currentLocale);
    }

    public function testArrayAccess()
    {
        $keys = [
            'latitude',
            'lat',
            'y',
            'longitude',
            'lng',
            'lon',
            'x',
        ];

        $LatLng = new LatLng(2, 1);

        foreach ($keys as $key) {
            $this->assertTrue(isset($LatLng[$key]));
            $this->assertNotNull($LatLng[$key]);
        }
    }

    public function testOffsetGetThrowsExceptionForInvalidKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid offset "foo".');

        $LatLng = new LatLng(2, 1);

        $LatLng['foo'];
    }

    public function testOffsetSetThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $LatLng = new LatLng(2, 1);

        $LatLng['lat'] = 5;
    }

    public function testOffsetUnsetThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $LatLng = new LatLng(2, 1);

        unset($LatLng['lat']);
    }

    public function testToStringShouldReturnLatitudeAndLongitudeAsCommaSeparatedString()
    {
        $LatLng = new LatLng(2.5678, 1.1234);

        $this->assertSame(sprintf('%F,%F', 2.5678, 1.1234), (string)$LatLng);
    }

    public function testToStringShouldReturnLatitudeAndLongitudeAsCommaSeparatedStringWithLocalizedFloats()
    {
        $currentLocale = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'deu_deu');

        $latitude = floatval('1.1234');
        $longitude = floatval('2.5678');

        $LatLng = new LatLng($latitude, $longitude);

        $this->assertSame(sprintf('%F,%F', 1.1234, 2.5678), (string)$LatLng);
        setlocale(LC_NUMERIC, $currentLocale);
    }

    public function testNormalizeShouldThrowExceptionIfInvalidDataSupplied()
    {
        $this->expectException(\InvalidArgumentException::class);

        LatLng::normalize(null);
    }

    public function testNormalizeShouldAcceptLatLngArgument()
    {
        $LatLng1 = new LatLng(2.5678, 1.1234);
        $LatLng2 = LatLng::normalize($LatLng1);

        $this->assertEquals($LatLng1, $LatLng2);
    }

    public function testNormalizeShouldAcceptStringArgument()
    {
        $LatLng = LatLng::normalize('1.1234,2.5678');

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptArrayArgument()
    {
        $LatLng = LatLng::normalize(['latitude' => 1.1234, 'longitude' => 2.5678]);

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptArrayArgumentWithShortKeys()
    {
        $LatLng = LatLng::normalize(['lat' => 1.1234, 'lon' => 2.5678]);

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());

        $LatLng = LatLng::normalize(['lat' => 1.1234, 'lng' => 2.5678]);

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptArrayArgumentWithXYKeys()
    {
        $LatLng = LatLng::normalize(['y' => 1.1234, 'x' => 2.5678]);

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptArrayAccessArgument()
    {
        $LatLng = LatLng::normalize(new \ArrayObject(['latitude' => 1.1234, 'longitude' => 2.5678]));

        $this->assertSame(1.1234, $LatLng->getLatitude());
        $this->assertSame(2.5678, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptIndexedArrayArgument()
    {
        $LatLng = LatLng::normalize([2.5678, 1.1234]);

        $this->assertSame(2.5678, $LatLng->getLatitude());
        $this->assertSame(1.1234, $LatLng->getLongitude());
    }

    public function testNormalizeShouldAcceptObjectWithLatLngGetters()
    {
        $thirdPartyLatLng = new ThirdPartyLatLng(2.5678, 1.1234);

        $LatLng = LatLng::normalize($thirdPartyLatLng);

        $this->assertSame(2.5678, $LatLng->getLatitude());
        $this->assertSame(1.1234, $LatLng->getLongitude());
    }

    /**
     * @dataProvider normalizeLatDataProvider
     */
    public function testNormalizeLat($a, $b)
    {
        $this->assertEquals(LatLng::normalizeLat($a), $b);
    }

    public function normalizeLatDataProvider()
    {
        return [
            [-95, -90],
            [-90, -90],
            [5, 5],
            [90, 90],
            [180, 90],
        ];
    }

    /**
     * @dataProvider normalizeLngDataProvider
     */
    public function testNormalizeLng($a, $b)
    {
        $this->assertEquals(LatLng::normalizeLng($a), $b);
    }

    public function normalizeLngDataProvider()
    {
        return [
            [-545, 175],
            [-365, -5],
            [-185, 175],
            [-180, -180],
            [5, 5],
            [180, 180],
            [215, -145],
            [360, 0],
            [395, 35],
            [540, 180],
        ];
    }
}
