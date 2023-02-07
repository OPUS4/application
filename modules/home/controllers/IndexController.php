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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Repository;

class Home_IndexController extends Application_Controller_Action
{
    /**
     * Do some initialization on startup of every action.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * The home module is the place for all custom static pages.  This function
     * catches all action calls, thus making a new page available via
     * http://.../home/index/page by simply placing it in
     * modules/home/views/scripts/index/page.phtml
     *
     * @param  string $action     The name of the action that was called.
     * @param  array  $parameters The parameters passed to the action.
     *
     * TODO does it make sense? are we using this in the future? now?
     */
    public function __call($action, $parameters)
    {
        if ('Action' !== substr($action, -6)) {
            $this->getLogger()->info(__METHOD__ . ' undefined method: ' . $action);
            parent::__call($action, $parameters);
        }
        // it should be checked if the requested static page exists at all, as
        // otherwise this controller will not throw exceptions of type NO_ACTION
        $actionName = $this->getRequest()->getActionName();

        $phtmlFilesAvailable = $this->getViewScripts();

        if (array_search($actionName, $phtmlFilesAvailable) === false) {
            $this->getLogger()->info(
                __METHOD__ . ' requested file ' . $actionName . '.phtml is not readable or does not exist'
            );
            parent::__call($action, $parameters);
        }

        $help = Application_Translate_Help::getInstance();

        $this->view->text = $help->getContent($actionName);
    }

    /**
     * Switches the language for Zend_Translate and redirects back.
     */
    public function languageAction()
    {
        $module     = null;
        $controller = null;
        $action     = null;
        $language   = null;
        $params     = [];

        foreach ($this->getRequest()->getParams() as $param => $value) {
            switch ($param) {
                case 'rmodule':
                    $module = $value;
                    break;
                case 'rcontroller':
                    $controller = $value;
                    break;
                case 'raction':
                    $action = $value;
                    break;
                case 'rrmodule':
                    $params['rmodule'] = $value;
                    break;
                case 'rrcontroller':
                    $params['rcontroller'] = $value;
                    break;
                case 'rraction':
                    $params['raction'] = $value;
                    break;
                case 'language':
                    $language = $value;
                    break;
                default:
                    $params[$param] = $value;
            }
        }

        $appConfig = new Application_Configuration();

        if (
            $appConfig->isLanguageSelectionEnabled() && $language !== null
                && Application_Translate::getInstance()->isAvailable($language)
        ) {
            $sessiondata           = new Zend_Session_Namespace();
            $sessiondata->language = $language;
        }
        $this->_helper->Redirector->redirectTo($action, '', $controller, $module, $params);
    }

    public function indexAction()
    {
        $this->_helper->mainMenu('home');
        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setServerState('published');
        $this->view->totalNumOfDocs = $finder->getCount();
    }

    public function helpAction()
    {
        $help = Application_Translate_Help::getInstance();

        $this->view->help = $help;

        // this loads content if answers should be shown on separate pages
        if ($help->getSeparateViewEnabled()) {
            $content = $this->getRequest()->getParam('content');
            if ($content !== null) {
                // TODO find generic way to handle redirect 'content'
                if ($content === 'contact') {
                    $this->_helper->Redirector->redirectToAndExit('contact');
                }
                if ($content === 'imprint') {
                    $this->_helper->Redirector->redirectToAndExit('imprint');
                }

                $this->view->contenttitle = "help_title_$content";
                $this->view->content      = $help->getContent($content);
                $this->view->contentId    = $content;
            }
        }

        // active proper entry in main menu
        $this->_helper->mainMenu('help');
    }

    /**
     * only for testing purposes to display a warning via Zend's FlashMessenger
     *
     * TODO remove
     */
    public function failureAction()
    {
        $this->_helper->Redirector->redirectTo('index', ['failure' => 'This is a warning.']);
    }

    /**
     * only for testing purposes to display a notice via Zend's FlashMessenger
     *
     * TODO remove
     */
    public function noticeAction()
    {
        $this->_helper->Redirector->redirectTo('index', ['notice' => 'This is a notice.']);
    }

    /**
     * Returns basenames of all phtml files.
     *
     * @return array Basenames of phtml files for 'home' module
     */
    protected function getViewScripts()
    {
        $phtmlFilesAvailable = [];
        $dir                 = new DirectoryIterator($this->view->getScriptPath('index'));
        foreach ($dir as $file) {
            if ($file->isFile() && $file->getFilename() !== '.' && $file->getFilename() !== '..' && $file->isReadable()) {
                array_push($phtmlFilesAvailable, $file->getBasename('.phtml'));
            }
        }
        return $phtmlFilesAvailable;
    }
}
