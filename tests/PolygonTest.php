<?php

namespace Geokit\Tests;

use Geokit\LatLng;
use Geokit\Polygon;
use PHPUnit\Framework\TestCase;

class PolygonTest extends TestCase
{
    public function testConstructorShouldAcceptArrayOfLatLngs()
    {
        $points = [
            new LatLng(0, 0),
            ['latitude' => 0, 'longitude' => 1],
            '1, 1',
            'key' => [1, 0],
        ];

        $polygon = new Polygon($points);

        $this->assertEquals($points[0], $polygon[0]);
        $this->assertEquals(0, $polygon[1]->getLatitude());
        $this->assertEquals(1, $polygon[2]->getLatitude());
        $this->assertEquals(1, $polygon['key']->getLatitude());
    }

    public function testConstructorThrowsExceptionForInvalidLatLng()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot normalize LatLng from input "foo".');

        new Polygon(['foo']);
    }

    public function testIsClose()
    {
        $polygon = new Polygon();

        $this->assertFalse($polygon->isClosed());

        $polygon = new Polygon([
            new LatLng(0, 0),
            new LatLng(0, 1),
            new LatLng(1, 1),
            new LatLng(1, 0),
        ]);

        $this->assertFalse($polygon->isClosed());

        $polygon = new Polygon([
            new LatLng(0, 0),
            new LatLng(0, 1),
            new LatLng(1, 1),
            new LatLng(1, 0),
            new LatLng(0, 0),
        ]);

        $this->assertTrue($polygon->isClosed());
    }

    public function testCloseOpenPolygon()
    {
        $polygon = new Polygon([
            new LatLng(0, 0),
            new LatLng(0, 1),
            new LatLng(1, 1),
            new LatLng(1, 0),
        ]);

        $closedPolygon = $polygon->close();

        $this->assertEquals(new LatLng(0, 0), $closedPolygon[count($closedPolygon) - 1]);
    }

    public function testCloseEmptyPolygon()
    {
        $polygon = new Polygon();

        $closedPolygon = $polygon->close();

        $this->assertCount(0, $closedPolygon);
    }

    public function testCloseAlreadyClosedPolygon()
    {
        $polygon = new Polygon([
            new LatLng(0, 0),
            new LatLng(0, 1),
            new LatLng(1, 1),
            new LatLng(1, 0),
            new LatLng(0, 0),
        ]);

        $closedPolygon = $polygon->close();

        $this->assertEquals(new LatLng(0, 0), $closedPolygon[count($closedPolygon) - 1]);
    }

    /**
     * @dataProvider containsDataProvider
     */
    public function testContains($polygon, $point, $expected)
    {
        $polygon = new Polygon($polygon);

        $this->assertEquals($expected, $polygon->contains(LatLng::normalize($point)));
    }

    public function containsDataProvider()
    {
        return [
            // Closed counterclockwise polygons
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => 0.5, 'lat' => 0.5],
                true,
            ],
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => 1.5, 'lat' => 0.5],
                false,
            ],
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => -0.5, 'lat' => 0.5],
                false,
            ],
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => 0.5, 'lat' => 1.5],
                false,
            ],
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => 0.5, 'lat' => -0.5],
                false,
            ],

            // Closed clockwise polygons
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 0, 'lat' => 0],
                ],
                ['lng' => 0.5, 'lat' => 0.5],
                true,
            ],
            [
                [
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 3, 'lat' => 2],
                    ['lng' => 2, 'lat' => 3],
                    ['lng' => 1, 'lat' => 1],
                ],
                ['lng' => 1.5, 'lat' => 1.5],
                true,
            ],

            // Open counterclockwise polygons
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 0, 'lat' => 1],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 1, 'lat' => 0],
                ],
                ['lng' => 0.5, 'lat' => 0.5],
                true,
            ],

            // Open clockwise polygons
            [
                [
                    ['lng' => 0, 'lat' => 0],
                    ['lng' => 1, 'lat' => 0],
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 0, 'lat' => 1],
                ],
                ['lng' => 0.5, 'lat' => 0.5],
                true,
            ],
            [
                [
                    ['lng' => 1, 'lat' => 1],
                    ['lng' => 3, 'lat' => 2],
                    ['lng' => 2, 'lat' => 3],
                ],
                ['lng' => 1.5, 'lat' => 1.5],
                true,
            ],

            // Empty polygon
            [
                [],
                ['lng' => 0.5, 'lat' => 0.5],
                false,
            ],

            // Assoc polygon
            [
                [
                    'polygon1' => ['lng' => 1, 'lat' => 1],
                    'polygon2' => ['lng' => 3, 'lat' => 2],
                    'polygon3' => ['lng' => 2, 'lat' => 3],
                ],
                ['lng' => 1.5, 'lat' => 1.5],
                true,
            ],
        ];
    }

    public function testToBounds()
    {
        $points = [
            new LatLng(0, 0),
            new LatLng(0, 1),
            new LatLng(1, 1),
            new LatLng(1, 0),
        ];

        $polygon = new Polygon($points);

        $bounds = $polygon->toBounds();

        $this->assertEquals(0, $bounds->getSouthWest()->getLatitude());
        $this->assertEquals(0, $bounds->getSouthWest()->getLongitude());
        $this->assertEquals(1, $bounds->getNorthEast()->getLatitude());
        $this->assertEquals(1, $bounds->getNorthEast()->getLongitude());
    }

    public function testToBoundsThrowsExceptionForEmptyPolygon()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot create Bounds from empty Polygon.');

        $polygon = new Polygon();

        $polygon->toBounds();
    }

    public function testArrayAccessNumeric()
    {
        $points = [
            new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        $this->assertTrue(isset($polygon[0]));
        $this->assertNotNull($polygon[0]);
        $this->assertEquals($points[0], $polygon[0]);
    }

    public function testArrayAccessAssoc()
    {
        $points = [
            'key' => new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        $this->assertTrue(isset($polygon['key']));
        $this->assertNotNull($polygon['key']);
        $this->assertEquals($points['key'], $polygon['key']);
    }

    public function testOffsetGetThrowsExceptionForInvalidKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid offset 0.');

        $polygon = new Polygon();

        $polygon[0];
    }

    public function testOffsetSetThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $points = [
            new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        $polygon[0] = new LatLng(1, 1);
    }

    public function testOffsetUnsetThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $points = [
            new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        unset($polygon[0]);
    }

    public function testCountable()
    {
        $points = [
            new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        $this->assertCount(1, $polygon);
    }

    public function testIteratorAggregate()
    {
        $this->expectNotToPerformAssertions();

        $points = [
            new LatLng(0, 0),
        ];

        $polygon = new Polygon($points);

        foreach ($polygon as $point) {
            return;
        }

        $this->fail('Polygon is not iterable.');
    }
}
