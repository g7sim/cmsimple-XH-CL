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
  // echo $cluri; echo "<br>"; 
  $slen = strlen ($cluri); //echo $slen; zB 11
  /* ?search wieder mit ? versehen --------------------------------------*/
 
/*  if(strpos($cluri, 'search') !== false) { $cluri .= "";}
/* $orep = array("search", "&print", "foo");
$torep = array("?search", "?&print", "foo");
$cluri2 = str_replace($orep, $torep, $cluri);

echo $cluri2;
echo "istrue";  str_replace('search', '?search', $cluri); }
 if(strpos($cluri, '&print') !== false) str_replace('&print', '?&print', $cluri);
  
	/* if ( isset($svar, $cluri) ) { $cluri .= "?";} */
	 /* $cluri2 = preg_replace('/'.$svar.'/', $svar1, $cluri, 1); } */          		 
	/*  $cluri2 = substr_replace( $cluri, $svar1, $posi, strlen('search') ); */
/* echo $goto; echo $cluri2; ulclean.mb-info.eu/Languages?search=test&function=search 
                                            /Impressum?search=test&function=search */
											
// echo $cluri; echo "<br>...2...";
$servar = "search";  $repvar = "/?search"; 
$pos1 = strpos( $cluri, "search" );  //echo $pos1;  echo "-xxx-";
//$offst = $pos1 + strlen($svar); 
//echo $offst; echo "---";
$repl = array('?search', '?&print', 'foo');
 //if(strpos($cluri, $svar) !== false) { 
//$cluri2 =  substr_replace( $cluri, $repl, 3, 3 );}										   
											   
 $goto .= $cluri;	/* echo $goto; ulclean.mb-info.eu/Languages?search=test&function=search */
// echo $goto;
 
 if ( (!$adm) &&  ($su != '&login') ) { make301( $goto );  } 
}

