<?php namespace Versatile\Geo;

use Versatile\Geo\Contracts\Coder;
use Versatile\Geo\Contracts\Client;
use Versatile\Geo\Contracts\BoundingArea;

class GoogleCoder implements Coder
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|\Versatile\Geo\Contracts\Address $address
     * @param \Versatile\Geo\Contracts\BoundingArea $searchIn (optional)
     * @return \Versatile\Geo\Contracts\Coordinate
     **/
    public function geocode($address, BoundingArea $searchIn=null)
    {

        $addressString = $this->formatAddress($address);

        $params = $this->buildRequestParams($addressString);

        $response = $this->client->fetch('geocode', $params);

        return $this->buildGeoCoordinateFromResponse($response);

    }

    protected function buildGeoCoordinateFromResponse(array $response)
    {
        $latitude = $response['results'][0]['geometry']['location']['lat'];
        $longitude = $response['results'][0]['geometry']['location']['lng'];
        return new Coordinate($latitude, $longitude);
    }

    protected function buildRequestParams($address)
    {
        return ['address' => $address];
    }

    protected function formatAddress($address)
    {
        if (is_string($address)) {
            return str_replace('Str.', 'Straße', $address);
        }

        $methods = [
            'street', 'postCode', 'city', 'country'
        ];

        $parts = [];

        foreach ($methods as $method) {
            if ($res = $address->{$method}()) {
                $parts[] = $res;
            }
        }

        return implode(', ', $parts);

    }

}