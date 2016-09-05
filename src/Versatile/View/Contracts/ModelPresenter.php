<?php


namespace Versatile\View\Contracts;

/**
 * A ModelPresenter helps to scaffold your models into an interface.
 * It returns shortnames for objects to display them in select boxes, returns
 * default keys for search results or searchableKeys for search forms
 *
 **/
interface ModelPresenter
{

    /**
     * The default view.
     * shortName(): name in select boxes, "logged in as $shortname" and so on
     * keys(): The keys in the main search result of this objects
     * searchableKeys(): The search form of the main search
     *
     * @var string
     **/
    const VIEW_DEFAULT = 'default';

    /**
     * The details view.
     * shortName(): Perhaps a longer name than default
     * keys(): The keys of a tabular object view
     * searchableKeys(): All possible searchable keys
     *
     * @var string
     **/
    const VIEW_DETAILS = 'details';

    /**
     * The preview view.
     * shortName(): A more verbose view to show it in an autocompleter or so
     * keys(): Again lik ein autocompleters
     * searchableKeys(): if someone types in an autocompleter, search within this keys
     *
     * @var string
     **/
    const VIEW_PREVIEW = 'preview';

    /**
     * Searchable keys in fulltext search
     *
     * @var string
     **/
    const VIEW_FULLTEXT = 'fulltext';

    /**
     * Return all available vars
     *
     * @var string
     **/
    const VIEW_ALL = 'all';

    /**
     * Returns an id for object $object. Makes it easier to populate inputs
     *
     * @param object $object
     * @return int|string
     **/
    public function id($object);

    /**
     * Returns a short name for object $object. You can pass a view to allow
     * different lengths of short names
     *
     * @param object $object
     * @param string $view (optional)
     * @return string
     **/
    public function shortName($object, $view=self::VIEW_DEFAULT);

    /**
     * Returns the displayed keys of an model. (For example id, email,
     * last_login for a user). Pass a view to distinguish between different views
     *
     * @param string $class
     * @param string $view (optional)
     * @return array Just an indexed array of key names ([id, name, address.street])
     **/
    public function keys($class, $view=self::VIEW_DEFAULT);

    /**
     * Returns all searchable keys if you show a search form. The search form
     * could be generated this way but more important a autocompleter will
     * search inside this keys.
     *
     * @param string $class
     * @param string $view (optional)
     * @return array Just an indexed array of key names ([id, name, address.street])
     **/
    public function searchableKeys($class, $view=self::VIEW_DEFAULT);

}
