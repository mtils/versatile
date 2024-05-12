<?php


namespace Versatile\Search;

use Versatile\Search\Contracts\Queryable;
use Versatile\Search\Contracts\Sortable;


trait ForwardsToCriteria
{

    /**
     * @var \Versatile\Search\Contracts\Criteria
     **/
    protected $criteria;

    /**
     * Return the root model class name, so that types, titles, etc. can be
     * introspected
     *
     * @return string
     **/
    public function modelClass()
    {
        return $this->criteria->modelClass();
    }

    /**
     * Returns the criteria object
     *
     * @return \Versatile\Search\Contracts\Criteria
     */
    public function criteria()
    {
        return $this->criteria;
    }

    /**
     * {@inheritdoc} Forwards to criteria returns itself
     *
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     **/
    public function where($key, $operator, $value=null, $boolean=Queryable::AND_)
    {
        $this->criteria->where($key, $operator, $value, $boolean);
        return $this;
    }

    /**
     * Returns the sorting in a $key=>$direction array
     *
     * @return array
     **/
    public function sorting()
    {
        return $this->criteria->sorting();
    }

    /**
     * Adds a sort key
     *
     * @param string $key
     * @param string $direction
     * @return self
     **/
    public function sort($key, $order=Sortable::ASC)
    {
        $this->criteria->sort($key, $order);
        return $this;
    }

    /**
     * Removes a sort key (and direction)
     *
     * @param string $key
     * @return self
     **/
    public function removeSort($key)
    {
        $this->criteria->removeSort($key);
        return $this;
    }
}