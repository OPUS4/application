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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Publish_CollectionController
 *
 * @author Susanne Gottwald
 */
class Publish_CollectionController extends Controller_Action {

    public function topAction() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');
        $defaultNS->step = 1;

        $log->debug("TOP Session step: " . $defaultNS->step);

        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_collection_sub');

        $form = new Publish_Form_PublishingThird();

        $action_url = $this->view->url(array('controller' => 'collection', 'action' => 'sub'));

        $form->setMethod('post');

        $form->setAction($action_url);

        $this->view->form = $form;
    }

    public function subAction() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        if ($this->getRequest()->isPost() === true) {

            $post = $this->getRequest()->getPost();

            if (array_key_exists('send', $post)) {
                $this->_forward('deposit', 'deposit');
            } else {
                if (array_key_exists('top', $post)) {
                    $defaultNS->collection['top'] = $post['top'];
                }

                foreach ($post AS $p => $v) {
                    $log->debug("Post: " . $p . " => " . $v);
                    $defaultNS->collection[$p] = $v;
                }

                $defaultNS->step = (int) $defaultNS->step + 1;

                $log->debug("SUB Session step: " . $defaultNS->step);

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate('publish_controller_collection_sub');

                $form = new Publish_Form_PublishingThird();

                $action_url = $this->view->url(array('controller' => 'collection', 'action' => 'sub'));

                $form->setMethod('post');

                $form->setAction($action_url);

                $this->view->form = $form;
            }
        } else {
            return $this->_redirectTo('index', '', 'index');
        }
    }

}

?>
