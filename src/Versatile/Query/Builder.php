<?php namespace Versatile\Query;

use Versatile\Query\Contracts\SyntaxParser;
use Versatile\Query\SyntaxParser as DefaultParser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use UnderflowException;
use App;

class Builder
{

    protected $parser;

    protected $introspector;

    protected $model;

    protected $query;

    protected $joinClasses = [];

    protected $joinTables = [];

    protected $joinAliases = [];

    protected $relationNs = 'Illuminate\Database\Eloquent\Relations\\';

    protected $columns;

    protected $onlyQueryColumns = [];

    public function __construct(Model $model){
        $this->model = $model;
        $this->query = $model->newQuery();
        $this->parser = new DefaultParser;
    }

    public function with($relations){
        $this->query->with($relations);
        return $this;
    }

    public function withColumn($column){

        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $column) {

            $this->columns[] = $column;

            list($join, $column) = $this->toJoinAndKey($column);

            if($join){
                $this->with($join);
                $this->addJoinOnce($this->model, $join);
            }
        }

        return $this;
    }

    public function withColumns($columns){
        return $this->withColumn($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'){

        if($this->isRelatedKey($column)){

            list($join, $property) = $this->toJoinAndKey($column);

            if($join){

                $this->addJoinOnce($this->model, $join);

                $this->query->where($this->joinColumn($join, $property), $operator, $value, $boolean);

            }

        }
        else{
            $table = $this->model->getTable();
            $this->query->where("$table.$column", $operator, $value, $boolean);

        }

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed   $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc'){

        if($this->isRelatedKey($column)){

            list($join, $property) = $this->toJoinAndKey($column);

            if($join){

                $this->addJoinOnce($this->model, $join);

                $this->query->orderBy($this->joinColumn($join, $property), $direction);

            }

        }
        else{

            $this->query->orderBy($column, $direction);

        }

        return $this;

    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $value
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function skip($value){
        $this->query->skip($value);
        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function limit($value){
        $this->query->limit($value);
        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param  int  $value
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function take($value){
        return $this->limit($value);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key The key name
     * @return bool
     **/
    public function isRelatedKey($key)
    {
        return $this->parser->isRelatedKey($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $path Der Name der Eigenschaft
     * @return array Sowas: array('join.join','id')
     **/
    public function toJoinAndKey($path)
    {
        return $this->parser->toJoinAndKey($path);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $parts The segments
     * @return string The path
     **/
    public function buildPath(array $parts)
    {
        return $this->parser->buildPath($parts);
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function setParser(SyntaxParser $parser)
    {
        $this->parser = $parser;
        return $this;
    }

    public function getRelation($model, $name){

        if(!method_exists($model, $name)){
            throw new UnderflowException("Method $name does not exists on model " . get_class($this->model));
        }

        $relation = $model->{$name}();

        return $relation;

    }

    public function addJoinOnce($model, $name){

        if (!isset($this->joinClasses[$name])) {

            $relation = $this->getRelation($this->model, $name);

            $ns = $this->relationNs;
            $relationClass = get_class($relation);

            switch ($relationClass) {

                case "{$ns}BelongsTo":
                    $this->applyBelongsTo($model, $relation, $name);
                    break;

                case "{$ns}HasOne":
                    $this->applyHasOne($model, $relation, $name);
                    break;
                default:
                    throw new UnderflowException('Currently only BelongsTo and HasOne is supported');
            }

        }

    }

    protected function applyBelongsTo(Model $model, BelongsTo $belongsTo, $name){

        if(isset($this->joinClasses[$name])){
            return;
        }

        $modelTable = $model->getTable();
        $related = $belongsTo->getRelated();
        $relatedTable = $related->getTable();
        $foreignKey = $belongsTo->getForeignKey();
        $otherKey = $belongsTo->getOtherKey();

        $alias = $this->joinNameToAlias($name);

        $this->query->join("$relatedTable AS $alias", "$modelTable.$foreignKey",'=',"$alias.$otherKey");
        $this->query->distinct();

        $this->addQueryColumn($foreignKey);

        $this->joinClasses[$name] = $belongsTo->getRelated();
        $this->joinTable[$name] = $relatedTable;
        $this->joinAliases[$name] = $alias;

    }

    protected function applyHasOne(Model $model, HasOne $hasOne, $name){

        if(isset($this->joinClasses[$name])){
            return;
        }

        $modelTable = $model->getTable();
        $related = $hasOne->getRelated();
        $relatedTable = $related->getTable();
        $foreignKey = $hasOne->getPlainForeignKey();
        $qualifiedLocalKey = $hasOne->getQualifiedParentKeyName();

        $alias = $this->joinNameToAlias($name);

        $this->query->join("$relatedTable AS $alias", "$qualifiedLocalKey",'=',"$alias.$foreignKey");
        $this->query->distinct();

        $this->joinClasses[$name] = $hasOne->getRelated();
        $this->joinTable[$name] = $relatedTable;
        $this->joinAliases[$name] = $alias;

    }

    protected function joinNameToAlias($join){
        return str_replace($this->parser->joinDelimiter,'__', $join);
    }

    protected function joinColumn($join, $column){
        return str_replace($this->parser->joinDelimiter,'__', $join).".$column";
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getQueryColumns()
    {
        if (!$this->columns) {
            return $this->getDefaultColumns();
        }

        $queryColumns = [];

        $knownColumns = $this->mergedQueryColumns();

        $table = $this->model->getTable();

        $modelKey = $this->model()->getKeyName();

        if (!in_array($modelKey, $knownColumns)) {
            $queryColumns[] = "$table.$modelKey";
        }

        foreach ($knownColumns as $key) {

            if ($this->isRelatedKey($key)) {
                continue;
            }

            $queryColumns[] = "$table.$key";

        }

        return $queryColumns;

    }

    public function mergedQueryColumns()
    {

        $queryColumns = $this->columns;

        foreach ($this->onlyQueryColumns as $column) {
            if (!in_array($column, $queryColumns)) {
                $queryColumns[] = $column;
            }
        }

        return $queryColumns;
    }

    public function addQueryColumn($column)
    {
        $this->onlyQueryColumns[] = $column;
        return $this;
    }

    public function getDefaultColumns(){
        return [$this->model->getTable().'.*'];
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function find($id, $columns = array('*')){
        $columns = (func_num_args() > 1) ? $columns : $this->getQueryColumns();
        return $this->query->find($id, $columns);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|Collection|static
     */
    public function findMany($id, $columns = array('*'))
    {
        $columns = (func_num_args() > 1) ? $columns : $this->getQueryColumns();
        return $this->query->findMany($id, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = array('*')){
        $columns = (func_num_args() > 1) ? $columns : $this->getQueryColumns();
        return $this->query->findOrFail($id, $columns);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first($columns = array('*'))
    {
        $columns = (func_num_args() > 0) ? $columns : $this->getQueryColumns();
        return $this->query->first($columns);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function firstOrFail($columns = array('*'))
    {
        $columns = (func_num_args() > 0) ? $columns : $this->getQueryColumns();
        return $this->query->firstOrFail($columns);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = array('*')){
        $columns = (func_num_args() > 0) ? $columns : $this->getQueryColumns();
        return $this->query->get($columns);
    }

    /**
     * Pluck a single column from the database.
     *
     * @param  string  $column
     * @return mixed
     */
    public function pluck($column)
    {
        return $this->query->pluck($column);
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @param  callable  $callback
     * @return void
     */
    public function chunk($count, callable $callback){

        return $this->query->chunk($count, $callback);
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string  $column
     * @param  string  $key
     * @return array
     */
    public function lists($column, $key = null){
        return $this->query->lists($column, $key);
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int    $perPage
     * @param  array  $columns
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = null, $columns = array('*')){
        $columns = (func_num_args() > 1) ? $columns : $this->getQueryColumns();
        return $this->query->paginate($perPage, $columns);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @param  int    $perPage
     * @param  array  $columns
     * @return \Illuminate\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = array('*')){
        return $this->query->simplePaginate($perPage, $columns);
    }

    public function toSql(){
        return $this->query->toSql();
    }

    public function model()
    {
        return $this->model;
    }

    public function modelClass()
    {
        return get_class($this->model);
    }
}