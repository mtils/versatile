<?php


namespace Versatile\Search;


use OutOfBoundsException;
use Versatile\Search\Contracts\SearchFactory;
use Versatile\Search\Contracts\Criteria as CriteriaContract;
use Collection\Support\FindsCallableByInheritance;


class ProxySearchFactory implements SearchFactory
{

    use FindsCallableByInheritance;

    /**
     * {@inheritdoc}
     *
     * @param \Versatile\Search\Contracts\Criteria $criteria
     * @return \Versatile\Search\Contracts\Search
     **/
    public function search(CriteriaContract $criteria)
    {

        if (!$factory = $this->nearestForClass($criteria->modelClass())) {
            throw new OutOfBoundsException('No factory for ' . $criteria->modelClass() . ' found');
        }

        return call_user_func($factory, $criteria);

    }

    /**
     * Assign a callable to create a search object for $modelClass
     *
     * @param string $class
     * @param callable $factory
     * @return self
     **/
    public function forModelClass($class, callable $factory)
    {
        return $this->addCallable($class, $factory);
    }
}
