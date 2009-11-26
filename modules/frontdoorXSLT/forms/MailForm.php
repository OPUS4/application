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
 * @author      Wolfgang Filter <wolfgang.filter@ub.uni-stuttgart.de>
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * class to built the mail mask for document recommendation via e-mail
 */
class MailForm extends Zend_Form
{
    /**
     * Build easy mail form
     *
     * @return void
     */
    public function init()
    {
        // Create and configure query field elements:
        $recipient = new Zend_Form_Element_Text('recipient');
        $recipient->setRequired(false);
        $recipient->setLabel('frontdoor_recipientname');

        $recipient_mail = new Zend_Form_Element_Text('recipient_mail');
        $recipient_mail->setRequired(true);
        $recipient_mail->setLabel('frontdoor_recipientmail');

        $sender = new Zend_Form_Element_Text('sender');
        $sender->setRequired(false);
        $sender->setLabel('frontdoor_sendername');

        $sender_mail = new Zend_Form_Element_Text('sender_mail');
        $sender_mail->setRequired(false);
        $sender_mail->setLabel('frontdoor_sendermail');

        $message = new Zend_Form_Element_Textarea('message');
        $message->setRequired(false);
        $message->setLabel('frontdoor_messagetext');

        $submit = new Zend_Form_Element_Submit('frontdoor_send_recommendation');
        $submit->setLabel('frontdoor_sendrecommendation');

        // Add elements to form:
        $this->addElements(array($recipient, $recipient_mail, $sender, $sender_mail, $message, $submit));
    }
}
