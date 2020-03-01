<?php

function fullBackup()
      {
          global $pth, $o;
          $maxsize = 200000000;
          $part = 1;
      
          $date = date('Y-m-d');
          $archive = new ZipArchive();
          $archive->open(
              "./userfiles/Sicherung-{$date}_$part.zip",
              ZipArchive::CREATE
          );
      
          $totalSize = 0;
          $it = new RecursiveIteratorIterator(
              new RecursiveDirectoryIterator($pth['folder']['base'])
          );
          $it->rewind();
          while ($it->valid()) {
              if (!$it->isDot() && $it->key() != $pth['folder']['base'] . 'backup.zip') {
                  $size = filesize($it->key());
                  if ($totalSize + $size > $maxsize) {
                      $archive->close();
                      $part++;
                      $archive->open(
                          "./userfiles/Sicherung-[/color]{$date}_$part.zip",
                          ZipArchive::CREATE
                      );
                      $totalSize = 0;
                  }
                  $archive->addFile($it->key(), $it->getSubPathName());
                  $totalSize += $size;
              }
              $it->next();
          }
                $archive->close();
      }
            if (XH_ADM && isset($_GET['backup'])) {
          fullBackup();
	$GLOBALS['o'] .= XH_message('success', 'Backup finished : Download in Menu : Files');
      }
	 
/* $url = ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; */
/* parse_url($url, PHP_URL_SCHEME); */
/*  $protocol=$_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http'; */
	 
function site_protocol() {
    if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')  
	{ return $protocol = 'https://'; }  
    else {return $protocol = 'http://';}
}
 	 
/* uriclean ? filter */

function make301($goto)
{ ob_start();
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: http://$goto"); /* if You have a https-domain then use http://'... */
  header("Connection: close");
  
  ob_end_flush();     
  die();
}
 
 if ( strpos ( $_SERVER['REQUEST_URI'] , '?' ) )
{ 
  $goto  = $_SERVER['HTTP_HOST']; 
  $repl = array('?q=', '?', '==', '%', '<', 'cgi' , '404' );
  $cluri = str_replace( $repl , "" , $_SERVER['REQUEST_URI'] );
 			   
											   
 $goto .= $cluri;	
 
 if ( (!$adm) &&  ($su != '&login') ) { make301( $goto );  } 
}

