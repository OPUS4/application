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

class Application_View_Helper_TranslationEditLinkTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public function testRenderTranslationEditLink()
    {
        $helper = $this->getHelper();

        $html = $helper->translationEditLink('help_index_general');

        $this->assertEquals(
            '<a id="help_index_general" href="/setup/language/edit/key/help_index_general/back/help">'
            . '<i class="fas fa-edit"></i></a>',
            $html
        );
    }

    public function testRenderOnlyIfSetupAccess()
    {
        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,publish,help']]],
        ]);

        $helper = $this->getHelper();

        $this->enableSecurity();

        $this->loginUser('security8', 'security8pwd');
        $html = $helper->translationEditLink('help_index_general');
        $this->assertEquals('', $html);

        $this->logoutUser();
        $this->loginUser('security11', 'security11pwd');

        $html = $helper->translationEditLink('help_index_general');
        $this->assertEquals(
            '<a id="help_index_general" href="/setup/language/edit/key/help_index_general/back/help">'
            . '<i class="fas fa-edit"></i></a>',
            $html
        );
    }

    public function testRenderOnlyIfKeyEditable()
    {
        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default']]],
        ]);

        $helper = $this->getHelper();

        $html = $helper->translationEditLink('help_index_general');

        $this->assertEquals('', $html);

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,publish,help']]],
        ]);

        // TODO enable editing of help module

        $html = $helper->translationEditLink('help_index_general');

        $this->assertEquals(
            '<a id="help_index_general" href="/setup/language/edit/key/help_index_general/back/help">'
            . '<i class="fas fa-edit"></i></a>',
            $html
        );
    }

    /**
     * @return Application_View_Helper_TranslationEditLink
     */
    protected function getHelper()
    {
        $helper = new Application_View_Helper_TranslationEditLink();
        $helper->setView($this->getView());
        return $helper;
    }
}
