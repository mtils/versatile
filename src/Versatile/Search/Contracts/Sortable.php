<?php


namespace Versatile\Search\Contracts;


interface Sortable
{

    const ASC = 'asc';

    const DESC = 'desc';

    /**
     * Returns the sorting in a $key=>$direction array
     *
     * @return array
     **/
    public function sorting();

    /**
     * Adds a sort key
     *
     * @param string $key
     * @param string $direction
     * @return self
     **/
    public function sort($key, $direction=self::ASC);

    /**
     * Removes a sort key (and direction)
     *
     * @param string $key
     * @return self
     **/
    public function removeSort($key);

}