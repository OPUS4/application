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
 * @package     Module_Export
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Export_Model_DataciteExport extends Application_Export_ExportPluginAbstract
{

    /**
     * TODO add to interface or make optional
     */
    public function init()
    {
    }

    /**
     * Generates DataCite-XML for document.
     *
     * @return bool wurde (valides oder invalides) XML erzeugt, so gibt die Methode den Rückgabewert true zurück
     * @throws Application_Exception wenn kein Dokument mit der übergebenen ID gefunden werden konnte
     */
    public function execute()
    {
        $docId = $this->getRequest()->getParam('docId');
        if (is_null($docId)) {
            throw new Application_Exception('missing request parameter docId');
        }

        try {
            $document = new Opus_Document($docId);
        } catch (Opus\Model\Exception $e) {
            throw new Application_Exception('could not retrieve document with given ID from OPUS database');
        }

        if ($document->getServerState() != 'published' && ! $this->isAllowExportOfUnpublishedDocs()) {
            throw new Application_Export_Exception('export of unpublished documents is not allowed');
        }

        // wenn URL-Parameter validate auf no gesetzt, dann erfolgt keine Validierung des generierten XML
        $validate = $this->getRequest()->getParam('validate');
        $skipValidation = (! is_null($validate) && $validate === 'no');

        $requiredFieldsStatus = [];
        $generator = new Opus_Doi_DataCiteXmlGenerator();
        if (! $skipValidation) {
            // prüfe, ob das Dokument $document alle erforderlichen Pflichtfelder besitzt
            $requiredFieldsStatus = $generator->checkRequiredFields($document, false);
        }

        $output = null;
        $errors = [];
        try {
            // generiere DataCite-XML, wobei Pflichtfeld-Überprüfung nicht erneut durchgeführt werden soll
            $output = $generator->getXml($document, $skipValidation, true);
        } catch (Opus_Doi_DataCiteXmlGenerationException $e) {
            $errors = $e->getXmlErrors();
        }

        if (empty($errors) && ! is_null($output)) {
            // erzeugtes DataCite-XML zurückgeben (kann valide oder nicht valide sein)
            $response = $this->getResponse();
            $response->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
            // TODO Content-Disposition
            $response->setBody($output);
            return true;
        }

        // HTML-Statusseite mit Fehlermeldungen zurückgeben
        $this->prepareView($document, $requiredFieldsStatus, $errors);
        return false;
    }

    /**
     * TODO add to interface or make optional
     */
    public function postDispatch()
    {
    }

    /**
     * Setzt die View-Objekte für die Generierung der HTML-Statusseite mit den Fehlermeldungen der XML-Generierung.
     *
     * @param Opus_Document $document das aktuell verarbeitete Dokument
     * @param array $requiredFieldsStatus der Status (Existenz bzw. Nichtexistenz) der einzelnen Pflichtfelder
     * @param $errors die bei der DataCite-XML Generierung gefundenen Fehler
     */
    private function prepareView($document, $requiredFieldsStatus, $errors)
    {
        $view = $this->getView();
        $view->requiredFieldsStatus = $requiredFieldsStatus;
        $view->errors = $errors;
        $view->docId = $document->getId();
        $view->docServerState = $document->getServerState();
        $view->validUnpublishedDoc = $this->isUnpublishedDocValid($requiredFieldsStatus);
    }

    /**
     * Nicht freigeschaltete Dokumente haben keinen Wert im Feld ServerDatePublished. Daher
     * muss es bei solchen Dokumenten immer zu einem Validierungsfehler kommen, weil das Feld
     * publicationYear nicht befüllt werden kann.
     *
     * Ist das übergebene Dokument nicht freigeschaltet und ist das Fehlen eines Wertes für
     * publicationYear der einzige Fehler, so soll auf diesen Umstand bei der Anzeige des Validierungsstatus
     * gesondert hingewiesen werden.
     *
     * @param array $requiredFieldsStatus
     */
    private function isUnpublishedDocValid($requiredFieldsStatus)
    {
        $result = false;
        foreach ($requiredFieldsStatus as $fieldName => $status) {
            if (is_string($status) && $status == 'publication_date_missing_non_published') {
                $result = true;
            } else {
                if ($status !== true) {
                    return false;
                }
            }
        }
        return $result; // wurde nur der Wert publication_date_missing_non_published gefunden, so wird true zurückgeben
    }
}
