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
 * @package     Application_View_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_TranslateTest extends ControllerTestCase {
                                                         #
    /**
     * Return empty string for 'null' values.
     */
    public function testTranslateNull() {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals('', $model->translate(null));
    }

    /**
     * Return $this for no value (default behaviour).
     */
    public function testTranslateWithoutParameter() {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals($model, $model->translate());
    }

    public function testTranslateUnknownKey() {
        $model = new Application_View_Helper_Translate();

        $this->assertEquals('key123', $model->translate('key123'));
    }

    public function testTranslateKnownKey() {
        $this->useGerman();

        $model = new Application_View_Helper_Translate();

        $this->assertEquals('Deutsch', $model->translate('deu'));
    }

    public function testTranslateWithParameters() {
        $this->useEnglish();

        Zend_Registry::get('Zend_Translate')->loadModule('admin');

        $model = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Overview of access control for role \'collectionsadmin\'',
            $model->translate('access_select_module', 'collectionsadmin')
        );
    }

    public function testTranslateWithParameterArray() {
        $this->useEnglish();

        Zend_Registry::get('Zend_Translate')->loadModule('admin');

        $model = new Application_View_Helper_Translate();

        $this->assertEquals(
            'Allow transition from \'state1\' to \'state2\'.',
            $model->translate('acl_resource_workflow_generic', array('state1', 'state2'))
        );
    }

}
