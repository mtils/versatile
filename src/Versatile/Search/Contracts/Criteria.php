<?php namespace Versatile\Search\Contracts;

interface Criteria extends Queryable, Sortable
{

    public function modelClass();

    public function setModelClass($modelClass);

    public function filter();

    public function setFilter(Filter $filter);

}