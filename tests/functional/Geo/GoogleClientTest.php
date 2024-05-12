<?php

use Mockery as m;
use Versatile\Geo\GoogleClient;
use PHPUnit\Framework\TestCase;

class GoogleClientTest extends TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Versatile\Geo\Contracts\Client',
            $this->newClient()
        );
    }

    public function testClientFetchesGoogleResult()
    {
        $this->markTestSkipped('Google client test does live queries and fails');
        $client = $this->newClient();
        $address = 'Domkloster 4, 50667, Köln, Deutschland';

        $result = $client->fetch('geocode', ['address'=>$address]);

        $this->assertTrue((bool)count($result['results']));
    }

    public function testClientFetchesMultipleTimesIfTooManyRequests()
    {
        $this->markTestSkipped('Google client test does live queries and fails');
        $client = $this->newClient();
        $addresses = [
            'Domkloster 4, 50667, Köln, Deutschland',
            'Berliner Tor, Hamburg, Deutschland',
            'Karl-Friedrich-Straße 26, 76133 Karlsruhe, Deutschland',
            'Platz der Republik 1, 11011 Berlin, Deutschland',
            'Westminster, London SW1A 0AA',
            '1 Six Flags Blvd, Jackson, NJ 08527'
        ];

        $results = [];

        foreach ($addresses as $address) {
            $results[] = $client->fetch('geocode', ['address'=>$address]);
        }

        foreach ($results as $result) {
            $this->assertTrue((bool)count($result['results']));
        }
    }

    protected function newClient()
    {
        return new GoogleClient;
    }

    public function tearDown(): void
    {
        m::close();
    }

}
