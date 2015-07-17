<?php namespace Versatile\Geo\Contracts;

interface BoundingCircle extends BoundingArea
{

    /**
     * @return \Versatile\Geo\Contracts\GeoCoordinate
     **/
    public function center();

    /**
     * The radius in meters
     *
     * @return float
     **/
    public function radius();

}