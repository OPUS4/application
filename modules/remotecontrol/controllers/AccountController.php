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
 * @category    Application
 * @package     Module_Collection
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Remotecontrol_AccountController extends Controller_Action {

    public function addAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();

        $login      = $request->getParam('login');
        $password   = $request->getParam('password');
        $user_roles = $request->getParam('user-roles');

        $test_account = Opus_Account::fetchAccountByLogin($login);
        if (!is_null($test_account)) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: Account '$login' already exists.");
            return;
        }

        $account = new Opus_Account();
        $account->setLogin($login);
        $account->setPassword($password);

        foreach (explode(",", $user_roles) AS $role_name) {
            $role_name = trim($role_name);
            $role = Opus_Role::fetchByName($role_name);

            if ($role instanceof Opus_Role) {
                $account->addRole($role);
            }
        }

        try {
            $account->store();
        }
        catch (Opus_Security_Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: " . $e->getMessage());
            return;
        }

        $this->getResponse()->setBody('SUCCESS');
    }

    public function changePasswordAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();

        $login        = $request->getParam('login');
        $password     = $request->getParam('password');
        $password_new = $request->getParam('password-new');

        if (is_null($password_new) || trim($password_new) == '') {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: Empty password given.");
            return;
        }

        $account = Opus_Account::fetchAccountByLogin($login);
        if (is_null($account)) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: Account '$login' does not exist.");
            return;
        }

        if (true !== $account->isPasswordCorrect($password)) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: Incorrect password given.");
            return;
        }

        try {
            $account->setPassword($password_new);
            $account->store();
        }
        catch (Opus_Security_Exception $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->getResponse()->setBody("ERROR: " . $e->getMessage());
            return;
        }

        $this->getResponse()->setBody('SUCCESS');
    }
}
