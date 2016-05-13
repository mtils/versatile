<?php namespace Versatile\View;

use Versatile\View\Contracts\CollectionFactory;
use Versatile\Query\Builder;
use Versatile\Introspection\Contracts\TitleIntrospector;
use Versatile\Introspection\Contracts\TypeIntrospector;
use Collection\View\Collection;
use Collection\ColumnList;
use Collection\ValueGetter\DottedObjectAccess;
use Collection\Table\Column;
use Versatile\Search\Contracts\Search;

class SearchFactory implements CollectionFactory
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

        $perPage = (isset($params['per_page']) && is_numeric($params['per_page'])) ? $params['per_page'] : null;

        $collection->setSrc($searchable->paginate([], $perPage));
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
        if ($searchable instanceof Search && $view == 'html') {
            return true;
        }
        return false;
    }

    protected function createCollection(Search $search)
    {
        $collection = new Collection;
        $collection->setItemClass($search->modelClass());
        return $collection;
    }

    protected function assignColumns(Search $search, Collection $collection)
    {
        $columns = $this->findViewColumns($search, $collection);

        $columnList = new ColumnList;
        $accessor = new DottedObjectAccess;
        $modelClass = $search->modelClass();
        $rootModel = new $modelClass;

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

    protected function findViewColumns(Search $search, Collection $collection)
    {
        if ($columns = $search->keys()) {
            return $columns;
        }

        $src = $collection->getSrc();

        $modelClass = $search->modelClass();
        $rootModel = new $modelClass;

        if (!count($src)) {
            return [$rootModel->getKeyName()];
        }

        $firstResult = $src[0];

        return array_keys($firstResult->getAttributes());

    }

}
