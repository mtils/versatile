<?php namespace Versatile\Geo;

use OverflowException;
use RangeException;
use RuntimeException;
use Versatile\Geo\Contracts\Client;

class GoogleClient implements Client
{

    const   SUCCESS             = 'OK';
    const   BAD_REQUEST         = 'INVALID_REQUEST';
    const   UNKNOWN_ADDRESS     = 'ZERO_RESULTS';
    const   REQUEST_DENIED      = 'REQUEST_DENIED';
    const   TOO_MANY_QUERIES    = 'OVER_QUERY_LIMIT';

    protected $urls = [
        'geocode' => 'http://maps.google.com/maps/api/geocode/json'
    ];

    protected $maxRepeatedRequests = 10;

    /**
     * {@inheritdoc}
     *
     * @param string $method The method to call on the "WMS" server
     * @param array $params The parameters of the method
     * @return array The result
     **/
    public function fetch($method, array $params)
    {

        $url = $this->buildRequestUrl($method, $params);

        $recursionCounter = 0;

        while ($recursionCounter <= $this->maxRepeatedRequests) {

            try {
                return $this->getResponse($url);
            } catch (OverflowException $e) {
                $recursionCounter++;
                sleep(1);
                continue;
            }

        }

    }

    protected function getResponse($url)
    {

        $response = $this->parseResponse($this->sendRequest($url));

        switch ($response['status']) {

            case self::SUCCESS:
                return $response;
            case self::UNKNOWN_ADDRESS:
                throw new RangeException();
            case self::TOO_MANY_QUERIES:
                throw new OverflowException('Too many queries, ask later');

        }

        throw new RuntimeException("Google geocoder returned " . $response['status']);

    }

    protected function buildRequestUrl($method, array $params)
    {

        $url = $this->urls[$method];

        if ($method == 'geocode') {
            $params['sensor'] = 'false';
        }

        $query = http_build_query($params);

        return $url . '?' . $query;

    }

    protected function sendRequest($url)
    {
        return file_get_contents($url);
    }

    protected function parseResponse($jsonResponse)
    {
        return json_decode($jsonResponse, true);
    }

}