<?php


namespace Versatile\Search;

use XType\Casting\Contracts\InputCaster;
use XType\StringType;
use XType\AbstractType;
use Versatile\Search\Contracts\CriteriaBuilder;
use Illuminate\Http\Request;
use Versatile\Search\Contracts\Filter as FilterContract;
use Versatile\Search\Contracts\Criteria as CriteriaContract;
use Versatile\Search\Criteria;
use Versatile\Introspection\Contracts\TypeIntrospector;
use Versatile\View\Contracts\ModelPresenter;


/**
 * The FlatCriteriaBuilder builds criterias out of flat get arrays which doesnt
 * need to be encoded. This leads to readable get parameters but is not as
 * flexible as working with complete arrays.
 *
 * The main features are:
 *
 * Operators by $key-$operator. This class assumes you have no minus signs in
 * your model properties/columns.
 *
 * Supported operators:
 * greater:      >
 * less:         <
 * greaterEqual: >=
 * lessEqual:    <=
 * in:           IN (', ' separated value)
 * not:          <>
 * equals:       =
 * like:         like
 * between:      BETWEEN (' - ' separated value)
 *
 * So a query could look like this:
 * /users/?name-like=John&last_login-between=2015/21/05 - 2015/28/05
 *
 **/
class FlatCriteriaBuilder implements CriteriaBuilder
{

    public $operatorSeparator = '-';

    public $sortParam = 'sort';

    public $directionParam = 'order';

    public $fullTextParam = 'q';

    public $pageParam = 'page';

    /**
     * @var array
     **/
    protected $handlers = [];

    protected $caster;

    protected $types;

    protected $scaffold;

    /**
     * @var \Versatile\Search\Contracts\Criteria
     **/
    protected $criteriaPrototype;

    public function __construct(InputCaster $caster, TypeIntrospector $types,
                                ModelPresenter $scaffold, CriteriaContract $criteria=null)
    {
        $this->caster = $caster;
        $this->types = $types;
        $this->scaffold = $scaffold;
        $this->criteriaPrototype = $criteria ?: new Criteria;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $modelClass
     * @param array $parameters
     * @param string $contentType (optional)
     * @return \Versatile\Search\Contracts\Criteria
     **/
    public function criteria($modelClass, array $parameters, $contentType='text/html')
    {

        if ($this->hasHandler($contentType)) {
            return $this->callHandler($modelClass, $parameters, $contentType);
        }

        return $this->buildCriteria($modelClass, $parameters);

    }

    public function handleContentType($contentType, callable $handler)
    {
        $this->handlers[$contentType] = $handler;
        return $this;
    }

    public function hasHandler($contentType)
    {
        return isset($this->handlers[$contentType]);
    }

    public function setCriteriaPrototype(CriteriaContract $criteria)
    {
        $this->criteriaPrototype = $criteria;
        return $this;
    }

    protected function buildCriteria($modelClass, array $params)
    {

        $criteria = $this->newCriteria();

        $criteria->setModelClass($modelClass);

        $casted = $this->castInput($params);

        $this->addExpressions($modelClass, $criteria->filter(), $casted);

        $this->addSortIfPassed($criteria, $params);

        return $criteria;

    }

    protected function castInput($input)
    {
        return $this->caster->with('!nested','!type')->castInput($input);
    }

    protected function addExpressions($modelClass, FilterContract $filter, $casted)
    {
        foreach ($casted as $key=>$value) {
            if ($key == $this->sortParam || $key == $this->directionParam) {
                continue;
            }
            if ($key == $this->pageParam) {
                continue;
            }
            if ($key == $this->fullTextParam) {
                $this->addFullTextCriteria($modelClass, $filter, $value);
                continue;
            }
            if ($value === '') {
                continue;
            }
            $filter->add($this->toExpression($modelClass, $filter, $key, $value));
        }
    }

    protected function addFullTextCriteria($modelClass, $filter, $value)
    {

        $searchableKeys = $this->scaffold->searchableKeys($modelClass, ModelPresenter::VIEW_FULLTEXT);

        if (!$searchableKeys) {
            return;
        }

        foreach ($searchableKeys as $key) {
            $filter->where($key, 'like', "%$value%", 'or');
        }

    }

    protected function toExpression($modelClass, FilterContract $filter, $key, $value)
    {

        if (!$type = $this->types->keyType($modelClass, $key)) {
            $type = new StringType;
        }

        list($key, $operator) = $this->splitOperator($key);

        if (!$operator) {
            $operator = $this->getDefaultOperator($type, $value);
        }

        $castedValue = $type->castToModel($value);

        return new Expression($key, $operator, $this->formatForOperator($castedValue, $operator));

    }

    protected function splitOperator($key)
    {
        if (str_contains($key, $this->operatorSeparator)) {
            return explode($this->operatorSeparator, $key);
        }
        return [$key, ''];
    }

    protected function getDefaultOperator($type, $value)
    {
        switch ($type->getGroup()) {
            case AbstractType::STRING:
                return 'like';
            default:
                return '=';

        }
    }

    protected function formatForOperator($value, $operator)
    {
        if ($operator == 'like' && is_string($value)) {
            return str_replace('*','%', $value);
        }
        return $value;
    }

    protected function addSortIfPassed(CriteriaContract $criteria, array $params)
    {

        if (!isset($params[$this->sortParam])) {
            return;
        }

        $direction = isset($params[$this->directionParam]) ? $params[$this->directionParam] : 'asc';

        $direction = in_array($direction, ['asc','desc']) ? $direction : 'asc';

        $criteria->sort($params[$this->sortParam], $direction);

    }

    protected function callHandler($modelClass, $parameters, $contentType)
    {
        return call_user_func($this->handlers[$contentType], $modelClass, $parameters, $contentType);
    }

    protected function newCriteria()
    {
        return clone $this->criteriaPrototype;
    }

}