<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION
| APPLICATION BACKUP SCRIPT
|--------------------------------------------------------------------------
|
| Creates a ZIP archive of the complete R&R application.
|
| Included:
|   admin/
|   cashier/
|   config/
|   assets/
|   includes/
|   vendor/
|   and other application files
|
| Excluded:
|   backups/
|   backup ZIP files
|
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
*/

$projectDir = dirname(__DIR__);

$backupDir =
    $projectDir .
    DIRECTORY_SEPARATOR .
    'backups';

$retentionDays = 14;


/*
|--------------------------------------------------------------------------
| VERIFY BACKUP DIRECTORY
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
| ZIP SUPPORT
|--------------------------------------------------------------------------
*/

if (!class_exists('ZipArchive')) {

    die(
        "ERROR: PHP ZipArchive extension is not enabled.\n"
    );

}


/*
|--------------------------------------------------------------------------
| BACKUP FILE
|--------------------------------------------------------------------------
*/

$filename =
    'rnr_application_' .
    date('Y-m-d_H-i-s') .
    '.zip';

$filepath =
    $backupDir .
    DIRECTORY_SEPARATOR .
    $filename;


/*
|--------------------------------------------------------------------------
| CREATE ZIP
|--------------------------------------------------------------------------
*/

$zip = new ZipArchive();

$result = $zip->open(
    $filepath,
    ZipArchive::CREATE |
    ZipArchive::OVERWRITE
);


if ($result !== true) {

    die(
        "ERROR: Unable to create application backup ZIP.\n" .
        "ZIP error code: " .
        $result .
        "\n"
    );

}


/*
|--------------------------------------------------------------------------
| PROJECT DIRECTORY ITERATOR
|--------------------------------------------------------------------------
*/

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $projectDir,
        FilesystemIterator::SKIP_DOTS
    ),
    RecursiveIteratorIterator::SELF_FIRST
);


/*
|--------------------------------------------------------------------------
| ADD FILES TO ZIP
|--------------------------------------------------------------------------
*/

foreach ($iterator as $item) {

    $fullPath =
        $item->getPathname();


    /*
    |--------------------------------------------------------------------------
    | Relative path inside ZIP
    |--------------------------------------------------------------------------
    */

    $relativePath =
        substr(
            $fullPath,
            strlen($projectDir) + 1
        );


    /*
    |--------------------------------------------------------------------------
    | Normalize Windows paths
    |--------------------------------------------------------------------------
    */

    $relativePath =
        str_replace(
            '\\',
            '/',
            $relativePath
        );


    /*
    |--------------------------------------------------------------------------
    | EXCLUDE BACKUPS DIRECTORY
    |--------------------------------------------------------------------------
    */

    if (
        $relativePath === 'backups' ||
        strpos(
            $relativePath,
            'backups/'
        ) === 0
    ) {

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | EXCLUDE THIS APPLICATION BACKUP ZIP
    |--------------------------------------------------------------------------
    */

    if (
        strpos(
            $relativePath,
            'rnr_application_'
        ) === 0 &&
        substr(
            $relativePath,
            -4
        ) === '.zip'
    ) {

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD DIRECTORY
    |--------------------------------------------------------------------------
    */

    if ($item->isDir()) {

        $zip->addEmptyDir(
            $relativePath
        );

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD FILE
    |--------------------------------------------------------------------------
    */

    if ($item->isFile()) {

        if (
            !$zip->addFile(
                $fullPath,
                $relativePath
            )
        ) {

            $zip->close();

            if (file_exists($filepath)) {
                unlink($filepath);
            }

            die(
                "ERROR: Unable to add file to backup:\n" .
                $relativePath .
                "\n"
            );

        }

    }

}


/*
|--------------------------------------------------------------------------
| CLOSE ZIP
|--------------------------------------------------------------------------
*/

if (!$zip->close()) {

    if (file_exists($filepath)) {
        unlink($filepath);
    }

    die(
        "ERROR: Unable to finalize application backup.\n"
    );

}


/*
|--------------------------------------------------------------------------
| VERIFY ZIP
|--------------------------------------------------------------------------
*/

if (!file_exists($filepath)) {

    die(
        "ERROR: Application backup was not created.\n"
    );

}


$fileSize =
    filesize($filepath);


if (
    $fileSize === false ||
    $fileSize < 100
) {

    unlink($filepath);

    die(
        "ERROR: Application backup appears invalid or empty.\n"
    );

}


/*
|--------------------------------------------------------------------------
| VERIFY ZIP CAN BE OPENED
|--------------------------------------------------------------------------
*/

$verifyZip = new ZipArchive();

$verifyResult =
    $verifyZip->open(
        $filepath
    );


if ($verifyResult !== true) {

    unlink($filepath);

    die(
        "ERROR: Application backup ZIP failed verification.\n"
    );

}


$verifyZip->close();


/*
|--------------------------------------------------------------------------
| RETENTION CLEANUP
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


$applicationBackups = glob(
    $backupDir .
    DIRECTORY_SEPARATOR .
    'rnr_application_*.zip'
);


if ($applicationBackups !== false) {

    foreach (
        $applicationBackups as $oldBackup
    ) {

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


        $modifiedTime =
            filemtime($oldBackup);


        if ($modifiedTime === false) {
            continue;
        }


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
| SUCCESS
|--------------------------------------------------------------------------
*/

echo
    "R&R application backup completed successfully.\n\n";


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
    "Old application backups deleted: " .
    $deletedCount .
    "\n";

?>