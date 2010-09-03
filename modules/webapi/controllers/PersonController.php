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
 * @package    Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Handles REST requests for resource person
 */
class Webapi_PersonController extends Controller_Rest {

    /**
     * Handles get request for person. If no person id is submitted
     * an error will be returned.
     * There will be never a list of all persons available.
     *
     * @see    library/Controller/Controller_Rest#getAction()
     * @return void
     */
    public function getAction() {
        $person = new Webapi_Model_Person();
        $result = $person->getPerson($this->requestData['original_action']);
        $this->getResponse()->setBody($result);
        $this->getResponse()->setHttpResponseCode($person->getResponseCode());
    }

    /**
     * Add a new person to database.
     *
     * @see library/Controller/Controller_Rest#putAction()
     */
    public function putAction() {
        $rawBody = $this->getRequest()->getRawBody();
        $person = new Webapi_Model_Person();
        $result = $person->addNewPerson($rawBody);
        $this->getResponse()->setBody($result);
        $this->getResponse()->setHttpResponseCode($person->getResponseCode());
    }

    /**
     * Update person data.
     *
     * @see library/Controller/Controller_Rest#postAction()
     */
    public function postAction() {
        $personId = $this->requestData['original_action'];
        $rawBody = $this->getRequest()->getRawBody();
        $person = new Webapi_Model_Person();
        $result = $person->update($personId, $rawBody);
        $this->getResponse()->setBody($result);
        $this->getResponse()->setHttpResponseCode($person->getResponseCode());
    }

    /**
     * Delete a person.
     *
     * @see library/Controller/Controller_Rest#deleteAction()
     */
    public function deleteAction() {
        $personId = $this->requestData['original_action'];
        $person = new Webapi_Model_Person();
        $result = $person->delete($personId);
        $this->getResponse()->setHttpResponseCode($person->getResponseCode());
    }
}
