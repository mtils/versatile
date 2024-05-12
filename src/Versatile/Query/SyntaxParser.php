<?php namespace Versatile\Query;


use Versatile\Query\Contracts\SyntaxParser as ParserInterface;

/**
 * This is a little helper class for better testing
 **/
class SyntaxParser implements ParserInterface
{

    /**
     * @var string
     **/
    public $joinDelimiter = '.';

    /**
     * @var string
     **/
    public $keyDelimiter = '.';

    /**
     * @param string $joinDelimiter
     * @param string $keyDelimiter
     **/
    public function __construct($joinDelimiter='.', $keyDelimiter='.')
    {
        $this->joinDelimiter = $joinDelimiter;
        $this->keyDelimiter = $keyDelimiter;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key The key name
     * @return bool
     **/
    public function isRelatedKey($key)
    {
        return str_contains($key, $this->keyDelimiter);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $path Der Name der Eigenschaft
     * @return array Sowas: array('join.join','id')
     **/
    public function toJoinAndKey($path)
    {

        if(!$this->isRelatedKey($path)){
            return ['', $path];
        }

        $stack = $this->splitPath($path);

        $prop = array_pop($stack);

        return [implode($this->joinDelimiter, $stack), $prop];

    }

    /**
     * {@inheritdoc}
     *
     * @param string $path The path, e.g. address.id
     * @return array
     **/
    public function splitPath($path)
    {
        return explode($this->joinDelimiter, $path);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $parts The segments
     * @return string The path
     **/
    public function buildPath(array $parts)
    {
        return implode($this->joinDelimiter, $parts);
    }

}