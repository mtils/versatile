<?php namespace Versatile\Search\Contracts;

interface Criteria
{

    const ASC = 'asc';

    const DESC = 'desc';

    public function modelClass();

    public function setModelClass($modelClass);

    public function filter();

    public function setFilter(Filter $filter);

    public function sorting();

    public function sort($key, $order=self::ASC);

    public function removeSort($key);

    public function where($key, $operator, $value=null);

}