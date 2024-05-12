<?php


namespace Versatile\Search;

use ReturnTypeWillChange;
use Versatile\Search\Contracts\Filter as FilterContract;
use Versatile\Search\Contracts\Expression as ExpressionContract;
use Versatile\Search\Contracts\Queryable;

use ArrayIterator;
use OutOfBoundsException;


class Filter implements FilterContract
{

    /**
     * The added expressions
     *
     * @var array
     **/
    protected $expressions = [];

    /**
     * @var \Versatile\Search\Contracts\Expression
     **/
    protected $expressionPrototype;

    public function __construct(ExpressionContract $expressionPrototype=null)
    {
        $this->expressionPrototype = $expressionPrototype ?: new Expression;
    }

    /**
     * Add an expression to this filter
     *
     * @param \Versatile\Search\Contracts\Expression $expression
     * @return self
     **/
    public function add(ExpressionContract $e)
    {
        $this->expressions[] = $e;
        return $this;
    }

    /**
     * Remove an expression to this filter
     *
     * @param \Versatile\Search\Contracts\Expression $expression
     * @return self
     * @throws \OutOfBoundsException
     **/
    public function remove(ExpressionContract $e)
    {
        unset($this->expressions[$this->indexOf($e)]);
        $this->expressions = array_values($this->expressions);
        return $this;
    }

    /**
     * Finds the index of expression or column name
     *
     * @param \Versatile\Search\Contracts\Expression|string
     * @return int
     * @throws \OutOfBoundsException
     **/
    public function indexOf($expressionOrColumn)
    {
        if ($expressionOrColumn instanceof ExpressionContract) {
            return $this->indexOfExpression($expressionOrColumn);
        }
        return $this->indexOfColumn($expressionOrColumn);
    }

    /**
     * Removes all expressions from this filter
     *
     * @return self
     **/
    public function clear()
    {
        $this->expressions = [];
    }

    /**
     * Returns the amount of added expressions
     *
     * @return int
     **/
    public function count()
    {
        return count($this->expressions);
    }

    /**
     * Allows simple walking through foreach
     *
     * @return \ArrayIterator
     **/
    #[ReturnTypeWillChange] public function getIterator()
    {
        return new ArrayIterator($this->expressions);
    }

    /**
     * Checks if an expression exists at position $indexOrColumn or if an
     * expression was added vor column $indexOrColumn
     *
     * @param string|int $indexOrColumn
     * @return bool
     **/
    #[ReturnTypeWillChange] public function offsetExists($indexOrColumn)
    {

        if (is_numeric($indexOrColumn)) {
            return isset($this->expressions[(int)$indexOrColumn]);
        }

        try {
            return is_int($this->indexOfColumn($indexOrColumn));
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    #[ReturnTypeWillChange] public function offsetGet($indexOrColumn)
    {

        if (is_numeric($indexOrColumn) && isset($this->expressions[(int)$indexOrColumn])) {
            return $this->expressions[(int)$indexOrColumn];
        }

        return $this->expressions[$this->indexOf($indexOrColumn)];
    }

    #[ReturnTypeWillChange] public function offsetSet($indexOrColumn, $value)
    {
        if (is_numeric($indexOrColumn)) {
            $this->expressions[(int)$indexOrColumn] = $value;
        }

        try {
            $this->expressions[$this->indexOf($indexOrColumn)] = $value;
        } catch (OutOfBoundsException $e) {
            $this->add($value);
        }
    }

    #[ReturnTypeWillChange] public function offsetUnset($indexOrColumn)
    {
        if (is_numeric($indexOrColumn)) {
            unset($this->expressions[(int)$indexOrColumn]);
            $this->expressions = array_values($this->expressions);
        }

        try {
            unset($this->expressions[$this->indexOf($indexOrColumn)]);
        } catch (OutOfBoundsException $e) {}

    }

    protected function indexOfExpression(ExpressionContract $e)
    {

        $index = array_search($e, $this->expressions, true);

        if ($index === false) {
            throw new OutOfBoundsException('Expression not found');
        }

        return $index;

    }

    public function indexOfColumn($column)
    {
        foreach ($this->expressions as $index=>$expression) {
            if ($expression->key() == $column) {
                return $index;
            }
        }
        throw new OutOfBoundsException("Column $column not found in filter");
    }

    /**
     * {@inheritdoc} Adds a new expression and returns itself
     *
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     **/
    public function where($key, $operator, $value=null, $boolean=Queryable::AND_)
    {
        return $this->add($this->newExpression($key, $operator, $value, $boolean));
    }

    protected function newExpression($key, $operator, $value, $boolean)
    {
        $expression = clone $this->expressionPrototype;
        return $expression->where($key, $operator, $value, $boolean);
    }

}