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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Setup_Form_LanguageSearchTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'Translation';

    public function testInit()
    {
        $form = new Setup_Form_LanguageSearch();

        $this->assertCount(5, $form->getElements());
    }

    public function testPopulateFromRequest()
    {
        $form = new Setup_Form_LanguageSearch();

        $request = $this->getRequest();
        $request->setMethod('GET');

        $this->assertNull($form->getElement($form::ELEMENT_MODULES)->getValue());
        $this->assertNull($form->getElement($form::ELEMENT_SCOPE)->getValue());
        $this->assertNull($form->getElement($form::ELEMENT_STATE)->getValue());

        $request->setParam('modules', 'account');
        $request->setParam('scope', 'key');
        $request->setParam('state', 'edited');

        $form->populateFromRequest($request);

        $this->assertEquals('account', $form->getElement($form::ELEMENT_MODULES)->getValue());
        $this->assertEquals('key', $form->getElement($form::ELEMENT_SCOPE)->getValue());
        $this->assertEquals('edited', $form->getElement($form::ELEMENT_STATE)->getValue());
    }

    public function testPopulateFromRequestPost()
    {
        $form = new Setup_Form_LanguageSearch();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost([
            'search'  => '',
            'modules' => 'account',
            'scope'   => 'translation',
            'state'   => 'added',
            'show'    => 'Show',
        ]);

        $form->populateFromRequest($request);

        $this->assertEquals('account', $form->getElement($form::ELEMENT_MODULES)->getValue());
        $this->assertEquals('translation', $form->getElement($form::ELEMENT_SCOPE)->getValue());
        $this->assertEquals('added', $form->getElement($form::ELEMENT_STATE)->getValue());
    }

    public function testPopulateFromRequestAllPost()
    {
        $form = new Setup_Form_LanguageSearch();

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost([
            'search'  => '',
            'modules' => 'all',
            'scope'   => 'all',
            'state'   => 'all',
            'show'    => 'Show',
        ]);

        $form->populateFromRequest($request);

        $this->assertNull($form->getElement($form::ELEMENT_MODULES)->getValue());
        $this->assertNull($form->getElement($form::ELEMENT_SCOPE)->getValue());
        $this->assertNull($form->getElement($form::ELEMENT_STATE)->getValue());
    }
}
