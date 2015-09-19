<?php namespace Versatile\Search\Contracts;

use IteratorAggregate;
use Countable;
use ArrayAccess;

/**
 * A Filter holds all filter expressions. Like the WHERE part of sql expressions
 *
 * The IteratorAggregate has to iterate over all added expressions in insertion
 * order.
 * The ArrayAccess has work with int indexes (insertion order) or string indexes
 * (column name/alias)
 **/
interface Filter extends Queryable, Countable, IteratorAggregate, ArrayAccess
{

    /**
     * Add an expression to this filter
     *
     * @param \Versatile\Search\Contracts\Expression $expression
     * @return self
     **/
    public function add(Expression $e);

    /**
     * Remove an expression to this filter
     *
     * @param \Versatile\Search\Contracts\Expression $expression
     * @return self
     * @throws \OutOfBoundsException
     **/
    public function remove(Expression $e);

    /**
     * Finds the index of expression or column name
     *
     * @param \Versatile\Search\Contracts\Expression|string
     * @return int
     * @throws \OutOfBoundsException
     **/
    public function indexOf($expressionOrColumn);

    /**
     * Removes all expressions from this filter
     *
     * @return self
     **/
    public function clear();

}