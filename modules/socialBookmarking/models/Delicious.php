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

class Delicious
{		
	/**
	 * @var String Username for Delicious 
	 */
	var $user;
	
	/**
	 * @var String Delicious-Password
	 */
	var $password;
	
	/**
	 * @var Integer Timeout (constant)
	 */
	var $timeout = 20;
	
	function logit($stringtolog)
	{
		$fp = fopen(LOGFILE, "a");
		fwrite($fp, date("Y-m-d H:i:s")." ".$stringtolog."\n");
		fclose($fp);
	}

	/**
	 * @param array  $data_to_send  All values that should get posted to Delicious 
	 */
	function postit($data_to_send) 
	{
        $delicious = new Zend_Service_Delicious($this->user, $this->password);

        // create a new post and save it (without method call chaining)
        $post = $delicious->createNewPost($data_to_send['usertitle'], $data_to_send['uri']);
        $post->setNotes($data_to_send['description']);
          
        $tags = split("\ ", $data_to_send['tags']);
        foreach ($tags as $tag) {
        	$post->addTag($tag);
        }
        $post->save();
        #print_r($post);
	}
	
	/**
	 * @param String url URL der Datei, deren Tags abgefragt werden sollen 
	 * @return Array mit den einzelnen Tags 
	 */
	function gettags($url) 
	{
        $delicious = new Zend_Service_Delicious($this->user, $this->password);

        $posts = $delicious->getPosts(null, null, $url);
        
        $tags = null;
        
        foreach ($posts as $post) {
        	$tags = $post->getTags();
        }

		return ($tags);
	}

	/**
	 * @return Array mit den Bookmarks des Delicious-Users 
	 */
	function getbookmarks() 
	{
        $delicious = new Zend_Service_Delicious($this->user, $this->password);

        $posts = $delicious->getPosts();
        
		return ($posts);        
	}

	/**
	 * @param string $url URL der Datei, die beim User auf Vorhandensein gepr???ft werden soll
	 * @return boolean Boolean value representing if the user has bookmarked the given URL or not 
	 */
	function userHatBookmark($url) 
	{
        $delicious = new Zend_Service_Delicious($this->user, $this->password);

        $posts = $delicious->getPosts(null, null, $url);
        
        if (count($posts) > 0) {
        	return true;
        }
		return false;
	}

	/**
	 * @param Array POST-Werte des einzutragenden Eintrags
	 * @return Boolean 0 = Eintrag nicht erfolgreich (Timeout), 1 = Eintrag erfolgreich
	 */
	function addBookmark($params)
	{
		$this->postit($params);
	}

	/**
	 * @param String URL des zu entfernenden Eintrags
	 */
	function deleteBookmark($url)
	{
        $delicious = new Zend_Service_Delicious($this->user, $this->password);

        // by specifying URL
        $delicious->deletePost($url);
	}
}