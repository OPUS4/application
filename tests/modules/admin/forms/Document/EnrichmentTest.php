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
 */

/**
 * Unit Test fuer Unterformular fuer ein Enrichment im Metadaten-Formular.
 */
class Admin_Form_Document_EnrichmentTest extends ControllerTestCase {
    
    public function testCreateForm() {
        $form = new Admin_Form_Document_Enrichment();

        $this->assertEquals(3, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('KeyName'));
        $this->assertNotNull($form->getElement('Value'));

        $this->assertFalse($form->getDecorator('Fieldset'));
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_Document_Enrichment();
        
        $document = new Opus_Document(146);
        $enrichments = $document->getEnrichment();
        $enrichment = $enrichments[0];
        
        $form->populateFromModel($enrichment);
        
        $this->assertEquals($enrichment->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($enrichment->getKeyName(), $form->getElement('KeyName')->getValue());
        $this->assertEquals($enrichment->getValue(), $form->getElement('Value')->getValue());
    }
    
    public function testUpdateModel() {
        $form = new Admin_Form_Document_Enrichment();
        
        $enrichment = new Opus_Enrichment();
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren
        
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');
        
        $form->updateModel($enrichment);
        
        $this->assertEquals($keyName, $enrichment->getKeyName());
        $this->assertEquals('Test Enrichment Value', $enrichment->getValue());
    }
    
    public function testGetModel() {
        $form = new Admin_Form_Document_Enrichment();
        
        $document = new Opus_Document(146);
        $enrichments = $document->getEnrichment();
        $enrichment = $enrichments[0];
        
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren
        
        $form->getElement('Id')->setValue($enrichment->getId());
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');
        
        $model = $form->getModel();
        
        $this->assertEquals($enrichment->getId(), $model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }
    
    public function testGetNewModel() {
        $form = new Admin_Form_Document_Enrichment();

        $enrichment = new Opus_Enrichment();
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren
        
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');
        
        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    public function testGetModelUnknownId() {
        $form = new Admin_Form_Document_Enrichment();

        $enrichment = new Opus_Enrichment();
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $logger = new MockLogger();

        $form->setLogger($logger);
        $form->getElement('Id')->setValue(9999);
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Unknown enrichment ID = \'9999\'', $messages[0]);
    }

    public function testGetModelBadId() {
        $form = new Admin_Form_Document_Enrichment();

        $enrichment = new Opus_Enrichment();
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form->getElement('Id')->setValue('bad');
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    /**
     */
    public function testValidation() {
        $form = new Admin_Form_Document_Enrichment();
        
        $post = array(
            'KeyName' => ' ',
            'Value' => ' '
        );
        
        $this->assertFalse($form->isValid($post));

        $this->assertContains('isEmpty', $form->getErrors('KeyName'));
        $this->assertContains('isEmpty', $form->getErrors('Value'));
    }
    
}
