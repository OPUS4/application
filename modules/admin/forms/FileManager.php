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
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Form_FileManager extends Application_Form_Model_Abstract
{

    const SUBFORM_UPLOAD = 'Upload';
    const SUBFORM_FILES = 'Files';
    const SUBFORM_INFO = 'Info';
    const SUBFORM_ACTION = 'Action';

    private $_message;

    public function init()
    {
        parent::init();

        $this->addSubForm(new Admin_Form_ActionBox($this), self::SUBFORM_ACTION);
        $this->addSubForm(new Admin_Form_InfoBox(), self::SUBFORM_INFO);

        $this->getSubForm(self::SUBFORM_INFO)->addDecorator(
            'HtmlTag',
            ['class' => 'wrapper', 'openOnly' => true, 'placement' => 'prepend']
        );

        $this->addSubForm(new Admin_Form_Files(), self::SUBFORM_FILES);

        $this->setDecorators(
            [
            'FormElements',
            ['HtmlTag', ['class' => 'wrapper', 'closeOnly' => true]],
            'Form'
            ]
        );

        $this->setName('FileManager');
    }

    /**
     * Initialisiert das Formular mit Werten einer Model-Instanz.
     * @param $model
     */
    public function populateFromModel($document)
    {
        $this->getSubForm(self::SUBFORM_ACTION)->populateFromModel($document);
        $this->getSubForm(self::SUBFORM_INFO)->populateFromModel($document);
        $this->getSubForm(self::SUBFORM_FILES)->populateFromModel($document);
    }

    /**
     * Aktualsiert Model-Instanz mit Werten im Formular.
     * @param $model
     */
    public function updateModel($document)
    {
        $this->getSubForm(self::SUBFORM_FILES)->updateModel($document);
    }

    public function processPost($post, $context)
    {
        $result = parent::processPost($post, $context);

        if (is_null($result)) {
            foreach ($this->getSubForms() as $name => $subform) {
                if (array_key_exists($name, $post)) {
                    $result = $subform->processPost($post[$name], $context);
                    if (! is_null($result)) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function constructFromPost($post, $document = null)
    {
        $this->getSubForm(self::SUBFORM_ACTION)->populateFromModel($document); // TODO needed here?
        $this->getSubForm(self::SUBFORM_INFO)->populateFromModel($document); // TODO needed here?
        if (isset($post[self::SUBFORM_FILES])) {
            $this->getSubForm(self::SUBFORM_FILES)->constructFromPost($post[self::SUBFORM_FILES]);
        }
    }

    public function continueEdit($request, $post)
    {
        $this->getSubForm(self::SUBFORM_FILES)->continueEdit($request, $post[self::SUBFORM_FILES]);
    }

    public static function getInstanceFromPost($post, $document)
    {
        $form = new Admin_Form_FileManager();
        $form->constructFromPost($post, $document);
        return $form;
    }

    public function setMessage($message)
    {
        $this->_message = $message;
    }

    public function getMessage()
    {
        return $this->_message;
    }
}
