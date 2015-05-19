<?php namespace Versatile\Introspection\Contracts;

interface TitleIntrospector
{

    /**
     * Returns a readable title of a object property
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @return string The readable title
     **/
    public function keyTitle($class, $path);

    /**
     * Returns a readable title of an instance or instances of class $class
     *
     * @param string|object $class The class or an object of it
     * @param int $quantity (optional) The quantity (for singular/plural)
     * @return string A readable title of this object
     **/
    public function objectTitle($class, $quantity=1);

    /**
     * Returns a readable title of an enum value of property $key of class $class
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @param string An enum value. Preferable strings to allow easy translations
     * @return string A readable title of this enum value
     **/
    public function enumTitle($class, $path, $value);

}