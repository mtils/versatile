<?php namespace Versatile\Search\Contracts;

interface Expression
{

    public function key();

    public function operator();

    public function value();

    public function bool();

}