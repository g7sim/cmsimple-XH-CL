<?php

/**
 * Copyright 2011-2021 Christoph M. Becker
 *
 * This file is part of Codeeditor_XH.
 *
 * Codeeditor_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Codeeditor_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codeeditor_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Codeeditor;

class Plugin
{
    const VERSION = "2.0";

    /**
     * @return void
     */
    public static function dispatch()
    {
        global $plugin_cf;

        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(false);
            XH_registerPluginType('editor', 'codeeditor');
            if ($plugin_cf['codeeditor']['enabled']) {
                self::main();
            }
            if (self::isAdministrationRequested()) {
                self::handleAdministration();
            }
        }
    }

    /**
     * @return void
     */
    private static function main()
    {
        if (self::isEditingPhp()) {
            $mode = 'php';
            $class = 'xh_file_edit';
        } elseif (self::isEditingCss()) {
            $mode = 'css';
            $class = 'xh_file_edit';
        } else {
            return;
        }
        self::init([$class], '', $mode, false);
    }

    private static function isEditingPhp(): bool
    {
        global $action, $file;

        return $file == 'template' && ($action == 'edit' || $action == '')
            || $file == 'content' && ($action == 'edit' || $action == '');
    }

    private static function isEditingCss(): bool
    {
        global $admin, $action, $file;

        return $file == 'stylesheet' && ($action == 'edit' || $action == '')
            || $admin == 'plugin_stylesheet' && $action == 'plugin_text';
    }

    private static function isAdministrationRequested(): bool
    {
        return XH_wantsPluginAdministration('codeeditor');
    }

    /**
     * @return void
     */
    private static function handleAdministration()
    {
        global $admin, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                ob_start();
                (new InfoCommand)();
                $o .= ob_get_clean();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    private static function config(string $mode, string $config): string
    {
        global $pth, $e, $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['codeeditor'];
        $ptx = $plugin_tx['codeeditor'];
        $config = trim($config);
        if (empty($config) || $config[0] != '{') {
            $std = in_array($config, array('full', 'medium', 'minimal', 'sidebar', ''));
            $fn = $std
                ? $pth['folder']['plugins'] . 'codeeditor/inits/init.json'
                : $config;
            $config = ($config = file_get_contents($fn)) !== false ? $config : '{}';
        }
        $parsedConfig = json_decode($config, true);
        if (!is_array($parsedConfig)) {
            $e .= '<li><b>' . $ptx['error_json'] . '</b>' . '<br>'
                . (isset($fn) ? $fn : htmlspecialchars($config, ENT_QUOTES, 'UTF-8'))
                . '</li>';
            return "";
        }
        $config = $parsedConfig;
        if (!isset($config['mode']) || $config['mode'] == '%MODE%') {
            $config['mode'] = $mode;
        }
        if (!isset($config['theme']) || $config['theme'] == '%THEME%') {
            $config['theme'] = $pcf['theme'];
        }
        // We set the undocumented leaveSubmitMehtodAlone option; otherwise
        // multiple editors on the same form might trigger form submission
        // multiple times.
        $config['leaveSubmitMethodAlone'] = true;
        $config = (string) json_encode($config);
        return $config;
    }

    private static function filebrowser(): string
    {
        global $adm, $sn, $pth, $cf;

        // no filebrowser, if editor is called from front-end
        if (!$adm) {
            return '';
        }

        $script = '';
        if (!empty($cf['filebrowser']['external'])) {
            $connector = $pth['folder']['plugins'] . $cf['filebrowser']['external']
                . '/connectors/codeeditor/codeeditor.php';
            if (is_readable($connector)) {
                include_once $connector;
                $init = $cf['filebrowser']['external'] . '_codeeditor_init';
                if (is_callable($init)) {
                    $script = $init();
                }
            }
        } else {
            $_SESSION['codeeditor_fb_callback'] = 'wrFilebrowser';
            $url = $sn . '?filebrowser=editorbrowser&editor=codeeditor&prefix='
                . CMSIMPLE_BASE . '&base=./&type=';
            $script = <<<EOS
codeeditor.filebrowser = function(type) {
    window.open("$url" + type, "codeeditor_filebrowser",
            "toolbar=no,location=no,status=no,menubar=no," +
            "scrollbars=yes,resizable=yes,width=640,height=480");
}
EOS;
        }
        return $script;
    }

    /**
     * @return void
     */
    public static function doInclude()
    {
        global $hjs, $pth, $plugin_cf, $plugin_tx;
        static $again = false;

        if ($again) {
            return;
        }
        $again = true;

        $pcf = $plugin_cf['codeeditor'];
        $ptx = $plugin_tx['codeeditor'];
        $dir = $pth['folder']['plugins'] . 'codeeditor/';

        $css = '<link rel="stylesheet" type="text/css" href="' . $dir
            . 'codemirror/codemirror-combined.css">';
        $fn = $dir . 'codemirror/theme/' . $pcf['theme'] . '.css';
        if (file_exists($fn)) {
            $css .= '<link rel="stylesheet" type="text/css" href="' . $fn . '">';
        }
        $text = array('confirmLeave' => $ptx['confirm_leave']);
        $text = json_encode($text);
        $filebrowser = self::filebrowser();

        $hjs .= <<<EOS
$css
<script src="{$dir}codemirror/codemirror-compressed.js">
</script>
<script src="{$dir}codeeditor.min.js"></script>
<script>
codeeditor.text = $text;
$filebrowser
</script>
EOS;
    }

    public static function replace(string $elementId, string $config = ''): string
    {
        $config = self::config('php', $config);
        return "codeeditor.instantiate('$elementId', $config, true);";
    }

    /**
     * @param array<int,string> $classes
     * @param string|false $config
     * @return void
     */
    public static function init(array $classes = [], $config = false, string $mode = 'php', bool $mayPreview = true)
    {
        global $bjs;

        self::doInclude();
        if (empty($classes)) {
            $classes = array('xh-editor');
        }
        $classes = json_encode($classes);
        $config = self::config($mode, (string) $config);
        $mayPreview = json_encode($mayPreview);
        $bjs .= <<<EOS
<script>
CodeMirror.on(window, "load", function() {
    codeeditor.instantiateByClasses($classes, $config, $mayPreview);
})
</script>

EOS;
    }

    /**
     * @return array<int,string>
     */
    public static function getThemes(): array
    {
        global $pth;

        $themes = array('', 'default');
        $foldername = $pth['folder']['plugins'] . 'codeeditor/codemirror/theme';
        if ($dir = opendir($foldername)) {
            while (($entry = readdir($dir)) !== false) {
                if (pathinfo($entry, PATHINFO_EXTENSION) == 'css') {
                    $themes[] = basename($entry, '.css');
                }
            }
        }
        return $themes;
    }
}
