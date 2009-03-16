<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Controller_Plugin
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Routing plugin for REST action. Only available for module webapi.
 */
class Controller_Plugin_RestManipulation extends Zend_Controller_Plugin_Abstract {

    /**
     * Override generic routeShutdown method for wepapi module.
     *
     * @params Zend_Controller_Request_Abstract $request Holds request object.
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if (($request instanceOf Zend_Controller_Request_Http)
            and ('webapi' === $request->getModuleName())) {
            $request_method = 'get';
            // handle only 4 instead of all (missing: head, options) Rest actions
            // if needed all than use getMethod()
            if (true === $request->isPost()) {
                $request_method = 'post';
            } else if (true === $request->isPut()) {
                $request_method = 'put';
            } else if (true === $request->isDelete()) {
                $request_method = 'delete';
            }
            $current_action = $request->getActionName();
            $request->setActionName($request_method);
            $request->setParam('action', $request_method);
            $request->setParam('original_action', $current_action);
        }
    }
}