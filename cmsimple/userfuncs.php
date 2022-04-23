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

/*--------------------------------------------------------------*/