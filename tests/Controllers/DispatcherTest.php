<?php

namespace Controllers;

use Blog\Views\Dispatcher;
use Blog\Models\Dispatcher as DispatcherModel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase{
    private $dispatcher;
    private $dispatcherModelMock;

    /**
     * Configuration initale avant chaque test
     * @return void
     * @throws Exception
     */
    protected function setUp(): void {
        $this->dispatcherModelMock = $this->createMock(DispatcherModel::class);
        $this->dispatcher = new Dispatcher(
            $this->dispatcherModelMock,
            "some error message ",
            "some error message 2"
        );
    }

    public function testShow_AssociationDirect() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['searchTeacher'] = 'Teacher1';
        $_POST['searchInternship'] = 'Internship1';

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([]);
        $this->dispatcherModelMock->method('insertResponsible')->willReturn('Insertion réussie');

        // Appeler la méthode
        $result = $this->dispatcher->association_direct($this->dispatcherModelMock);

        // Vérifier que le message d'erreur est retourné
        $this->assertEquals('Merci de remplir tout les champs', $result);
    }

    public function testAssociationDirect_SuccessfulInsertion() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['searchTeacher'] = 'Teacher1';
        $_POST['searchInternship'] = 'Internship1';

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([]);
        $this->dispatcherModelMock->method('insertResponsible')->willReturn('Insertion réussie');

        $result = $this->dispatcher->association_direct($this->dispatcherModelMock);

        $this->assertEquals('Insertion réussie', $result);
    }

    public function testAssociationDirect_MissingFields() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['searchTeacher'] = '';
        $_POST['searchInternship'] = '';

        $result = $this->dispatcher->association_direct($this->dispatcherModelMock);

        $this->assertEquals('Merci de remplir tout les champs', $result);
    }

    public function testAssociationDirect_InvalidTeacherOrInternship() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['searchTeacher'] = 'NonExistentTeacher';
        $_POST['searchInternship'] = 'NonExistentInternship';

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([]);

        $result = $this->dispatcher->association_direct($this->dispatcherModelMock);

        $this->assertEquals('Internship_identifier ou Id_Teacher inexistant dans ce departement', $result);
    }

    public function testAssociationAfterSort_SuccessfulInsertion() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['listTupleAssociate'] = ['Teacher1$Internship1$5.0'];

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([]);
        $this->dispatcherModelMock->method('insertIs_responsible')->willReturn('Insertion réussie');

        $result = $this->dispatcher->association_after_sort($this->dispatcherModelMock);

        $this->assertStringContainsString('Insertion réussie', $result);
    }

    public function testAssociationAfterSort_AlreadyExists() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['listTupleAssociate'] = ['Teacher1$Internship1$5.0'];

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([['Teacher1', 'Internship1']]); // Association déjà existante

        $result = $this->dispatcher->association_after_sort($this->dispatcherModelMock);

        $this->assertStringContainsString('Teacher1 et Internship1, cette association existe déjà', $result);
    }

    public function testAssociationAfterSort_InvalidData() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['listTupleAssociate'] = ['NonExistentTeacher$NonExistentInternship$5.0'];

        $this->dispatcherModelMock->method('createListTeacher')->willReturn(['Teacher1']);
        $this->dispatcherModelMock->method('createListInternship')->willReturn(['Internship1']);
        $this->dispatcherModelMock->method('createListAssociate')->willReturn([]);

        $result = $this->dispatcher->association_after_sort($this->dispatcherModelMock);

        $this->assertStringContainsString('NonExistentTeacher ou NonExistentInternship, inexistant dans ce departement', $result);
    }
}