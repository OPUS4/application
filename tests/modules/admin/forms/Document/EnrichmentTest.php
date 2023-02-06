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
 */

use Opus\Common\Document;
use Opus\Common\Enrichment;
use Opus\Common\EnrichmentInterface;
use Opus\Common\EnrichmentKey;
use Opus\Enrichment\RegexType;
use Opus\Enrichment\SelectType;
use Opus\Enrichment\TextType;
use Opus\Enrichment\TypeInterface;

/**
 * Unit Test für Unterformular für ein Enrichment im Metadaten-Formular.
 */
class Admin_Form_Document_EnrichmentTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

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

        $enrichment = Enrichment::new();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('keywithouttype');

        // wenn kein Typ zugeordnet wurde, dann erscheint ein Texteingabefeld
        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelWithUnknownType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('keywithunknowntype', 'FooBarType');

        $enrichment = Enrichment::new();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('keywithunknowntype');

        // bei unbekanntem Typ wird standardmäßig ein Eingabefeld vom Typ Text angezeigt
        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelBooleanTypeChecked()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('boolean', 'BooleanType');

        $enrichment = Enrichment::new();
        $enrichment->setValue(1);
        $enrichment->setKeyName('boolean');

        $valueElement = $this->assertFormElementValues($enrichment, 'Application_Form_Element_Checkbox');
        $this->assertTrue($valueElement->isChecked());

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelBooleanTypeUnchecked()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('boolean', 'BooleanType');

        $enrichment = Enrichment::new();
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
            $enrichment = Enrichment::new();
            $enrichment->setValue($option);
            $enrichment->setKeyName('select');

            $this->assertFormElementValues($enrichment, 'Application_Form_Element_Select');
        }

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelSelectTypeWithInvalidValue()
    {
        $options = ['foo', 'bar', 'baz'];

        $enrichmentKey = $this->createTestEnrichmentKey(
            'select',
            'SelectType',
            ['values' => $options, 'validation' => 'strict']
        );

        $enrichment = Enrichment::new();
        $enrichment->setValue('foobar'); // dieser Wert ist gemäß Konfiguration nicht gültig
        $enrichment->setKeyName('select');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Select');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelTextType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('text', 'TextType');

        $enrichment = Enrichment::new();
        $enrichment->setValue('foo');
        $enrichment->setKeyName('text');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelTextareaType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('textarea', 'TextareaType');

        $enrichment = Enrichment::new();
        $enrichment->setValue("foo\nbar\baz");
        $enrichment->setKeyName('textarea');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Textarea');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelRegexType()
    {
        $enrichmentKey = $this->createTestEnrichmentKey('regexkey', 'RegexType', ["regex" => "^foo$"]);

        $enrichment = Enrichment::new();
        $enrichment->setValue("foo");
        $enrichment->setKeyName('regexkey');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    public function testPopulateFromModelRegexTypeWithInvalidValue()
    {
        $enrichmentKey = $this->createTestEnrichmentKey(
            'regexkey',
            'RegexType',
            [
                'regex'      => '^foo$',
                'validation' => 'strict',
            ]
        );

        $enrichment = Enrichment::new();
        $enrichment->setValue("bar"); // dieser Wert ist gemäß der Typkonfiguration nicht gültig
        $enrichment->setKeyName('regexkey');

        $this->assertFormElementValues($enrichment, 'Application_Form_Element_Text');

        $enrichmentKey->delete();
    }

    /**
     * Helper Method to prevent code duplication in tests
     *
     * @param EnrichmentInterface $enrichment
     * @param string              $valueFormElementName
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
        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement($keyName);

        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $enrichment = Enrichment::new();
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
        $form->initValueFormElement('select');

        $form->getElement('KeyName')->setValue('select');
        $form->getElement('Value')->setValue(1); // Index des ausgewählten Werts

        $enrichment = Enrichment::new();
        $enrichment->setValue('foo'); // das Enrichment-Formular wird nur für Enrichments mit gesetztem Wert aufgerufen
        $form->updateModel($enrichment);

        $enrichmentKey->delete();

        $this->assertEquals('select', $enrichment->getKeyName());
        $this->assertEquals('bar', $enrichment->getValue());
    }

    public function testUpdateModelWithSelectTypeWithInvalidValueAndNoValidation()
    {
        $enrichmentKey = $this->createTestEnrichmentKey(
            'select',
            'SelectType',
            [
                'values'     => ['foo', 'bar', 'baz'],
                'validation' => 'none',
            ]
        );

        $enrichmentId = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement('select', $enrichmentId);
        $form->getElement('KeyName')->setValue('select');
        $form->getElement('Value')->setValue(0); // Index des ausgewählten Werts: der Ursprungswert des Enrichments (foobar)

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);

        // cleanup
        $enrichmentKey->delete();

        $this->assertEquals('select', $enrichment->getKeyName());
        $this->assertEquals('testvalue', $enrichment->getValue());
    }

    public function testUpdateModelWithSelectTypeWithInvalidValueAndNoValidationAndValidValue()
    {
        $enrichmentKey = $this->createTestEnrichmentKey(
            'select',
            'SelectType',
            [
                'values'     => ['foo', 'bar', 'baz'],
                'validation' => 'none',
            ]
        );

        $enrichmentId = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement('select', $enrichmentId);
        $form->getElement('KeyName')->setValue('select');
        $form->getElement('Value')->setValue(1); // Index des ausgewählten Werts: foo

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);

        $enrichmentKey->delete();

        $this->assertEquals('select', $enrichment->getKeyName());
        $this->assertEquals('foo', $enrichment->getValue());
    }

    public function testGetModel()
    {
        $document    = Document::get(146);
        $enrichments = $document->getEnrichment();
        $enrichment  = $enrichments[0];

        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement($keyName);
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
        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement($keyName);

        $form->getElement('KeyName')->setValue($keyName);
        $form->getElement('Value')->setValue('Test Enrichment Value');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals($keyName, $model->getKeyName());
        $this->assertEquals('Test Enrichment Value', $model->getValue());
    }

    public function testGetModelUnknownId()
    {
        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement($keyName);

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
        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->initValueFormElement($keyName);

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
        $keyNames = EnrichmentKey::getAll();
        $keyName  = $keyNames[1]->getName(); // Test geht davon aus, dass mindestens 2 Enrichment Keys existieren

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement($keyName);

        $post = [
            'Enrichment0' => [
                'KeyName' => ' ',
                'Value'   => '',
            ],
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertCount(2, $form->getErrors('KeyName'));
        $this->assertContains('isEmpty', $form->getErrors('KeyName'));
        $this->assertContains('notInArray', $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    /**
     * @param array $options
     * @param bool  $strictValidation
     * @return SelectType
     */
    private function createTestSelectType($options, $strictValidation = false)
    {
        $selectOptions = ['values' => $options];
        if ($strictValidation) {
            $selectOptions['validation'] = 'strict';
        } else {
            $selectOptions['validation'] = 'none';
        }

        $type = new SelectType();
        $type->setOptions($selectOptions);
        return $type;
    }

    /**
     * @param string $regex
     * @param bool   $strictValidation
     * @return RegexType
     */
    private function createTestRegexType($regex, $strictValidation = false)
    {
        $options = ['regex' => $regex];
        if ($strictValidation) {
            $options['validation'] = 'strict';
        } else {
            $options['validation'] = 'none';
        }

        $type = new RegexType();
        $type->setOptions($options);
        return $type;
    }

    public function testValidationNoneWithSelectTypeFirstOption()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select', $options[0]);

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => 0, // entspricht dem ersten Auswahlwert (foo)
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertTrue($result);

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));
    }

    public function testValidationNoneWithSelectTypeLastOption()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select', $options[count($options) - 1]);

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => count($options) - 1, // entspricht dem letzten Auswahlwert (baz)
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertTrue($result);

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));
    }

    public function testValidationNoneWithSelectTypeAndMissingValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select');

        // im POST-Request fehlt die Angabe des ausgewählten Wertes des Select-Formularfelds
        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertFalse($result);

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationNoneWithSelectTypeAndInvalidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => count($options) + 1, // diese Option nicht zulässig
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertFalse($result);

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationNoneWithSelectTypeAndValidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId, count($options));

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => count($options), // Option ist zulässig (weil Select-Liste um ungültigen Wert ergänzt wurde)
            ],
        ];

        $result = $form->isValid($post);

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);
        $enrichmentValue = $enrichment->getValue();

        // cleanup
        $enrichmentKey->delete();

        $this->assertTrue($result);

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $this->assertEquals($options[count($options) - 1], $enrichmentValue);
    }

    public function testValidationNoneWithSelectTypeAndAcceptedValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId, count($options));

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => 0, // Option ist zulässig (weil Select-Liste um ungültigen Wert ergänzt wurde)
            ],
        ];

        $result = $form->isValid($post);

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);
        $enrichmentValue = $enrichment->getValue();

        // cleanup
        $enrichmentKey->delete();

        $this->assertTrue($result);

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $this->assertEquals('testvalue', $enrichmentValue);
    }

    public function testValidationStrictWithSelectTypeAndInvalidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options, true);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => 0, // wählt den im Enrichment gespeicherten Wert (foobar) aus, der aber nicht mehr zulässig ist
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertFalse($result);

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationStrictWithSelectTypeAndFirstValidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options, true);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => 1, // wählt den ersten Auswahlfeld aus Typkonfiguration (foo) aus
            ],
        ];

        $result = $form->isValid($post);

        $this->assertTrue($result);
        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);
        $this->assertEquals($options[0], $enrichment->getValue());

        // cleanup (darf erst nach dem Aufruf der updateModel-Methode passieren)
        $enrichmentKey->delete(false);
    }

    public function testValidationStrictWithSelectTypeAndLastValidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options, true);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => count($options), // wählt den letzten Auswahlfeld aus Typkonfiguration (baz) aus
            ],
        ];

        $result = $form->isValid($post);

        $this->assertTrue($result);
        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);
        $this->assertEquals($options[count($options) - 1], $enrichment->getValue());

        // cleanup (darf erst nach dem Aufruf der updateModel-Methode passieren)
        $enrichmentKey->delete(false);
    }

    public function testValidationWithSelectTypeAndNoValidationAndInvalidValue()
    {
        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('select', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('select');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('select', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'select',
                'Value'   => 0, // wählt den im Enrichment gespeicherten Wert (foobar) aus
            ],
        ];

        $this->assertTrue($form->isValid($post));
        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));

        $enrichment = Enrichment::get($enrichmentId);
        $form->updateModel($enrichment);
        $this->assertEquals('testvalue', $enrichment->getValue());

        $enrichmentKey->delete();
    }

    public function testValidationWithRegexType()
    {
        $type = $this->createTestRegexType('^abc$');

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'xyz', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationWithRegexTypeWithMissingValue()
    {
        $type = $this->createTestRegexType('^.*$');

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => '', // empty enrichment values are not allowed
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationWithRegexTypeUsedByFirstEnrichmentKey()
    {
        $type = $this->createTestRegexType('^abc$');

        // mit dem Namen soll sichergestellt werden, dass dieser Enrichment-Key
        // in der Auswahlliste als erster Eintrag auftritt
        $enrichmentKey = $this->createEnrichmentKey('aaaaaaaa', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('aaaaaaaa');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement();

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'aaaaaaaa',
                'Value'   => 'xyz', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationStrictWithRegexTypeAndInvalidOriginalValue()
    {
        $type = $this->createTestRegexType('^abc$', true);

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'invalidvalue');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'invalidvalue', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationStrictWithRegexTypeAndInvalidChangedValue()
    {
        $type = $this->createTestRegexType('^abc$', true);

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'invalidvalue');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'anotherinvalidvalue', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationStrictWithRegexTypeAndValidValue()
    {
        $type = $this->createTestRegexType('^abc$', true);

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'abc');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'abc',
            ],
        ];

        $this->assertTrue($form->isValid($post));

        //cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));
    }

    public function testValidationNoneWithRegexTypeAndInvalidOriginalValue()
    {
        $type = $this->createTestRegexType('^abc$');

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'invalidvalue');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'invalidvalue', // der Wert ist zwar ungülitg, wird aber dennoch akzeptiert, weil es der Ursprungswert ist
            ],
        ];

        $this->assertTrue($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));
    }

    public function testValidationNoneWithRegexTypeAndInvalidChangedValue()
    {
        $type = $this->createTestRegexType('^abc$');

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'invalidvalue');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'anotherinvalidvalue', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testValidationNoneWithRegexTypeAndValidValue()
    {
        $type = $this->createTestRegexType('^abc$');

        $enrichmentKey = $this->createEnrichmentKey('regex', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('regex', 'abc');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('regex', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'regex',
                'Value'   => 'abc',
            ],
        ];

        $this->assertTrue($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(0, $form->getErrors('KeyName'));
        $this->assertCount(0, $form->getErrors('Value'));
    }

    public function testEnrichmentKeySpecificTranslationWithRegexType()
    {
        $translate = Application_Translate::getInstance();
        $translate->setTranslations('admin_enrichment_ektest_errorMessage', ['de' => 'de', 'en' => 'en']);
        $translate->loadTranslations(true);

        $type          = $this->createTestRegexType('^abc$');
        $enrichmentKey = $this->createEnrichmentKey('ektest', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('ektest', 'invalidvalue');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('ektest', $enrichmentId);

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'ektest',
                'Value'   => 'anotherinvalidvalue', // invalid value
            ],
        ];

        $this->assertFalse($form->isValid($post));

        // cleanup
        $enrichmentKey->delete();

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_ektest_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    public function testEnrichmentKeySpecificTranslationWithSelectType()
    {
        $translate = Application_Translate::getInstance();
        $translate->setTranslations('admin_enrichment_ektest_errorMessage', ['de' => 'de', 'en' => 'en']);
        $translate->loadTranslations(true);

        $options = ['foo', 'bar', 'baz'];
        $type    = $this->createTestSelectType($options);

        $enrichmentKey = $this->createEnrichmentKey('ektest', $type);
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('ektest');

        $form = new Admin_Form_Document_Enrichment();
        $form->setName('Enrichment0');
        $form->initValueFormElement('ektest');

        $post = [
            'Enrichment0' => [
                'Id'      => $enrichmentId,
                'KeyName' => 'ektest',
                'Value'   => count($options) + 1, // diese Option nicht zulässig
            ],
        ];

        $result = $form->isValid($post);

        // cleanup
        $enrichmentKey->delete();

        $this->assertFalse($result);

        $this->assertCount(0, $form->getErrors('KeyName'));

        $this->assertCount(1, $form->getErrors('Value'));
        $this->assertContains('admin_enrichment_ektest_errorMessage', $form->getElement('Value')->getErrorMessages());
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @param null|array  $options
     * @return EnrichmentInterface
     */
    private function createTestEnrichmentKey($name, $type = null, $options = null)
    {
        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName($name);

        if ($type !== null) {
            $enrichmentKey->setType($type);
        }

        if ($options !== null) {
            if (is_array($options)) {
                $options = json_encode($options);
            }
            $enrichmentKey->setOptions($options);
        }

        $enrichmentKey->store();
        return $enrichmentKey;
    }

    /**
     * @param string        $name
     * @param TypeInterface $type
     * @return EnrichmentInterface
     */
    private function createEnrichmentKey($name, $type)
    {
        $enrichmentKey = $this->createTestEnrichmentKey($name, $type->getName(), $type->getOptions());

        // Methodenaufruf des All-Finders hier erforderlich, damit der interne Cache, in dem
        // alle EnrichmentKeys gehalten werden, neu aufgesetzt wird
        EnrichmentKey::getAll();

        return $enrichmentKey;
    }

    public function testPrepareRenderingAsView()
    {
        $enrichmentKey = $this->createEnrichmentKey('text', new TextType());
        $enrichmentId  = $this->createTestDocWithEnrichmentOfGivenKey('text');

        $form = new Admin_Form_Document_Enrichment();
        $form->populateFromModel(Enrichment::get($enrichmentId));
        $form->prepareRenderingAsView();

        $enrichmentKey->delete();

        $this->assertFalse($form->isRemoveEmptyCheckbox());
    }

    /**
     * Erzeugt ein Testdokument, in dem ein Enrichment mit dem übergebenen Key angelegt wird.
     * Die Methode gibt die in der Datenbank gespeicherte ID des angelegten Enrichments zurück.
     *
     * @param string $keyName Name des Enrichment-Keys
     * @param string $enrichmentValue Wert des Enrichments
     * @return int ID des Enrichments
     */
    private function createTestDocWithEnrichmentOfGivenKey($keyName, $enrichmentValue = 'testvalue')
    {
        $doc        = $this->createTestDocument();
        $enrichment = Enrichment::new();
        $enrichment->setKeyName($keyName);
        $enrichment->setValue($enrichmentValue);
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $doc        = Document::get($docId);
        $enrichment = $doc->getEnrichment()[0];
        return $enrichment->getId();
    }
}
