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
 * class to built the mail mask for document recommendation via e-mail
 */
class Frontdoor_Form_MailForm extends Zend_Form
{
    /**
     * Build easy mail form
     */
    public function init()
    {
        // Create and configure query field elements:
        $recipient = new Zend_Form_Element_Text('recipient');
        $recipient->setRequired(false);
        $recipient->setLabel('frontdoor_recipientname');

        $recipientMail = new Zend_Form_Element_Text('recipient_mail');
        $recipientMail->setRequired(true);
        $recipientMail->setLabel('frontdoor_recipientmail');

        $sender = new Zend_Form_Element_Text('sender');
        $sender->setRequired(false);
        $sender->setLabel('frontdoor_sendername');

        $senderMail = new Zend_Form_Element_Text('sender_mail');
        $senderMail->setRequired(false);
        $senderMail->setLabel('frontdoor_sendermail');

        $message = new Zend_Form_Element_Textarea('message');
        $message->setRequired(false);
        $message->setLabel('frontdoor_messagetext');

        $title   = new Zend_Form_Element_Hidden('title');
        $htmlTag = $title->getDecorator('htmlTag');
        $htmlTag->setOption('tag', 'div');
        $title->removeDecorator('label');

        $docId   = new Zend_Form_Element_Hidden('doc_id');
        $htmlTag = $docId->getDecorator('htmlTag');
        $htmlTag->setOption('tag', 'div');
        $docId->removeDecorator('label');

        $docType = new Zend_Form_Element_Hidden('doc_type');
        $htmlTag = $docType->getDecorator('htmlTag');
        $htmlTag->setOption('tag', 'div');
        $docType->removeDecorator('label');

        $submit = new Zend_Form_Element_Submit('frontdoor_send_recommendation');
        $submit->setLabel('frontdoor_sendrecommendation');

        // Add elements to form:
        $this->addElements(
            [$recipient, $recipientMail, $sender, $senderMail, $message, $title, $docId, $docType, $submit]
        );
    }
}
