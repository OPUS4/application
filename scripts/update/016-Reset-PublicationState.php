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
use Symfony\Component\Console\Output\ConsoleOutput;

$help = <<<TXT
Resetting <info>PublicationState</info>
--------------------------

In the past the document field <info>PublicationState</info> already existed in the database, but it was not used in standard OPUS 4. For OPUS 4.9 the field has been activated and integrated into the user interface. 

The field should be reset before starting to use it, because it might have been set to DRAFT or other default values that are not correct.

<error>If OPUS 4 was modified locally in order to use PublicationState, you might want to keep the current values.</error>
TXT;

$helper = new Application_Update_Helper();

$output = new ConsoleOutput();
$output->writeln($help);
$output->writeln('');

if ($helper->askYesNo('Reset PublicationState of all documents to NULL [Y|n]? ', true)) {
    $helper->log('Reseting PublicationState values ...');

    $database = new Database();

    $sql = <<<SQL
UPDATE `documents` SET `publication_state` = NULL;
SQL;

    $database->exec($sql);
}
