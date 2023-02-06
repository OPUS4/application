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
use Opus\Common\EnrichmentKeyInterface;
use Opus\Common\Model\ModelException;

/**
 * Unit Tests für Admin_Form_Document_MultiEnrichmentSubForm Formular das
 * mehrere Unterformulare vom Typ Admin_Form_Document_Enrichment verwalten kann.
 */
class Admin_Form_Document_MultiEnrichmentSubFormTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /**
     * dieser Enrichment-Key Name stellt sicher, dass der Enrichment-Key
     * im Auswahlfeld aller Enrichment-Keys an der ersten Position steht
     *
     * @var string
     */
    private static $firstEnrichmentKeyName = 'aaaaaaaaaaaa';

    public function testGetFieldValues()
    {
        $form = new Admin_Form_Document_MultiEnrichmentSubForm('Admin_Form_Document_Enrichment', 'Enrichment');

        // create a test document with four enrichments
        $doc = Document::new();

        $enrichments   = [];
        $enrichments[] = $this->createEnrichment('Audience', 'val1');
        $enrichments[] = $this->createEnrichment('Audience', 'val2');
        $enrichments[] = $this->createEnrichment('opus.doi.autoCreate', 'val3');
        $enrichments[] = $this->createEnrichment('opus.urn.autoCreate', 'val4');

        $doc->setEnrichment($enrichments);
        $doc->store();

        $result = $form->getFieldValues($doc);

        // cleanup: remove latest test document
        $doc->delete();

        // die beiden Enrichments mit dem Schlüssel opus.doi/urn.autoCreate
        // werden gesondert behandelt und erscheinen daher nicht im Unterformular
        $this->assertCount(2, $result);
        $this->assertContains('Audience', $result[0]->getKeyName());
        $this->assertContains('Audience', $result[1]->getKeyName());
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_MultiEnrichmentSubForm(
            'Admin_Form_Document_Enrichment',
            'Enrichment',
            null,
            [
                'columns' => [
                    ['label' => 'KeyName'],
                    ['label' => 'Value'],
                ],
            ],
            'Enrichments'
        );

        // create a test document with two enrichments
        $doc = Document::new();

        $enrichments   = [];
        $enrichments[] = $this->createEnrichment('Audience', 'val1');
        $enrichments[] = $this->createEnrichment('Audience', 'val2');

        $doc->setEnrichment($enrichments);
        $doc->store();

        $form->populateFromModel($doc);

        foreach ($form->getSubForms() as $subForm) {
            $decorators = $subForm->getDecorators();
            $this->assertArrayHasKey('tableRowWrapper', $decorators);
        }

        $this->assertCount(count($enrichments), $form->getSubForms());

        // cleanup: remove latest test document
        $doc->delete();
    }

    public function testConstructFromPost()
    {
        $post = [
            'Enrichment0' => [
                'KeyName' => 'Audience',
                'Value'   => 'foo',
            ],
            'Enrichment1' => [
                'KeyName' => 'Audience',
                'Value'   => 'bar',
            ],
        ];

        $form = new Admin_Form_Document_MultiEnrichmentSubForm(
            'Admin_Form_Document_Enrichment',
            'Enrichment',
            null,
            [
                'columns' => [
                    ['label' => 'KeyName'],
                    ['label' => 'Value'],
                ],
            ],
            'Enrichments'
        );
        $form->constructFromPost($post);

        $this->assertEquals(count($post), count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Text');
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment1', 'Application_Form_Element_Text');
    }

    public function testConstructFromPostSelectType()
    {
        $enrichmentKey = $this->createEnrichmentKey('SelectType', ["values" => ["foo", "bar"]]);

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 1);

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Select');

        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(2, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_InArray', $valueElement->getValidators()));
    }

    public function testConstructFromPostBooleanType()
    {
        $enrichmentKey = $this->createEnrichmentKey('BooleanType');

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 1);

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Checkbox');
    }

    public function testConstructFromPostRegexType()
    {
        $enrichmentKey = $this->createEnrichmentKey('RegexType', ["regex" => "^.*$"]);

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'a');

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Text');

        // check that regex validator is present
        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(2, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_Regex', $valueElement->getValidators()));
    }

    public function testConstructFromPostWithoutType()
    {
        $enrichmentKey = $this->createEnrichmentKey();

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'value');

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        // wird kein Enrichment-Typ angegeben, so wird standardmäßig ein Texteingabefeld für den Enrichment-Wert angezeigt
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Text');

        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(1, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
    }

    public function testConstructFromPostUnknownEnrichmentType()
    {
        $enrichmentKey = $this->createEnrichmentKey('FooBarType');

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'value');

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        // wird ein unbekannter Enrichment-Typ angegeben, so wird standardmäßig ein Texteingabefeld für den Enrichment-Wert angezeigt
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Text');

        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(1, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
    }

    public function testConstructFromPostTextType()
    {
        $enrichmentKey = $this->createEnrichmentKey('TextType');

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'value');

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Text');

        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(1, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
    }

    public function testConstructFromPostTextareaType()
    {
        $enrichmentKey = $this->createEnrichmentKey('TextareaType');

        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, "foo\bar");

        // cleanup step
        $enrichmentKey->delete();

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertEnrichmentSubformWasCreatedProperly($form, 'Enrichment0', 'Application_Form_Element_Textarea');

        $valueElement = $form->getSubForm('Enrichment0')->getElement('Value');
        $this->assertEquals(1, count($valueElement->getValidators()));
        $this->assertTrue(array_key_exists('Zend_Validate_NotEmpty', $valueElement->getValidators()));
    }

    public function testProcessPostSelectionChanged()
    {
        $form    = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'value', Admin_Form_Document_MultiEnrichmentSubForm::ELEMENT_SELECTION_CHANGED);
        $subform = $form->getSubForm('Enrichment0');
        $this->assertTrue(array_key_exists('currentAnchor', $subform->getDecorators()));
    }

    public function testProcessPostRemove()
    {
        $form = $this->createTestPostDataAndConstructForm(self::$firstEnrichmentKeyName, 'value', Admin_Form_Document_MultiEnrichmentSubForm::ELEMENT_ADD);
        $this->assertCount(2, $form->getSubForms());
        $subform = $form->getSubForm('Enrichment1');
        $this->assertCount(4, $subform->getElements());
    }

    /**
     * Hilfsfunktion zum Erzeugen eines neuen Enrichments für den übergebenen
     * Enrichment-Key.
     *
     * @param string $keyName Name des Enrichment-Keys
     * @param string $value Wert des Enrichments
     * @return EnrichmentInterface neu erzeugtes Enrichment-Objekt
     * @throws ModelException
     */
    private function createEnrichment($keyName, $value)
    {
        $enrichment = Enrichment::new();
        $enrichment->setKeyName($keyName);
        $enrichment->setValue($value);
        return $enrichment;
    }

    /**
     * Hilfsfunktion zum Erzeugen eines neuen Enrichment-Keys mit dem übergebenen
     * Namen. Optional kann ein Typ sowie Konfigurationsoptionen übergeben werden.
     *
     * @param string|null $type optionaler Typ des Enrichment-Keys
     * @param array|null  $options optionale Konfigurationsoptionen des Typs
     * @return EnrichmentKeyInterface neu erzeugter Enrichment-Key
     * @throws ModelException
     */
    private function createEnrichmentKey($type = null, $options = null)
    {
        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName(self::$firstEnrichmentKeyName);

        if ($type !== null) {
            $enrichmentKey->setType($type);
        }

        if ($options !== null) {
            $enrichmentKey->setOptions(json_encode($options));
        }

        $enrichmentKey->store();

        $enrichmentKey = EnrichmentKey::fetchByName(self::$firstEnrichmentKeyName);
        $this->assertNotNull($enrichmentKey);

        return $enrichmentKey;
    }

    /**
     * @param Zend_Form $form
     * @param string    $name
     * @param string    $valueElementType
     */
    private function assertEnrichmentSubformWasCreatedProperly($form, $name, $valueElementType)
    {
        $subforms = $form->getSubForms();
        $this->assertArrayHasKey($name, $subforms);

        $subform = $subforms[$name];
        $this->assertEquals($name, $subform->getName());

        $keyNameElement = $subform->getElement('KeyName');
        $this->assertTrue(array_key_exists('tableCellWrapper', $keyNameElement->getDecorators()));

        $valueElement = $subform->getElement('Value');
        $this->assertInstanceOf($valueElementType, $valueElement);
        $this->assertTrue(array_key_exists('tableCellWrapper', $valueElement->getDecorators()));

        $removeElement = $subform->getElement('Remove');
        $this->assertTrue(array_key_exists('tableCellWrapper', $removeElement->getDecorators()));
    }

    /**
     * @param string      $keyName
     * @param string      $value
     * @param null|string $clickedButton
     * @return Admin_Form_Document_MultiEnrichmentSubForm
     * @throws Application_Exception
     */
    private function createTestPostDataAndConstructForm($keyName, $value, $clickedButton = null)
    {
        $post = [
            'Enrichment0' => [
                'KeyName' => $keyName,
                'Value'   => $value,
            ],
        ];

        EnrichmentKey::getAll();

        // trifft nur zu, wenn der Add-Button gedrückt oder ein Enrichment-Key im Select-Feld ausgewählt wurde
        if ($clickedButton !== null) {
            $post[$clickedButton] = '';
        }

        $form = new Admin_Form_Document_MultiEnrichmentSubForm(
            'Admin_Form_Document_Enrichment',
            'Enrichment',
            null,
            [
                'columns'
                => [
                    ['label' => 'KeyName'],
                    ['label' => 'Value'],
                ],
            ],
            'Enrichments'
        );

        $form->constructFromPost($post);

        if ($clickedButton !== null) {
            $form->processPost($post, []);
        }

        return $form;
    }
}
