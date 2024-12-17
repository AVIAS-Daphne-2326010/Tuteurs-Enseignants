<?php

namespace Controllers;

use Blog\Views\Layout;
use Blog\Controllers\Error404;
use Blog\Views\Error404 as Error404View;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de Error404Test
 *
 * Test du contrôleur Error404 : s'assure que la méthode show()
 * fonctionne comme prévu
 */
class Error404Test extends TestCase{
    private $layoutMock;
    private $error404;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void {
        // Création d'un mock pour la classe Layout
        $this->layoutMock = $this->createMock(Layout::class);

        // Initialisation de l'instance de Error404 en passant le mock Layout
        $this->error404 = new Error404($this->layoutMock);

    }

    /**
     * Test de la méthode show() du contrôleur Error404
     * @return void
     * @throws Exception
     */
    public function testShow() {
        // Création d'un mock pour la vue Error404
        $viewMock = $this->createMock(Error404View::class);

        // Configuration du mock de la vue pour que showView soit appelée
        $viewMock->expects($this->once())
            ->method('showView');

        // Vérifier que renderTop a été appelé une fois
        $this->layoutMock->expects($this->once())
            ->method('renderTop')
            ->with("Erreur 404", '/_assets/styles/erreur404.css');

        // Vérifier que renderBottom a été appelé une fois
        $this->layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with('');

        // Appeler la méthode show() du contrôleur
        $this->error404->show();
    }
}