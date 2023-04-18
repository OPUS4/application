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

use Opus\Common\EnrichmentKey;
use Opus\Common\Model\ModelException;

class Application_Form_Element_EnrichmentKeyTest extends FormElementTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var string Name des Enrichment-Keys, der für Testzwecke angelegt wird */
    private static $testEnrichmentKeyName = 'TestEnrichmentKey';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_EnrichmentKey';
        $this->expectedDecorators     = [
            'ViewHelper',
            'Errors',
            'Description',
            'ElementHtmlTag',
            'LabelNotEmpty',
            'dataWrapper',
            'ElementHint',
        ];
        $this->expectedDecoratorCount = count($this->expectedDecorators);
        $this->staticViewHelper       = 'viewFormSelect';
        parent::setUp();

        // create a new enrichment key with an untranslated name
        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName(self::$testEnrichmentKeyName);
        $enrichmentKey->store();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // remove previously created enrichment key
        $enrichmentKey = EnrichmentKey::get(self::$testEnrichmentKeyName);
        if ($enrichmentKey !== null) {
            $enrichmentKey->delete();
        }
    }

    /**
     * TODO 2 keys are being excluded - needs formal framework for that with configuration
     */
    public function testOptions()
    {
        // NOTE: This also refreshes the cache for enrichment keys. Static state can carry over between tests.
        $allOptions = EnrichmentKey::getAll();

        $element = $this->getElement(); // creates form element for enrichment keys using cached keys

        $this->assertEquals(count($allOptions) - 2, count($element->getMultiOptions()));
    }

    public function testValidation()
    {
        $element = $this->getElement();

        $this->assertTrue($element->getValidator('InArray') !== false);

        $this->assertTrue($element->isValid('City'));
        $this->assertFalse($element->isValid('UnknownEnrichmentKey'));
    }

    public function testMessageTranslated()
    {
        $translator = Application_Translate::getInstance();

        $this->assertTrue($translator->isTranslated('validation_error_unknown_enrichmentkey'));
    }

    public function testKeysTranslated()
    {
        $this->useEnglish();

        $element = $this->getElement();

        $options = $element->getMultiOptions();

        $this->assertContains('Country', array_keys($options));
        $this->assertEquals('Country of event', $options['Country']);
    }

    /**
     * Wenn es keine Übersetzung für den Schlüssel gibt, soll kein Präfix hinzugefügt werden.
     */
    public function testKeysWithoutTranslationNotPrefixed()
    {
        $this->useEnglish();

        $element = $this->getElement();

        $options = $element->getMultiOptions();

        $this->assertContains(self::$testEnrichmentKeyName, array_keys($options));
        $this->assertNotEquals('EnrichmentTestEnrichmentKey', $options[self::$testEnrichmentKeyName]);
        $this->assertEquals(self::$testEnrichmentKeyName, $options[self::$testEnrichmentKeyName]);
    }

    /**
     * Durch die Einführung eines internen Caches sollen Datenbankanfragen
     * eingespart werden. Damit ein neu hinzugefügter Enrichment-Key im
     * Formularelement berücksichtigt wird, muss der Cache im Test explizit
     * zurückgesetzt werden.
     *
     * @throws ModelException
     */
    public function testNewEnrichmentKeyIsAvailableAsOption()
    {
        $element = $this->getElement();
        $options = $element->getMultiOptions();

        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName('thisnamedoesnotexist');
        $enrichmentKey->store();

        $element                              = $this->getElement();
        $optionsAfterInsertOfNewEnrichmentKey = $element->getMultiOptions();

        $this->assertEquals(count($options), count($optionsAfterInsertOfNewEnrichmentKey));

        // Cache zurücksetzen, so dass der neu angelegte Enrichment-Key berücksichtigt wird
        EnrichmentKey::getAll(true);

        $element                              = $this->getElement();
        $optionsAfterInsertOfNewEnrichmentKey = $element->getMultiOptions();

        // jetzt sollte der neu eingefügte Enrichment-Key für das Formularelement sichtbar sein
        $this->assertEquals(count($options) + 1, count($optionsAfterInsertOfNewEnrichmentKey));

        $enrichmentKey->delete();
    }
}
