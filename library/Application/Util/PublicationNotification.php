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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Email notification class for PUBLICATION context.
 *
 * TODO this is just a first step in refactoring Application_Util_Notification
 */
class Application_Util_PublicationNotification extends Application_Util_Notification
{
    /*
     * TODO old prepareMail code to get author emails in proper format
     *
     * if ($context === self::PUBLICATION) {
                    $email = trim($author->getEmail());
                    if (!empty($email) && (empty($notifyAuthors) || (isset($notifyAuthors[$index])
                                && $notifyAuthors[$index]))) {
                        array_push($authorAddresses, array( "name" => $name, "address" => $email));
                    }
                }
     */

    /**
     * @param array|null             $authorAddresses
     * @param null|DocumentInterface $document
     * @param bool                   $notifySubmitter
     * @return array
     * @throws Zend_Exception
     */
    public function getRecipients($authorAddresses = null, $document = null, $notifySubmitter = true)
    {
        $addresses = [];

        $config = $this->getConfig();
        $logger = $this->getLogger();

        if (isset($config->notification->document->published->email)) {
            $addresses = $this->buildAddressesArray(
                $config->notification->document->published->email
            );
        }

        if ($authorAddresses !== null) {
            for ($i = 0; $i < count($authorAddresses); $i++) {
                $authorAddress = $authorAddresses[$i];
                array_push($addresses, $authorAddress);
                $logger->debug(
                    'send publication notification mail to author ' . $authorAddress['address']
                    . ' (' . $authorAddress['name'] . ')'
                );
            }
        }

        if ($notifySubmitter && $document !== null) {
            $submitter = $document->getPersonSubmitter();
            if (! empty($submitter)) {
                $name  = trim($submitter[0]->getLastName() . ', ' . $submitter[0]->getFirstName());
                $email = $submitter[0]->getEmail();
                if ($email !== null) {
                    $email = trim($email);
                    if (strlen($email) > 0) {
                        array_push($addresses, ["name" => $name, "address" => $email]);
                        $logger->debug("send publication notification mail to submitter $email ($name)");
                    }
                }
            }
        }

        return $addresses;
    }

    /**
     * @param int    $docId
     * @param array  $authors
     * @param string $title
     * @param string $url
     * @return string|null
     * @throws Zend_Exception
     */
    public function getMailBody($docId, $authors, $title, $url)
    {
        $config = $this->getConfig();

        if (isset($config->notification->document->published->template)) {
            return $this->getTemplate(
                $config->notification->document->published->template,
                $docId,
                $authors,
                $title,
                $url
            );
        }

        return null;
    }

    /**
     * @return bool
     * @throws Zend_Exception
     */
    public function isEnabled()
    {
        $config = $this->getConfig();
        return isset($config->notification->document->published->enabled)
            && filter_var($config->notification->document->published->enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string|null
     * @throws Zend_Exception
     */
    public function getSubjectTemplate()
    {
        $config = $this->getConfig();
        if (isset($config->notification->document->published->subject)) {
            return $config->notification->document->published->subject;
        }
        return null;
    }
}
