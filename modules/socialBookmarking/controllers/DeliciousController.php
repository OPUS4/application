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
 * @package     Module_SocialBookmarking
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for Bookmarking in Connotea
 * 
 */
class SocialBookmarking_DeliciousController extends Zend_Controller_Action
{
	/**
	 * Show the login form for Connotea, if the user isnt logged in already
	 *
	 * @return void
	 *
	 */
    public function indexAction() 
    {
        $connotea = new Zend_Session_Namespace('delicious');
        try {
        if (true === $connotea->authorized) {
            $connoteaPost = new Delicious;
            $connoteaPost->user = $connotea->user;
            $connoteaPost->password = $connotea->password;
            $this->view->delicioususer = $connotea->user;
            #$this->view->note = '<a href="' . $this->view->url(array('module' => "socialBookmarking", "controller"=>'delicious', "action"=>"logout")) . '">Logout</a>';

            $data = $this->_request->getParams();            
   
            if (true === array_key_exists('docId', $data))
            {
                $connotea->uri = 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('module' => "frontdoor", "controller"=>'index', "action"=>"index", 'docId'=>$data['docId']));
                $connotea->docId = $data['docId'];
                $this->view->deliciouslink = $connoteaPost->gettags($connotea->uri);
                $userHatBookmark = $connoteaPost->userHatBookmark($connotea->uri);

                if ($userHatBookmark === true) {
                    // Show Delete-form
                    $bookmarkForm = new DeliciousBookmarkDeleteForm();
                    $bookmarkForm->setAction($this->view->url(array('module' => "socialBookmarking", "controller"=>'delicious', "action"=>"deletebookmark")));
                    $bookmarkForm->setMethod('post');
                    $this->view->bookmark = $bookmarkForm;
                } else {
                    $bookmarkForm = new DeliciousBookmarkForm();
                    $bookmarkForm->setAction($this->view->url(array('module' => "socialBookmarking", "controller"=>'delicious', "action"=>"postbookmark")));
                    $bookmarkForm->setMethod('post');
                    $this->view->bookmark = $bookmarkForm;
                }
            }
            else 
            {
          	    $this->view->bookmark = $this->view->translate('delicious_no_parameter');
            }
        }
        else {
        	// show login mask
        	$this->view->delicioususer = null;
            $loginForm = new DeliciousLoginForm();
            $loginForm->setAction($this->view->url(array('module' => "socialBookmarking", "controller"=>'delicious', "action"=>"login")));
            $loginForm->setMethod('post');
            $this->view->loginform = $loginForm;
            $this->view->note = '<a href="https://secure.delicious.com/register" target="_blank">' . $this->view->translate('delicious_register') . "</a>\n";
            if ($connotea->loginfail === true) {
            	$this->view->note .= $this->view->translate('delicious_loginfailed');
            	$connotea->loginfail = false;
            }
        }
        }
        catch (Exception $e) {
        	// show login mask
        	$this->view->delicioususer = null;
            $loginForm = new DeliciousLoginForm();
            $loginForm->setAction($this->view->url(array('module' => "socialBookmarking", "controller"=>'delicious', "action"=>"login")));
            $loginForm->setMethod('post');
            $this->view->loginform = $loginForm;
            $this->view->note = '<a href="https://secure.delicious.com/register" target="_blank">' . $this->view->translate('delicious_register') . "</a><br/>\n";
           	$this->view->note .= 'Error ' . $e->getCode() . ': ' . $e->getMessage();
        }
    }
    
    public function loginAction() {
       if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
           	// check login
           	$connotea = new Zend_Session_Namespace('delicious');
           	$connotea->authorized = false;
           	try { 
                $connoteaPost = new Delicious;
                $connoteaPost->user = $data['user'];
                $connoteaPost->password = $data['password'];
           	    $connoteaPost->gettags('http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array('module' => "frontdoor", "controller"=>'index', "action"=>"index", 'docId'=>"1")));
           	}
           	catch (Exception $e) {
            	#if ($e->getCode() === 0) {
                	$connotea->user = null;
                	$connotea->password = null;
                	$connotea->authorized = false;
                	$this->_forward('index');
            	#}           		
           	}
           	$connotea->user = $data['user'];
           	$connotea->password = $data['password'];
           	$connotea->authorized = true;
        }
        $this->_forward('index');
    }

    public function logoutAction() {
       $connotea = new Zend_Session_Namespace('delicious');
       unset($connotea->user);
       $connotea->authorized = false;
       $this->_forward('index');
    }

    public function postbookmarkAction() {
        $connotea = new Zend_Session_Namespace('delicious');
        $connoteaPost = new Delicious;
        $connoteaPost->user = $connotea->user;
        $connoteaPost->password = $connotea->password;
        
        $config = new Zend_Config_Ini('../config/config.ini');
		$system_tags = $config->production->socialBookmarking->connotea->systemTags;
        
        $data = $this->_request->getPost();
        
        $params = array('uri' => $connotea->uri, 'tags' => $system_tags . " " . $data['user_tags'], 'usertitle' => $data['usertitle'], 'description' => $data['userdescription']);
        $post_status = $connoteaPost->addBookmark($params);
        $this->_forward('index');
    }

    public function deletebookmarkAction() {
        $connotea = new Zend_Session_Namespace('delicious');
        $connoteaPost = new Delicious;
        $connoteaPost->user = $connotea->user;
        $connoteaPost->password = $connotea->password;

        $delete_status = $connoteaPost->deleteBookmark($connotea->uri);
        $this->_forward('index');
    }
}