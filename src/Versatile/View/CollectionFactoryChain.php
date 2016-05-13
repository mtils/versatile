<?php namespace Versatile\View;

use Signal\NamedEvent\BusHolderTrait;
use Versatile\View\Contracts\CollectionFactory;
use OutOfBoundsException;

class CollectionFactoryChain implements CollectionFactory
{

    use BusHolderTrait;

    protected $factories = [];

    protected $booted = false;

    public function add(CollectionFactory $factory)
    {
        $this->factories[] = $factory;
        return $this;
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
        return $this->findFactoryFor($searchable, $params, $view)
                    ->create($searchable, $params, $view);
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
        return $this->findFactoryFor($searchable, $params, $view)
                    ->paginate($searchable, $params, $view);
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

        foreach ($this->factories as $factory) {
            if ($factory->canCreate($searchable, $params, $view)) {
                return true;
            }
        }

        return false;

    }

    protected function findFactoryFor($searchable, array $params, $view)
    {

        $this->boot();

        foreach ($this->factories as $factory) {
            if ($factory->canCreate($searchable, $params, $view)) {
                return $factory;
            }
        }

        throw new OutOfBoundsException("No factory supports this searchable");

    }

    public function extend(callable $factoryCreator)
    {
        $this->listen('collection-factory.load', $factoryCreator);
    }

    protected function boot()
    {
        if ($this->booted) {
            return;
        }
        $this->fireOnce('collection-factory.load', $this);
        $this->booted = true;
    }

}
