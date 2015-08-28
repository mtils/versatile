<?php namespace Versatile\Introspection\Contracts;


interface TypeIntrospector
{

    /**
     * Returns a xtype object for an object property
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @return \Xtype\AbstractType
     **/
    public function keyType($class, $path);

}