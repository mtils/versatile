<?php


namespace Versatile\View;

use Versatile\View\Contracts\ModelPresenter;
use ReflectionClass;

class ModelPresenterRegistry implements ModelPresenter
{

    protected $idProviders = [];

    protected $shortNameProviders = [];

    protected $keyProviders = [];

    protected $searchableKeyProviders = [];

    protected $cache = [
        'id'                => [],
        'shortName'         => [],
        'keys'              => [],
        'searchableKeys'    => []
    ];

    /**
     * {@inheritdoc}
     *
     * @param object $object
     * @return int|string
     **/
    public function id($object)
    {
        if ($provider = $this->nearestForClass('id', get_class($object))) {
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
        if ($provider = $this->nearestForClass('shortName', get_class($object))) {
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
        if ($provider = $this->nearestForClass('keys', $class)) {
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
        if ($provider = $this->nearestForClass('searchableKeys', $class)) {
            return call_user_func($provider, $class, $view);
        }
        return [];
    }

    public function provideId($class, callable $provider)
    {
        $this->idProviders[$class] = $provider;
        return $this;
    }

    public function provideShortName($class, callable $provider)
    {
        $this->shortNameProviders[$class] = $provider;
        return $this;
    }

    public function provideKeys($class, callable $provider)
    {
        $this->keyProviders[$class] = $provider;
        return $this;
    }

    public function provideSearchableKeys($class, callable $provider)
    {
        $this->searchableKeyProviders[$class] = $provider;
        return $this;
    }

    protected function nearestForClass($type, $findClass)
    {

        if (isset($this->cache[$type][$findClass])) {
            return $this->cache[$type][$findClass];
        }

        $providers = $this->providers($type);

        if (!$nearest = $this->findNearestForClass($providers, $findClass)) {
            return;
        }

        $this->cache[$type][$findClass] = $nearest;

        return $nearest;

    }

    protected function &providers($type)
    {

        switch ($type) {
            case 'id':
                return $this->idProviders;
            case 'shortName':
                return $this->shortNameProviders;
            case 'keys':
                return $this->keyProviders;
            case 'searchableKeys':
                return $this->searchableKeyProviders;
        }

    }

    protected function findNearestForClass(&$providers, $findClass)
    {
        $all = $this->findAllForClass($providers, $findClass);

        if (!count($all)) {
            return;
        }

        if (count($all) == 1) {
            return array_values($all)[0];
        }

        foreach (static::classInheritance($findClass) as $parentClass) {
            if (isset($all[$parentClass]) ) {
                return $all[$parentClass];
            }
        }

    }

    protected function findAllForClass(&$providers, $findClass)
    {

        $all = [];

        foreach ($providers as $class=>$provider) {
            if (is_subclass_of($findClass, $class) || $findClass == $class) {
                $all[$class] = $provider;
            }
        }

        return $all;

    }

    protected static function classInheritance($object){

        $class = new ReflectionClass($object);
        $classNames = [$class->getName()];

        while($class = $class->getParentClass()){
            $classNames[] = $class->getName();
        }

        return $classNames;

    }

}