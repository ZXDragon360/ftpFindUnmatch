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
    foreach ($rootDir as $file) {
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
    $companyID = '';
    digDirectories($ftp_resource,ftp_pwd($ftp_resource));
} catch (Exception $excetion) {
    echo $excetion->getMessage();
}

function download($ftp_resource, string $source, string $dest = null, bool $debug = false): bool
{
    if ($dest === null){
        $separator = __DIR__;
        substr($separator,-1) !== '/' ? $separator .= '/':null; // appends trailing slash if not present
        $dest == null ? $separator.substr($source, strrpos($source,'/') + 1 ) : $dest;
    }
    if($debug){
        echo 'Source: '.$source."\nDestination: ".$dest.PHP_EOL;
        return true;
    }
    else {
        return ftp_get($ftp_resource, $dest, $source, FTP_BINARY);
    }
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

function digDirectories($ftp_resource, string $path, bool $isPass = false, string $companyID = null){
    $list = ftp_nlist($ftp_resource,$path);
    if($isPass){
        if(count($list) === 0){return;}
        foreach($list as $unMatchDir){
            $dest = $unMatchDir;
            is_int(strrpos($dest,'/')) ? $dest = substr($unMatchDir,strrpos($dest,'/')+1) : null;
            download($ftp_resource,$unMatchDir,__DIR__.'/'.$companyID.'_'.$dest);
        }
    }
    else {
        foreach($list as $ID){
            $innerDir = ftp_nlist($ftp_resource,$ID);
            foreach($innerDir as $inDir){
                $lastString = substr($inDir,strrpos($inDir,'/')+1);
                strcasecmp($lastString,'unmatched') === 0 ? digDirectories($ftp_resource,$inDir,true,$ID) : null ;
            }
        }
    }
}