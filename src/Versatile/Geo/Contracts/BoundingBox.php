<?php namespace Versatile\Geo\Contracts;

interface BoundingBox extends BoundingArea
{

    /**
     * @return \Versatile\Geo\Contracts\GeoCoordinate
     **/
    public function topLeft();

    /**
     * @return \Versatile\Geo\Contracts\GeoCoordinate
     **/
    public function topRight();

    /**
     * @return \Versatile\Geo\Contracts\GeoCoordinate
     **/
    public function bottomLeft();

    /**
     * @return \Versatile\Geo\Contracts\GeoCoordinate
     **/
    public function bottomRight();

}