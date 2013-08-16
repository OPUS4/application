<?php

$options = getopt('', array(
    "dbname:",
    "user:",
    "password:",
    "host::",
    "port::"
        ));
if (!isset($options['dbname']) || !isset($options['user']) || !isset($options['password'])
) {
    echo "ERROR: argument is missing for update script\n";
    exit;
}

$dsnParts[] = "mysql:dbname={$options['dbname']}";
if (isset($options['host']))
    $dsnParts[] = "host={$options['host']}";
if (isset($options['port']))
    $dsnParts[] = "port={$options['port']}";

$dsn = implode(';', $dsnParts);

try {
    $pdo = new PDO($dsn, $options['user'], $options['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $result = $pdo->query('SELECT id, name, department FROM dnb_institutes');
    foreach ($result as $row) {
        if (empty($row['department']) && strpos($row['name'], ',') !== false) {
            echo "Updating DNB-Institute with ID {$row['id']}...";
            list($name, $department) = explode(',', $row['name'], 2);
            $name = $pdo->quote(trim($name));
            $department = $pdo->quote(trim($department));
            echo "old name: {$row['name']}\n";
            echo "new name: {$name}\n";
            echo "new department name: {$department}\n";
            $updateSql = "UPDATE dnb_institutes SET name=$name, department=$department WHERE id={$row['id']}";
            $updateResult = $pdo->exec($updateSql);
            if ($updateResult === false) {
                $errorString = 'Unknown Error';
                $errorInfo = $pdo->errorInfo();
                if (isset($errorInfo[2]) && !empty($errorInfo[2]))
                    $errorString = $errorInfo[2];
                throw new Exception('Database update failed: ' . $errorString);
            }
            echo "Database update " . ($updateResult != 1 ? "FAILED." : "success.") . "\n";
        }
    }
} catch (Exception $e) {
    echo "$e";
}
