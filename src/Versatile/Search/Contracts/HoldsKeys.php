<?php


namespace Versatile\Search\Contracts;


interface HoldsKeys
{

    /**
     * Adds a key or many keys (columns) to the result.
     * Pass an array of keys or just one key or multiple (string) arguments
     *
     * @param array|string $key
     * @return self
     **/
    public function withKey($key);

    /**
     * Returns all assigned keys (columns)
     *
     * @return array
     **/
    public function keys();

    /**
     * Clears a key or many keys (columns). Pass a single key, an array of keys
     * or multiple (string) arguments
     *
     * @param array|string $key
     * @return self
     **/
    public function clearKey($key);

    /**
     * Clears all keys and replaces em
     *
     * @return self
     **/
    public function replaceKeys($keys);

};