<?php

function fullBackup()
{
    global $pth, $o;

    $sourceDirectory = realpath($pth['folder']['base']);
    if ($sourceDirectory === false) {
        $errorMessage = 'Error: Source directory path is invalid or does not exist: ' . htmlspecialchars($pth['folder']['base']);
        if (function_exists('XH_message')) {
             $GLOBALS['o'] .= XH_message('fail', $errorMessage);
        } else {
            error_log('Backup Error: ' . $errorMessage);
        }
        return false;
    }
    
    $sourceDirectory = rtrim(str_replace('\\', '/', $sourceDirectory), '/');

    $backupDirectoryRelative = 'userfiles/backups'; 
    $backupDirectory = $sourceDirectory . '/' . $backupDirectoryRelative; 

    $maxsize = 200000000; // 200 MB limit 
    $part = 1;
    $date = date('Y-m-d');
    $backupFileBaseName = "Sicherung-{$date}"; // Base name for zip files

    if (!is_dir($sourceDirectory) || !is_readable($sourceDirectory)) {
        $errorMessage = 'Error: Source directory not found or not readable: ' . htmlspecialchars($sourceDirectory);
         if (function_exists('XH_message')) {
             $GLOBALS['o'] .= XH_message('fail', $errorMessage);
        } else {
            error_log('Backup Error: ' . $errorMessage);
        }
        return false;
    }

    if (!is_dir($backupDirectory)) {
        if (!mkdir($backupDirectory, 0775, true)) { // Recursive creation
            $errorMessage = 'Error: Could not create backup directory: ' . htmlspecialchars($backupDirectory);
             if (function_exists('XH_message')) {
                 $GLOBALS['o'] .= XH_message('fail', $errorMessage);
            } else {
                error_log('Backup Error: ' . $errorMessage);
            }
            return false;
        }
    }
    if (!is_writable($backupDirectory)) {
         $errorMessage = 'Error: Backup directory is not writable: ' . htmlspecialchars($backupDirectory);
         if (function_exists('XH_message')) {
            $GLOBALS['o'] .= XH_message('fail', $errorMessage);
        } else {
            error_log('Backup Error: ' . $errorMessage);
        }
        return false;
    }

    $absoluteExcludeDir = str_replace('\\', '/', $backupDirectory); 

    $archive = new ZipArchive();
    $currentArchiveName = $backupDirectory . '/' . $backupFileBaseName . "_$part.zip";

    if ($archive->open($currentArchiveName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        $errorMessage = 'Error: Cannot create zip file: ' . htmlspecialchars($currentArchiveName);
        if (function_exists('XH_message')) {
             $GLOBALS['o'] .= XH_message('fail', $errorMessage);
        } else {
             error_log('Backup Error: ' . $errorMessage);
        }
        return false;
    }

    $totalSize = 0;

    try {
        $directoryIterator = new RecursiveDirectoryIterator(
            $sourceDirectory,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS // Use UNIX paths for consistency
        );

        $it = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $fileinfo) {
           
            $filePath = $fileinfo->getPathname(); 

            if (strpos($filePath, $absoluteExcludeDir . '/') === 0) { 
			                continue; 
            }

            $relativePath = $it->getSubPathname();
            
            if (empty($relativePath)) {
                continue;
            }
            
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($fileinfo->isDir()) {
                
                 $archive->addEmptyDir($relativePath);
                
            } elseif ($fileinfo->isFile()) {
                $fileSize = $fileinfo->getSize();

                if ($totalSize > 0 && ($totalSize + $fileSize) > $maxsize) {
                    $archive->close();
                    $part++;
                    $currentArchiveName = $backupDirectory . '/' . $backupFileBaseName . "_$part.zip";
                    if ($archive->open($currentArchiveName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                        $errorMessage = 'Error: Cannot create zip file part: ' . htmlspecialchars($currentArchiveName);
                        if (function_exists('XH_message')) {
                             $GLOBALS['o'] .= XH_message('fail', $errorMessage);
                        } else {
                             error_log('Backup Error: ' . $errorMessage);
                        }
                        return false; 
                    }
                    $totalSize = 0;
                }

                if ($archive->addFile($filePath, $relativePath)) {
                    $totalSize += $fileSize;
                    
                } else {
                     $warningMessage = 'Warning: Could not add file to zip: ' . htmlspecialchars($relativePath);
                     if (function_exists('XH_message')) {
                        $GLOBALS['o'] .= XH_message('warning', $warningMessage);
                    } else {
                        error_log('Backup Warning: ' . $warningMessage);
                    }
                }
            }
        }

    } catch (Exception $e) {
         $errorMessage = 'Error during backup iteration: ' . $e->getMessage();
        if (function_exists('XH_message')) {
             $GLOBALS['o'] .= XH_message('fail', $errorMessage);
        } else {
            error_log('Backup Error: Exception during iteration: ' . $e->getMessage());
        }
        if ($archive->filename) {
            @$archive->close();
        }
        return false;
    }

    $archive->close();
    return true; 
}


if (defined('XH_ADM') && XH_ADM && isset($_GET['backup'])) {
    if (fullBackup()) {
        if (function_exists('XH_message')) {
             $GLOBALS['o'] .= XH_message('success', 'Backup finished successfully. Download available in Menu: Files -> backups.');
        } else {
            error_log('Backup finished successfully.');
        }
    } else {
        if (!function_exists('XH_message')) {
             error_log('Backup process failed.'); 
         }
    }
}


/* -------------------- uriclean and security filter -----------------------*/

function redir301($goto)
{ if(!ob_start("ob_gzhandler")) ob_start();
  $proto = "Location: http://"; /*in a https-domain use https:// */
  /* $protocol = apache_getenv('HTTPS') ? 'https:' : 'http:'; */
  header("HTTP/1.1 301 Moved Permanently");
  header( $proto.$goto ); 
  header("Connection: close");
  
  ob_end_flush();     
  die();
}
 
 if ( strpos ( $_SERVER['REQUEST_URI'], '?' ) ||  strpos($_SERVER['REQUEST_URI'], '==') )
{ 
  $goto  = $_SERVER['HTTP_HOST']; 
  $repl = array('?q=', '?', '==', '%', '<', 'cgi' , '404' );
  $cluri = str_replace( $repl , "" , $_SERVER['REQUEST_URI'] ); 			   
											   
 $goto .= $cluri;	
 
 if ( (!$adm) &&  ($su != '&login') ) { redir301( $goto );  } 
}

/* ----------------------  anchors -------------------*/

if (!(XH_ADM && $edit)) {
$i = $s > -1 ? $s : 0;
$c[$i] = preg_replace(
'/<a([^>]*)href="?#(.*)"?/',
'<a$1href="' . $su . '#$2',
$c[$i]
);
}

/*----------------------CssCrush init----------------------------------------*/
require_once ('assets/crush/CssCrush.php');
use CssCrush\Crush;

/*----------------------Jshrink init----------------------------------------*/
require_once ('assets/js/Minifier.php');
use JShrink\Minifier;
