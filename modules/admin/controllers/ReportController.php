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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Controller for generating reports.
 */
class Admin_ReportController extends Application_Controller_Action {

    /**
     * Show overview of registration status for local DOIs.
     */
    public function doiAction() {
        $params = $this->getRequest()->getParams();

        // TODO sollte Registrierung und Prüfung von DOIs besser
        // TODO in einen eigenen Controller wandern? (mit separater Zugriffsberechtigung)
        if (array_key_exists('op', $params)) {
            $operation = $params['op'];
            $docId = array_key_exists('docId', $params) ? $params['docId'] : null;
            switch ($operation) {

                case 'register':
                    if (is_null($docId)) {
                        return $this->handleBulkRegistration();
                    }
                    return $this->handleDoiRegistration($docId);
                    break;

                case 'verify':
                    if (is_null($docId)) {
                        return $this->handleBulkVerification();
                    }
                    return $this->handleDoiVerification($docId);
                    break;
            }
        }

        // Einschränkung der anzuzeigenden lokalen DOIs nach ihrem Registrierungsstatus gewünscht?
        $filter = null;
        if (array_key_exists('filter', $params)) {
            $filter = $params['filter'];
        }
        $this->view->filter = $filter;

        $doiReport = new Admin_Model_DoiReport($filter);

        $this->view->docList = $doiReport->getDocList();
        $this->view->numDoisForBulkRegistration = $doiReport->getNumDoisForBulkRegistration();
        $this->view->numDoisForBulkVerification = $doiReport->getNumDoisForBulkVerification();
        $this->view->title = 'admin_title_doireport';
    }

    /**
     * Löst die Registrierung der lokalen DOI des Dokuments mit der übergebenen ID aus.
     *
     * @param $docId ID des Dokuments
     */
    private function handleDoiRegistration($docId) {
        try {
            $doiManager = new Opus_Doi_DoiManager();
            $doiRegistered = $doiManager->register($docId, true);
            if (!is_null($doiRegistered)) {
                return $this->_helper->Redirector->redirectTo(
                    'doi',
                    $this->view->translate(
                        'admin_report_doi_registered_successfully', $doiRegistered->getValue()
                    )
                );
            }
        }
        catch (Opus_Doi_RegistrationException $e) {
            return $this->_helper->Redirector->redirectTo(
                'doi',
                array('failure' =>
                    $this->view->translate(
                        'admin_report_doi_registration_unexpected_error_specific', $e->getDoi()->getValue()
                    ) . ': ' . $e->getMessage()
                )
            );
        }
        catch (Opus_Doi_DoiException $e) {
            return $this->_helper->Redirector->redirectTo(
                'doi',
                array('failure' =>
                    $this->view->translate('admin_report_doi_registration_unexpected_error', $docId) . ': ' . $e->getMessage()
                )
            );
        }

        return $this->_helper->Redirector->redirectTo(
            'doi',
            array('failure' =>
                $this->view->translate('admin_report_doi_registration_could_not_be_executed', $docId)
            )
        );
    }

    /**
     * Löst die Prüfung der lokalen DOI des Dokuments mit der übergebenen ID aus.
     *
     * @param $docId ID des Dokuments
     */
    private function handleDoiVerification($docId) {
        $doiManager = new Opus_Doi_DoiManager();
        $status = new Opus_Doi_DoiManagerStatus();
        $verifiedDoi = $doiManager->verify($docId, true, null, $status);

        if (!is_null($verifiedDoi)) {
            $docsWithDoiStatus = $status->getDocsWithDoiStatus();
            if (array_key_exists($docId, $docsWithDoiStatus)) {
                $doiStatus = $docsWithDoiStatus[$docId];
                if (!$doiStatus['error']) {
                    return $this->_helper->Redirector->redirectTo(
                        'doi',
                        $this->view->translate('admin_report_doi_verified_successfully', $verifiedDoi->getValue())
                    );
                }

                $failureMsg = $this->view->translate(
                    'admin_report_doi_verification_failed_specific',
                    $verifiedDoi->getValue()
                );
                if ($doiStatus['msg'] != $verifiedDoi->getValue()) {
                    $failureMsg .= ': ' . $doiStatus['msg'];
                }

                return $this->_helper->Redirector->redirectTo('doi', array('failure' => $failureMsg));
            }
        }

        return $this->_helper->Redirector->redirectTo(
            'doi',
            array('failure' => $this->view->translate('admin_report_doi_verification_unexpected_error', $docId))
        );
    }

    private function handleBulkRegistration() {
        $doiManager = new Opus_Doi_DoiManager();
        $status = $doiManager->registerPending();
        return $this->handleBulkOperation($status, 'registration');
    }

    private function handleBulkVerification() {
        $doiManager = new Opus_Doi_DoiManager();
        $status = $doiManager->verifyRegistered();
        return $this->handleBulkOperation($status, 'verification');
    }

    /**
     * Führt eine Bulkoperation auf einer Menge von Dokumenten aus.
     * Es wird die Registrierung ($mode == 'registration') sowie die Prüfung ($mode == 'verification')
     * als Operation unterstützt.
     *
     * @param Opus_Doi_DoiManagerStatus $status
     * @param string $mode
     */
    private function handleBulkOperation($status, $mode) {
        if ($status->isNoDocsToProcess()) {
            // es wurden keine DOIs zur Registrierung oder Prüfung gefunden: springe zur Übersichtsseite zurück
            return $this->_helper->Redirector->redirectTo('doi');
        }

        $numOfSuccessfulOps = 0;
        $numOfFailedOps = 0;
        foreach ($status->getDocsWithDoiStatus() as $docId => $docWithDoiStatus) {
            if ($docWithDoiStatus['error']) {
                $numOfFailedOps++;
            }
            else {
                $numOfSuccessfulOps++;
            }
        }

        if ($numOfFailedOps == 0) {
            return $this->_helper->Redirector->redirectTo(
                'doi',
                $this->view->translate('admin_report_doi_bulk_' . $mode . '_successfully', $numOfSuccessfulOps)
            );
        }

        // es sind Fehler aufgetreten: generische Fehlermeldung anzeigen
        // TODO optional könnte man die einzelnen Fehler ausgeben (werden zusätzlich per Mail verschickt)
        return $this->_helper->Redirector->redirectTo(
            'doi',
            array('failure' =>
                $this->view->translate(
                    'admin_report_doi_bulk_' . $mode . '_failed',
                    $numOfFailedOps,
                    $numOfSuccessfulOps,
                    count($status->getDocsWithDoiStatus())))
        );
    }

}
