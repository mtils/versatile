<?php namespace Versatile\Query\Contracts;

interface SyntaxParser
{

    /**
     * Returns if a key is a related key (e.g. \Contact->'adresse.id')
     *
     * @param string $key The key name
     * @return bool
     **/
    public function isRelatedKey($key);

    /**
     * Extracts the join "path" to a foreign object and the key
     * Without a join the join will be an empty string ('')
     * (e.g. Contact, address.id => ['adresse','id'],
     *       Contact, id => ['','id'],
     *       Contact, address.country.name => ['address.country','name']
     * )
     *
     * @param string $path Der Name der Eigenschaft
     * @return array Sowas: array('join.join','id')
     **/
    public function toJoinAndKey($path);

    /**
     * Splits a model "path" into its segments
     *
     * @param string $path The path, e.g. address.id
     * @return array
     **/
    public function splitPath($path);

    /**
     * The counterpart of splitPath. Builds a path by an array of segments
     *
     * @param array $parts The segments
     * @return string The path
     **/
    public function buildPath(array $parts);

}