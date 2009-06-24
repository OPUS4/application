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
 * @package     Module_Metis
 * @author      Simone Finkbeiner (simone.finkbeiner@ub.uni-stuttgart.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Metis
 */
//class Edit_IndexController extends Zend_Controller_Action {
class Metis_IndexController extends Zend_Controller_Action {

	/**
	 * Just to be there. No actions taken.
	 *
	 * @return void
	 *
	 */
	public function indexAction() {
		$this->view->title = 'Metis';
		// create Client
		$wsdl = "https://213.61.127.251/TOM/services/1.0/pixelService.wsdl?";
		$options = array('trace' => '1');
		$client = new SoapClient ($wsdl,$options);
		// request the available functions of the webservice
		$avail = $client->__getFunctions();
		$this->view->avail = $avail;
        // request the datatypes
		$types = $client->__getTypes();
        $this->view->types = $types;
		// calling PixelOrder
		try {
        // so funktioniert es nicht, waere korrekt, wenn Datentyp String verlangt waere !
        //  $response = $client->orderPixel('1');
            $param = array('count' => '1');
            $response = $client->orderPixel($param);
            print_r($response);
		}
		catch (SoapFault $sf) {
		    print_r($sf);
		}
	}
}