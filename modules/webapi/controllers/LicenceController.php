<?php

/**
 * TODO
 */
class Webapi_LicenceController extends Controller_Rest {

    /**
     * TODO
     *
     * @see    library/Controller/Controller_Rest#getAction()
     * @return void
     */
    public function getAction() {
        //
        $licence = new Licence();
        $original_action = $this->requestData['original_action'];
        if ((false === empty($original_action))
            and ('index' !== $original_action)) {
            $this->getResponse()->setBody($licence->getLicence($original_action));
        } else {
            $this->getResponse()->setBody($licence->getAllLicences());
        }
        $this->getResponse()->setHttpResponseCode($licence->getResponseCode());
    }
}
