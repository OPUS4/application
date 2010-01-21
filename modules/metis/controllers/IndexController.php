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
class Metis_IndexController extends Zend_Controller_Action {

	/**
     * build the form for pixel-order
     *
	 * @return void
	 *
	 */
	public function indexAction() {
	   $metisForm = new OrderForm();
       $metisForm->setAction($this->view->url(array('module' => "metis", "controller"=>'index', "action"=>'getpixel')));
       $metisForm->setMethod('post');
       $this->view->form = $metisForm;
	}

    /**
     * get pixels from the VG Wort-Webservice
     *
     * @return void
     *
     */
	public function getpixelAction() {
	    $this->view->title = 'Metis';
        if (true === $this->getRequest()->isPost()) {
           $data = $this->getRequest()->getPost();
           $form = new OrderForm();
           if (true === $form->isValid($data)) {
               $this->view->ok = '1';
               $user = $form->getValue('user');
               $password = $form->getValue('passwd');
               $number = $form->getValue('number');

               // create Client
               $wsdl = "https://213.61.127.251/services/1.0/pixelService.wsdl";
               $options = array('trace' => '1',
                                'login' => $user,
                                'password' => $password);
               $client = new SoapClient ($wsdl,$options);
               // calling PixelOrder
               try {
                   $param = array('count' => $number);
                   $response = $client->orderPixel($param);
                   print_r($response);  // nur zu Testzwecken
                   $publicIds = array();
                   $privateIds = array();
                   $pixels = $response->pixels->pixel;
                   if (true === is_array($pixels)) {
                       for ($pi = 0; $pi < count($pixels); $pi++) {
                           $publicIds [$pi] = $pixels[$pi]->publicIdentificationId;
                           $privateIds[$pi] = $pixels[$pi]->privateIdentificationId;
                       }
                   } else {
                       $publicIds [0] = $response->pixels->pixel->publicIdentificationId;
                       $privateIds[0] = $response->pixels->pixel->privateIdentificationId;
                   }
                   //TODO save pixels in SQL-table
                   $this->view->domain = $response->domain;
                   $this->view->datetime = $response->orderDateTime;
                   $this->view->publicIds = $publicIds;
                   $this->view->privateIds = $privateIds;
               }
               catch (SoapFault $sf) {
                   $this->view->form = $form;
                   $this->view->ok = '0';
                   if (true === isset($sf->detail->orderPixelFault->errormsg)) {
                       $this->view->fault = $sf->detail->orderPixelFault->errormsg;
                   } else {
                        $this->view->fault = $sf->faultstring;
                   }
               }

           } else {      // form not valid
                $this->view->form = $form;
           }
        } else {         // not post
           $form = new OrderForm();
           $this->view->form = $form;
        }

   }
}