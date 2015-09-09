<?php namespace Versatile\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Versatile\View\Contracts\ModelPresenter
 */
class Scaffold extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'versatile.model-presenter';
    }

}