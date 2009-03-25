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
 * @package     Module_statistic
 * @author      Birgit Dressler (b.dressler@sulb.uni-saarland.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_statistik
 */
class Statistic_IndexController extends Zend_Controller_Action {

	/**
	 * Just to be there. No actions taken.
	 *
	 * @return void
	 *
	 */
	public function indexAction() {
		$this->view->title = 'statistic';
		$counter = Statistic_LocalCounter::getInstance();
        $form = new Test();
        print_r($_POST);
        $form->populate($_POST);
        $this->view->form = $form;

        $documentId = $form->getValue('document_id');
        $fileId = $form->getValue('file_id');
        $ip = $form->getValue('ip');
        $userAgent = $form->getValue('user_agent');
		$result = $counter->count($documentId, $fileId, $ip, $userAgent);
		if ($result === FALSE) {
		    $this->view->doubleClick = true;
		} else {
		    $this->view->doubleClick = false;
		    $this->view->count = $result;
		}

		$this->view->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->view->redirectStatus = $_SERVER['REDIRECT_STATUS'];
		//print_r($_SERVER);
		//$registry = Zend_Registry::getInstance();
		//print_r($registry);
	}

}
