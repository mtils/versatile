<?php namespace Versatile\Introspection;

use UnexpectedValueException;
use Illuminate\Translation\Translator;
use Versatile\Introspection\Contracts\TitleIntrospector;
use Versatile\Introspection\Contracts\PathIntrospector;
use Versatile\Query\Contracts\SyntaxParser;



class EloquentTitleIntrospector implements TitleIntrospector
{

    public $baseLangKey = 'models';

    protected $translator;

    protected $pathIntrospector;

    protected $syntaxParser;

    protected $manualKeyTitles = [];

    protected $manualObjectTitles = [];

    protected $namespaces = [];

    protected $modelToLangName = [];

    public function __construct(Translator $translator,
                                PathIntrospector $introspector,
                                SyntaxParser $parser)
    {
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
    public function objectTitle($class, $quantity=1)
    {
        return $this->translator->choice(
            $this->langKeyPrefix($this->getClassName($class)).".name",
            $quantity
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param string|object $class The class or an object of it
     * @param string $path A key name. Can be dotted like address.street.name
     * @param string An enum value. Preferable strings to allow easy translations
     * @return string A readable title of this enum value
     **/
    public function enumTitle($class, $path, $value)
    {
        return $this->translator->get(
            $this->langKeyPrefix($class).".enums.$path.$value"
        );
    }

    /**
     * Returns the langClassName of $modelName
     *
     * @param string $modelName
     * @return string
     **/
    public function model2LangKey($modelName)
    {
        if (!is_string($modelName)) {
            $modelDisplay = is_object($modelName) ? get_class($modelName) : gettype($modelName);
            throw new \UnexpectedValueException("modelName has to be string not $modelDisplay");
        }

        if (isset($this->modelToLangName[$modelName])) {
            return $this->modelToLangName[$modelName];
        }

        $matches = [];

        if (preg_match('@\\\\([\w]+)$@', $modelName, $matches)) {
            $modelName = $matches[1];
        }

        return snake_case($modelName);
    }

    /**
     * Returns the translation key for the keyTitle translation
     *
     * @param string $className
     * @param string $key
     * @return string
     **/
    public function key2QualifiedLangKey($className, $key)
    {
        return $this->langKeyPrefix($className) . ".fields.$key";
    }

    /**
     * Returns the manually setted objectTitle
     * @see self::setObjectTitle
     *
     * @param string $class
     * @param int $quantity
     * @return string|null
     **/
    public function getManualObjectTitle($class, $quantity=1)
    {
        if(isset($this->manualObjectTitles[$class])) {
            return $this->manualObjectTitles[$class];
        }
    }

    /**
     * Set a manual object title. Bypasses all internal handling. You have to
     * handle translations yourself
     *
     * @param string $class
     * @param string $title
     * @param int $quantity (optional)
     * @return self
     **/
    public function setObjectTitle($class, $title, $quantity=1)
    {
        $this->manualObjectTitles[$class] = $title;
        return $this;
    }

    /**
     * Return a manually overwritten title for $class->$column
     *
     * @param string $class
     * @param string $column
     * @return string
     **/
    public function getManualKeyTitle($class, $column)
    {

        $class = ltrim($this->getClassName($class),'\\');
        if(isset($this->manualKeyTitles[$class.'|'.$column])){
            return $this->manualKeyTitles[$class.'|'.$column];
        }
    }

    /**
     * Set a manual key title. Overwrites the translated titles, so you have to
     * handle translation yourself
     *
     * @param string|object $class
     * @param string $column
     * @param string $title
     * @return self
     **/
    public function setKeyTitle($class, $column, $title)
    {

        $class = ltrim($this->getClassName($class),'\\');
        $this->manualKeyTitles[$class.'|'.$column] = $title;
        return $this;

    }

    /**
     * Return the namespace for class with $langClassName
     * @see self::setLangNameSpace
     *
     * @param string
     * @return string
     **/
    public function getLangNamespace($langClassName)
    {
        return isset($this->namespaces[$langClassName]) ? $this->namespaces[$langClassName] : '';
    }

    /**
     * Set a namespace for a _langClassName_. So you can prefix project with
     * your project package prefix. setLangNameSpace('project', 'projects')
     * The resulting key will be "projects::project.models"
     *
     * @param string $langClassName
     * @param string $namespace (Without colons)
     * @return self
     **/
    public function setLangNameSpace($langClassName, $namespace)
    {
        $this->namespaces[$langClassName] = $namespace;
        return $this;
    }

    /**
     * Manually map a class to a model name. This is needed if you overwrite
     * a class like User to ExtendedUser. So instead of copying your lang entry
     * for extended_user you can map App\ExtendedUser to user
     *
     * @param string $modelName The classname you want to map
     * @param string $langName The name of that class inside lang files
     * @return void
     **/
    public function mapModelToLangName($modelName, $langName)
    {
        $modelName = $this->getClassName($modelName);
        $this->modelToLangName[$modelName] = $langName;
    }

    /**
     * Return the complete prefix for $className ($namespace.$baseLangKey.$langClass)
     *
     * @param string $className
     * @return string
     **/
    protected function langKeyPrefix($className)
    {

        $langClass = $this->model2LangKey($className);

        if ($namespace = $this->getLangNamespace($langClass)) {
            return "$namespace::{$this->baseLangKey}.$langClass";
        }

        return "{$this->baseLangKey}.$langClass";
    }

    /**
     * Returns the passed value if it is not an object and the class if an
     * object was passed
     *
     * @param object|string $class
     * @return string The classname
     **/
    protected function getClassName($class)
    {
        if(is_object($class)){
            return get_class($class);
        }
        else{
            return $class;
        }
    }

}
