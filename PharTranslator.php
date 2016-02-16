<?php

$dataDir = 'data/';
$pharDir = $dataDir . 'phars/';
$pharBz2Dir = $dataDir . 'pharBz2s/';
$pharGzDir = $dataDir . 'pharGzs/';
$tarDir = $dataDir . 'tars/';
$tarBz2Dir = $dataDir . 'tarBz2s/';
$tarGzDir = $dataDir . 'tarGzs/';
$zipDir = $dataDir . 'zips/';
$zipBz2Dir = $dataDir . 'zipBz2s/';
$zipGzDir = $dataDir . 'zipGzs/';

$debug = false;

cleanDestDirs(array($pharBz2Dir, $pharGzDir, $tarDir, $tarBz2Dir, $tarGzDir, $zipDir, $zipBz2Dir, $zipGzDir));

if (Phar::canWrite()) {
    debug("Can write\n", $debug);
    try {
        $baseDir = $pharDir;
        foreach (new DirectoryIterator($baseDir) as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            echo 'Current file: ' . $file->getFilename() . "\n";

            $fullFile = $baseDir . $file;
            debug("file " . $file . "\n", $debug);

            transferPhar($pharBz2Dir, $file, $fullFile, null, Phar::BZ2, null, $debug);
            transferPhar($pharGzDir, $file, $fullFile, null, Phar::GZ, null, $debug);
            //todo: add per-file compression for phar files
            transferPhar($tarDir, $file, $fullFile, Phar::TAR, null, null, $debug);
            transferPhar($tarBz2Dir, $file, $fullFile, Phar::TAR, Phar::BZ2, null, $debug);
            transferPhar($tarGzDir, $file, $fullFile, Phar::TAR, Phar::GZ, null, $debug);
            transferPhar($zipDir, $file, $fullFile, Phar::ZIP, null, null, $debug);
            transferPhar($zipBz2Dir, $file, $fullFile, Phar::ZIP, null, Phar::BZ2, $debug);
            transferPhar($zipGzDir, $file, $fullFile, Phar::ZIP, null, Phar::GZ, $debug);

        }
    } catch (Exception $e) {
        echo 'Could not open Phar: ', $e;
    }
} else {
    echo "Enable phar writing, see http://php.net/manual/en/phar.canwrite.php";
}


function transferPhar($destDir, $file, $fullFile, $execType, $archiveCompression, $perFileCompression, $debug)
{
    $newFile = $destDir . $file;
    debug("newFile " . $newFile . "\n", $debug);
    debug("copy " . copy($fullFile, $newFile) . "\n", $debug);
    $phar = new Phar($newFile);

    $pharExecutable = $phar->convertToExecutable($execType, $archiveCompression);

    if($perFileCompression != null){
       $pharExecutable->compressFiles($perFileCompression);
    }

    debug("remove " . unlink($newFile) . "\n", $debug);
}


function cleanDestDirs($destDirs)
{
    foreach ($destDirs as $dir) {
        deleteDirectory($dir);
        mkdir($dir);
    }
}

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function debug($message, $debug)
{
    if ($debug) {
        echo $message;
    }
}