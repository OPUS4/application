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
 * @category    Tests
 * @package     Application_View_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_AdminMenuTest extends ControllerTestCase
{

    protected $additionalResources = ['database', 'authz', 'view', 'navigation', 'translation'];

    private $_helper;

    public function setUp()
    {
        parent::setUpWithEnv('production');
        $this->assertSecurityConfigured();
        $this->_helper = new Application_View_Helper_AdminMenu();
        $this->_helper->setView(Zend_Registry::get('Opus_View'));
    }

    private function getPageByLabel($label)
    {
        return $this->_helper->view->navigation()->findByLabel($label);
    }

    public function testAdminMenu()
    {
        $this->assertSame($this->_helper, $this->_helper->adminMenu());
    }

    public function testGetAcl()
    {
        $this->assertSame(Zend_Registry::get('Opus_Acl'), $this->_helper->getAcl());
    }

    public function testHasAllowedChildren()
    {
        $this->loginUser('security8', 'security8pwd');

        $page = $this->getPageByLabel('admin_title_info');
        $this->assertTrue($this->_helper->hasAllowedChildren($page));

        $page = $this->getPageByLabel('admin_title_documents');
        $this->assertTrue($this->_helper->hasAllowedChildren($page));

        $page = $this->getPageByLabel('admin_title_collections');
        $this->assertTrue($this->_helper->hasAllowedChildren($page));

        $page = $this->getPageByLabel('admin_title_setup');
        $this->assertFalse($this->_helper->hasAllowedChildren($page));

        // activate sub entry below 'admin_title_setup'
        $acl = Zend_Registry::get('Opus_Acl');
        $acl->allow(Application_Security_AclProvider::ACTIVE_ROLE, 'options');

        $page = $this->getPageByLabel('admin_title_config');
        $this->assertTrue($this->_helper->hasAllowedChildren($page));
    }

    public function testIsRenderDescription()
    {
        // show description if attribute is set and translation exists
        $page = $this->getPageByLabel('admin_title_documents');
        $this->assertTrue($this->_helper->isRenderDescription($page));

        // only show description if translation exits,even if description is set
        $page = $this->getPageByLabel('admin_title_setup');
        $this->assertFalse($this->_helper->isRenderDescription($page));
        $this->assertNotNull($page->description);
    }

    public function testOaiLinkRendered()
    {
        $this->loginUser('security19', 'security19pwd');

        $page = $this->getPageByLabel('admin_title_oailink');
        $this->assertTrue($this->_helper->hasAllowedChildren($page));
        $this->assertTrue($this->_helper->isRenderActive($page));
    }
}
