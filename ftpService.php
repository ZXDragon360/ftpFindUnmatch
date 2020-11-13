<?php
try {
    $user = 'ZXDragon360';
    $pwd = 'Czx18Finale';
    $uri = 'vftp.logicbroker.com';
    $ftp_resource = ftp_connect($uri);

    fLogin($ftp_resource, $user, $pwd, true, false) ? null : exit(0);

    // check if ManagedInventory exists
    $targetDirectory = 'ManagedInventory';
    $rootDir = ftp_nlist($ftp_resource, ftp_pwd($ftp_resource));
    $statusFound = false;
    foreach ($top as $file) {
        if (strcasecmp($file, $targetDirectory) === 0) {
            $statusFound = $file;
            break;
        }
    }

    // process check inventory for CSV file
    if ($statusFound === false) {
        echo 'Inventory directory not found.' . PHP_EOL;
        exit(0);
    }
    ftp_chdir($ftp_resource, $statusFound);
    $listDir = ftp_nlist($ftp_resource, ftp_pwd($ftp_resource));
    foreach ($listDir as $dir) {
        // var_dump($dir);
        $vendorDir = ftp_nlist($ftp_resource, $dir);
        // var_dump($vendorDir);
        $statusCSV = false;
        foreach ($vendorDir as $folder) {
            if (strcasecmp($folder, 'unmatched') == 0) {
                $statusCSV = $folder;
                break;
            }
        }
    }
} catch (Exception $excetion) {
    echo $excetion->getMessage();
}

function download($ftp_resource, string $source, string $dest = null): bool
{
    if ($dest === null){
        $separator = __DIR__;
        substr($separator,-1) !== '/' ? $separator .= '/':null; // appends trailing slash if not present
        $dest == null ? $separator.substr($source, strrpos($source,'/') + 1 ) : $dest;
    }
    return ftp_get($ftp_resource, $dest, $source, FTP_BINARY);
}

function fLogin($ftp_resource, string $user, string $pwd, bool $enablePassiveMode = false, bool $enablePassveOptions = true): bool
{
    $status = ftp_login($ftp_resource, $user, $pwd);
    echo $status ? 'Login successfully.' : 'Login invalid.';
    echo PHP_EOL;
    ftp_set_option($ftp_resource, FTP_USEPASVADDRESS, $enablePassveOptions);
    ftp_pasv($ftp_resource, $enablePassiveMode);
    return $status;
}

function recursiveDirectory($ftp_resource, string $path, bool $isPass = false){
    $list = ftp_nlist($ftp_resource,$path);
    if($isPass){
        echo '';
    }
    else {
        foreach($list as $li){
            $vendor = $li;
            $targetUnMatched = 'unmatched';
            $innerDir = ftp_nlist($ftp_resource,$li);
            foreach($innerDir as $inDir){
                if(strcasecmp($inDir,$targetUnMatched) === 0){
                    recursiveDirectory()
                }
            }
        }
    }
}