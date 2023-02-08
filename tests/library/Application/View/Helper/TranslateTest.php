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

class Application_View_Helper_TranslateTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /**
     * Return empty string for 'null' values.
     */
    public function testTranslateNull()
    {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals('', $model->translate(null));
    }

    /**
     * Return $this for no value (default behaviour).
     */
    public function testTranslateWithoutParameter()
    {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals($model, $model->translate());
    }

    public function testTranslateUnknownKey()
    {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals('key123', $model->translate('key123'));
    }

    public function testTranslateKnownKey()
    {
        $this->useGerman();

        $model = new Application_View_Helper_Translate();

        $this->assertEquals('Signatur', $model->translate('SignatureValue'));
    }

    public function testTranslateWithParameters()
    {
        $this->useEnglish();

        $model = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Overview of access control for role \'collectionsadmin\'',
            $model->translate('access_select_module', 'collectionsadmin')
        );
    }

    public function testTranslateWithParameterArray()
    {
        $this->useEnglish();

        $model = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Allow transition from \'state1\' to \'state2\'.',
            $model->translate('acl_resource_workflow_generic', ['state1', 'state2'])
        );
    }

    /**
     * Make sure first value is not interpreted as locale (default behaviour).
     */
    public function testTranslateWithPlaceholderValueMatchingLocale()
    {
        $this->useEnglish();

        $helper = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Collection \'de\' was edited successfully.',
            $helper->translate('admin_collections_edit', 'de')
        );

        $this->assertEquals(
            'Sammlungseintrag \'test\' wurde erfolgreich bearbeitet.',
            $helper->translate('admin_collections_edit', 'test', 'de')
        );
    }

    /**
     * Make sure there is a way to force a different locale without placeholder values.
     */
    public function testTranslateWithLocale()
    {
        $this->useEnglish();

        $helper = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Manage Collections',
            $helper->translate('admin_collectionroles_index')
        );

        $this->assertEquals(
            'Sammlungsverwaltung',
            $helper->translate('admin_collectionroles_index', null, 'de')
        );
    }

    public function testTranslationMultipleParameters()
    {
        $this->useEnglish();

        $helper = new Application_View_Helper_Translate();

        $result = $helper->translate("search_results_from_to", 1, 10);

        $this->assertEquals('Showing results <b>1</b> to <b>10</b>', $result);
    }

    public function testTranslationWithTwoPlaceholders()
    {
        $this->useEnglish();

        $helper = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Showing results <b>1</b> to <b>5</b>',
            $helper->translate('search_results_from_to', [1, 5])
        );

        $this->assertEquals(
            'Ergebnisse <b>1</b> bis <b>5</b>',
            $helper->translate('search_results_from_to', 1, 5, 'de')
        );

        $this->assertEquals(
            'Ergebnisse <b>1</b> bis <b>5</b>',
            $helper->translate('search_results_from_to', [1, 5], 'de')
        );

        $this->assertEquals(
            'Showing results <b>1</b> to <b>5</b>',
            $helper->translate('search_results_from_to', 1, 5)
        );
    }
}
