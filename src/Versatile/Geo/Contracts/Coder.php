<?php namespace Versatile\Geo\Contracts;

interface Coder
{

    /**
     * Retrieve the GeoCoordinate for address $address. You can pass a string
     * of a Address object.
     *
     * @param string|\Versatile\Geo\Contracts\Address $address
     * @param \Versatile\Geo\Contracts\BoundingArea $searchIn (optional)
     * @return \Versatile\Geo\Contracts\Coordinate
     **/
    public function geocode($address, BoundingArea $searchIn=null);

}