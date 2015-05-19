<?php namespace Versatile\Introspection;

use Versatile\Introspection\Contracts\PathIntrospector;
use Versatile\Query\Contracts\SyntaxParser;
use Versatile\Query\SyntaxParser as DefaultParser;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class EloquentPathIntrospector implements PathIntrospector
{

    protected $parser;

    protected $cache = [];

    public function __construct(SyntaxParser $parser=null)
    {
        $parser = $parser ?: new DefaultParser;
        $this->parser = $parser;
    }

     /**
     * {@inheritdoc}
     *
     * @param string|object $rootObject The root object
     * @param string $path The path relative from root
     * @return string classname of class related by $path
     **/
    public function classOfPath($rootObject, $path)
    {

        $rootObject = $this->rootInstance($rootObject);
        $cacheId = $this->getCacheId($rootObject, $path);

        if (isset($this->cache[$cacheId])) {
            return $this->cache[$cacheId];
        }

        $parts = $this->parser->splitPath($path);

        $partCount = count($parts);
        $last = $partCount-1;

        $currentObject = $rootObject;

        for ($i=0; $i<$partCount; $i++) {

            $part = $parts[$i];

            $methodName = '';

            if (method_exists($currentObject, $part)) {
                $methodName = $part;
            } else {
                $camelMethod = camel_case($part);
                if(method_exists($currentObject, $camelMethod)){
                    $methodName = $camelMethod;
                }
            }

            if(!$methodName){
                continue;
            }

            $relation = $currentObject->{$methodName}();

            if ($relation instanceof Relation) {

                if (!$model = $relation->getQuery()->getModel()) {
                    continue;
                }

                if($i != $last){
                    $currentObject = $model;
                    continue;
                }

                $this->cache[$cacheId] = get_class($model);
                return $this->cache[$cacheId];
            }

        }

        throw new UnexpectedValueException("$path does not point to an relation");
    }

    protected function rootInstance($rootObject){
        if(!is_object($rootObject)){
            return new $rootObject;
        }
        return $rootObject;
    }

    protected function getCacheId($model, $path)
    {
        $modelId = get_class($model);
        return "$modelId|$path";
    }

}