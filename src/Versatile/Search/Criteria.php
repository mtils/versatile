<?php


namespace Versatile\Search;

use Versatile\Search\Contracts\Criteria as CriteriaContract;
use Versatile\Search\Contracts\Queryable;
use Versatile\Search\Contracts\Sortable;
use Versatile\Search\Contracts\Filter as FilterContract;


class Criteria implements CriteriaContract
{

    protected $modelClass = '';

    protected $filter;

    protected $sorting = [];

    public function __construct($modelClass='', FilterContract $filter=null)
    {
        $this->modelClass = $modelClass;
        $this->filter = $filter ?: new Filter;
    }

    public function modelClass()
    {
        return $this->modelClass;
    }

    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function filter()
    {
        return $this->filter;
    }

    public function setFilter(FilterContract $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * {@inheritdoc} Forwards to filter returns itself
     *
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     **/
    public function where($key, $operator, $value=null, $boolean=Queryable::AND_)
    {
        $this->filter->where($key, $operator, $value, $boolean);
        return $this;
    }

    public function sorting()
    {
        return $this->sorting;
    }

    public function sort($key, $order=Sortable::ASC)
    {
        $this->sorting[$key] = $order;
        return $this;
    }

    public function removeSort($key)
    {
        unset($this->sorting[$key]);
        $this->sorting = array_values($this->sorting);
        return $this;
    }

}