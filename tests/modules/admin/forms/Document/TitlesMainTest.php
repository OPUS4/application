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

use Opus\Common\DocumentInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Title;

/**
 * Unit Tests fuer das Unterformular fuer die Haupttitel eines Dokuments.
 */
class Admin_Form_Document_TitlesMainTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'translation'];

    /**
     * Prueft, das der Titel in der Dokumentensprache an erste Position zurueckgegeben wird.
     */
    public function testGetFieldValues()
    {
        $form = new Admin_Form_Document_TitlesMain();

        $document = $this->getTestDocument();

        $values = $form->getFieldValues($document);

        $this->assertEquals(2, count($values));
        $this->assertEquals('deu', $values[0]->getLanguage());
        $this->assertEquals('eng', $values[1]->getLanguage());
    }

    public function testIsDependenciesValidTrue()
    {
        $form = new Admin_Form_Document_TitlesMain();

        $document = $this->getTestDocument();

        $form->populateFromModel($document);

        $this->assertEquals(2, count($form->getSubForms()));

        $globalContext = ['General' => ['Language' => 'deu']]; // es gibt einen Titel in 'deu'

        $post = $this->getPostData();

        $this->assertTrue($form->isDependenciesValid($post, $globalContext));
    }

    public function testIsDependenciesValidFalse()
    {
        $form = new Admin_Form_Document_TitlesMain();

        $document = $this->getTestDocument();

        $form->populateFromModel($document);

        $this->assertEquals(2, count($form->getSubForms()));

        $globalContext = ['General' => ['Language' => 'rus']]; // es gibt keinen Titel in 'rus'

        $post = $this->getPostData();

        $this->assertFalse($form->isDependenciesValid($post, $globalContext));
    }

    /**
     * @return DocumentInterface
     * @throws ModelException
     */
    protected function getTestDocument()
    {
        $document = $this->createTestDocument();

        $document->setLanguage('deu');

        $title1 = Title::new();
        $title1->setLanguage('deu');
        $title1->setValue('Deutscher Titel');

        $title2 = Title::new();
        $title2->setLanguage('eng');
        $title2->setValue('English Title');

        $document->setTitleMain([$title1, $title2]);

        return $document;
    }

    /**
     * @return array[]
     */
    protected function getPostData()
    {
        return [
            'TitleMain0' => [
                'Language' => 'eng',
            ],
            'TitleMain1' => [
                'Language' => 'deu',
            ],
        ];
    }
}
