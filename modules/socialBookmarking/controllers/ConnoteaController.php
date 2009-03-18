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
class SocialBookmarking_ConnoteaController extends Zend_Controller_Action
{
	/**
	 * Show the login form for Connotea, if the user isnt logged in already
	 *
	 * @return void
	 *
	 */
    public function indexAction() 
    {
        $searchForm = new ConnoteaLoginForm();
        $searchForm->setAction($this->view->url(array('module' => "socialBookmarking", "controller"=>'connotea', "action"=>"login")));
        $searchForm->setMethod('post');

        $connotea = new Zend_Session_Namespace('connotea');
        if (false === isset($connotea->user)) {
        	// show login mask
            $this->view->form = $searchForm;
            $this->view->note = '<a href="http://www.connotea.org/register" target="_blank">' . $this->view->translate('connotea_register') . "</a></div>\n";
        }
        else {
        	$this->view->note = 'Eingeloggt als ' . $connotea->user . '! ';
        	$this->view->note .= '<a href="' . $this->view->url(array('module' => "socialBookmarking", "controller"=>'connotea', "action"=>"logout")) . '">Logout</a>';
        }
        /*    $connoteaPost = new Connotea;
            $connoteaPost->user = $_SESSION["connoteauser"];
            $connoteaPost->password = $_SESSION["connoteapassword"];
            print ("<div>$connotea_loggedin1 " . $connoteaPost->user . " $connotea_loggedin2 <a href=\"" . $_SERVER["PHP_SELF"] . "?source_opus=" . $source_opus . "&la=" . $la . "&connotealogout=true\">Logout</a></div>");
            $return = $connoteaPost->listTags("$volltext_url/$jahr/$source_opus/");
            if ($return == -1) {
                echo "<div>$taglist_failure</div>";
            } else if (count($return) > 0) {
                echo "<div>$connotea_tags";
                echo "<ul>";
                foreach($return as $giventag) {
                    echo "<li>$giventag</li>";
                }
                echo "</ul></div>";
            } else {
                echo "<div>$connotea_no_tags</div>";
            }
            print ("<form action=\"" . $_SERVER["PHP_SELF"] . "?source_opus=" . $source_opus . "&la=" . $la . "\" method=\"post\">\n");
            print ("<input type=\"hidden\" name=\"uri\" value=\"$volltext_url/$jahr/$source_opus/\"/><br/>\n");
            // Pr�fen: hat User dieses Dokument schon gebookmarkt?
            $userHatBookmark = $connoteaPost->userHatBookmark("$volltext_url/$jahr/$source_opus/");
            if ($userHatBookmark == -1) {
                echo $failure_bmcheck;
            } else if ($userHatBookmark == 1) {
                // Wenn ja: L�schen Button
                print ("<input type=\"submit\" name=\"delete_connotea_bm\" value=\"$delete_connotea_bookmark\"/>\n");
            } else {
                // Sonst: Bookmarking-Formular
                $system_tags = $opus->value("system_tags");
                print ("<input type=\"hidden\" name=\"system_tags\" value=\"" . $system_tags . "\" /><br/>\n");
                #print("<input type=\"hidden\" name=\"redirect\" value=\"http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."\"/><br/>\n");
                print ("Eigene Tags: <input type=\"text\" name=\"user_tags\" /><br/>\n");
                print ("Titel: <input type=\"text\" name=\"usertitle\" value=\"" . $title . "\" /><br/>\n");
                print ("Beschreibung: <textarea name=\"userdescription\"></textarea><br/>\n");
                print ("<input type=\"submit\" name=\"connotea_bm\" value=\"$connotea_bookmark\"/>\n");
            }
            print ("</form>\n");*/
    }
    
    public function loginAction() {
       if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
            $connoteaPost = new Connotea;
            $connoteaPost->user = $data['user'];
            $connoteaPost->password = $data['password'];
            if (true === $connoteaPost->login()) {
            	$connotea = new Zend_Session_Namespace('connotea');
            	$connotea->user = $data['user'];
            }
        }
        $this->_forward('index');
    }

    public function logoutAction() {
       $connotea = new Zend_Session_Namespace('connotea');
       unset($connotea->user);
       $this->_forward('index');
    }

    public function postBookmarkAction() {
        if ($_POST["connotea_bm"] && $_SESSION["connoteauser"]) {
            $params = array('uri' => $_POST["uri"], 'tags' => $_POST["system_tags"] . " " . $_POST["user_tags"], 'usertitle' => $_POST["usertitle"], 'description' => $_POST["userdescription"]);
            $connoteaPost = new Connotea;
            $connoteaPost->user = $_SESSION["connoteauser"];
            $connoteaPost->password = $_SESSION["connoteapassword"];
            $post_status = $connoteaPost->addBookmark($params);
            if ($post_status) {
                echo $add_success;
            } else {
                echo $add_failure;
            }
        }
        if ($_POST["delete_connotea_bm"] && $_SESSION["connoteauser"]) {
            $connoteaPost = new Connotea;
            $connoteaPost->user = $_SESSION["connoteauser"];
            $connoteaPost->password = $_SESSION["connoteapassword"];
            $delete_status = $connoteaPost->deleteBookmark($_POST["uri"]);
            if ($delete_status) {
                echo $delete_success;
            } else {
                echo $delete_failure;
            }
        }
    }
}