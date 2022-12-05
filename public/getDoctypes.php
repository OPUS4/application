<?php
// TODO move into a controller class and use configuration object (not bypassing everything)
$content = file_get_contents('../application/configs/config.ini');
if (preg_match('/[\\n]documentTypes.include =([a-z, ]+)[\\n]/i', $content, $matches)) {
    $includes = array_map('trim', explode(",", $matches[1]));
    //echo "includes[0]: " . $includes[0] . "<br>";
    echo $matches[1];
} else {
    echo "---";
}

if (preg_match('/[\\n]documentTypes.exclude =([a-z, ]+)[\\n]/i', $content, $matches)) {
    $excludes = array_map('trim', explode(",", $matches[1]));
    //echo "excludes[0]: " . $excludes[0] . "<br>";
    echo ";" . $matches[1];
} else {
    echo ";---";
}
//return [$includes,$excludes];
