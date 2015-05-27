<?php namespace Versatile\Search\Contracts;

use IteratorAggregate;
use Countable;

interface Filter extends Countable, IteratorAggregate
{

    public function add(Expression $e);

    public function remove(Expression $e);

    public function clear();

}