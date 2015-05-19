<?php namespace Versatile\Introspection\Contracts;

interface PathIntrospector
{

    /**
     * Returns the class of path $path relative to $rootObject
     * e.g: $rootObject '\App\User'
     *      $path 'category.parent'
     *      result: '\App\UserCategory'
     *
     * @param string|object $rootObject The root object
     * @param string $path The path relative from root
     * @return string classname of class related by $path
     **/
    public function classOfPath($rootObject, $path);

}