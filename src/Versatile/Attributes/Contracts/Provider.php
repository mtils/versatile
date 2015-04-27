<?php namespace Versatile\Attributes\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Provider
{

    public function getAttribute(Model $model, $key);

    public function setAttribute(Model $model, $key, $value);

}