<?php namespace Versatile\Geo\Contracts;

interface Coordinate
{

    /**
     * @return float
     **/
    public function latitude();

    /**
     * @return float
     **/
    public function longitude();

    /**
     * @return float
     **/
    public function altitude();

    /**
     * Returns the epsg code of projection/coordinate format
     * (Google maps has 3857)
     *
     * @return int
     **/
    public function epsgCode();

}