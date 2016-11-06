<?php
class Application_Util_MaxUploadSizeTest extends ControllerTestCase {
    
    /**
     * Der Wert von sword:maxUploadSize ist als das Minimum von den folgenden
     * drei Werten definiert:
     * 
     * 1. Konfigurationsparameter publish.maxfilesize
     * 2. PHP-Laufzeitkonfiguration post_max_size
     * 3. PHP-Laufzeitkonfiguration upload_max_filesize
     * 
     */
    public function testMaxUploadSize() {
        $maxUploadSize = new Application_Util_MaxUploadSize();
        $maxUploadSizeByte = $maxUploadSize->getMaxUploadSizeInByte();
        $maxUploadSizeKByte = $maxUploadSize->getMaxUploadSizeInKB();
        
        $config = Zend_Registry::get('Zend_Config');
        $configMaxFileSize = intval($config->publish->maxfilesize);
        $this->assertTrue($maxUploadSizeByte <= $configMaxFileSize, "cond1: $maxUploadSizeByte is greater than $configMaxFileSize");
        
        $postMaxSize = $this->convertToKByte(ini_get('post_max_size'));
        $this->assertTrue($maxUploadSizeKByte <= $postMaxSize, "cond2: $maxUploadSizeKByte is greater than $postMaxSize");
        
        $uploadMaxFilesize = $this->convertToKByte(ini_get('upload_max_filesize'));
        $this->assertTrue($maxUploadSizeKByte <= $uploadMaxFilesize, "cond3: $maxUploadSizeKByte is greater than $uploadMaxFilesize");
    }
    
    private function convertToKByte($val) {
        $valTrim = trim($val);
        $valInt = intval($valTrim);
        $last = strtolower($valTrim[strlen($valTrim) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $valInt *= 1024;
            case 'm':
                $valInt *= 1024;
            case 'k':
                // do nothing
                break;
            default:
                $valInt /= 1024;
        }

        return $valInt;
    }    
}
