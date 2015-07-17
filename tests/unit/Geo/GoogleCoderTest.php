<?php 

use Mockery as m;
use Versatile\Geo\GoogleCoder;
use Versatile\Geo\Contracts\Client;

class GoogleCoderTest extends PHPUnit_Framework_TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Versatile\Geo\Contracts\Coder',
            $this->newCoder()
        );
    }

    public function testReturnsGeoCoordinate()
    {

        $client = $this->mockClient();
        $coder = $this->newCoder($client);
        $address = 'foo';
        $lat = 12.345678;
        $lon = 87.654321;
        $result = [
            'results' => [
                0 => [
                    'geometry' => [
                        'location' => [
                            'lat' => $lat,
                            'lng' => $lon
                        ]
                    ]
                ]
            ]
        ];


        $client->shouldReceive('fetch')
               ->with('geocode', ['address'=>'foo'])
               ->once()
               ->andReturn($result);

        $coordinate = $coder->geocode($address);

        $this->assertInstanceOf(
            'Versatile\Geo\Contracts\Coordinate',
            $coordinate
        );

        $this->assertEquals($lat, $coordinate->latitude());
        $this->assertEquals($lon, $coordinate->longitude());

    }


    protected function newCoder(Client $client=null)
    {

        $client = $client ?: $this->mockClient();
        return new GoogleCoder($client);
    }

    protected function mockClient()
    {
        return m::mock('Versatile\Geo\Contracts\Client');
    }

    public function tearDown()
    {
        m::close();
    }

}