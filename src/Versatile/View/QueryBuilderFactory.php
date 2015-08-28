<?php namespace Versatile\View;

use Versatile\View\Contracts\CollectionFactory;
use Versatile\Query\Builder;
use Versatile\Introspection\Contracts\TitleIntrospector;
use Versatile\Introspection\Contracts\TypeIntrospector;
use Collection\View\Collection;
use Collection\ColumnList;
use Collection\ValueGetter\DottedObjectAccess;
use Collection\Table\Column;

class QueryBuilderFactory implements CollectionFactory
{

    protected $namer;

    protected $types;

    public function __construct(TitleIntrospector $namer,
                                TypeIntrospector $types)
    {
        $this->namer = $namer;
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return \Collection\Collection
     **/
    public function create($searchable, array $params=[], $view='html')
    {
        $collection = $this->createCollection($searchable);
        $collection->setSrc($searchable->get());
        $this->assignColumns($searchable, $collection);
        return $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return \Collection\Collection
     **/
    public function paginate($searchable, array $params=[], $view='html')
    {
        $collection = $this->createCollection($searchable);
        $collection->setSrc($searchable->paginate());
        $this->assignColumns($searchable, $collection);

        return $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $searchable
     * @param array $params
     * @param string $view
     * @return bool
     **/
    public function canCreate($searchable, array $params=[], $view='html')
    {
        if ($searchable instanceof Builder) {
            return true;
        }
        return false;
    }

    protected function createCollection(Builder $builder)
    {
        $collection = new Collection;
        $collection->setItemClass($builder->modelClass());
        return $collection;
    }

    protected function assignColumns(Builder $builder, Collection $collection)
    {
        $columns = $this->findViewColumns($builder, $collection);

        $columnList = new ColumnList;
        $accessor = new DottedObjectAccess;
        $rootModel = $builder->model();

        foreach ($columns as $columnName) {

            $column = Column::create()
                            ->setAccessor($columnName, $accessor)
                            ->setTitle($this->namer->keyTitle($rootModel, $columnName));
            $columnList->push($column);

            if ($type = $this->types->keyType($rootModel, $columnName)) {
                $column->setValueFormatter($type);
            }
        }

        $collection->setColumns($columnList);

    }

    protected function findViewColumns(Builder $builder, Collection $collection)
    {
        if ($columns = $builder->getColumns()) {
            return $columns;
        }

        $src = $collection->getSrc();

        if (!count($src)) {
            return [$builder->model()->getKeyName()];
        }

        $firstResult = $src[0];

        return array_keys($firstResult->getAttributes());

    }

}