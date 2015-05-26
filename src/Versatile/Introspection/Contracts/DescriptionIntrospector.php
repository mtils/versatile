<?php namespace Versatile\Introspection\Contracts;

interface DescriptionIntrospector extends TitleIntrospector
{

    /**
     * Returns a readable description of an object property.
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @return string The readable title
     **/
    public function keyDescription($class, $path);

    /**
     * Returns a readable description of an instance or instances of class $class
     *
     * @param string|object $class The class or an object of it
     * @param int $quantity (optional) The quantity (for singular/plural)
     * @return string A readable title of this object
     **/
    public function objectDescription($class, $quantity=1);

}