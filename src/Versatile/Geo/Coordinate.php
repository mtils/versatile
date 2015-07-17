<?php namespace Versatile\Geo;

use Versatile\Geo\Contracts\Coordinate as CoordinateContract;

class Coordinate implements CoordinateContract
{

    protected $_latitude;

    protected $_longitude;

    protected $_altitude;

    protected $_epsgCode = 3857;

    public function __construct($lat, $lon, $altitude=0.0, $epsg=3857)
    {
        $this->setLatitude($lat);
        $this->setLongitude($lon);
        $this->setAltitude($altitude);
        $this->setEpsgCode($epsg);
    }

    /**
     * @return float
     **/
    public function latitude()
    {
        return $this->_latitude;
    }

    public function setLatitude($latitude)
    {
        $this->_latitude = $latitude;
        return $this;
    }

    /**
     * @return float
     **/
    public function longitude()
    {
        return $this->_longitude;
    }

    public function setLongitude($longitude)
    {
        $this->_longitude = $longitude;
        return $this;
    }

    /**
     * @return float
     **/
    public function altitude()
    {
        return $this->_altitude;
    }

    public function setAltitude($altitude)
    {
        $this->_altitude = $altitude;
        return $this;
    }

    /**
     * Returns the epsg code of projection/coordinate format
     * (Google maps has 3857)
     *
     * @return int
     **/
    public function epsgCode()
    {
        return $this->_epsgCode;
    }

    public function setEpsgCode($epsgCode)
    {
        $this->_epsgCode = $epsgCode;
        return $this;
    }

}