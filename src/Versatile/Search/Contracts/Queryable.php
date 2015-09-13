<?php


namespace Versatile\Search\Contracts;


interface Queryable
{

    public function where($key, $operator=null, $value=null, $not=false);

}