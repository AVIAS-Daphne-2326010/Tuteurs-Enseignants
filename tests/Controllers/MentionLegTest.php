<?php

namespace Controllers;

use Blog\Controllers\MentionLeg;
use Blog\Views\Layout;
use Blog\Views\MentionLeg as MentionLegView;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de MentionLegTest
 *
 * Test du contrôler MentionLeg : s'assure que show()
 * fonctionne comme prévu
 */
class MentionLegTest extends TestCase {
    /**
     * Test de la méthode show() du contrôleur MentionLeg
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testShow(){
        //Mock des classes layout et vue
        $mockLayout = $this->createMock(Layout::class);
        $mockView = $this->createMock(MentionLegView::class);

        //attente pour le mock du layout
        $mockLayout->expects($this->once())
            ->method('renderTop')
            ->with($this->equalTo('Mentions légales'),$this->equalTo('_assets/styles/mentionLeg.css'));

        $mockLayout->expects($this->once())
            ->method('renderBottom')
            ->with($this->equalTo(''));

        //attente pour le mock de la vue
        $mockView->expects($this->once())
            ->method('showView');

        //instanciation des mocks
        $controller = new MentionLeg($mockLayout,$mockView);

        //exécution
        $controller->show();
    }
}