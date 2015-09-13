<?php


namespace Versatile\Search\Contracts;

/**
 * The search factory creates a search object out of a criteria.
 * The main parameter is criteria->modelClass, which would
 * normally be deferred to a distinct factory for this model class
 *
 **/
interface SearchFactory
{

    /**
     * Creates a search object out of $modelClass and $criteria
     *
     * @param \Versatile\Search\Contracts\Criteria $criteria
     * @return \Versatile\Search\Contracts\Search
     **/
    public function search(Criteria $criteria);

}
