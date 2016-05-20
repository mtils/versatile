<?php namespace Versatile\Query;

use Versatile\Query\Contracts\SyntaxParser as Parser;
use Versatile\Query\SyntaxParser as DefaultParser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as Query;
use Versatile\Introspection\EloquentPathIntrospector;
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

    protected $joinMethods = [];

    protected $relationNs = 'Illuminate\Database\Eloquent\Relations\\';

    protected $columns;

    protected $withs = [];

    protected $wheres = [];

    protected $whereIns = [];

    protected $orderBys = [];

    protected $onlyQueryColumns = [];

    public function __construct(Model $model){
        $this->model = $model;
        $this->query = $model->newQuery();
        $this->parser = new DefaultParser;
        $this->introspector = new EloquentPathIntrospector($this->parser);
    }

    public function with($relations)
    {

        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->withs = array_merge($this->withs, $relations);
        return $this;
    }

    public function withColumn($column){

        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    public function withColumns($columns, $clear=false)
    {
        if ($clear) {
            $this->columns = [];
        }
        return $this->withColumn($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'){

        $this->wheres[] = [
            'column'    => $column,
            'operator'  => $operator,
            'value'     => $value,
            'boolean'   => $boolean
        ];

        return $this;

    }

    public function whereIn($column, $values = null, $boolean = 'and', $not=false){

        $this->whereIns[] = [
            'column'    => $column,
            'values'    => $values,
            'boolean'   => $boolean,
            'not'       => $not
        ];

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

        $this->orderBys[] = [
            'column'    => $column,
            'direction' => $direction
        ];

        return $this;

    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param  int  $value
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function skip($value){
        $this->buildQuery($this->getQueryColumns())->skip($value);
        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function limit($value){
        $this->buildQuery($this->getQueryColumns())->limit($value);
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
        if (!$this->parser->isRelatedKey($key)) {
            return false;
        }

        list($join, $key) = $this->toJoinAndKey($key);

        return $this->model->getTable() != $join;

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

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
        return $this;
    }

    public function getRelation($model, $name){

        if ($this->parser->isRelatedKey($name)) {
            list($join, $key) = $this->parser->toJoinAndKey($name);
            $class = $this->introspector->classOfPath($model, $join);
            $model = new $class();
            $name = $key;
        }

        if (!method_exists($model, $name)) {
            throw new UnderflowException("Method $name does not exists on model " . get_class($this->model));
        }

        $relation = $model->{$name}();

        return $relation;

    }

    protected function addColumnsToQuery(Query $query, $columns)
    {

        $mainTable = $this->model->getTable();

        foreach ($this->mergedQueryColumns() as $column) {

            list($join, $column) = $this->toJoinAndKey($column);

            if ($join && $join != $mainTable) {
                $query->with($join);
                $this->addJoinOnce($query, $this->model, $join);
            }
        }

        // Check again if columns where added by the joins
//         foreach 
    }

    protected function addWithsToQuery(Query $query)
    {
        foreach ($this->withs as $relation) {
            $query->with($relation);
        }
    }

    protected function addWheresToQuery(Query $query)
    {

        $mainTable = $this->model->getTable();

        foreach ($this->wheres as $where) {

            $column     = $where['column'];
            $operator   = $where['operator'];
            $value      = $where['value'];
            $boolean    = $where['boolean'];

            if ($this->isRelatedKey($column)) {

                list($join, $property) = $this->toJoinAndKey($column);

                if($join){

                    $this->addJoinOnce($query, $this->model, $join);

                    $query->where($this->joinColumn($join, $property), $operator, $value, $boolean);

                }

            } else {
                $query->where("$mainTable.$column", $operator, $value, $boolean);

            }
        }
    }

    protected function addWhereInsToQuery(Query $query)
    {

        foreach ($this->whereIns as $where) {

            $column  = $where['column'];
            $values  = $where['values'];
            $boolean = $where['boolean'];
            $not     = $where['not'];

            if ($this->isRelatedKey($column)) {

                list($join, $property) = $this->toJoinAndKey($column);

                if ($join) {

                    $this->addJoinOnce($query, $this->model, $join);

                    $query->whereIn($this->joinColumn($join, $property), $values, $boolean, $not);

                }

            } else {
                $table = $this->model->getTable();
                $query->whereIn("$table.$column", $values, $boolean, $not);
            }
        }
    }

    protected function addOrderBysToQuery(Query $query)
    {

        foreach ($this->orderBys as $orderBy) {

            $column = $orderBy['column'];
            $direction = $orderBy['direction'];

            if ($this->isRelatedKey($column)) {

                list($join, $property) = $this->toJoinAndKey($column);

                if($join){

                    $this->addJoinOnce($query, $this->model, $join);

                    $query->orderBy($this->joinColumn($join, $property), $direction);

                }

            }
            else{
                $table = $this->model->getTable();
                $query->orderBy("$table.$column", $direction);

            }

        }

    }

    public function buildQuery(array $columns)
    {

        $this->joinClasses = [];
        $this->joinTable = [];
        $this->joinAliases = [];

        $query = clone $this->query;

        $this->addWithsToQuery($query);

        $this->addColumnsToQuery($query, $columns);

        $this->addWheresToQuery($query);

        $this->addWhereInsToQuery($query);

        $this->addOrderBysToQuery($query);

        return $query;

    }

    public function addJoinOnce(Query $query, $model, $name){

        if (!isset($this->joinClasses[$name])) {

            $relation = $this->getRelation($this->model, $name);

            $ns = $this->relationNs;
            $relationClass = get_class($relation);

            switch ($relationClass) {

                case "{$ns}BelongsTo":
                    $this->applyBelongsTo($query, $model, $relation, $name);
                    break;

                case "{$ns}HasOne":
                    $this->applyHasOne($query, $model, $relation, $name);
                    break;
                case "{$ns}BelongsToMany":
                    $this->applyBelongsToMany($query, $model, $relation, $name);
                    break;
                default:
                    throw new UnderflowException('Currently only BelongsTo and HasOne is supported');
            }

        }

    }

    protected function applyBelongsTo(Query $query, Model $model, BelongsTo $belongsTo, $name){

        if(isset($this->joinClasses[$name])){
            return;
        }

        $modelTable = '';

        // If the table has already an alias
        if (str_contains($name, '.')) {
            $path = explode('.', $name);
            array_pop($path);
            $parentPath = implode('.', $path);
            if (isset($this->joinAliases[$parentPath])) {
                $modelTable = $this->joinAliases[$parentPath];
            }
        }

        if (!$modelTable) {
            $modelTable = $belongsTo->getParent()->getTable();
        }
        $related = $belongsTo->getRelated();
        $relatedTable = $related->getTable();
        $foreignKey = $belongsTo->getForeignKey();
        $otherKey = $belongsTo->getOtherKey();

        $alias = $this->joinNameToAlias($name);

        $joinMethod = $this->getJoinMethod($name);

        $query->{$joinMethod}("$relatedTable AS $alias", "$modelTable.$foreignKey",'=',"$alias.$otherKey");
        $query->distinct();

        $this->addQueryColumn("$modelTable.$foreignKey");
        $this->joinClasses[$name] = $belongsTo->getRelated();
        $this->joinTable[$name] = $relatedTable;
        $this->joinAliases[$name] = $alias;

    }

    protected function applyHasOne(Query $query, Model $model, HasOne $hasOne, $name){

        if(isset($this->joinClasses[$name])){
            return;
        }

        $modelTable = $model->getTable();
        $related = $hasOne->getRelated();
        $relatedTable = $related->getTable();
        $foreignKey = $hasOne->getPlainForeignKey();

        $qualifiedLocalKey = $hasOne->getQualifiedParentKeyName();
        list($parentTable, $localKey) = explode('.', $qualifiedLocalKey);

        if ($this->parser->isRelatedKey($name)) {
            list($parentPath, $key) = $this->parser->toJoinAndKey($name);
            $this->addJoinOnce($query, $this->model, $parentPath);
            $qualifiedLocalKey = $this->joinAliases[$parentPath] . ".$localKey";
        }
        else {
            $qualifiedLocalKey = $hasOne->getQualifiedParentKeyName();
        }

        $alias = $this->joinNameToAlias($name);

        $joinMethod = $this->getJoinMethod($name);

        $query->{$joinMethod}("$relatedTable AS $alias", "$qualifiedLocalKey",'=',"$alias.$foreignKey");
        $query->distinct();

        $this->joinClasses[$name] = $hasOne->getRelated();
        $this->joinTable[$name] = $relatedTable;
        $this->joinAliases[$name] = $alias;

    }

    protected function applyBelongsToMany(Query $query, Model $model, BelongsToMany $belongsToMany, $name){

        if(isset($this->joinClasses[$name])){
            return;
        }

        $modelTable = $model->getTable();
        $related = $belongsToMany->getRelated();
        $pivotTable = $belongsToMany->getTable();
        $pivotAlias = $pivotTable.'_pivot';
        $pivotLocalKey = $belongsToMany->getOtherKey();
        $relatedTable = $related->getTable();
        $relationKey = $related->getKeyName();
        $foreignKey = $belongsToMany->getForeignKey();
        $qualifiedLocalKey = $belongsToMany->getQualifiedParentKeyName();

        $alias = $this->joinNameToAlias($name);

        $joinMethod = $this->getJoinMethod($name);

        $query->{$joinMethod}("$pivotTable", "$qualifiedLocalKey",'=',"$foreignKey");
        $query->{$joinMethod}("$relatedTable AS $alias", "$relatedTable.$relationKey",'=',"$pivotLocalKey");
        $query->distinct();

        $this->joinClasses[$name] = $related;
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

            if ($this->parser->isRelatedKey($key)) {
                $queryColumns[] = $key;
                continue;
            }

            $queryColumns[] = "$table.$key";

        }

        return $queryColumns;

    }

    public function mergedQueryColumns()
    {

        $queryColumns = (array)$this->columns;

        foreach ($this->onlyQueryColumns as $column) {
            if (!in_array($column, $queryColumns)) {
                $queryColumns[] = $column;
            }
        }

        return $queryColumns;
    }

    public function leftJoinOn($path)
    {
        $this->joinMethods[$path] = 'left';
        return $this;
    }

    public function rightJoinOn($path)
    {
        $this->joinMethods[$path] = 'right';
        return $this;
    }

    public function outerJoinOn($path)
    {
        $this->joinMethods[$path] = 'outer';
        return $this;
    }

    public function innerJoinOn($path)
    {
        $this->joinMethods[$path] = 'outer';
        return $this;
    }

    public function getJoinMethod($path)
    {
        if (!isset($this->joinMethods[$path])) {
            return 'join';
        }

        return $this->joinMethods[$path] . 'Join';

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
    public function find($id, $columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->find($id, $columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->find($id, $this->getQueryColumns());

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

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->findMany($id, $columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->findMany($id, $this->getQueryColumns());

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
    public function findOrFail($id, $columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->findOrFail($id, $columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->findOrFail($id, $this->getQueryColumns());

    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function first($columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->first($columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->first($this->getQueryColumns());

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

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->firstOrFail($columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->firstOrFail($this->getQueryColumns());

    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if (!$columnsPassed) {
            // addJoinOnce adds queryColumns again...
            return $query->get($this->getQueryColumns());
        }

        if (!$columns) {
            return $query->get();
        }

        return $query->get($columns);

    }

    /**
     * Pluck a single column from the database.
     *
     * @param  string  $column
     * @return mixed
     */
    public function pluck($column)
    {
        return $this->buildQuery([$column])->pluck($column);
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @param  callable  $callback
     * @return void
     */
    public function chunk($count, callable $callback){

        return $this->buildQuery($this->getQueryColumns())->chunk($count, $callback);
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string  $column
     * @param  string  $key
     * @return array
     */
    public function lists($column, $key = null){
        return $this->buildQuery([$column])->lists($column, $key);
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int    $perPage
     * @param  array  $columns
     * @return \Illuminate\Pagination\Paginator
     */
    public function paginate($perPage = null, $columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();
        $columns = $columns ?: ['*'];
        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->paginate($perPage, $columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->paginate($perPage, $this->getQueryColumns());

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
    public function simplePaginate($perPage = null, $columns = array('*'))
    {

        $columnsPassed = (func_num_args() > 1);

        $columns = $columnsPassed ? $columns : $this->getQueryColumns();

        $query = $this->buildQuery($columns);

        if ($columnsPassed) {
            return $query->simplePaginate($perPage, $columns);
        }

        // addJoinOnce adds queryColumns again...
        return $query->simplePaginate($perPage, $this->getQueryColumns());

    }

    public function toSql(){
        return $this->buildQuery($this->getQueryColumns())->toSql();
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
