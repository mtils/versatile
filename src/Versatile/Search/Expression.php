<?php


namespace Versatile\Search;

use Versatile\Search\Contracts\Expression as ExpressionContract;
use Versatile\Search\Contracts\Queryable;

class Expression implements ExpressionContract
{

    protected $key;

    protected $operator;

    protected $value;

    protected $bool = Queryable::AND_;

    public function __construct($key=null, $operator=null, $value=null, $boolean=Queryable::AND_)
    {
        $this->where($key, $operator, $value, $boolean);
    }

    /**
     * Returns the key for this expression
     *
     * @return string
     **/
    public function key()
    {
        return $this->key;
    }

    /**
     * Returns the operator
     *
     * @return string
     **/
    public function operator()
    {
        return $this->operator;
    }


    /**
     * Returns the right part of the expression
     *
     * @return mixed
     **/
    public function value()
    {
        return $this->value;
    }

    /**
     * Returns the connection of this expression relative to the last added
     * expression can be and|or
     *
     * @return string
     **/
    public function bool()
    {
        return $this->bool;
    }

    /**
     * {@inheritdoc} Sets all values and returns itself
     *
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     **/
    public function where($key, $operator, $value=null, $boolean=Queryable::AND_)
    {

        if ($value !== null) {
            $this->key = $key;
            $this->operator = $operator;
            $this->value = $value;
            $this->bool = $boolean;
            return $this;
        }

        if (is_array($operator)) {
            $this->key = $key;
            $this->operator = 'in';
            $this->value = $operator;
            $this->bool = $boolean;
            return $this;
        }

        $this->key = $key;
        $this->operator = '=';
        $this->value = $operator;
        $this->bool = $boolean;
        return $this;

    }

}