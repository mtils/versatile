<?php namespace Versatile\Attributes;

trait UsesVirtualAttributes
{

    protected static $virtualAttributDispatchers = [];

    public function getAttribute($key)
    {
        if ($this->hasVirtualAttribute($key)) {
            return $this->getVirtualAttributeValue($key);
        }
        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasVirtualAttribute($key)) {
            return $this->setVirtualAttributeValue($key, $value);
        }
        return parent::setAttribute($key, $value);
    }

    public function hasVirtualAttribute($key)
    {
        return isset($this->virtualAttributes[$key]);
    }

    public function getVirtualAttributeValue($key)
    {
        return static::getVirtualAttributeDispatcher($key)
                       ->getAttribute($this, $key);
    }

    public function setVirtualAttributeValue($key, $value)
    {
        return static::getVirtualAttributeDispatcher($key)
                       ->setAttribute($this, $key, $value);
    }

    public function getVirtualAttributes()
    {
        return $this->virtualAttributes;
    }

    public static function getVirtualAttributeDispatcher()
    {
        $class = get_called_class();

        if (!isset(static::$virtualAttributDispatchers[$class])) {
            static::$virtualAttributDispatchers[$class] = new Dispatcher();
        }

        return static::$virtualAttributDispatchers[$class];
    }

    public static function setVirtualAttributeDispatcher(Dispatcher $dispatcher)
    {
        $class = get_called_class();
        static::$virtualAttributDispatchers[$class] = $dispatcher;
    }

}