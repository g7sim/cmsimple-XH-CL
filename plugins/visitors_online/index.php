<?php
 
define('VISITORS_FILENAME', $pth['folder']['plugins'].'visitors_online/data/online.csv');
/* define('VISITORS_DURATION', 5*60); */
define('VISITORS_DURATION', $plugin_cf['visitors_online']['duration']*60);
/* define('VISITORS_TEXT', 'Visitors online'); */
define('VISITORS_TEXT', $plugin_tx['visitors_online']['text']);
 
 
function visitors_read() {
    $data = file(VISITORS_FILENAME);
    $res = array();
    foreach ($data as $line) {
	list($ip, $timestamp) = explode("\t", rtrim($line));
	$res[$ip] = $timestamp;
    }
    return $res;
}
 
 
function visitors_write($visits) {
    $o = '';
    foreach ($visits as $ip => $timestamp) {
	$o .= $ip."\t".$timestamp."\n";
    }
    $fh = fopen(VISITORS_FILENAME, 'w');
    fwrite($fh, $o);
    fclose($fh);
}
 
 
function visitors_is_online($visit) {
    return time() - $visit <= VISITORS_DURATION;
}
 
 
function visitors() {
    $visits = visitors_read();
    $visits = array_filter($visits, 'visitors_is_online');
    $visits[$_SERVER['REMOTE_ADDR']] = time();
    visitors_write($visits);
    return '<span id="visitors-online">'.VISITORS_TEXT.': '.count($visits).'</span>'."\n";
}
 
?>