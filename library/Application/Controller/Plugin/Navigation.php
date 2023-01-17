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

use Opus\Common\Security\Realm;

/**
 * Initialize the navigation bar.
 */
class Application_Controller_Plugin_Navigation extends Zend_Controller_Plugin_Abstract
{
    /**
     * Set up Navigation.
     *
     * @param Zend_Controller_Request_Abstract $request The current request.
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // Hide menu entries based on privileges
        $navigation = Zend_Registry::get('Opus_Navigation');

        if (empty($navigation)) {
            return;
        }

        // Create a Realm instance.
        $realm = Realm::getInstance();

        // Der folgende Code sorgt dafür, daß für Nutzer mit Zugriff auf das 'admin' und das 'review' Modul der Link
        // zu den Review Seiten in der Administration angezeigt wird.
        if ($realm->checkModule('admin') || ! $realm->checkModule('review')) {
            // Entferne Link zu Review
            $page = $navigation->findBy('label', 'review_menu_label');
            $navigation->removePage($page);
        }

        if (! $realm->checkModule('admin')) {
            // Entferne Link zu Admin
            $page = $navigation->findBy('label', 'admin_menu_label');
            $navigation->removePage($page);
        }
    }
}
