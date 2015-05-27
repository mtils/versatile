<?php namespace Versatile\Search\Contracts;

interface CriteriaBuilder
{

    public function build($modelClass, array $params);

}