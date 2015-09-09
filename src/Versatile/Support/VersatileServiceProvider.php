<?php namespace Versatile\Support;


use Illuminate\Support\ServiceProvider;
use Signal\Support\Laravel\IlluminateBus;

class VersatileServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->registerSyntaxParser();
        $this->registerPathIntrospector();
        $this->registerTitleIntrospector();
        $this->registerTypeIntrospector();
        $this->registerViewCollectionFactory();
        $this->registerBuilderViewCollectionFactory();
        $this->registerModelPresenter();
    }

    protected function registerSyntaxParser()
    {

        $this->app->alias(
            'versatile.syntax-parser',
            'Versatile\Query\Contracts\SyntaxParser'
        );

        $this->app->bind('versatile.syntax-parser',function($app){
            return $app->make('Versatile\Query\SyntaxParser');
        });

    }

    protected function registerPathIntrospector()
    {

        $this->app->alias(
            'versatile.path-introspector',
            'Versatile\Introspection\Contracts\PathIntrospector'
        );

        $this->app->singleton('versatile.path-introspector',function($app){
            return $app->make('Versatile\Introspection\EloquentPathIntrospector');
        });

    }

    protected function registerTitleIntrospector()
    {

        $this->app->alias(
            'versatile.title-introspector',
            'Versatile\Introspection\Contracts\TitleIntrospector'
        );

        $this->app->singleton('versatile.title-introspector',function($app){
            return $app->make('Versatile\Introspection\EloquentTitleIntrospector');
        });

    }

    protected function registerTypeIntrospector()
    {

        $this->app->alias(
            'versatile.type-introspector',
            'Versatile\Introspection\Contracts\TypeIntrospector'
        );

        $this->app->singleton('versatile.type-introspector',function($app){
            return $app->make('Versatile\Introspection\EloquentTypeIntrospector');
        });

    }

    protected function registerViewCollectionFactory()
    {

        $this->app->alias(
            'versatile.view-collection-factory',
            'Versatile\View\Contracts\CollectionFactory'
        );

        $this->app->singleton('versatile.view-collection-factory',function($app) {
            $factory = $app->make('Versatile\View\CollectionFactoryChain');
            $factory->setEventBus(new IlluminateBus($app['events']));
            return $factory;
        });

    }

    protected function registerBuilderViewCollectionFactory()
    {

        $this->app['events']->listen('collection-factory.load', function($factory) {
            $factory->add($this->app->make('Versatile\View\QueryBuilderFactory'));
        });

    }

    protected function registerModelPresenter()
    {

        $this->app->alias(
            'versatile.model-presenter',
            'Versatile\View\Contracts\ModelPresenter'
        );

        $this->app->singleton('versatile.model-presenter', function($app) {
            return $app->make('Versatile\View\ModelPresenterRegistry');
        });

        $this->app->resolving('versatile.model-presenter', function($presenter) {

            $presenter->provideId('Illuminate\Database\Eloquent\Model', function($object){
                return $object->getKey();
            });

            $presenter->provideShortName('Illuminate\Database\Eloquent\Model', function($object, $view){

                if (method_exists($object, 'shortName')) {
                    return $object->shortName($view);
                }

                if (isset($object->title)) {
                    return $object->title;
                }

                if (isset($object->name)) {
                    return $object->name;
                }

                return class_basename($object) . ' #' .  $object->getKey();
            });
        });

    }

}