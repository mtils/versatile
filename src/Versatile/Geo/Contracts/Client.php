<?php namespace Versatile\Geo\Contracts;

interface Client
{

    /**
     * Returns the response as an array. In case of google the params are:
     * getResponse('geocode',['q'=>'the address']) and the client will fire
     * a request to googles geocoding service with params $params. The response
     * should always be an array.
     *
     * @param string $method The method to call on the "WMS" server
     * @param array $params The parameters of the method
     * @return array The result
     **/
    public function fetch($method, array $params);

}