<?php


namespace Versatile\View;

use Ems\Core\Patterns\ExtendableByClassHierarchyTrait;
use Versatile\Support\CallableContainer;
use Versatile\View\Contracts\ModelPresenter;

class ModelPresenterRegistry implements ModelPresenter
{

    use ExtendableByClassHierarchyTrait;

    /**
     * @var CallableContainer
     */
    protected $idContainer;

    /**
     * @var CallableContainer
     */
    protected $keyContainer;

    /**
     * @var CallableContainer
     */
    protected $shorNameContainer;

    /**
     * @var CallableContainer
     */
    protected  $searchableKeyContainer;

    public function __construct()
    {
        $this->idContainer = new CallableContainer();
        $this->keyContainer = new CallableContainer();
        $this->shorNameContainer = new CallableContainer();
        $this->searchableKeyContainer = new CallableContainer();
    }

    /**
     * {@inheritdoc}
     *
     * @param object $object
     * @return int|string
     **/
    public function id($object)
    {
        if ($provider = $this->idContainer->nearestForClass(get_class($object))) {
            return call_user_func($provider, $object);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @param object $object
     * @param string $view (optional)
     * @return string
     **/
    public function shortName($object, $view=self::VIEW_DEFAULT)
    {
        if ($provider = $this->shorNameContainer->nearestForClass(get_class($object))) {
            return call_user_func($provider, $object, $view);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     * @param string $view (optional)
     * @return array Just an indexed array of key names ([id, name, address.street])
     **/
    public function keys($class, $view=self::VIEW_DEFAULT)
    {
        if ($provider = $this->keyContainer->nearestForClass($class)) {
            return call_user_func($provider, $class, $view);
        }
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     * @param string $view (optional)
     * @return array Just an indexed array of key names ([id, name, address.street])
     **/
    public function searchableKeys($class, $view=self::VIEW_DEFAULT)
    {
        if ($provider = $this->searchableKeyContainer->nearestForClass($class)) {
            return call_user_func($provider, $class, $view);
        }
        return [];
    }

    /**
     * Add a callable which will handling getting the id for class $class
     *
     * @param string $class
     * @param callable $provider
     * @return self
     **/
    public function provideId($class, callable $provider)
    {
        $this->idContainer->extend($class, $provider);
        return $this;
    }

    /**
     * Add a callable which will handling getting the shortName for class $class
     *
     * @param string $class
     * @param callable $provider
     * @return self
     **/
    public function provideShortName($class, callable $provider)
    {
        $this->shorNameContainer->extend($class, $provider);
        return $this;
    }

    /**
     * Add a callable which will handling getting the keys for class $class
     *
     * @param string $class
     * @param callable $provider
     * @return self
     **/
    public function provideKeys($class, callable $provider)
    {
        $this->keyContainer->extend($class, $provider);
        return $this;
    }

    /**
     * Add a callable which will handling getting the searchableKeys for $class
     *
     * @param string $class
     * @param callable $provider
     * @return self
     **/
    public function provideSearchableKeys($class, callable $provider)
    {
        $this->searchableKeyContainer->extend($class, $provider);
        return $this;
    }

}