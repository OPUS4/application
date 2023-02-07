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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

/**
 * Unterformular mit Haupttitel, ID, und Authoren eines Dokuments.
 *
 * Dieses Formular wird in das Metadaten-Formular mit eingegliedert, um einige Grundinformationen über das Dokument
 * anzuzeigen. Es enthält keine aktiven Formularelemente.
 */
class Admin_Form_InfoBox extends Admin_Form_AbstractDocumentSubForm
{
    /**
     * Dokument das angezeigt wird.
     *
     * @var DocumentInterface
     */
    private $document;

    /**
     * Initialisiert das Formular.
     *
     * Setzt den ViewScript Dekorator für die Ausgabe der Dokumentinformationen.
     */
    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);

        parent::init();

        $this->setDecorators(
            [
                ['ViewScript', ['viewScript' => 'infobox.phtml']],
            ]
        );
    }

    /**
     * Initialisiert Formular mit Dokument.
     *
     * @param DocumentInterface $document
     */
    public function populateFromModel($document)
    {
        if ($document instanceof DocumentInterface) {
            $this->document = $document;
        } else {
            $objclass = $document !== null ? get_class($document) : 'null';
            $this->getLogger()->err(__METHOD__ . " Called with instance of '$objclass'.");
        }
    }

    /**
     * Initialisiert Formular nach POST.
     *
     * @param array                  $post
     * @param null|DocumentInterface $document
     */
    public function constructFromPost($post, $document = null)
    {
        if ($document instanceof DocumentInterface) {
            $this->document = $document;
        } else {
            $objclass = $document !== null ? get_class($document) : 'null';
            $this->getLogger()->err(__METHOD__ . " Called with instance of '$objclass'.");
        }
    }

    /**
     * Liefert Dokument zurück.
     *
     * Wird vom ViewScript verwendet, um das Dokument zu holen.
     *
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Meldet, ob Formular leer ist.
     *
     * Dieses Formular soll immer angezeigt werden, daher liefert diese Funktion immer FALSE zurück.
     *
     * @return false FALSE immer
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * Bereitet Formular auf Ausgabe in Metadaten-Übersicht vor.
     *
     * Für dieses Formular solle bei der Vorbereitung nichts passieren, also keine Element entfernt werden.
     */
    public function prepareRenderingAsView()
    {
        // do nothing
    }
}
