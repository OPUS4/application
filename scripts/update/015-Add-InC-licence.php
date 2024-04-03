<?PHP

/**
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * This update script adds the rightsstatements.org "In Copyright" licence to
 * the database.
 */

require_once dirname(__FILE__) . '/../common/update.php';

use Opus\Common\Licence;
use Opus\Database;

$helper = new Application_Update_Helper();

$licence = Licence::fetchByName('In Copyright');

if ($licence !== null) {
    $helper->log('In Copyright licence seems to be present in database.');
}

if ($helper->askYesNo('Add In Copyright licence to database [Y|n]? ')) {
    $helper->log('Add In Copyright licence ...');

    $database = new Database();

    $script = APPLICATION_PATH . '/db/masterdata/025-add-in-copyright-licence.sql';

    $database->import($script);
}
