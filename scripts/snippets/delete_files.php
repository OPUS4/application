<?php

$startId = 1;
$endId = 90;

for ($i = $startId; $i <= $endId; $i++) {

    $d = null;
    try {
        $d = new Opus_Document($i);
    }
    catch (Opus_Model_NotFoundException $e) {
        // document with id $i does not exist
        continue;
    }
    $files = $d->getFile();
    foreach ($files as $file) {
        try {
            $file->doDelete($file->delete());
        }
        catch (Exception $e) {
            // ignore exception (is thrown since file does not exist physically)
        }
    }
    echo 'delete all files associated with docId: ' . $d->getId() . "\n";
}

exit();