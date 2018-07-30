<?php namespace Versatile\View;

use Versatile\View\Contracts\CollectionFactory;
use OutOfBoundsException;
use Ems\Core\Patterns\HookableTrait;

class CollectionFactoryChain implements CollectionFactory
{
    use HookableTrait;

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

    /**
     * @param callable $factoryCreator
     * @deprecated Use $app->afterResolving()
     **/
    public function extend(callable $factoryCreator)
    {
        $this->onAfter('boot', $factoryCreator);
    }

    protected function boot()
    {
        if ($this->booted) {
            return;
        }
        $this->callBeforeListeners('boot', [$this]);
        $this->callAfterListeners('boot', [$this]);
        $this->booted = true;
    }

}
