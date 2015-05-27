<?php namespace Versatile\Search\Contracts;

/**
 *  A Search object is a proxy object between typically your controller
 *  and your view. It accepts parameters and is responsible to cast, rename, ...
 *  them. A Search should not be performed before get() or paginate was called
 *  Its the best to get the results later. Than you have a chance to output the
 *  Result in different formats (like excel)
 **/
interface Search
{

    /**
     * Return the (casted|renamed) parameters
     *
     * @return array
     **/
    public function getParams();

    /**
     * Set the search parameter. Contains all it needs: filters, order, page,..
     *
     * @param array $params
     * @return static
     **/
    public function setParams(array $params);

    /**
     * Get the complete result without pagination
     *
     * @return \Traversable
     **/
    public function get();

    /**
     * Get the paginated result
     *
     * @param $perPage (optional)
     * @return \Traversable
     **/
    public function paginate($perPage = null);

    /**
     * Return the root model class name, so that types, titles, etc. can be
     * introspected
     *
     * @return string
     **/
    public function modelClass();

    /**
     * Return an array of column names. Names have to be the plain
     * (not readable) Model keys
     *
     * @return array
     **/
    public function columnNames();

}