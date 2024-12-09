<?php

use PHPUnit\Framework\TestCase;
require_once "./index.php";

/**
 * Classe de RouterTest
 *
 * Test du routeur : s'assure que les méthodes de la classe
 * fonctionne comme prévu
 */
class RouterTest extends TestCase {
    private $router;

    /**
     * Configure l'environnement avant chaque méthode
     * @return void
     */
    protected function setUp(): void {
        $_SERVER['REQUEST_URI'] = '/example-route';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->router = new Router($_SERVER['REQUEST_URI']);
    }

    /**
     * Test de la methode get() qui ajoute une route dans
     * la collection des routes GET
     * @return void
     */
    public function testGetMethodAddsRoute() {
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->router->get('/test', function () {
            return 'success';
        });

        $this->assertArrayHasKey('/test', $this->router->getRoutes()['GET']);
        $this->assertIsCallable($this->router->getRoutes()['GET']['/test']);
        $this->assertNotEmpty($this->router->getRoutes());
    }

    /**
     * Test de la méthode post() qui ajoute une route dans
     * la collection des routes POST
     * @return void
     */
    public function testPostMethodAddsRoute(): void {
        $_SERVER['REQUEST_URI'] = '/post-route';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $callable = function () {
            echo "Test POST";
        };

        $this->router->post('/post-route', $callable);

        $this->assertArrayHasKey('/post-route', $this->router->getRoutes()['POST']);
        $this->assertSame($callable, $this->router->getRoutes()['POST'][0]->getCallable());
        $this->assertNotEmpty($this->router->getRoutes());
    }

    /**
     * Test de la méthode run() qui exécute une route
     * correspondante
     * @return void
     * @throws RouterException
     */
    public function testRunExecutesCorrectRoute(): void {
        $_SERVER['REQUEST_URI'] = '/run-test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->router->get('/run-test', function () {
            echo 'Route executed';
        });

        $this->expectOutputString('Route executed!');
        $this->router->run();
    }

    /**
     * Test de la méthode run() qui lève une exception pour
     * une route inconnue
     * @return void
     * @throws RouterException
     */
    public function testRunThrowsExceptionForUnknownRoute(): void {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Erreur 404');

        $this->router->run();
        $this->assertNotEmpty($this->router->getRoutes());
    }

    /**
     * Tests de la méthode run() qui lève une exception pour
     * une méthode HTTP non supportée
     * @return void
     * @throws RouterException
     */
    public function testRunThrowsExceptionForUnsupportedRequestMethod(): void {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('REQUEST_METHOD n\'existe pas');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->router->run();
        $this->assertNotEmpty($this->router->getRoutes());
    }

    /**
     * Test le fonctionnement de la correspondance des routes
     * @return void
     * @throws RouterException
     */
    public function testRouteMatching(): void {
        $callable = function () {
            echo "Matched!";
        };

        $this->router->get($_SERVER['REQUEST_URI'], $callable);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectOutputString('Matched!');
        $this->assertNotEmpty($this->router->getRoutes());
        $this->router->run();
    }

    protected function tearDown(): void {
        $_SERVER['REQUEST_METHOD'] = null;
        $_SERVER['REQUEST_URI'] = null;
    }
}
