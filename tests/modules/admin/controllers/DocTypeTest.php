<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 4/3/14
 * Time: 5:01 PM
 * To change this template use File | Settings | File Templates.
 */

class DocTypeTest extends ControllerTestCase {

    /*
     * Testet, ob die Validierung der Dokumenttypen korrekt ist.
     */
    public function testDoctypeModel() {
        $doctypeModel = new Admin_Model_Doctypes(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/configs/doctypes/');
        $validationArray = $doctypeModel->getDocumentValidation();
        $this->assertTrue($validationArray['foobar'], 1);
        $this->assertTrue($validationArray['bazbar'], 1);
        $this->assertTrue($validationArray['demo_invalidfieldname'] === 0);
        $this->assertTrue($validationArray['demo_invalid'] === 0);
    }

    /*
     * Testet, ob die Dokumenttypen, die inkludiert oder exkludiert sind, ausgegeben werden
     */
    public function testActiveDoctypes() {
        $doctypeModel = new Admin_Model_Doctypes(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/configs/doctypes/');
        $validationArray = $doctypeModel->getActiveDoctypes();
        $this->assertTrue(in_array('all', $validationArray));
        $this->assertTrue(in_array('preprint', $validationArray));
        $this->assertTrue(in_array('demo_invalid', $validationArray));
        $this->assertTrue(in_array('foobar', $validationArray));
        $this->assertFalse(in_array('article', $validationArray));
    }

    /*
     * Testet, ob die korrekte Fehlermeldung ausgegeben wird, wenn das Dokument nicht valide ist
     */
    public function testErrorMessage() {
        $doctypeModel = new Admin_Model_Doctypes(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/configs/doctypes/');
        $doctypeModel->getValidation('demo_invalid');
        $errors = $doctypeModel->getErrors();
        $this->assertTrue($errors['demo_invalid'] === "DOMDocument::schemaValidate(): Element ".
            "'{http://schemas.opus.org/documenttype}field', attribute 'dataType': The attribute 'dataType' is not allowed.");
    }

    /*
     * Ruft die Dokumenttyp-Validierungsseite auf und prüft ob diese korrekt angezeigt wird
     */
    public function testDoctypePage() {
        $this->loginUser('admin', 'adminadmin');
        $this->useEnglish();
        $this->dispatch('/admin/doctype/index');
        $this->assertResponseCode(200);
        $this->assertQuery('//a[@href="doctype/show/document/demo_invalid"]');
        $this->assertQueryContentContains('//div', 'The red-marked document types are not valid.');
    }

    /*
     * Ruft die Fehlerseite für einzelne Dokumenttypen auf und prüft ob diese korrekt angezeigt wird
     */
    public function testDoctypeErrorMessagePage() {
        $this->loginUser('admin', 'adminadmin');
        $this->useEnglish();
        $this->dispatch('/admin/doctype/show/document/demo_invalid');
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('//h4', 'Error message of document type demo_invalid:');
    }
}
