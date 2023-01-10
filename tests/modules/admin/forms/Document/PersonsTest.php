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

/**
 * Unit Tests fuer Unterformular fuer Personen im Metadaten-Formular.
 */
class Admin_Form_Document_PersonsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var string[] */
    private $roles;

    public function setUp(): void
    {
        parent::setUp();

        $this->roles = Admin_Form_Document_Persons::getRoles();
    }

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Persons();

        $this->assertCount(8, $form->getSubForms());

        foreach ($this->roles as $role) {
            $this->assertNotNull($form->getSubForm($role), "Unterformular '$role' fehlt.");
        }

        $this->assertNotNull($form->getElement(Admin_Form_Document_Persons::ELEMENT_SORT));

        $this->assertNotNull($form->getLegend());
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Persons();

        $document = Document::get(146); // 1 Person in jeder Rolle

        $form->populateFromModel($document);

        foreach ($this->roles as $role) {
            $subform = $form->getSubForm($role);
            $this->assertNotNull($subform, "Unterformular '$role' fehlt.");
            $this->assertCount(
                1,
                $subform->getSubForms(),
                "Unterformular '$role' sollte ein Unterformular haben."
            );
        }
    }

    public function testConstructFromPost()
    {
        $form = new Admin_Form_Document_Persons();

        $post = [
            'author'  => [
                'PersonAuthor0' => [
                    'PersonId' => '310',
                ],
                'PersonAuthor1' => [
                    'PersonId' => '311',
                ],
            ],
            'advisor' => [
                'PersonAdvisor0' => [
                    'PersonId' => '312',
                ],
            ],
        ];

        $form->constructFromPost($post);

        $this->assertEquals(2, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('translator')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('contributor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('other')->getSubForms()));
        $this->assertEquals(1, count($form->getSubForm('advisor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('referee')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('submitter')->getSubForms()));
    }

    /**
     * TODO Was sollte passieren wenn PersonId is missing? (Manipulierter Post)
     *
     * Es sollte eine Meldung ins Log geschrieben werden und das Unterformular entweder erst garnicht angelegt werden
     * oder aufgrund des fehlens einer Person-ID an späterer Stelle wieder entfernt werden. Es wird das
     * constructFromPost in Admin_Form_Document_MultiSubForm verwendet.
     *
     * TODO test Logging
     */
    public function testConstructFromPostWithMissingPersonId()
    {
        $form = new Admin_Form_Document_Persons();

        $post = [
            'author'  => [
                'PersonAuthor0' => [
                    'PersonId' => '310',
                ],
                'PersonAuthor1' => [ // Es wird kein Unterformular angelegt
                ],
            ],
            'advisor' => [
                'PersonAdvisor0' => [
                    'PersonId' => '312',
                ],
            ],
        ];

        $form->constructFromPost($post);

        $this->assertEquals(1, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('translator')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('contributor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('other')->getSubForms()));
        $this->assertEquals(1, count($form->getSubForm('advisor')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('referee')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('submitter')->getSubForms()));
    }

    public function testContinueEdit()
    {
        $form = new Admin_Form_Document_Persons();

        $request = $this->getRequest();
        $request->setParams([
            'continue' => 'addperson',
            'person'   => '310',
            'role'     => 'editor',
            'order'    => '2',
            'contact'  => '0',
        ]);

        $session = new Admin_Model_DocumentEditSession(100);

        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));

        $form->continueEdit($request, $session);

        $this->assertEquals(1, count($form->getSubForm('editor')->getSubForms()));

        $subform = $form->getSubForm('editor')->getSubForm('PersonEditor0');

        $this->assertNotNull($subform);
        $this->assertEquals(310, $subform->getElementValue('PersonId'));
        $this->assertEquals(1, $subform->getElementValue('SortOrder')); // nur ein Editor
        $this->assertEquals(0, $subform->getElementValue('AllowContact'));
    }

    public function testContinueEditWithoutPersonId()
    {
        $form = new Admin_Form_Document_Persons();

        $logger = new MockLogger();

        $form->setLogger($logger);

        $request = $this->getRequest();
        $request->setParams([
            'continue' => 'addperson',
            'role'     => 'editor',
            'order'    => '2',
            'contact'  => '0',
        ]);

        $session = new Admin_Model_DocumentEditSession(100);

        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));

        $form->continueEdit($request, $session);

        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Attempt to add person without ID.', $messages[0]);
    }

    public function testContinueEditAddTwoPersons()
    {
        $form = new Admin_Form_Document_Persons();

        $request = $this->getRequest();
        $request->setParams([
            'continue' => 'addperson',
        ]);

        $session = new Admin_Model_DocumentEditSession(100);
        $session->addPerson([
            'person' => 310,
            'role'   => 'author',
        ]);
        $session->addPerson([
            'person' => 311,
            'role'   => 'editor',
        ]);

        $this->assertEquals(0, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()));

        $form->continueEdit($request, $session);

        $this->assertEquals(1, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(1, count($form->getSubForm('editor')->getSubForms()));

        $author = $form->getSubForm('author')->getSubForm('PersonAuthor0');

        $this->assertNotNull($author);
        $this->assertEquals(310, $author->getElementValue('PersonId'));

        $editor = $form->getSubForm('editor')->getSubForm('PersonEditor0');

        $this->assertNotNull($editor);
        $this->assertEquals(311, $editor->getElementValue('PersonId'));
    }

    public function testProcessPostChangeRole()
    {
        $form = new Admin_Form_Document_Persons();

        $document = Document::get(250);

        $form->populateFromModel($document);

        $this->assertEquals(3, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('advisor')->getSubForms()));

        $post = [
            'author' => [
                'PersonAuthor0' => [
                    'PersonId' => '310',
                    'Roles'    => [
                        'RoleAdvisor' => 'Advisor',
                    ],
                ],
            ],
        ];

        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, null));

        // ein Autor weniger
        $this->assertEquals(2, count($form->getSubForm('author')->getSubForms()));
        $this->assertNotNull($form->getSubForm('author')->getSubForm('PersonAuthor0'));
        $this->assertNotNull($form->getSubForm('author')->getSubForm('PersonAuthor1'));

        // jetzt ein Advisor
        $this->assertEquals(1, count($form->getSubForm('advisor')->getSubForms()));
        $this->assertNotNull($form->getSubForm('advisor')->getSubForm('PersonAdvisor0'));
        $this->assertEquals(
            310,
            $form->getSubForm('advisor')->getSubForm('PersonAdvisor0')->getElementValue('PersonId')
        );
    }

    public function testProcessPostSort()
    {
        $form = new Admin_Form_Document_Persons();

        $document = Document::get(146);

        $form->populateFromModel($document);

        $post = [
            'Sort' => 'Sortieren',
        ];

        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, null));
    }

    public function testProcessPostEmptyWithoutPersons()
    {
        $form = new Admin_Form_Document_Persons();

        $this->assertNull($form->processPost([], null));
    }

    /**
     * Unbekannte Unterformulare werden ignoriert.
     */
    public function testProcessPostWithUnknownSubform()
    {
        $form = new Admin_Form_Document_Persons();

        $this->assertNull($form->processPost([
            'unknown' => [ // unbekannter Name für Unterformular (sollte Rolle für Person sein, z.B. 'author')
                'key' => 'value',
            ],
        ], null));
    }

    public function testProcessPostEmptyWithPersons()
    {
        $form = new Admin_Form_Document_Persons();

        $document = Document::get(146);

        $form->populateFromModel($document);

        $this->assertNull($form->processPost([], null));
    }

    public function testProcessPostResultNull()
    {
        $form = new Admin_Form_Document_Persons();

        $document = Document::get(146);

        $form->populateFromModel($document);

        $post = [
            'author' => [],
        ];

        $this->assertNull($form->processPost($post, null));
    }

    /**
     * Dieser POST wird von Unterformular verarbeitet und das Ergebnis von Admin_Form_Document_Persons nur
     * durchgereicht.
     */
    public function testProcessPostAddPersonInSubform()
    {
        $form = new Admin_Form_Document_Persons();

        $post = [
            'author' => [
                'Add' => 'Hinzufügen',
            ],
        ];

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);
    }

    public function testGetRoles()
    {
        $roles = Admin_Form_Document_Persons::getRoles();

        $this->assertEquals(8, count($roles));
    }

    public function testGetSubFormForRole()
    {
        $form = new Admin_Form_Document_Persons();

        foreach (Admin_Form_Document_Persons::getRoles() as $role) {
            $this->assertNotNull($form->getSubFormForRole($role));
        }

        $this->assertNull($form->getSubFormForRole('unknown'));
    }

    public function testAddPerson()
    {
        $form = new Admin_Form_Document_Persons();

        $person = [
            'person'  => 310, // von Document 250
            'order'   => 2,
            'role'    => 'editor',
            'contact' => 1,
        ];

        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()), 'Es sollte keinen Editor geben.');

        $form->addPerson($person);

        $this->assertEquals(1, count($form->getSubForm('editor')->getSubForms()), 'Es sollte einen Editor geben.');

        $subform = $form->getSubForm('editor')->getSubForm('PersonEditor0');

        $this->assertNotNull($subform);
        $this->assertEquals(310, $subform->getElementValue('PersonId'));
        $this->assertEquals(1, $subform->getElementValue('SortOrder')); // nur ein Editor
        $this->assertEquals(1, $subform->getElementValue('AllowContact'));
    }

    public function testAddPersonSortOrderEqualsExistingPersonCount()
    {
        $form = new Admin_Form_Document_Persons();

        $this->assertEquals(0, count($form->getSubForm('editor')->getSubForms()), 'Es sollte keinen Editor geben.');

        $form->addPerson(['person' => 310, 'role' => 'editor']);
        $form->addPerson(['person' => 311, 'role' => 'editor']);

        $this->assertEquals(2, count($form->getSubForm('editor')->getSubForms()), 'Es sollte zwei Personen geben.');

        $subform = $form->getSubForm('editor')->getSubForm('PersonEditor0');

        $this->assertNotNull($subform);
        $this->assertEquals(310, $subform->getElementValue('PersonId'));

        $subform = $form->getSubForm('editor')->getSubForm('PersonEditor1');

        $this->assertNotNull($subform);
        $this->assertEquals(311, $subform->getElementValue('PersonId'));

        $form->addPerson(['person' => 312, 'role' => 'editor', 'order' => 2]);

        $this->assertEquals(310, $form->getSubForm('editor')->getSubForm('PersonEditor0')->getElementValue('PersonId'));
        $this->assertEquals(312, $form->getSubForm('editor')->getSubForm('PersonEditor1')->getElementValue('PersonId'));
        $this->assertEquals(311, $form->getSubForm('editor')->getSubForm('PersonEditor2')->getElementValue('PersonId'));
    }
}
