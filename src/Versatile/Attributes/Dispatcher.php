<?php namespace Versatile\Attributes;


use OutOfBoundsException;
use Versatile\Attributes\Contracts\Provider;
use Illuminate\Database\Eloquent\Model;

class Dispatcher
{

    protected $providers = [];

    protected static $providerFactories = [];

    public function getProvider(Model $model, $key)
    {
        if (!isset($this->providers[$key])) {
            $this->providers[$key] = static::createProvider($model, $key);
        }

        return $this->providers[$key];

    }

    public static function extend($name, callable $factory)
    {
        static::$providerFactories[$name] = $factory;
    }

    public function setProvider($key, Provider $provider)
    {
        $this->providers[$key] = $provider;
    }

    public function getAttribute(Model $model, $key)
    {
        return $this->getProvider($model, $key)->getAttribute($model, $key);
    }

    public function setAttribute(Model $model, $key, $value)
    {
        return $this->getProvider($model, $key)->setAttribute($model, $key, $value);
    }

    public static function createProvider(Model $model, $key)
    {

        $virtualAttributes = $model->getVirtualAttributes();

        if (!isset($virtualAttributes[$key])) {
            throw new OutOfBoundsException("No virtual property set for key $key");
        }

        list($providerKey, $params) = static::splitConfiguration(
            $model->getVirtualAttributes()[$key]
        );

        if (!isset(static::$providerFactories[$providerKey])) {
            throw new OutOfBoundsException("No provider found for key $providerKey");
        }

        $factory = static::$providerFactories[$providerKey];

        return call_user_func_array($factory, $params);


    }

    public static function splitConfiguration($config)
    {
        $mainParts = explode(':', $config);

        if (count($mainParts) == 1) {
            return [$mainParts[0], []];
        }

        return [$mainParts[0], explode(',', $mainParts[1])];

    }

}