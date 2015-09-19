<?php


namespace Versatile\Search;

use Versatile\Search\Contracts\Criteria as CriteriaContract;
use Versatile\Search\Contracts\Queryable;
use Versatile\Search\Contracts\Sortable;
use Versatile\Search\Contracts\Filter as FilterContract;
use Versatile\Query\Builder;

trait ProvidesKeyInterface
{

    protected $keys = [];

    /**
     * Adds a key or many keys (columns) to the result.
     * Pass an array of keys or just one key or multiple (string) arguments
     *
     * @param array|string $key
     * @return self
     **/
    public function withKey($key)
    {
        $keys = func_num_args() > 1 ? func_get_args() : (array)$key;

        foreach ($keys as $key) {
            $this->keys[] = $key;
        }

        return $this;
    }

    /**
     * Returns all assigned keys (columns)
     *
     * @return array
     **/
    public function keys()
    {
        return $this->keys;
    }

    /**
     * Clears a key or many keys (columns). Pass a single key, an array of keys
     * or multiple (string) arguments
     *
     * @param array|string $key
     * @return self
     **/
    public function clearKey($key)
    {
        $keys = func_num_args() > 1 ? func_get_args() : (array)$key;

        foreach ($keys as $key) {
            $idx = array_search($key, $this->keys);
            if ($idx !== false) {
                unset($this->keys[$idx]);
            }
        }

        $this->keys = array_values($this->keys);
        return $this;

    }

    /**
     * Clears all keys and replaces em
     *
     * @return self
     **/
    public function replaceKeys($keys)
    {
        $this->keys = [];
        call_user_func_array([$this, 'withKey'], func_get_args());
        return $this;
    }

}