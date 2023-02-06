<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 **/

use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;

/**
 * Unit Tests fuer Unterformular fuer Personen in einer Rolle im Metadaten-Formular.
 */
class Admin_Form_Document_PersonRoleTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $this->assertCount(1, $form->getElements());
        $this->assertCount(0, $form->getSubForms());
        $this->assertEquals('author', $form->getRoleName());
        $this->assertNotNull($form->getElement('Add'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $document = Document::get(21); // hat zwei Authoren

        $this->assertCount(0, $form->getSubForms());

        $form->populateFromModel($document);

        $this->assertCount(2, $form->getSubForms());
    }

    public function testProcessPostAdd()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $post = [
            'Add' => 'Hinzufügen',
        ];

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);
        $this->assertArrayHasKey('target', $result);

        $target = $result['target'];

        $this->assertArrayHasKey('role', $target);
        $this->assertEquals('author', $target['role']);
    }

    public function testProcessPostRemove()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $post = [
            'PersonAuthor0' => [
                'Remove' => 'Entfernen',
            ],
        ];

        $document = Document::get(21); // hat zwei Authoren

        $form->populateFromModel($document);

        $this->assertCount(2, $form->getSubForms(), 'Ungenügend Unterformulare.');

        $form->processPost($post, null);

        $this->assertCount(1, $form->getSubForms(), 'Unterformular wurde nicht entfernt.');

        // TODO prüfe Namen von Unterformularen
    }

    public function testProcessPostEdit()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $post = [
            'PersonAuthor0' => [
                'Edit' => 'Editieren',
            ],
        ];

        $document = Document::get(21); // hat zwei Authoren

        $form->populateFromModel($document);

        $this->assertCount(2, $form->getSubForms());

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);
        $this->assertArrayHasKey('target', $result);

        $target = $result['target'];

        $this->assertArrayHasKey('role', $target);
        $this->assertEquals('author', $target['role']);
    }

    public function testProcessPost()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $post = [];

        $this->assertNull($form->processPost($post, null));
    }

    public function testProcessPostMoveFirst()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor2' => [
                'Moves' => [
                    'First' => 'First',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [312, 310, 311]);
    }

    /**
     * Wenn nur nach den SortOrder Werten sortiert würde, müsste PersonAuthor1 auf Position 3 landen. Da aber auf den
     * First-Button für PersonAuthor1 geklickt wurde, muss das Ergebnis die Reihenfolge (Author1, Author0, Author2)
     * sein.
     */
    public function testProcessPostMoveFirstAndSortBySortOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $post = [
            'PersonAuthor1' => [
                'Moves' => [
                    'First' => 'First',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [311, 312, 310]);
    }

    public function testProcessPostMoveLast()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor0' => [
                'Moves' => [
                    'Last' => 'Last',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [311, 312, 310]);
    }

    public function testProcessPostMoveLastAndSortBySortOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);

        $post = [
            'PersonAuthor1' => [
                'Moves' => [
                    'Last' => 'Last',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 312, 311]);
    }

    public function testProcessPostMoveLastAndSortBySortOrderCase2()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(3);

        $post = [
            'PersonAuthor0' => [
                'Moves' => [
                    'Last' => 'Last',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [311, 312, 310]);
    }

    public function testProcessPostMoveLastAndSortBySortOrderCase3()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);

        $post = [
            'PersonAuthor0' => [
                'Moves' => [
                    'Last' => 'Last',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [312, 311, 310]);
    }

    public function testProcessPostMoveUp()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor2' => [
                'Moves' => [
                    'Up' => 'Hoch',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 312, 311]);
    }

    public function testProcessPostMoveUpForFirst()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor0' => [
                'Moves' => [
                    'Up' => 'Hoch',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 311, 312]);
    }

    public function testProcessPostMoveUpAndSortBySortOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $post = [
            'PersonAuthor2' => [
                'Moves' => [
                    'Up' => 'Hoch',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 312, 311]);
    }

    public function testProcessPostMoveDown()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor1' => [
                'Moves' => [
                    'Down' => 'Runter',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 312, 311]);
    }

    /**
     * Für unbekannte Richtungen tue nichts.
     */
    public function testMoveSubFormUnknownDirection()
    {
        $form = $this->getFormForSorting();

        $method = $this->getMethod('Admin_Form_Document_PersonRole', 'moveSubForm');

        $method->invokeArgs($form, ['PersonAuthor1', 'left']);

        $this->assertNotEquals(-1, $form->getSubForm('PersonAuthor1')->getOrder(), "Formular wurde modifiziert.");
        $this->assertEquals(1, $form->getSubForm('PersonAuthor1')->getOrder());
    }

    public function testProcessPostMoveDownForLast()
    {
        $form = $this->getFormForSorting();

        $post = [
            'PersonAuthor2' => [
                'Moves' => [
                    'Down' => 'Runter',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 311, 312]);
    }

    public function testProcessPostMoveDownAndSortBySortOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $post = [
            'PersonAuthor1' => [
                'Moves' => [
                    'Down' => 'Runter',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [312, 310, 311]);
    }

    public function testProcessPostMoveDownAndSortBySortOrderCase2()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(3);

        $post = [
            'PersonAuthor0' => [
                'Moves' => [
                    'Down' => 'Runter',
                ],
            ],
        ];

        $form->processPost($post, null);

        $this->verifyExpectedOrder($form, [310, 311, 312]);
        // $this->verifyExpectedOrder($form, array(312, 310, 311)); // TODO was wäre die sinnvollste Erwartung?
    }

    public function testCreateSubForm()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $subform = $form->createSubForm();

        $this->assertNotNull($subform);
        $this->assertTrue($subform instanceof Admin_Form_Document_Person);
        $this->assertNotNull($subform->getSubForm('Roles'));
        $this->assertNull($subform->getSubForm('Roles')->getElement('RoleAuthor')); // Unterformular richtig
        $this->assertNotNull($subform->getSubForm('Moves'));
    }

    /**
     * Prüft, ob Unterformulare von einer anderen Rolle eingefügt werden können.
     */
    public function testAddSubFormForPerson()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $form->createSubForm();
    }

    /**
     * Erster und letzter Autor werden ausgetauscht.
     */
    public function testSortSubFormsBySortOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(3);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, [312, 311, 310]);
    }

    public function testSortSubFormsBySortOrderRepeatedValuesRespectOldOrderAndModified()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, [311, 312, 310]);
    }

    public function testSortSubFormsBySortOrderRepeatedValuesRespectOldOrder()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(2);

        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, [311, 310, 312]);
    }

    /**
     * Für Autor 3 wurde explizit SortOrder = 1 gesetzt. Das heißt dieser Autor muss auf Position 1 und Author 1 muss
     * auf Position 2 rutschen. Author 2 landet auf Position 3.
     */
    public function testSortSubFormsBySortOrderRepeatedValuesRespectModified()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(1);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(1);

        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, [312, 310, 311]);
    }

    public function testSortSubFormsBySortOrderEmptyValues()
    {
        $form = $this->getFormForSorting();

        $form->getSubForm('PersonAuthor0')->getElement('SortOrder')->setValue(null);
        $form->getSubForm('PersonAuthor1')->getElement('SortOrder')->setValue(2);
        $form->getSubForm('PersonAuthor2')->getElement('SortOrder')->setValue(null);

        $form->sortSubFormsBySortOrder();

        $this->verifyExpectedOrder($form, [311, 310, 312]);
    }

    public function testGetSubFormModels()
    {
        $form = $this->getFormForSorting();

        $doc = Document::get(250);

        $authors = $form->getSubFormModels($doc);

        $this->assertCount(3, $authors);

        $this->assertEquals(310, $authors[0]->getModel()->getId());
        $this->assertEquals(311, $authors[1]->getModel()->getId());
        $this->assertEquals(312, $authors[2]->getModel()->getId());
    }

    public function testUpdateModel()
    {
        $form = $this->getFormForSorting();

        $doc = Document::get(250);

        $form->updateModel($doc);

        $authors = $doc->getPersonAuthor();

        $this->assertCount(3, $authors);

        $this->assertEquals(310, $authors[0]->getModel()->getId());
        $this->assertEquals(311, $authors[1]->getModel()->getId());
        $this->assertEquals(312, $authors[2]->getModel()->getId());
    }

    public function testAddPersonLastPosition()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $form->addPerson(['person' => '310']);
        $form->addPerson(['person' => '311']);
        $form->addPerson(['person' => '312']);

        $this->assertCount(3, $form->getSubForms());

        $this->verifyExpectedOrder($form, [310, 311, 312]);
    }

    public function testAddPersonLastPosition2()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '4']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [310, 311, 312, 259]);
    }

    public function testAddPersonLastPosition3()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '99']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [310, 311, 312, 259]);
    }

    public function testAddPersonPositionEqualsFormCount()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '3']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [310, 311, 259, 312]);
    }

    public function testAddPersonFirstPosition()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '1']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [259, 310, 311, 312]);
    }

    public function testAddPersonFirstPosition2()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '0']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [259, 310, 311, 312]);
    }

    public function testAddPersonFirstPosition3()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '-1']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [259, 310, 311, 312]);
    }

    public function testAddPersonMiddlePosition()
    {
        $form = $this->getFormForSorting(); // form with three authors

        $form->addPerson(['person' => '259', 'order' => '2']); // Autor von Dokument 146

        $this->verifyExpectedOrder($form, [310, 259, 311, 312]);
    }

    public function testAttemptToAddPersonTwiceInSameRole()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $form->addPerson(['person' => '310']);
        $form->addPerson(['person' => '310']);

        $this->assertCount(1, $form->getSubForms());

        $this->assertEquals(310, $form->getSubForm('PersonAuthor0')->getElementValue('PersonId'));
    }

    public function testAddPersonWithoutId()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $logger = new MockLogger();

        $form->setLogger($logger);

        $form->addPerson([]);

        $this->assertCount(0, $form->getSubForms());

        $messages = $logger->getMessages();

        $this->assertCount(1, $messages);
        $this->assertContains('Attempt to add person without ID.', $messages[0]);
    }

    public function testIsValidSubFormTrue()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $this->assertTrue($form->isValidSubForm(['PersonId' => 310]));
    }

    public function testIsValidSubFormFalse()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $this->assertFalse($form->isValidSubForm([]));
    }

    public function testGetSubFormForPerson()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $form->addPerson(['person' => '312']);

        $this->assertNotNull($form->getSubFormForPerson(312));
        $this->assertEquals(312, $form->getSubFormForPerson(312)->getElementValue('PersonId'));
    }

    /**
     * @param Zend_Form $form
     * @param array     $expected
     */
    protected function verifyExpectedOrder($form, $expected)
    {
        foreach ($expected as $index => $personId) {
            $this->assertEquals($personId, $form->getSubForm('PersonAuthor' . $index)->getElement(
                'PersonId'
            )->getValue(), "Person $personId ist nicht an $index. Stelle.");
        }
    }

    /**
     * @return Admin_Form_Document_PersonRole
     * @throws NotFoundException
     */
    protected function getFormForSorting()
    {
        $form = new Admin_Form_Document_PersonRole('author');

        $document = Document::get(250);

        $authors   = $document->getPersonAuthor();
        $authorId0 = $authors[0]->getModel()->getId(); // 310
        $authorId1 = $authors[1]->getModel()->getId(); // 311
        $authorId2 = $authors[2]->getModel()->getId(); // 312

        $form->populateFromModel($document);

        $this->verifyExpectedOrder($form, [310, 311, 312]);

        return $form;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return ReflectionMethod
     * @throws ReflectionException
     *
     * TODO move to common class (make it reusable)
     */
    private function getMethod($className, $methodName)
    {
        $class  = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
