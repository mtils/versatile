<?php namespace Versatile\Search\Contracts;

interface Expression extends Queryable
{

    /**
     * Returns the key for this expression
     *
     * @return string
     **/
    public function key();

    /**
     * Returns the operator
     *
     * @return string
     **/
    public function operator();

    /**
     * Returns the right part of the expression
     *
     * @return mixed
     **/
    public function value();

    /**
     * Returns the connection of this expression relative to the last added
     * expression can be and|or
     *
     * @return string
     **/
    public function bool();

}