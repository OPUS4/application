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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * class to built the mail form for mail contact to author
 */
class Frontdoor_Form_ToauthorForm extends Zend_Form
{
    /**
     * hold author information (name, mail)
     *
     * @var array ('name' => ..., 'mail' => ...)
     *
     * TODO LAMINAS name cannot be 'authors' because this creates a conflict with subform 'authors' in Zend_Form code
     */
    protected $authorsInfo;

    /**
     * Build mail form
     */
    public function init()
    {
        $atLeastOne = new Frontdoor_Form_AtLeastOneValidator();
        $authorSub  = new Zend_Form_SubForm('a');

        if ($this->authorsInfo !== null) {
            $authCheck = null;
            foreach ($this->authorsInfo as $author) {
                $options = ['checked' => true];
                if (count($this->authorsInfo) === 1) {
                    $options['disabled'] = true;
                }
                $authCheck = new Zend_Form_Element_Checkbox(strval($author['id']), $options);
                $atLeastOne->addField($authCheck);
                $authCheck->setLabel($author['name']);
                $authorSub->addElement($authCheck);

                if (count($this->authorsInfo) === 1) {
                    $authCheck->setUncheckedValue(1);
                }
            }

            $authCheck->addValidator($atLeastOne);
            $this->addSubForm($authorSub, 'authors');
        }

        $sender = new Zend_Form_Element_Text('sender');
        $sender->setRequired(true);
        $sender->setLabel('frontdoor_sendername');

        $senderMail = new Zend_Form_Element_Text('sender_mail');
        $senderMail->setRequired(true);
        $senderMail->setLabel('frontdoor_sendermail');
        $senderMail->addValidator('EmailAddress');

        $message = new Zend_Form_Element_Textarea('message');
        $message->setRequired(true);
        $message->setLabel('frontdoor_messagetext');

        $captcha = new Zend_Form_Element_Captcha(
            'foo',
            [
                'label'   => 'label_captcha',
                'captcha' => [
                    'captcha' => 'Figlet',
                    'wordLen' => 6,
                    'timeout' => 300,
                ],
            ]
        );

        $submit = new Zend_Form_Element_Submit('frontdoor_send_mailtoauthor');
        $submit->setLabel('frontdoor_send_mailtoauthor');

        $this->addElements([$sender, $senderMail, $message, $captcha, $submit]);
    }

    /**
     * @param array $authors
     * @return $this
     */
    public function setAuthors($authors)
    {
        $this->authorsInfo = $authors;
        return $this;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data)
    {
        return parent::isValid($data);
    }
}
