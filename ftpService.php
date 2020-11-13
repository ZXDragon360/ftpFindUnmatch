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

function digDirectories($ftp_resource, string $path, bool $isPass = false, string $comanyID = null){
    $list = ftp_nlist($ftp_resource,$path);
    if($isPass){
        if(count($list) === 0){exit(0);}
        foreach($list as $unmatchDir){
            $dest = $unmatchDir;
            is_int(strrpos($dest,'/')) ? $dest = substr($unmatchDir,strrpos($dest,'/')+1) : null;
            download($ftp_resource,$unmatchDir,__DIR__.'/'.$comanyID.'_'.$dest,true); 
        }
    }
    else {
        foreach($list as $companyID){
            $innerDir = ftp_nlist($ftp_resource,$companyID);
            foreach($innerDir as $inDir){
                $lastString = substr($inDir,strrpos($inDir,'/')+1);
                if(strcasecmp($lastString,'unmatched') !== 0) {continue;}
                echo $inDir.PHP_EOL;
                // echo strcasecmp($lastString,'unmatched') === 0 ? $inDir.PHP_EOL : null ;
                // digDirectories($ftp_resource,$inDir,true,$companyID);
            }
        }
    }
}