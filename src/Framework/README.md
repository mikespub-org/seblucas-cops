# COPS Framework Integration

This document outlines the architecture that allows COPS (Calibre OPDS PHP Server) to be run either as a standalone application or integrated within other popular PHP frameworks like Symfony, Laravel, or Slim.

## Core Concepts

The primary architectural goal is to ensure that the core business logic of COPS can be reused in a **framework-agnostic** way. This core logic—which includes everything from fetching book data from the Calibre database to rendering OPDS feeds and HTML pages—is encapsulated within a set of `Handler` classes (e.g., `HtmlHandler`, `FeedHandler`, `JsonHandler`) and the components they use.

To achieve this, the `Handlers` are decoupled from the specifics of any single web framework. They operate on a generic COPS `Request` object and produce a generic COPS `Response` object.

This decoupling is achieved using the **Adapter Pattern**. The central piece of this design is the `Adapter\AdapterInterface`. Each supported framework has a corresponding class in the `Adapter/` directory that implements this interface, acting as a bridge between the host framework and the reusable COPS handlers.

### The Role of an Adapter

An adapter acts as a bridge between the COPS core application and a host framework. Its primary responsibilities are:

1.  **Route Registration**: The adapter is responsible for taking all the routes defined within COPS's various `Handler` classes and registering them with the host framework's routing component. This makes all COPS URLs (e.g., `/feed.php`, `/download/{id}`) available within the host application.

2.  **Service Bridging**: It provides the COPS `RequestContext` with access to essential services like the router (`RouterInterface`) and the `HandlerManager`. In an integrated setup, the `RouterInterface` would be a wrapper around the host framework's router.

3.  **Request Handling**: The adapter facilitates the execution of a COPS request handler and helps translate the resulting COPS `Response` object into a response that the host framework can send to the client.

### Helper Classes

The integration relies on several key classes:

*   **`Input\RequestContext.php`**: This class holds all the contextual information for a single request, including the `Request` object, the matched route, and access to services like the configuration and the router. It is created at the beginning of the request lifecycle.

*   **`Handlers\HandlerManager.php`**: This class is a factory and registry for all the `BaseHandler` implementations in COPS. It discovers handlers, creates instances of them, and provides their routes to the adapter for registration.

## Framework-Specific Usage

### Standalone (Custom Adapter)

*   **Kernel**: `Framework.php` / `FrameworkTodo.php`
*   **Adapter**: `Adapter\CustomAdapter.php`
*   **How it works**: This is the default mode where COPS runs as its own application. The main `index.php` file calls `Framework::run()`, which boots the application kernel.
    *   The `Framework` class is the original kernel, which relies heavily on static methods and a singleton pattern to manage the application.
    *   `FrameworkTodo.php` is a more modern, refactored version of the kernel that uses constructor dependency injection and is designed to work cleanly with the adapter system. It is currently used primarily in tests but represents the future direction of the standalone application.
    *   In this mode, the kernel is responsible for the entire request-response lifecycle: creating the `Request`, matching a route, executing the appropriate `Handler` (and any middleware), and sending the final `Response` to the browser. The `CustomAdapter` is used to provide the routing and handler logic specific to this standalone operation.

### Slim Integration

*   **Adapter**: `Adapter\SlimAdapter.php`
*   **Action**: `Action\CopsAction.php`
*   **How it works**: Integration is handled by registering COPS services in Slim's container and using the `SlimAdapter` to register routes that point to the `CopsAction`.
    1.  **Service Configuration**: The developer registers `HandlerManager`, `RouterInterface`, and `CopsAction` in the Slim DI container.
    2.  **Adapter Setup**: The `SlimAdapter` is instantiated with the Slim `App` instance.
    3.  **Route Registration**: Calling the adapter's `registerRoutes()` method iterates through all COPS routes and adds them to the Slim app, pointing them to the `CopsAction` class as the handler.
    4.  **Request Handling**: When a request matches a COPS route, Slim invokes `CopsAction`, which bridges the PSR-7 request to the appropriate COPS `Handler` and converts the COPS `Response` back to a PSR-7 response.

    **Configuration Example (`public/index.php`):**
    ```php
    <?php
    use DI\Container;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use SebLucas\Cops\Framework\Action\CopsAction;
    use SebLucas\Cops\Framework\Adapter\SlimAdapter;
    use SebLucas\Cops\Handlers\HandlerManager;
    use SebLucas\Cops\Routing\RouteCollection;
    use SebLucas\Cops\Routing\RouterInterface;
    use SebLucas\Cops\Routing\Routing;
    use Slim\Factory\AppFactory;

    require __DIR__ . '/../vendor/autoload.php';

    $container = new Container();
    AppFactory::setContainer($container);
    $app = AppFactory::create();

    // Register COPS services
    $container->set(HandlerManager::class, fn() => new HandlerManager());

    // The processed route collection for internal COPS use
    $container->set(RouteCollection::class, fn(Container $c) => new RouteCollection($c->get(HandlerManager::class)));

    // The internal COPS router service, which depends on the processed collection
    $container->set(RouterInterface::class, fn(Container $c) => new Routing($c->get(RouteCollection::class)));

    $container->set(CopsAction::class, fn(Container $c) => new CopsAction($c->get(HandlerManager::class), $c->get(RouterInterface::class)));

    // Register COPS routes
    $adapter = new SlimAdapter($app);
    $adapter->registerRoutes();

    // ... add other Slim routes and middleware

    $app->run();
    ```

### Symfony Integration

*   **Adapter**: `Adapter\SymfonyAdapter.php`
*   **Controller**: `Controller\CopsController.php`
*   **How it works**: Integration is achieved by registering COPS services and routes within the Symfony application, and using a dedicated controller to bridge requests.
    1.  **Service Configuration**: In `config/services.yaml`, the developer registers COPS's `HandlerManager`, `RouterInterface`, `SymfonyAdapter`, and `CopsController` as services.
    2.  **Route Registration**: A small PHP routing file is created (e.g., `config/routes/cops.php`). This file uses the `SymfonyAdapter` service to dynamically load all COPS routes into a `RouteCollection` and assigns `CopsController` as the controller for all of them.
    3.  **Request Handling**: When an incoming request matches a COPS route, Symfony dispatches it to the `CopsController`.
    4.  **Bridging**: The controller converts the `Symfony\Component\HttpFoundation\Request` into a COPS `Request`, uses the `HandlerManager` to execute the correct COPS `Handler`, and converts the resulting COPS `Response` back into a `Symfony\Component\HttpFoundation\Response`.

    **Configuration Example (`config/services.yaml`):**
    ```yaml
    services:
        _defaults:
            autowire: true
            autoconfigure: true

        # COPS Services
        SebLucas\Cops\Handlers\HandlerManager: ~
        SebLucas\Cops\Routing\RouterInterface:
            class: SebLucas\Cops\Routing\Routing
            arguments: ['@SebLucas\Cops\Routing\RouteCollection']

        # The processed route collection for internal COPS use
        SebLucas\Cops\Routing\RouteCollection:
            arguments: ['@SebLucas\Cops\Handlers\HandlerManager']

        # The adapter and controller for bridging Symfony and COPS
        SebLucas\Cops\Framework\Adapter\SymfonyAdapter:
            arguments: ['@kernel']
        SebLucas\Cops\Framework\Controller\CopsController: ~
    ```

    **Route Configuration (`config/routes.yaml`):**
    Import the dynamic PHP route loader.
    ```yaml
    cops_routes:
        resource: 'routes/cops.php'
        type: php
    ```

    **Dynamic Route Loader (`config/routes/cops.php`):**
    This file uses the adapter to load all routes dynamically. The `SymfonyAdapter::getRouteCollection()` method is responsible for:
    1.  Getting all route definitions from the COPS `HandlerManager`.
    2.  Converting them into a `Symfony\Component\Routing\RouteCollection`.
    3.  Setting the `_controller` default for every route to `CopsController::class`.
    ```php
    <?php
    use SebLucas\Cops\Framework\Adapter\SymfonyAdapter;
    use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

    return function (RoutingConfigurator $routes, SymfonyAdapter $adapter) {
        $routes->collection()->addCollection($adapter->getRouteCollection());
    };
    ```

### Laravel Integration

*   **Adapter**: `Adapter\LaravelAdapter.php`
*   **Service Provider**: `Providers\CopsServiceProvider.php`
*   **How it works**: The integration is almost entirely automated through a Laravel Service Provider.
    1.  **Provider Registration**: The developer adds `CopsServiceProvider` to the `providers` array in their `config/app.php` file.
    2.  **Service Binding**: The provider's `register()` method automatically binds all necessary COPS services (like `HandlerManager` and `RouterInterface`) into Laravel's service container.
    3.  **Route Registration**: The provider's `boot()` method calls the `LaravelAdapter` to register all COPS routes directly with the Laravel router.
    4.  **Request Handling**: When a request matches a COPS route, the action defined by the `LaravelAdapter` takes over. It handles the conversion between Laravel and COPS request/response objects and executes the appropriate COPS `Handler`.

    **Configuration Example (`config/app.php`):**
    Simply add the `CopsServiceProvider` to the `providers` array.
    ```php
    'providers' => [
        // ... other service providers

        SebLucas\Cops\Framework\Providers\CopsServiceProvider::class,
    ],
    ```
    The service provider handles the rest of the setup automatically.

This flexible architecture ensures that the core logic of COPS remains independent of any specific framework, maximizing its reusability.

---
*This document was prepared by Gemini Code Assist.*