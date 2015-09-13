<?php


namespace Versatile\Search\Contracts;


interface Sortable
{

    const ASC = 'asc';

    const DESC = 'desc';

    public function sorting();

    public function sort($key, $order=self::ASC);

    public function removeSort($key);

}