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

/**
 * View helper for access control aspects of rendering administration menu.
 *
 * TODO better way to get ACL?
 */
class Application_View_Helper_AdminMenu extends Zend_View_Helper_Abstract
{
    /**
     * Returns current instance.
     *
     * @return $this
     */
    public function adminMenu()
    {
        return $this;
    }

    /**
     * Determines if page should be rendered as accessible (with link).
     *
     * @param Zend_Navigation_Page $page
     * @return bool
     */
    public function isRenderActive($page)
    {
        $acl = $this->getAcl();

        if (
            ($acl === null
            || $acl->isAllowed(
                Application_Security_AclProvider::ACTIVE_ROLE,
                $page->getResource(),
                $page->getPrivilege()
            )
            || ! $acl->has($page->getResource()))
            && $this->hasAllowedChildren($page)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get parent page.
     *
     * @return array|null
     */
    public function getParent()
    {
        $activePages = $this->view->navigation()->findActive($this->view->container, 0, 1);

        if ($activePages !== null && isset($activePages['page'])) {
            return $activePages['page'];
        } else {
            return null;
        }
    }

    /**
     * Returns ACL object for application.
     *
     * @return Zend_Acl
     */
    public function getAcl()
    {
        return $this->view->navigation()->getAcl();
    }

    /**
     * Determines if description for page can be rendered.
     *
     * @param Zend_Navigation_Page $page
     * @return bool
     */
    public function isRenderDescription($page)
    {
        return $page->description !== null
            && $this->view->translate()->getTranslator()->isTranslated($page->description);
    }

    /**
     * Determines if a page has any accessible children.
     *
     * This function is used to render menu entries as inactive if there are no children
     * that would be rendered active. If there are no children the function returns true
     * as well in order to not prevent rendering of the parent node as active.
     *
     * Some child pages are not tied to resources. Access to those pages is granted.
     *
     * @param Zend_Navigation_Page $page
     * @return bool
     */
    public function hasAllowedChildren($page)
    {
        $pages = $page->getPages();

        $acl = $this->getAcl();

        if ($pages !== null && (count($pages) > 0) && $acl !== null) {
            foreach ($pages as $childPage) {
                if (
                    $acl->isAllowed(
                        Application_Security_AclProvider::ACTIVE_ROLE,
                        $childPage->getResource(),
                        $childPage->getPrivilege()
                    ) || $childPage->getResource() === null
                ) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
