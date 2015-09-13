<?php


namespace Versatile\Search\Contracts;


interface HoldsColumns
{

    public function withColumn($column);
    public function columns();
    public function clearColumn($column);

};