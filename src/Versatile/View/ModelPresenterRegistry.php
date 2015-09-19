<?php


namespace Versatile\View;

use Versatile\View\Contracts\ModelPresenter;
use Collection\Support\FindsCallableByInheritance;
use ReflectionClass;

class ModelPresenterRegistry implements ModelPresenter
{

    use FindsCallableByInheritance;

    /**
     * {@inheritdoc}
     *
     * @param object $object
     * @return int|string
     **/
    public function id($object)
    {
        if ($provider = $this->nearestForClass(get_class($object), 'id')) {
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
        if ($provider = $this->nearestForClass(get_class($object), 'shortName')) {
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
        if ($provider = $this->nearestForClass($class, 'keys')) {
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
        if ($provider = $this->nearestForClass($class, 'searchableKeys')) {
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
        $this->addCallable($class, $provider, 'id');
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
        $this->addCallable($class, $provider, 'shortName');
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
        $this->addCallable($class, $provider, 'keys');
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
        $this->addCallable($class, $provider, 'searchableKeys');
        return $this;
    }

}