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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Helper class for getting an array with current request parameters.
 *
 * This is used for repeating requests that have been redirected to login.
 *
 * It is used by the LoginBar and the init.php files.
 */
class Controller_Helper_ReturnParams extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Look for current module, controller, action and parameters. Forwards them to auth controller.
     *
     * returns mixed Associative array containing parameters for auth controller.
     */
    public function getReturnParameters() {
        // TODO put into constructor
        $log = Zend_Registry::get('Zend_Log');

        $params = array();
        foreach (Zend_Controller_Front::getInstance()->getRequest()->getUserParams() as $key => $value) {
            switch ($key) {
            case 'module' :
                $params['rmodule'] = $value;
                break;
            case 'controller' :
                $params['rcontroller'] = $value;
                break;
            case 'action' :
                $params['raction'] = $value;
                break;
            case 'error_handler':
                // don't use for URL generation
                break;
            default :
                $log->debug('Login extra param: ' . $key . " -> " . $value);
                $params[$key] = $value;
                break;
            }
        }

        return $params;
    }

    /**
     * Gets called when the helper is used like a method of the broker.
     *
     * @return array
     */
    public function direct() {
        return $this->getReturnParameters();
    }

}

?>
