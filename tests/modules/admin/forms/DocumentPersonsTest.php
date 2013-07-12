<?php
/*
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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 **/

/**
 * Unit Tests fuer Unterformular fuer Personen im Metadaten-Formular.
 */
class Admin_Form_DocumentPersonsTest extends ControllerTestCase {
    
    private $roles;
    
    public function setUp() {
        parent::setUp();
        
        $this->roles = Admin_Form_DocumentPersons::getRoles();
    }
    
    public function testCreateForm() {
        $form = new Admin_Form_DocumentPersons();
        
        $this->assertEquals(8, count($form->getSubForms()));
        
        foreach ($this->roles as $role) {
            $this->assertNotNull($form->getSubForm($role), "Unterformular '$role' fehlt.");
        }
        
        $this->assertNotNull($form->getElement(Admin_Form_DocumentPersons::ELEMENT_SORT));
        
        $this->assertNotNull($form->getLegend());
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_DocumentPersons();
        
        $document = new Opus_Document(146); // 1 Person in jeder Rolle
        
        $form->populateFromModel($document);
        
        foreach ($this->roles as $role) {
            $subform = $form->getSubForm($role);
            $this->assertNotNull($subform, "Unterformular '$role' fehlt.");
            $this->assertEquals(1, count($subform->getSubForms()), 
                    "Unterformular '$role' sollte ein Unterformlar haben.");
        }
    }
    
    public function testConstructFromPost() {
        $form = new Admin_Form_DocumentPersons();
        
        $post = array(
            'author' => array(
                'PersonAuthor0' => array(
                    'PersonId' => '310'
                ),
                'PersonAuthor1' => array(
                    'PersonId' => '311'
                )
            ),
            'advisor' => array(
                'PersonAdvisor0' => array(
                    'PersonId' => '312'
                )
            )
        );
        
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
     */
    public function testConstructFromPostWithMissingPersonId() {
        $this->markTestIncomplete('Ist das dir richtige Stelle für den Test?');
        $form = new Admin_Form_DocumentPersons();
        
        $post = array(
            'author' => array(
                'PersonAuthor0' => array(
                    'PersonId' => '310'
                ),
                'PersonAuthor1' => array(
                )
            ),
            'advisor' => array(
                'PersonAdvisor0' => array(
                    'PersonId' => '312'
                )
            )
        );
        
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
        
    public function testContinueEdit() {
        $this->markTestIncomplete('Funktionalität muss noch fertiggestellt werden.');
    }
    
    public function testProcessPostChangeRole() {
        $form = new Admin_Form_DocumentPersons();
        
        $document = new Opus_Document(250);
        
        $form->populateFromModel($document);
        
        $this->assertEquals(3, count($form->getSubForm('author')->getSubForms()));
        $this->assertEquals(0, count($form->getSubForm('advisor')->getSubForms()));

        $post = array(
            'author' => array(
                'PersonAuthor0' => array(
                    'PersonId' => '310',
                    'Roles' => array(
                        'RoleAdvisor' => 'Advisor'
                    )
                )
            )
        );
        
        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, null));
        
        // ein Autor weniger
        $this->assertEquals(2, count($form->getSubForm('author')->getSubForms()));
        $this->assertNotNull($form->getSubForm('author')->getSubForm('PersonAuthor0'));
        $this->assertNotNull($form->getSubForm('author')->getSubForm('PersonAuthor1'));

        // jetzt ein Advisor
        $this->assertEquals(1, count($form->getSubForm('advisor')->getSubForms()));
        $this->assertNotNull($form->getSubForm('advisor')->getSubForm('PersonAdvisor0'));
        $this->assertEquals(310, 
                $form->getSubForm('advisor')->getSubForm('PersonAdvisor0')->getElementValue('PersonId'));
    }
    
    public function testProcessPostSort() {
        $form = new Admin_Form_DocumentPersons();
        
        $document = new Opus_Document(146);
        
        $form->populateFromModel($document);
        
        $post = array(
            'Sort' => 'Sortieren'
        );
        
        $this->assertEquals(Admin_Form_Document::RESULT_SHOW, $form->processPost($post, null));
    }
    
    public function testProcessPostWithoutSubforms() {
        $form = new Admin_Form_DocumentPersons();
        
        $this->assertNull($form->processPost(array(), null));
    }
    
    public function testProcessPostEmpty() {
        $form = new Admin_Form_DocumentPersons();
        
        $document = new Opus_Document(146);
        
        $form->populateFromModel($document);
        
        $this->assertNull($form->processPost(array(), null));
    }
    
    public function testGetRoles() {
        $roles = Admin_Form_DocumentPersons::getRoles();
        
        $this->assertEquals(8, count($roles));
    }
    
}
