<?php namespace Versatile\Search\Contracts;

interface CriteriaBuilder
{

    /**
     * Build a criteria for $request. Request can be anything
     *
     * @param mixed $request
     * @return Versatile\Search\Contracts\Criteria
     **/
    public function criteria($request);

    /**
     * Dependency chain method
     *
     * @param mixed $request
     * @return bool
     **/
    public function canHandle($request);

}