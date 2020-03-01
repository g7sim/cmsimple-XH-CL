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
	  


function make301($goto)
{ ob_start();
       header("HTTP/1.1 301 Moved Permanently");
  if ( substr($goto,0,4)=='http' )
       header('Location: '       .$goto);
  else header('Location: http://'.$goto);
  exit(); ob_end_flush();     die();
}
if ( strpos ( $_SERVER['REQUEST_URI'] , '?' ) )
{
  $goto  = $_SERVER['HTTP_HOST'];
  $repl = array('?q=', '?', '==', '%', '<', 'cgi' , '404' );
  $goto .= str_replace( $repl , "" , $_SERVER['REQUEST_URI'] );

if ( (!$adm) &&  ($su != '&login') )    make301( $goto );
}