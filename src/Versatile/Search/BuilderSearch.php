<?php


namespace Versatile\Search;

use Versatile\Search\Contracts\Search;
use Versatile\Search\Contracts\Criteria as CriteriaContract;
use Versatile\Search\Contracts\Queryable;
use Versatile\Search\Contracts\Sortable;
use Versatile\Search\Contracts\Filter as FilterContract;
use Versatile\Query\Builder;


class BuilderSearch implements Search
{

    use ProvidesKeyInterface;
    use ForwardsToCriteria;

    protected $builder;

    protected $queryListener;

    protected $wasBuilded = false;

    public function __construct(Builder $builder, CriteriaContract $criteria)
    {
        $this->builder = $builder;
        $this->criteria = $criteria;
        $this->queryListener = function($query){};
    }

    /**
     * Get the complete result without pagination
     *
     * @param $keys (optional)
     * @return \Traversable
     **/
    public function get($keys=[])
    {
        $this->buildIfNotBuilded();
        $keys = $keys == [] ? $this->keys() : $keys;

        if (!$keys) {
            return $this->builder->get();
        }

        return $this->builder->withColumns($keys)->get($keys);
    }

    /**
     * Get the paginated result
     *
     * @param array $keys (optional)
     * @param $perPage (optional)
     * @return \Traversable
     **/
    public function paginate($keys=[], $perPage = null)
    {
        $this->buildIfNotBuilded();
        $keys = $keys == [] ? $this->keys() : $keys;

        if (!$keys) {
            return $this->builder->paginate($perPage);
        }

        return $this->builder->withColumns($keys)->paginate($perPage);

    }

    public function onBuilding(callable $listener)
    {
        $this->queryListener = $listener;
        return $this;
    }

    protected function buildIfNotBuilded()
    {
        if ($this->wasBuilded) {
            return;
        }

        call_user_func($this->queryListener, $this->builder);

        $this->appendWheresToBuilder();
        $this->appendSortingToBuilder();
    }

    protected function appendWheresToBuilder()
    {
        foreach ($this->criteria->filter() as $expression) {
            $this->builder->where(
                $expression->key(),
                $expression->operator(),
                $expression->value(),
                $expression->bool()
            );
        }
    }

    protected function appendSortingToBuilder()
    {
        foreach ($this->criteria->sorting() as $key=>$direction) {
            if (trim($key) != '') {
                $this->builder->orderBy($key, $direction);
            }
        }
    }
}
