<?php namespace Versatile\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Versatile\View\Contracts\CollectionFactory
 */
class Listing extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'versatile.view-collection-factory';
    }

}