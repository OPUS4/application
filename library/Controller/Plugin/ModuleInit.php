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
 * This file contains code written by Daniel Cousineau (copyright 2009).
 * He released his parts under http://opensource.org/licenses/mit-license.php
 * MIT Licence. So we felt free to change and use his work!
 *
 * @category    Application
 * @package     Controller
 * @author		Daniel Cousineau
 * @copyright   2009 Daniel Cousineau
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version     $Id$
 */

/**
 * This checks the current request module's directory for an initFile (defaults
 * to init.php) and runs it before the controller is loaded.
 *
 * @category    Application
 * @package     Controller
 */
class Controller_Plugin_ModuleInit extends Zend_Controller_Plugin_Abstract {

	public static $initFileName = 'init.php';
	
	/**
	 * @param Zend_Controller_Request_Abstract $request
	 * @return null
	 */
	// public function routeShutdown(Zend_Controller_Request_Abstract $request)
	public function routeShutdown(Zend_Controller_Request_Abstract $request)
	{
		$moduleName = $request->getModuleName();

		$moduleDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory($moduleName);

		//Trim the paths and filenames to prevent any problems
		$initFile = rtrim($moduleDirectory,'/\\') . '/' . ltrim(self::$initFileName,'/\\');

		$this->runInitFile($initFile);
	}

	/**
	 * Run the file in its own cleaned scope
	 *
	 * @param string $_initFile location of the input file
	 * @return null
	 */
	protected function runInitFile($_initFile)
	{
		if( file_exists($_initFile) )
			include_once $_initFile;
	}
}
