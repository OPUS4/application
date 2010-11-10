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
 * @package     Module_Frontdoor
 * @author      Tobias Leidinger <tobias.leidinger@googlemail.com>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * class to built the mail form for mail contact to author
 */
class Frontdoor_Form_ToauthorForm extends Zend_Form
{
    /**
     * hold author information (name, mail)
     * @var array('name' => ..., 'mail' => ...)
     */
    protected $_authors;

    /**
     * Build mail form
     *
     * @return void
     */
    public function init() {
        $first = true;
        $numberOfAuthors = 0;
        $atLeastOne = new Frontdoor_Form_AtLeastOneValidator();
        $displayGroupElements = array();
        $authorSub = new Zend_Form_SubForm('a');

        if (!is_null($this->_authors)) {
            foreach($this->_authors as $author) {
                $mail = $author['mail'];
                $allow = $author['allowMail'];
                if ($allow && !empty($mail)) {
                    $numberOfAuthors++;
                    $options = array('checked' => true);
                    if (sizeof($this->_authors) == 1) {
                        $options['disabled'] = true;
                    }

                } else {
                    $options = array('disabled' => true);
                }
                $authCheck = new Zend_Form_Element_Checkbox($author['id'], $options);
                if ($first) {
                    $firstAuthorCheckbox = $authCheck;
                    $first = false;
                }
                $label = $author['name'];
                if (!$allow) {
                    $translate = Zend_Registry::get('Zend_Translate');
                    $label .= ' (' . $translate->_('frontdoor_mailform_notallowed') .')';
                } else {
                    $atLeastOne->addField($authCheck);
                }
                $authCheck->setLabel($label);
                //$displayGroupElements[] = $author['id'];
                $authorSub->addElement($authCheck);
            }
            if ($numberOfAuthors == 1) {
                $firstAuthorCheckbox->setOptions(array('disabled' => true));
                $firstAuthorCheckbox->setUncheckedValue(1);
            }
            $authCheck->addValidator($atLeastOne);
            //print_r($displayGroupElements);
            //$this->addDisplayGroup($displayGroupElements, 'author_group');
            $this->addSubForm($authorSub, 'authors');
        }
        $sender = new Zend_Form_Element_Text('sender');
        $sender->setRequired(true);
        $sender->setLabel('frontdoor_sendername');

        $sender_mail = new Zend_Form_Element_Text('sender_mail');
        $sender_mail->setRequired(true);
        $sender_mail->setLabel('frontdoor_sendermail');
        $sender_mail->addValidator('EmailAddress');

        $message = new Zend_Form_Element_Textarea('message');
        $message->setRequired(true);
        $message->setLabel('frontdoor_messagetext');

        $captcha = new Zend_Form_Element_Captcha('foo', array(
            'label' => 'Please verify you are human.',
            'captcha' => array(
                'captcha' => 'Figlet',
                'wordLen' => 6,
                'timeout' => 300,
            )));

        $submit = new Zend_Form_Element_Submit('frontdoor_send_mailtoauthor');
        $submit->setLabel('frontdoor_send_mailtoauthor');

        // Add elements to form:
        $this->addElements(array($sender, $sender_mail, $message, $captcha, $submit));
    }

    public function setAuthors($authors) {
        $this->_authors = $authors;
        return $this;
    }

    public function isValid($data) {
        return parent::isValid($data);
        print_r($data);
    }
}
