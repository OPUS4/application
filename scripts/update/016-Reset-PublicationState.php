<?PHP

/**
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Reset PublicationState values after activation of field in OPUS 4.
 */

require_once dirname(__FILE__) . '/../common/update.php';

use Opus\Database;

$helper = new Application_Update_Helper();

if ($helper->askYesNo('Reset PublicationState of all documents to NULL [Y|n]? ', true)) {
    $helper->log('Reseting PublicationState values ...');

    $database = new Database();

    $sql = <<<SQL
UPDATE `documents` SET `publication_state` = NULL;
SQL;

    $database->exec($sql);
}
