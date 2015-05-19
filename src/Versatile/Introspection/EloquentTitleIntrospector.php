<?php namespace Versatile\Introspection;

use UnexpectedValueException;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Signal\NamedEvent\BusHolderTrait;
use Versatile\Introspection\Contracts\TitleIntrospector;
use Versatile\Introspection\Contracts\PathIntrospector;
use Versatile\Query\Contracts\SyntaxParser;



class EloquentTitleIntrospector implements TitleIntrospector
{

    use BusHolderTrait;

    public $baseLangKey = 'models';

    protected $translator;

    protected $pathIntrospector;

    protected $syntaxParser;

    protected $manualKeyTitles = [];

    protected $manualObjectTitles = [];

    public function __construct(Translator $translator,
                                PathIntrospector $introspector,
                                SyntaxParser $parser){
        $this->translator = $translator;
        $this->pathIntrospector = $introspector;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|object $class The class or an object of it
     * @param string $key A key name. Can be dotted like address.street.name
     * @return string The readable title
     **/
    public function keyTitle($class, $path)
    {

        // Allows manual overwrites if titles
        if ($title = $this->getManualKeyTitle($this->getClassName($class),$path)) {
            return $title;
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

            if ($title = $this->getManualKeyTitle($className, $key)) {
                return $title;
            }

            $langKey = $this->key2QualifiedLangKey($className, $key);
            $title = $this->translator->get($langKey);

            // If a translation was found return it
            if($title != $langKey){
                return $title;
            }

        }
        catch(UnexpectedValueException $e){
            return $path;
        }

        // If no translation was found try to return an object title
        // (e.g. $class=User, $key=address if address is an object
        try{
            return $this->objectTitle($this->pathIntrospector->classOfPath($class, $path));
        }
        catch(UnexpectedValueException $e){
            return $path;
        }

        return $title;
    }

    /**
     * {@inheritdoc}
     *
     * @param string|object $class The class or an object of it
     * @param int $quantity (optional) The quantity (for singular/plural)
     * @return string A readable title of this object
     **/
    public function objectTitle($class, $quantity=1){
        $langKey = $this->model2LangKey($this->getClassName($class));
        return $this->translator->choice("{$this->baseLangKey}.$langKey.name",$quantity);
    }

    /**
     * {@inheritdoc}
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @param string An enum value. Preferable strings to allow easy translations
     * @return string A readable title of this enum value
     **/
    public function enumTitle($class, $path, $value){

        $langClass = $this->model2LangKey($class);
        $langKey = "{$this->baseLangKey}.$langClass.enums.$path.$value";

        return $this->translator->get($langKey);

    }

    public function model2LangKey($modelName){
        $matches = array();
        if (preg_match('@\\\\([\w]+)$@', $modelName, $matches)) {
            $modelName = $matches[1];
        }
        return snake_case($modelName);
    }

    public function key2QualifiedLangKey($className, $key)
    {
        $langClass = $this->model2LangKey($className);
        return "{$this->baseLangKey}.$langClass.fields.$key";
    }

    protected function getClassName($class){
        if(is_object($class)){
            return get_class($class);
        }
        else{
            return $class;
        }
    }

    public function getManualObjectTitle($class,$quantity=1){
        if(isset($this->manualObjectTitles[$class])){
            return $this->manualObjectTitles[$class];
        }
    }

    public function setObjectTitle($class, $title, $quantity=1){
        $this->manualObjectTitles[$class] = $title;
        return $this;
    }

    public function getManualKeyTitle($class, $column){

        $this->fireOnce('title-introspector.load',[$this]);

        $class = ltrim($this->getClassName($class),'\\');
        if(isset($this->manualKeyTitles[$class.'|'.$column])){
            return $this->manualKeyTitles[$class.'|'.$column];
        }
    }

    public function setKeyTitle($class, $column, $title){

        $class = ltrim($this->getClassName($class),'\\');
        $this->manualKeyTitles[$class.'|'.$column] = $title;
        return $this;

    }

}