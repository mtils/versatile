<?php


namespace Versatile\Search\Contracts;


interface Queryable
{

    /**
     * The and connection between two expressions
     *
     * @return string
     **/
    const AND_ = 'and';

    /**
     * The or connection between two expressions
     *
     * @return string
     **/
    const OR_ = 'or';

    /**
     * Adds an expression to the search. If $value is null, the operator is
     * = and the $operator param will be interpreted as the value.
     *
     * If $operator is an array, it will assumed as:
     * where($key, 'in', $operator)
     *
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     **/
    public function where($key, $operator, $value=null, $boolean=self::AND_);

}