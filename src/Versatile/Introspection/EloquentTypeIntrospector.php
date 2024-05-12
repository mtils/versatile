<?php namespace Versatile\Introspection;

use UnexpectedValueException;
use Versatile\Introspection\Contracts\TypeIntrospector;
use Versatile\Introspection\Contracts\PathIntrospector;
use Versatile\Query\Contracts\SyntaxParser;
use XType\AbstractType;
use XType\TemporalType;


class EloquentTypeIntrospector implements TypeIntrospector
{

    protected $pathIntrospector;

    protected $parser;

    protected $manualKeyTypes = [];

    protected $defaultDateFormat = 'Y-m-d H:i:s';

    public function __construct(PathIntrospector $introspector,
                                SyntaxParser $parser){
        $this->pathIntrospector = $introspector;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @return \Xtype\AbstractType
     **/
    public function keyType($class, $path)
    {

        // Allows manual overwrites if titles
        if ($type = $this->getManualKeyType($this->getClassName($class),$path)) {
            return $type;
        }

        try{

            // First check if path is a related key (account.owner.id)
            if ($this->parser->isRelatedKey($path)) {

                list($join, $key) = $this->parser->toJoinAndKey($path);
                $className = $this->pathIntrospector->classOfPath($class, $join);

            } else { // No related key

                $className = $this->getClassName($class);
                $key = $path;

            }

            if ($type = $this->getManualKeyType($className, $key)) {
                return $type;
            }

            if ($this->isDate($className, $key)) {
                $type = new TemporalType();
                $type->setFormat($this->defaultDateFormat);
                return $type;
            }

        }
        catch(UnexpectedValueException $e){
            return;
        }

    }

    protected function getClassName($class){
        if(is_object($class)){
            return get_class($class);
        }
        else{
            return $class;
        }
    }

    public function setDefaultDateFormat($format)
    {
        $this->defaultDateFormat = $format;
        return $this;
    }

    /**
     * @param object|string $class
     * @param string $column
     * @return \Xtype\AbstractType
     **/
    public function getManualKeyType($class, $column){

        $class = ltrim($this->getClassName($class),'\\');
        if(isset($this->manualKeyTypes[$class.'|'.$column])){
            return $this->manualKeyTypes[$class.'|'.$column];
        }
    }

    public function setKeyType($class, $column, AbstractType $type){

        $class = ltrim($this->getClassName($class),'\\');
        $this->manualKeyTypes[$class.'|'.$column] = $type;
        return $this;

    }

    protected function isDate($class, $key)
    {
        $model = new $class;
        return in_array($key, $model->getDates());
    }

}
