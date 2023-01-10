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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Identifier;

/**
 * Unit Test fuer Identifier Formular Klasse.
 */
class Admin_Form_Document_IdentifierTest extends ControllerTestCase
{
    /** @var string[]  */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Identifier();

        $this->assertEquals(3, count($form->getElements()));

        $this->assertNotNull($form->getElement('Value'));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Type'));

        $this->assertFalse($form->getDecorator('Fieldset'));
    }

    /**
     * Testet das Setzen der Elemente entsprechend Identifier.
     *
     * Dokument 146 wird verwendet, da es vollständig besetzt ist und normalerweise in den Unit Tests nicht modifiziert
     * wird.
     */
    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Identifier();

        $document    = Document::get(146);
        $identifiers = $document->getIdentifier();
        $identifier  = $identifiers[0];

        $form->populateFromModel($identifier);

        $this->assertEquals($identifier->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($identifier->getType(), $form->getElement('Type')->getValue());
        $this->assertEquals($identifier->getValue(), $form->getElement('Value')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Identifier();

        $form->getElement('Type')->setValue('url');
        $form->getElement('Value')->setValue('test-urn-1');

        $identifier = Identifier::new();

        $form->updateModel($identifier);

        $this->assertEquals('url', $identifier->getType());
        $this->assertEquals('test-urn-1', $identifier->getValue());
    }

    /**
     * Prueft, dass bei nicht gesetztem Id-Element ein neuer Identifier zurueck geliefert wird.
     */
    public function testGetModel()
    {
        $form = new Admin_Form_Document_Identifier();

        $form->getElement('Type')->setValue('url');
        $form->getElement('Value')->setValue('test-urn-1');

        $identifier = $form->getModel();

        $this->assertNull($identifier->getId());
        $this->assertEquals('url', $identifier->getType());
        $this->assertEquals('test-urn-1', $identifier->getValue());
    }

    /**
     * Prueft, dass bei gesetztem Id-Element, der Identifier mit dieser Id und aktualisierten Werten zurueck geliefert
     * wird.
     */
    public function testGetModelExistingIdentifier()
    {
        $form = new Admin_Form_Document_Identifier();

        $document     = Document::get(146);
        $identifiers  = $document->getIdentifier();
        $identifierId = $identifiers[0]->getId();

        $form->getElement('Id')->setValue($identifierId);
        $form->getElement('Type')->setValue('url');
        $form->getElement('Value')->setValue('test-urn-1');

        $identifier = $form->getModel();

        $this->assertEquals($identifierId, $identifier->getId());
        $this->assertEquals('url', $identifier->getType());
        $this->assertEquals('test-urn-1', $identifier->getValue());
    }

    public function testGetModelBadId()
    {
        $form = new Admin_Form_Document_Identifier();

        $form->getElement('Id')->setValue('bad');
        $form->getElement('Type')->setValue('url');
        $form->getElement('Value')->setValue('test-urn-1');

        $logger = new MockLogger();
        $form->setLogger($logger);

        $identifier = $form->getModel();

        $this->assertNull($identifier->getId());
        $this->assertEquals('url', $identifier->getType());
        $this->assertEquals('test-urn-1', $identifier->getValue());

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Unknown identifier ID = \'bad\'.', $messages[0]);
    }

    public function testGetModelUnknownId()
    {
        $form = new Admin_Form_Document_Identifier();

        $form->getElement('Id')->setValue('7777');
        $form->getElement('Type')->setValue('url');
        $form->getElement('Value')->setValue('test-urn-1');

        $logger = new MockLogger();
        $form->setLogger($logger);

        $identifier = $form->getModel();

        $this->assertNull($identifier->getId());
        $this->assertEquals('url', $identifier->getType());
        $this->assertEquals('test-urn-1', $identifier->getValue());

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Unknown identifier ID = \'7777\'.', $messages[0]);
    }
}
