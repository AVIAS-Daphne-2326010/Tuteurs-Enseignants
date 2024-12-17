<?php

namespace Controllers;

use Blog\Controllers\Dashboard;
use Blog\Views\Layout;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Classe de DashboardTest
 *
 * Test du contrôleur Dashboard : s'assure que show()
 * fonctionne comme prévu
 */
class DashboardTest extends TestCase {
    /**
     * @return void
     * @throws Exception
     */
    public function setUp(): void {
        $this->layoutMock = $this->createMock(Layout::class);
        $this->dashboard = new Dashboard($this->layoutMock);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testHandleExceptionMessage_SQLStateError() {
        // Créer une exception contenant un message avec SQLSTATE
        $exception = new \Exception("SQLSTATE[23000]: Integrity constraint violation");

        // Appeler la méthode handleExceptionMessage
        $message = $this->invokeMethod($this->dashboard, 'handleExceptionMessage', [$exception]);

        // Vérifier si le message est celui attendu
        $this->assertEquals("Une erreur de base de données est survenue. Une donnée que vous souhaitez insérer existe peut-être déjà.", $message);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testHandleExceptionMessage_PermissionDeniedError() {
        // Créer une exception contenant un message avec permission denied
        $exception = new \Exception("permission denied");

        // Appeler la méthode handleExceptionMessage
        $message = $this->invokeMethod($this->dashboard, 'handleExceptionMessage', [$exception]);

        // Vérifier si le message est celui attendu
        $this->assertEquals("Vous n'avez pas les droits nécessaires pour effectuer cette action.", $message);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testHandleExceptionMessage_GenericError() {
        // Créer une exception générique
        $exception = new \Exception("Some unknown error");

        // Appeler la méthode handleExceptionMessage
        $message = $this->invokeMethod($this->dashboard, 'handleExceptionMessage', [$exception]);

        // Vérifier si le message générique est retourné
        $this->assertEquals("Une erreur inattendue est survenue. Veuillez contacter l'administrateur.", $message);
    }

    /**
     * @return void
     */
    public function testShow_WithValidRole() {
        // Simuler la session avec un rôle valide
        $_SESSION['role_name'] = 'Admin_dep';

        // Mocker la vue
        $this->layoutMock->expects($this->once())->method('renderTop');
        $this->layoutMock->expects($this->once())->method('renderBottom');

        // Appeler la méthode show()
        ob_start();
        $this->dashboard->show();
        $output = ob_get_clean();

        // Vérifier que la méthode show() a bien été exécutée
        $this->assertStringContainsString("Dashboard", $output);
    }

    /**
     * Fonction utilitaire pour appeler une méthode privée dans les tests
     * @throws ReflectionException
     */
    protected function invokeMethod($object, $methodName, array $parameters = []) {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
