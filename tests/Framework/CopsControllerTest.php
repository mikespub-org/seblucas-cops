<?php

namespace SebLucas\Cops\Tests\Framework;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework\Controller\CopsController;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouteLoader;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;
use Symfony\Component\Routing\RouteCollection;

#[RequiresMethod('\Symfony\Component\HttpKernel\KernelInterface', 'getContainer')]
class CopsControllerTest extends TestCase
{
    private ContainerBuilder $container;
    private RouteCollection $routeCollection;

    protected function setUp(): void
    {
        // Reset COPS routes for tests
        //Route::setRoutes();

        // 1. Create a DI Container
        $this->container = new ContainerBuilder();

        // 2. Create a custom RouteLoader for this test that sets the CopsController
        $routeLoader = new class extends RouteLoader {
            public function load(mixed $resource, string $type = null): mixed
            {
                $routes = parent::load($resource, $type);
                foreach ($routes->all() as $route) {
                    $route->setDefault('_controller', CopsController::class);
                }
                return $routes;
            }
        };

        // 3. Teach the container how to create COPS services
        $this->container->set(HandlerManager::class, new HandlerManager());
        // Use the custom loader for the router
        $this->container->set(RouterInterface::class, new Routing());
        $this->container->set(CopsController::class, new CopsController($this->container->get(HandlerManager::class), $this->container->get(RouterInterface::class)));

        // 4. Get the collection of routes with the CopsController set
        /** @var RouterInterface $copsRouter */
        $copsRouter = $this->container->get(RouterInterface::class);
        $copsRouter->setLoader($routeLoader);
        $this->routeCollection = $copsRouter->getRouter()->getRouteCollection();
    }

    public function testHandleRequestWithKernel(): void
    {
        // Create a Symfony request for a specific COPS route
        $request = Request::create('/check');

        // Simulate a minimal Symfony Kernel to handle the request
        // Create a container-aware controller resolver for this test
        $controllerResolver = new class ($this->container) extends ControllerResolver {
            public function __construct(private ContainerBuilder $container)
            {
                parent::__construct();
            }
            protected function instantiateController(string $class): object
            {
                return $this->container->has($class) ? $this->container->get($class) : parent::instantiateController($class);
            }
        };

        $matcher = new UrlMatcher($this->routeCollection, new SymfonyRequestContext());
        $argumentResolver = new ArgumentResolver();
        $kernel = new HttpKernel(new EventDispatcher(), $controllerResolver, null, $argumentResolver);

        // Manually add the matched route attributes to the request, as the full kernel would
        $request->attributes->add($matcher->match($request->getPathInfo()));

        // The kernel will use the ControllerResolver to find our CopsController
        // and the ArgumentResolver to pass the Request to it.
        $response = $kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);

        // Assert the response is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $response->getContent());
    }
}
