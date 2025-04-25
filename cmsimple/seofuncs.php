<?php

/**
 * @file seofuncs.php
 *
 * SEO functions.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2020 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 * 2025 modified by github.com/g7sim
 */

/**
 * SEO functionality
 *
 * Integration of the ADC-Core_XH plugin with extended functions (optional)
 *
 * Remove empty path segments in an URL
 * Remove $su from FirstPublicPage
 *
 * @return void
 *
 * @since 1.7.3
 */
function XH_URI_Cleaning()
{
    global $su, $s, $xh_publisher, $pth;

    $parts = parse_url(CMSIMPLE_URL);
	assert(isset($parts['scheme'], $parts['host'], $parts['path']));
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $port = '';
    if (!empty($parts['port'])) {
        $port = ':' . $parts['port'];
    }
    $path = $parts['path'];
    $query_str = '';
    if (isset($_SERVER['QUERY_STRING'])) {
        $query_str = $_SERVER['QUERY_STRING'];
    }

    $redir = false;

//Integration of the ADC-Core_XH plugin with extended functions (optional)
    if (is_readable($pth['folder']['plugins'] . 'adc_core/seofuncs.php')) {
        include_once $pth['folder']['plugins'] . 'adc_core/seofuncs.php';
    }

//Remove empty path segments in an URL
//https://github.com/cmsimple-xh/cmsimple-xh/issues/282
    $ep_count = 0;
    $path = preg_replace(
        '#(/){2,}#s',
        '/',
        $path,
        -1,
        $ep_count
    );
    if ($ep_count > 0) {
        $redir = true;
    }

//Remove $su from FirstPublicPage
    if (!XH_ADM && $s === $xh_publisher->getFirstPublishedPage()
    && !isset($_GET['login'])
    && !isset($_POST['login'])) {
        $fpp_count = 0;
        $query_str = preg_replace('/^'
                   . preg_quote($su, '/')
                   . '/', '', $query_str, -1, $fpp_count);
        if ($fpp_count > 0) {
            $redir = true;
            header("Cache-Control: no-cache, no-store, must-revalidate");
        }
    }

//Redirect if adjustments were necessary
    if ($redir) {
        if (isset($_SERVER['PROTOCOL'])
        && !empty($_SERVER['PROTOCOL'])) {
            $protocol = $_SERVER['PROTOCOL'];
        } else {
            $protocol = 'HTTP/1.1';
        }
        $url = $scheme . '://' . $host . $port . $path;
        if ($query_str != '') {
            $url .= '?' . XH_uenc_redir($query_str);
        }
        header("$protocol 301 Moved Permanently");
        header("Location: $url");
        header("Connection: close");
        exit;
    }
}

//Encode QUERY_STRING for redirect with use uenc()
function XH_uenc_redir(string $url_query_str = ''): string
{
    global $cf; 
    if ($url_query_str === '') {
        return '';
    }

    if (!isset($cf['uri']['seperator']) || !is_string($cf['uri']['seperator']) || $cf['uri']['seperator'] === '') {
        error_log('XH_uenc_redir: Configuration $cf[\'uri\'][\'seperator\'] is not defined or empty.');
        return $url_query_str;
    }
    $url_sep = $cf['uri']['seperator'];

    $path_part = $url_query_str;
    $param_part = ''; 
    $ampersand_pos = strpos($url_query_str, '&');

    if ($ampersand_pos !== false) {
        $path_part = substr($url_query_str, 0, $ampersand_pos);
        $param_part = substr($url_query_str, $ampersand_pos);
    }

    $encoded_path_part = '';

    if (strpos($path_part, '=') === false) {
        $segments = explode($url_sep, $path_part);

        $encoded_segments = [];
        foreach ($segments as $segment) {
            $encoded_segments[] = rawurlencode($segment);
        }

        $encoded_path_part = implode($url_sep, $encoded_segments);
    } else {
        $encoded_path_part = $path_part;
    }

    return $encoded_path_part . $param_part;
}
