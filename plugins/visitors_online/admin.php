<?php
 
if (isset($visitors_online) && $visitors_online == 'true') {
    $o .= print_plugin_admin('off');
    switch ($admin) {
    case '':
        $o .= 'This is the intro page of the plugin';
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}
 
?>