<?php namespace Versatile\Search\Contracts;

/**
 *  A Search object is a proxy object between typically your controller
 *  and your view. It assures that you do not put this logic into your 
controller.
 * A search object should never perform anything before get() or paginate() is
 * called. So you can pass the whole search object to your view and the view
 * will deceide which method will be called. (Autocompleters, Search Pages,
 * REST Interfaces can share the controllers this way)
 *
 * The intended sequence by the interfaces is:
 *
 * $criteria = CriteriaBuilder::criteria(Request::instance());
 * $search = SearchFactory::search($criteria);
 * $result = $search->get();
 *
 * The responsability of the CriteriaBuilder is: Build a criteria from some
 * input. (One builder for user filled forms, another one for apis,...)
 * The CriteriaBuilder casts all values to application usable types like
 * DateTime objects and floats instead of strings.
 *
 * SearchFactory: Create the right Search Object for a criteria
 *
 * Search: Take the (uniformed and every time same looking) criteria
 * objects and control a querybuilder or something similar to perform get its
 * results.
 *
 * The criteria objects should be serializable to store searches
 *
 *
**/
interface Search extends Queryable, Sortable, HoldsColumns
{


    /**
     * Get the complete result without pagination
     *
     * @return \Traversable
     **/
    public function get($columns=[]);

    /**
     * Get the paginated result
     *
     * @param $perPage (optional)
     * @return \Traversable
     **/
    public function paginate($columns=[], $perPage = null);

    /**
     * Return the root model class name, so that types, titles, etc. can be
     * introspected
     *
     * @return string
     **/
    public function modelClass();

}