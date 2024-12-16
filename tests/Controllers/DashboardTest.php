<?php

namespace Controllers;

use Blog\Controllers\Dashboard;
use Includes\Database;
use Blog\Views\Layout;
use Blog\Views\Dashboard as DashboardView;
use Blog\Models\Dashboard as DashboardModel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de DashboardTest
 *
 * Test du contrôleur Dashboard : s'assure que show()
 * fonctionne comme prévu
 */
class DashboardTest extends TestCase {
    private $layoutMock;
    private $modelMock;
    private $viewMock;
    private $dbMock;
    private $dashboard;

    /**
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void {
        // Mock de Layout, Model, Vue et simulation de la base de données
        $this->layoutMock = $this->createMock(Layout::class);
        $this->dbMock = $this->createMock(Database::class);
        $this->modelMock = $this->createMock(DashboardModel::class);
        $this->viewMock = $this->createMock(DashboardView::class);
        $this->dashboard = new Dashboard($this->layoutMock);
    }

    /**
     *
     * @return void
     */
    public function testShowRedirectionIfNotAdmin(): void {
        // Simulation d'une session sans le rôle d'administrateur
        $_SESSION['role_name'] = 'User';

        $this->expectOutputString('');
        $this->dashboard->show();
        $this->assertSame('Location: /homepage', xdebug_get_headers()[0]);
    }

    public function testShowRendersDashboardForAuthorizedUser(): void
    {
        // Simulate admin user
        $_SESSION['role_name'] = 'Admin_dep';

        // Configure mock layout expectations
        $this->layoutMock->expects($this->once())
            ->method('renderTop')
            ->with('Dashboard', '_assets/styles/dashboard.css');

        $this->layoutMock->expects($this->once())
            ->method('renderBottom')
            ->with('_assets/scripts/dashboard.js');

        // Configure mock view expectations
        $this->viewMock->expects($this->once())
            ->method('showView');

        // Call the method
        $this->dashboard->show();
    }

    public function testHandleCsvImportSuccess(): void
    {
        // Simulate admin user
        $_SESSION['role_name'] = 'Admin_dep';

        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES['student'] = ['tmp_name' => 'test.csv'];
        $_POST['table_name'] = 'students';

        // Mock Model methods
        $this->modelMock->method('isValidTable')->willReturn(true);
        $this->modelMock->method('getCsvHeaders')->willReturn(['name', 'age', 'grade']);
        $this->modelMock->method('validateHeaders')->willReturn(true);
        $this->modelMock->method('uploadCsv')->willReturn(true);

        // Expect success message in output
        $this->expectOutputString("L'importation du fichier CSV pour la table students a été réalisée avec succès!");

        // Call the method
        $this->dashboard->show();
    }

    public function testHandleCsvImportInvalidTable(): void
    {
        // Simulate admin user
        $_SESSION['role_name'] = 'Admin_dep';

        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['table_name'] = 'invalid_table';

        // Mock Model method
        $this->modelMock->method('isValidTable')->willReturn(false);

        // Expect error message in output
        $this->expectOutputString("Table non valide ou non reconnue.");

        // Call the method
        $this->dashboard->show();
    }

    public function testHandleCsvExportSuccess(): void
    {
        // Simulate admin user
        $_SESSION['role_name'] = 'Admin_dep';

        // Simulate POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['export_list'] = 'students';

        // Mock Model methods
        $this->modelMock->method('isValidTable')->willReturn(true);
        $this->modelMock->method('getTableColumn')->willReturn(['name', 'age', 'grade']);
        $this->modelMock->expects($this->once())
            ->method('exportToCsvByDepartment');

        // Call the method
        $this->dashboard->show();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['role_name']);
        unset($_SERVER['REQUEST_METHOD']);
        unset($_FILES);
        unset($_POST);
    }



//    /**
//     * Test la présence de la database
//     * @return void
//     */
//    public function testDatabaseClassExists(){
//        $this->assertTrue(class_exists('Includes\Database'), 'La classe n existe pas');
//    }
//
//    /**
//     * Test de l'instance de la database
//     * @return void
//     */
//    public function testDatabaseInstance(){
//        $db = new Database();
//        $this->assertInstanceOf(Database::class, $db);
//    }
//
//    /**
//     * Test de la méthode show() du contrôleur Dashboard
//     * et simule le téléchargement d'un fichier csv
//     * @return void
//     * @throws Exception
//     * @throws \Exception
//     */
//    public function testShow(){
//        //Mock des classes layout et vue
//        $mockLayout = $this->createMock(Layout::class);
//        $mockView = $this->createMock(DashboardView::class);
//
//        //attentes pour le mock du layout
//        $mockLayout->expects($this->once())
//            ->method('renderTop')
//            ->with($this->equalTo('Dashboard'), $this->equalTo('_assets/styles/dashboard.css'));
//
//        $mockLayout->expects($this->once())
//            ->method('renderBottom')
//            ->with($this->equalTo(''));
//
//        //attentes pour le mock de la vue
//        $mockView->expects($this->once())
//            ->method('showView');
//
//        //instanciation des mocks
//        $controller = new Dashboard($mockLayout,$mockView);
//
//        //simulation d'une requête POST avec un fichier CSV (student par exemple)
//        $_SERVER['REQUEST_METHOD'] = 'POST';
//        $_FILES['csv_file_student'] = ['tmp_name' => 'path/to/temp/file.csv'];
//
//        //exécution
//        $controller->show();
//    }



}
