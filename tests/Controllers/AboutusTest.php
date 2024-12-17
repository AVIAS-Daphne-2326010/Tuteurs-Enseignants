<?php

namespace Controllers;

use Blog\Controllers\Aboutus;
use Blog\Views\Layout;
use Blog\Views\Aboutus as AboutUsView;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de AboutusTest
 *
 * Test du contrôleur Aboutus : s'assure que la méthode show()
 * fonctionne comme prévu
 */
class AboutusTest extends TestCase {
    private $mockLayout;
    private $mockView;

    /**
     * Configuration initale avant chaque test
     * @return void
     * @throws Exception
     */
    protected function setUp(): void{
        $this->mockLayout = $this->createMock(Layout::class);
        $this->mockView = $this->createMock(AboutUsView::class);
    }

    /**
     * Test de la méthode show() du contrôleur Aboutus
     * @return void
     * @throws Exception
     */
    public function testShow(){
        // Paramètres attendus
        $title = "A Propos";
        $cssFilePath = '';
        $jsFilePath = '';

        // S'assure que renderTop et renderBottom sont appelées
        $this->mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($title,$cssFilePath);

        $this->mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($jsFilePath);

        // Création du contrôleur, avec un constructeur personnalisé
        $controller = new Aboutus($this->mockLayout);

        // Appel de la méthode show() du contrôleur
        $controller->show();
    }
}
