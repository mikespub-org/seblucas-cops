<?php

/**
 * Add COPS Service Provider to the `providers` array in Laravel config/app.php
 */

namespace SebLucas\Cops\Framework\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use SebLucas\Cops\Framework\Adapter\LaravelAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;

class CopsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is used to bind your COPS services into Laravel's service container.
     */
    public function register(): void
    {
        // Bind the core COPS services as singletons, so they are only created once.
        $this->app->singleton(HandlerManager::class, function (Container $app) {
            return new HandlerManager();
        });

        $this->app->singleton(RouterInterface::class, function (Container $app) {
            // Initialize the static COPS routes so the router can find them.
            Route::setRoutes();
            Route::init();
            return new Routing();
        });

        // Bind the LaravelAdapter itself, giving it access to the container.
        $this->app->singleton(LaravelAdapter::class, function (Container $app) {
            return new LaravelAdapter($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been registered.
     * Here, we use the adapter to register all COPS routes with Laravel's router.
     */
    public function boot(): void
    {
        // Resolve the adapter from the container.
        $adapter = $this->app->make(LaravelAdapter::class);

        // Tell the adapter to register all of its routes.
        $adapter->registerRoutes();
    }
}
