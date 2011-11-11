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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_FormConfigTest extends ControllerTestCase {

    /**
     * Verifies that every configured field does actually exist for the model.
     *
     * Some models are more complicated to instantiate, espially the link
     * models. At the moment only support for
     * Opus_Model_Dependent_Link_DocumentPerson
     * is implemented.
     */
    public function testValidityOfFieldsConfiguration() {
        $config = Admin_Model_FormConfig::getFieldsConfig();

        $configArray = $config->toArray();

        foreach($configArray as $key => $values) {
            $keySplit = explode('.', $key);
            $modelClass = $keySplit[0];
            foreach($values as $fieldName) {
                $model = new $modelClass;
                // TODO Generic way to cover all link models?
                if ($model instanceof Opus_Model_Dependent_Link_DocumentPerson) {
                    $model->setModel(new Opus_Person());
                }
                $this->assertNotNull($model->getField($fieldName), "$modelClass does not have field $fieldName");
            }
        }
    }

    public function testGetHiddenFields() {
        $config = new Admin_Model_FormConfig();

        $fields = $config->getHiddenFields('Opus_TitleAbstract');

        $this->assertNotNull($fields);
        $this->assertEquals(1, count($fields));
        $this->assertContains('Type', $fields);
    }

    public function testGetDisabledFields() {
        $config = new Admin_Model_FormConfig();

        $fields = $config->getDisabledFields('Opus_Document');

        $this->assertNotNull($fields);
        $this->assertEquals(2, count($fields));
        $this->assertContains('ServerDatePublished', $fields);
        $this->assertContains('ServerDateModified', $fields);
    }

    public function testGetFields() {
        $config = new Admin_Model_FormConfig();

        $fields = $config->getFields('Opus_Reference');

        $this->assertNotNull($fields);
        $this->assertEquals(4, count($fields));
        $this->assertEquals('Type', $fields[0]);
        $this->assertEquals('Value', $fields[1]);
        $this->assertEquals('Label', $fields[2]);
        $this->assertEquals('Relation', $fields[3]);
    }

}
