<?php

/**
 * Test class for Setup_Model_Language_TranslationManager.
 */
class Setup_Model_Language_TranslationManagerTest extends ControllerTestCase {

    /**
     * @var Setup_Model_Language_TranslationManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp() {
        parent::setUp();
        $this->object = new Setup_Model_Language_TranslationManager;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown() {
        
    }

    /**
     * @todo Implement testGetFiles().
     */
    public function testGetFiles() {
        $files = $this->object->getFiles();
        $this->assertEquals(array(), $files, 'Expected empty result with no modules set');
        $this->object->setModules(array('default'));
        $files = $this->object->getFiles();
        $this->assertNotEquals(array(), $files, 'Expected non empty result with module set');
    }

    /**
     */
    public function testGetTranslations() {

        $sortKeys = array(
            Setup_Model_Language_TranslationManager::SORT_DIRECTORY,
            Setup_Model_Language_TranslationManager::SORT_FILENAME,
            Setup_Model_Language_TranslationManager::SORT_LANGUAGE,
            Setup_Model_Language_TranslationManager::SORT_MODULE,
            Setup_Model_Language_TranslationManager::SORT_UNIT,
            Setup_Model_Language_TranslationManager::SORT_VARIANT
        );

        $this->object->setModules(array('default'));

        foreach (array(SORT_ASC, SORT_DESC) as $sortOrder) {
            foreach ($sortKeys as $sortKey) {
                $actualValues = array();
                $translations = $this->object->getTranslations($sortKey, $sortOrder);
                foreach ($translations as $translation) {
                    $actualValues[] = $translation[$sortKey];
                }
                $sortedValues = $actualValues;
                if ($sortOrder == SORT_ASC)
                    sort($sortedValues, SORT_STRING);
                elseif ($sortOrder == SORT_DESC)
                    rsort($sortedValues, SORT_STRING);
                $this->assertEquals($sortedValues, $actualValues);
            }
        }
    }

    /**
     * 
     */
    public function testSetModules() {

        $this->object->setModules(array('default'));
        $files = $this->object->getFiles();
        $this->assertEquals(array('default'), array_keys($files));

        $this->object->setModules(array('default', 'home'));
        $files = $this->object->getFiles();
        $this->assertEquals(array('default', 'home'), array_keys($files));
    }

    /**
     * 
     */
    public function testSetFilter() {
        $filter = 'error';

        $this->object->setModules(array('default'));
        $allTranlsations = $this->object->getTranslations();

        $this->object->setFilter($filter);
        $filteredTranlsations = $this->object->getTranslations();

        $this->assertLessThan(count($allTranlsations), count($filteredTranlsations), 'Expected count of filtered subset of translations to be less than all translations');

        foreach ($filteredTranlsations as $translation) {
            $this->assertTrue(strpos($translation['unit'], $filter) !== false, 'Expected filtered translation unit to contain filter string');
        }
    }

}

?>
