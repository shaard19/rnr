<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION
| DATABASE BACKUP SCRIPT
|--------------------------------------------------------------------------
|
| Creates a complete SQL backup of the rnr_collection database.
|
| Backup location:
|     /backups/
|
| Retention:
|     Keeps backups for 14 days.
|
|--------------------------------------------------------------------------
*/


error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| DATABASE CONFIGURATION
|--------------------------------------------------------------------------
*/

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'rnr_collection';


/*
|--------------------------------------------------------------------------
| BACKUP CONFIGURATION
|--------------------------------------------------------------------------
*/

$retentionDays = 14;


/*
|--------------------------------------------------------------------------
| BACKUP DIRECTORY
|--------------------------------------------------------------------------
*/

$backupDir = dirname(__DIR__) .
    DIRECTORY_SEPARATOR .
    'backups';


/*
|--------------------------------------------------------------------------
| CREATE BACKUP DIRECTORY IF IT DOES NOT EXIST
|--------------------------------------------------------------------------
*/

if (!is_dir($backupDir)) {

    if (!mkdir($backupDir, 0755, true)) {

        die(
            "ERROR: Unable to create backup directory.\n"
        );

    }

}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database
);


if ($conn->connect_error) {

    die(
        "ERROR: Database connection failed: " .
        $conn->connect_error .
        "\n"
    );

}


$conn->set_charset('utf8mb4');


/*
|--------------------------------------------------------------------------
| BACKUP FILE NAME
|--------------------------------------------------------------------------
*/

$filename =
    'rnr_collection_' .
    date('Y-m-d_H-i-s') .
    '.sql';


$filepath =
    $backupDir .
    DIRECTORY_SEPARATOR .
    $filename;


/*
|--------------------------------------------------------------------------
| CREATE BACKUP FILE
|--------------------------------------------------------------------------
*/

$file = fopen($filepath, 'w');


if ($file === false) {

    $conn->close();

    die(
        "ERROR: Unable to create backup file.\n"
    );

}


/*
|--------------------------------------------------------------------------
| BACKUP HEADER
|--------------------------------------------------------------------------
*/

fwrite(
    $file,
    "-- ========================================================\n"
);

fwrite(
    $file,
    "-- R&R COLLECTION DATABASE BACKUP\n"
);

fwrite(
    $file,
    "-- Database: {$database}\n"
);

fwrite(
    $file,
    "-- Created: " .
    date('Y-m-d H:i:s') .
    "\n"
);

fwrite(
    $file,
    "-- ========================================================\n\n"
);


fwrite(
    $file,
    "SET FOREIGN_KEY_CHECKS=0;\n\n"
);


/*
|--------------------------------------------------------------------------
| GET DATABASE TABLES
|--------------------------------------------------------------------------
*/

$tablesResult = $conn->query(
    "SHOW TABLES"
);


if ($tablesResult === false) {

    fclose($file);

    if (file_exists($filepath)) {
        unlink($filepath);
    }

    $conn->close();

    die(
        "ERROR: Unable to retrieve database tables: " .
        $conn->error .
        "\n"
    );

}


/*
|--------------------------------------------------------------------------
| EXPORT TABLES
|--------------------------------------------------------------------------
*/

while (
    $tableRow =
    $tablesResult->fetch_row()
) {

    $table = $tableRow[0];


    /*
    |--------------------------------------------------------------------------
    | TABLE HEADER
    |--------------------------------------------------------------------------
    */

    fwrite(
        $file,
        "-- --------------------------------------------------------\n"
    );

    fwrite(
        $file,
        "-- TABLE: {$table}\n"
    );

    fwrite(
        $file,
        "-- --------------------------------------------------------\n\n"
    );


    /*
    |--------------------------------------------------------------------------
    | DROP TABLE
    |--------------------------------------------------------------------------
    */

    fwrite(
        $file,
        "DROP TABLE IF EXISTS `" .
        $conn->real_escape_string($table) .
        "`;\n\n"
    );


    /*
    |--------------------------------------------------------------------------
    | CREATE TABLE
    |--------------------------------------------------------------------------
    */

    $createResult = $conn->query(
        "SHOW CREATE TABLE `" .
        $conn->real_escape_string($table) .
        "`"
    );


    if ($createResult === false) {

        fclose($file);

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $conn->close();

        die(
            "ERROR: Unable to read table structure for: " .
            $table .
            "\n"
        );

    }


    $createRow =
        $createResult->fetch_assoc();


    if (
        !isset(
            $createRow['Create Table']
        )
    ) {

        fclose($file);

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $conn->close();

        die(
            "ERROR: Invalid table structure for: " .
            $table .
            "\n"
        );

    }


    fwrite(
        $file,
        $createRow['Create Table'] .
        ";\n\n"
    );


    /*
    |--------------------------------------------------------------------------
    | EXPORT TABLE DATA
    |--------------------------------------------------------------------------
    */

    $dataResult = $conn->query(
        "SELECT * FROM `" .
        $conn->real_escape_string($table) .
        "`"
    );


    if ($dataResult === false) {

        fclose($file);

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $conn->close();

        die(
            "ERROR: Unable to export data from table: " .
            $table .
            "\n"
        );

    }


    /*
    |--------------------------------------------------------------------------
    | WRITE INSERT STATEMENTS
    |--------------------------------------------------------------------------
    */

    if ($dataResult->num_rows > 0) {

        while (
            $dataRow =
            $dataResult->fetch_assoc()
        ) {

            $columns = [];
            $values = [];


            foreach (
                $dataRow as $column => $value
            ) {

                $columns[] =
                    "`" .
                    str_replace(
                        "`",
                        "``",
                        $column
                    ) .
                    "`";


                if ($value === null) {

                    $values[] = "NULL";

                } else {

                    $values[] =
                        "'" .
                        $conn->real_escape_string(
                            $value
                        ) .
                        "'";

                }

            }


            $insertSql =
                "INSERT INTO `" .
                $conn->real_escape_string(
                    $table
                ) .
                "` (" .
                implode(
                    ', ',
                    $columns
                ) .
                ") VALUES (" .
                implode(
                    ', ',
                    $values
                ) .
                ");\n";


            fwrite(
                $file,
                $insertSql
            );

        }


        fwrite(
            $file,
            "\n"
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FREE RESULT MEMORY
    |--------------------------------------------------------------------------
    */

    $dataResult->free();

    $createResult->free();

}


/*
|--------------------------------------------------------------------------
| RESTORE FOREIGN KEY CHECKING
|--------------------------------------------------------------------------
*/

fwrite(
    $file,
    "SET FOREIGN_KEY_CHECKS=1;\n"
);


/*
|--------------------------------------------------------------------------
| CLOSE BACKUP FILE
|--------------------------------------------------------------------------
*/

fclose($file);


/*
|--------------------------------------------------------------------------
| CLOSE DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$conn->close();


/*
|--------------------------------------------------------------------------
| VERIFY BACKUP FILE
|--------------------------------------------------------------------------
*/

if (!file_exists($filepath)) {

    die(
        "ERROR: Backup file was not created.\n"
    );

}


$fileSize = filesize($filepath);


if (
    $fileSize === false ||
    $fileSize < 100
) {

    unlink($filepath);

    die(
        "ERROR: Backup file appears invalid or empty.\n"
    );

}


/*
|--------------------------------------------------------------------------
| RETENTION CLEANUP
|--------------------------------------------------------------------------
|
| Delete only R&R SQL backup files older than 14 days.
|
|--------------------------------------------------------------------------
*/

$deletedCount = 0;

$cutoffTime =
    time() -
    (
        $retentionDays *
        24 *
        60 *
        60
    );


$backupFiles = glob(
    $backupDir .
    DIRECTORY_SEPARATOR .
    'rnr_collection_*.sql'
);


if ($backupFiles !== false) {

    foreach (
        $backupFiles as $oldBackup
    ) {

        /*
        |--------------------------------------------------------------------------
        | Safety checks
        |--------------------------------------------------------------------------
        */

        if (!is_file($oldBackup)) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Never delete the backup just created
        |--------------------------------------------------------------------------
        */

        if (
            realpath($oldBackup) ===
            realpath($filepath)
        ) {

            continue;

        }


        /*
        |--------------------------------------------------------------------------
        | Check file age
        |--------------------------------------------------------------------------
        */

        $modifiedTime =
            filemtime($oldBackup);


        if ($modifiedTime === false) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Delete only backups older than retention period
        |--------------------------------------------------------------------------
        */

        if (
            $modifiedTime <
            $cutoffTime
        ) {

            if (
                unlink($oldBackup)
            ) {

                $deletedCount++;

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

echo
    "R&R database backup completed successfully.\n\n";


echo
    "Database: " .
    $database .
    "\n";


echo
    "Backup file: " .
    $filename .
    "\n";


echo
    "Location: " .
    $filepath .
    "\n";


echo
    "Size: " .
    number_format(
        $fileSize / 1024,
        2
    ) .
    " KB\n";


echo
    "Retention: " .
    $retentionDays .
    " days\n";


echo
    "Old backups deleted: " .
    $deletedCount .
    "\n";