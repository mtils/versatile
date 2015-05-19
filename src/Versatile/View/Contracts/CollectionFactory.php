<?php namespace Versatile\View\Contracts;

interface CollectionFactory
{

    /**
     * Creates a view collection from $searchable with $params
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return \Collection\Collection
     **/
    public function create($searchable, array $params=[], $view='html');

    /**
     * Creates a paginated view collection from $searchable with $params
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return \Collection\Collection
     **/
    public function paginate($searchable, array $params=[], $view='html');

    /**
     * Check if this CollectionFactory can create a collection of $searchable
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return bool
     **/
    public function canCreate($searchable, array $params=[], $view='html');

}