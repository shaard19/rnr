<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION
| MASTER BACKUP SCRIPT
|--------------------------------------------------------------------------
|
| Runs:
|   1. Database backup
|   2. Application backup
|
| This is the ONLY script that Windows Task Scheduler needs to run.
|--------------------------------------------------------------------------
*/


error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| PATHS
|--------------------------------------------------------------------------
*/

$projectDir = dirname(__DIR__);

$databaseScript =
    $projectDir .
    DIRECTORY_SEPARATOR .
    'config' .
    DIRECTORY_SEPARATOR .
    'backup_database.php';

$applicationScript =
    $projectDir .
    DIRECTORY_SEPARATOR .
    'config' .
    DIRECTORY_SEPARATOR .
    'backup_application.php';


/*
|--------------------------------------------------------------------------
| VERIFY SCRIPTS
|--------------------------------------------------------------------------
*/

if (!file_exists($databaseScript)) {

    die(
        "ERROR: Database backup script not found:\n" .
        $databaseScript .
        "\n"
    );

}


if (!file_exists($applicationScript)) {

    die(
        "ERROR: Application backup script not found:\n" .
        $applicationScript .
        "\n"
    );

}


/*
|--------------------------------------------------------------------------
| RUN DATABASE BACKUP
|--------------------------------------------------------------------------
*/

echo "============================================\n";
echo "R&R COLLECTION MASTER BACKUP\n";
echo "============================================\n\n";

echo "[1/2] Starting database backup...\n\n";


ob_start();

include $databaseScript;

$databaseOutput = ob_get_clean();


echo $databaseOutput;


/*
|--------------------------------------------------------------------------
| RUN APPLICATION BACKUP
|--------------------------------------------------------------------------
*/

echo "\n";
echo "[2/2] Starting application backup...\n\n";


ob_start();

include $applicationScript;

$applicationOutput = ob_get_clean();


echo $applicationOutput;


/*
|--------------------------------------------------------------------------
| COMPLETE
|--------------------------------------------------------------------------
*/

echo "\n";
echo "============================================\n";
echo "MASTER BACKUP COMPLETED SUCCESSFULLY\n";
echo "============================================\n";

?>