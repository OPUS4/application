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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2013-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Test für Unterformular für ein Enrichment im Metadaten-Formular.
 */
class Admin_Form_Document_EnrichmentTest extends ControllerTestCase
{

    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Enrichment();

        // das Value-Element wird beim initialen Erzeugen des Formulars nicht mehr eingefügt
        $this->assertEquals(2, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('KeyName'));

        $this->assertFalse($form->getDecorator('Fieldset'));
    }

    public function testPopulateFromModelWithoutType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('keywithouttype');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('keywithouttype');

        // wenn kein Typ zugeordnet wurde, dann erscheint ein Texteingabefeld
        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelWithUnknownType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('keywithunknowntype', 'FooBarType');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('keywithunknowntype');

        // bei unbekanntem Typ wird standardmäßig ein Eingabefeld vom Typ Text angezeigt
        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelBooleanTypeChecked()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('boolean', 'BooleanType');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue(1);
        $enrichment->setKeyName('boolean');

        $valueElement = $this->assertFormElementValues($enrichment, 'Application_Form_Element_Checkbox');
        $this->assertTrue($valueElement->isChecked());

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelBooleanTypeUnchecked()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('boolean', 'BooleanType');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue(0);
        $enrichment->setKeyName('boolean');

        $valueElement = $this->assertFormElementValues($enrichment, 'Application_Form_Element_Checkbox');
        $this->assertFalse($valueElement->isChecked());

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelSelectType()
    {
        $options = ['foo', 'bar', 'baz'];

        $enrichmentKey = $this->createTestEnrichmentKey(
            'select',
            'SelectType',
            ['values' => $options]
        );

        foreach ($options as $option) {
            $enrichment = new Opus_Enrichment();
            $enrichment->setValue($option);
            $enrichment->setKeyName('select');

            $this->assertFormElementValues($enrichment, 'Application_Form_Element_Select');
        }

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelTextType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('text', 'TextType');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('text');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelTextareaType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('textarea', 'TextareaType');

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue("foo\nbar\baz");
        $enrichment->setKeyName('textarea');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Textarea');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelRegexType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('regexkey', 'RegexType', ["regex" => "^foo$"]);

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue("foo");
        $enrichment->setKeyName('regexkey');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    /**
     * Helper Method to prevent code duplication in tests
     *
     * @param $enrichment
     * @param $valueFormElementName
     *
     * @return Zend_Form_Element|null
     */
    private function assertFormElementValues($enrichment, $valueFormElementName)
    {
        $form = new Admin_Form_Document_Enrichment();
        $form->populateFromModel($enrichment);

        $this->assertEquals(
            $enrichment->getId(),
            $form->getElement(Admin_Form_Document_Enrichment::ELEMENT_ID)->getValue()
        );
        $this->assertEquals(
            $enrichment->getKeyName(),
            $form->getElement(Admin_Form_Document_Enrichment::ELEMENT_KEY_NAME)->getValue()
        );

        $valueElement = $form->getElement(Admin_Form_Document_Enrichment::ELEMENT_VALUE);
        $this->assertInstanceOf($valueFormElementName, $valueElement);

        if ($valueElement instanceof Application_Form_Element_Select) {
            $this->assertEquals($enrichment->getValue(), $valueElement->getMultiOptions()[$valueElement->getValue()]);
        } else {
            $this->assertEquals($enrichment->getValue(), $valueElement->getValue());
        }
        return $valueElement;
    }

    public function testUpdateModel()
    {
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);

        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $enrichment = new Opus_Enrichment();
        $form->updateModel($enrichment);

        $this->assertEquals($keyName, $enrichment->getKeyName());
        $this->assertEquals('Test Enrichment Value', $enrichment->getValue());
    }

    public function testUpdateModelWithSelectType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey(
            'select',
            'SelectType',
            ['values' => ['foo', 'bar', 'baz']]
        );

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('select');

        $form->getElement('KeyName')->setValue('select');
        $form->getElement('Value')->setValue(1); // Index des ausgewählten Werts

        $enrichment = new Opus_Enrichment();
        $form->updateModel($enrichment);

        $this->assertEquals('select', $enrichment->getKeyName());
        $this->assertEquals('bar', $enrichment->getValue());

        $enrichmentKey->delete();
    }

    public function testGetModel()
    {
        $document = new Opus_Document(146);
        $enrichments = $document->getEnrichment();
        $enrichment = $enrichments[0];

        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);
        $form->getElement('Id')->setValue($enrichment->getId());
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertEquals($enrichment->getId(), $model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    public function testGetNewModel()
    {
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);

        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    public function testGetModelUnknownId()
    {
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);

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

    public function testGetModelBadId()
    {
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);

        $form->getElement('Id')->setValue('bad');
        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    public function testValidationWithoutType()
    {
        $keyNames = Opus_EnrichmentKey::getAll();
        $keyName = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement($keyName);

        $post = [
            'KeyName' => ' ',
            'Value' => ''
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(2, $form->getErrors('KeyName'));
        $this->assertContains('isEmpty', $form->getErrors('KeyName'));
        $this->assertContains('notInArray', $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('isEmpty', $form->getErrors('Value'));
    }

    public function testValidationWithSelectType()
    {
        $options = ['foo', 'bar', 'baz'];
        $selectOptions = ['values' => $options];
        $type = new Opus_Enrichment_SelectType();
        $type->setOptions($selectOptions);

        $enrichmentKey = $this->createEnrichmentKeyAndForm('select', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('select');

        $post = [
            'KeyName' => 'select',
            'Value' => 1
        ];

        $result = $form->isValid($post);
        $this->assertTrue($result);

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    public function testValidationWithSelectTypeMissingValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $selectOptions = ['values' => $options];
        $type = new Opus_Enrichment_SelectType();
        $type->setOptions($selectOptions);

        $enrichmentKey = $this->createEnrichmentKeyAndForm('select', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('select');

        $post = [
            'KeyName' => 'select',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('isEmpty', $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    public function testValidationWithSelectTypeInvalidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $selectOptions = ['values' => $options];
        $type = new Opus_Enrichment_SelectType();
        $type->setOptions($selectOptions);

        $enrichmentKey = $this->createEnrichmentKeyAndForm('select', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('select');

        $post = [
            'KeyName' => 'select',
            'Value' => 3 // es gibt keine 4. Option (nur Werte von 0 bis 2 erlaubt)
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('notInArray', $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    public function testValidationWithRegexType()
    {
        $type = new Opus_Enrichment_RegexType();
        $type->setOptions(['regex' => '^abc$']);

        $enrichmentKey = $this->createEnrichmentKeyAndForm('regex', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('regex');

        $post = [
            'KeyName' => 'regex',
            'Value' => 'xyz' // invalid value
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('regexNotMatch', $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    public function testValidationWithRegexTypeWithMissingValue()
    {
        $type = new Opus_Enrichment_RegexType();
        $type->setOptions(['regex' => '^.*$']); // this regex allows empty values

        $enrichmentKey = $this->createEnrichmentKeyAndForm('regex', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement('regex');

        $post = [
            'KeyName' => 'regex',
            'Value' => '' // empty enrichment values are not allowed
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('isEmpty', $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    public function testValidationWithRegexTypeUsedByFirstEnrichmentKey()
    {
        $type = new Opus_Enrichment_RegexType();
        $type->setOptions(['regex' => '^abc$']);

        // mit dem Namen soll sichergestellt werden, dass dieser Enrichment-Key
        // in der Auswahlliste als erster Eintrag auftritt
        $enrichmentKey = $this->createEnrichmentKeyAndForm('aaaaaaaa', $type);

        $form = new Admin_Form_Document_Enrichment();
        $form->initEnrichmentValueElement();

        $post = [
            'KeyName' => 'aaaaaaaa',
            'Value' => 'xyz' // invalid value
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('regexNotMatch', $form->getErrors('Value'));

        $enrichmentKey->delete();
    }

    private function createTestEnrichmentKey($name, $type = null, $options = null)
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName($name);

        if (! is_null($type)) {
            $enrichmentKey->setType($type);
        }

        if (! is_null($options)) {
            if (is_array($options)) {
                $options = json_encode($options);
            }
            $enrichmentKey->setOptions($options);
        }

        $enrichmentKey->store();
        return $enrichmentKey;
    }

    private function createEnrichmentKeyAndForm($name, $type)
    {
        $enrichmentKey = $this->createTestEnrichmentKey($name, $type->getName(), $type->getOptions());

        // Methodenaufruf hier erforderlich, damit der interne Cache, in dem
        // alle EnrichmentKeys gehalten werden, neu aufgesetzt wird
        Opus_EnrichmentKey::getAll();

        return $enrichmentKey;
    }

    public function testPrepareRenderingAsView()
    {
        $form = new Admin_Form_Document_Enrichment();
        $form->prepareRenderingAsView();
        $this->assertFalse($form->isRemoveEmptyCheckbox());
    }
}
