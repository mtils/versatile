<?php namespace Versatile\Search\Contracts;

interface CriteriaBuilder
{

    /**
     * Build a criteria for $request. Request can be anything
     *
     * @param string $modelClass
     * @param array $parameters
     * @param string $contentType (optional)
     * @return Versatile\Search\Contracts\Criteria
     **/
    public function criteria($modelClass, array $parameters, $contentType='text/html');

}