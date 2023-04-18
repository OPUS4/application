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

/**
 * View helper for linking to edit form for translation.
 */
class Application_View_Helper_TranslationEditLink extends Application_View_Helper_Abstract
{
    /**
     * @param string $key
     * @return string
     */
    public function translationEditLink($key)
    {
        $html = '';

        if ($this->isEditingEnabled()) {
            $url = $this->getTargetUrl($key);

            $html = "<a id=\"$key\" href=\"$url\"><i class=\"fas fa-edit\"></i></a>";
        }
        return $html;
    }

    /**
     * @return bool
     *
     * TODO check editing of specific key
     */
    public function isEditingEnabled()
    {
        $accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');

        if ($accessControl === null) {
            return false; // just in case - deny editing if access control mechanism isn't available
        }

        $manager        = $this->getTranslationManager();
        $modules        = $manager->getAllowedModules();
        $editingAllowed = false;
        if ($modules === null || in_array('help', $modules)) {
            $editingAllowed = true;
        }

        return $accessControl->accessAllowed('helppages') && $editingAllowed;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getTargetUrl($key)
    {
        return $this->view->url([
            'module'     => 'setup',
            'controller' => 'language',
            'action'     => 'edit',
            'key'        => $key,
            'back'       => 'help',
        ]);
    }

    /**
     * @return Application_Translate_TranslationManager
     */
    protected function getTranslationManager()
    {
        return new Application_Translate_TranslationManager();
    }
}
