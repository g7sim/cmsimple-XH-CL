<?php

/**
 * @file tplfuncs.php
 *
 * Template functions.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * Renders the prev link.
 *
 * @return string HTML
 *
 * @global string The script name.
 * @global array  The page URLs.
 *
 * @since 1.6.3
 */
function XH_renderPrevLink()
{
     global $sn, $u, $cf;

    $index = XH_findPreviousPage();
    if ($index !== false) {
         return '<link rel="prev" href="' . $sn . ($cf['uri']['clean'] ? '' : '?') . $u[$index] . '">';
    } else {
        return '';
    }
}

/**
 * Renders the next link.
 *
 * @return string HTML
 *
 * @global string The script name.
 * @global array  The page URLs.
 *
 * @since 1.6.3
 */
function XH_renderNextLink()
{
     global $sn, $u, $cf;

    $index = XH_findNextPage();
    if ($index !== false) {
        return '<link rel="next" href="' . $sn . ($cf['uri']['clean'] ? '' : '?') . $u[$index] . '">';
    } else {
        return '';
    }
}

/**
 * Returns the complete HEAD element.
 *
 * @return string HTML
 *
 * @global string The page title.
 * @global array  The configuration of the core.
 * @global array  The paths of system files and folders.
 * @global array  The localization of the core.
 * @global string HTML to be inserted to the HEAD Element.
 */
function head()
{
    global $title, $cf, $pth, $tx, $hjs, $hqs;

    $t = XH_title($cf['site']['title'], $title);
    $t = '<title>' . strip_tags($t) . '</title>' . "\n";
    foreach (array_keys(array_merge($cf['meta'], $tx['meta'])) as $i) {
        $t .= meta($i);
    }
    $t = '<meta http-equiv="content-type" content="text/html;charset=UTF-8">'
        . "\n" . $t;
    $plugins = implode(', ', XH_plugins());
    $o = $t;
    if (error_reporting() > 0) {
        $o .= '<meta name="generator" content="' . CMSIMPLE_XH_VERSION . ' '
            . CMSIMPLE_XH_BUILD . ' - www.cmsimple-xh.org">'
            . "\n"
            . '<!-- plugins: ' . $plugins . ' -->' . "\n";
    }
    $o .= XH_renderPrevLink() . XH_renderNextLink()
        . '<link rel="stylesheet" href="' . XH_pluginStylesheet()
        . '" type="text/css">' . PHP_EOL
        . $hjs
        . '<link rel="stylesheet" href="' . $pth['file']['stylesheet']
        . '" type="text/css">' . "\n";
	$o .=  $hqs;		
    return $o;
}

/**
 * Returns the language dependend site title.
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 */
function sitename()
{
    global $tx;

    return isset($tx['site']['title']) ? XH_hsc($tx['site']['title']) : '';
}


/**
 * Returns the global site title.
 *
 * @return string HTML
 *
 * @global array The configuration of the core.
 */
function pagename()
{
    global $cf;

    return isset($cf['site']['title']) ? XH_hsc($cf['site']['title']) : '';
}


/**
 * Returns the onload attribute for the body element.
 *
 * @return string HTML
 *
 * @global string JavaScript for the onload attribute of the BODY element.
 */
function onload()
{
    global $onload;

    return ' onload="' . $onload . '"';
}


/**
 * Returns the table of contents.
 *
 * @param int      $start The menu level to start with.
 * @param int      $end   The menu level to end with.
 * @param callable $li    A callback that actually creates the view.
 *
 * @return string HTML
 *
 * @global int   The number of pages.
 * @global int   The index of the current page.
 * @global array The menu levels of the pages.
 * @global array The configuration of the core.
 */
function toc($start = null, $end = null, $li = 'li')
{
    global $cl, $s, $l, $cf;

    if (isset($start)) {
        if (!isset($end)) {
            $end = $start;
        }
    } else {
        $start = 1;
    }
    if (!isset($end)) {
        $end = $cf['menu']['levels'];
    }
    $ta = array();
    if ($s > -1) {
        $tl = $l[$s];
        for ($i = $s; $i > -1; $i--) {
            if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end) {
                if (!hide($i)
                    || ($i == $s && $cf['show_hidden']['pages_toc'] == 'true')
                ) {
                    $ta[] = $i;
                }
            }
            if ($l[$i] < $tl) {
                $tl = $l[$i];
            }
        }
        sort($ta);
        $tl = $l[$s];
    } else {
        $tl = 0;
    }
    $tl += 1 + $cf['menu']['levelcatch'];
    for ($i = $s + 1; $i < $cl; $i++) {
        if ($l[$i] <= $tl && $l[$i] >= $start && $l[$i] <= $end) {
            if (!hide($i)) {
                $ta[] = $i;
            }
        }
        if ($l[$i] < $tl) {
            $tl = $l[$i];
        }
    }
    return $li($ta, $start);
}


/**
 * Returns a menu structure of certain pages.
 *
 * @param array $ta The indexes of the pages.
 * @param mixed $st The menu level to start with or the type of menu.
 *
 * @return string HTML
 */
function li(array $ta, $st)
{
    $li = new XH\Li();
    return $li->render($ta, $st);
}

/**
 * Sets global variables for CSS/DHTML menus.
 *
 * The most important variable is <var>$hc</var>, which is an array of page
 * indexes of the pages of the menu. This is normally passed as first argument
 * to li(), e.g. <kbd>li($hc)</kbd>. <var>$hl</var> holds the number of these
 * pages. <var>$si</var> holds the index of the current page within
 * <var>$hc</var>; it might be useful for advanced menus.
 *
 * @return void
 *
 * @global int   The number of pages.
 * @global int   The current page index.
 * @global array The configuration of the core.
 * @global int   The index of the current page in {@link $hc}.
 * @global array The page indexes of the visible menu items.
 * @global int   The length of {@link $hc}.
 *
 * @since 1.6.2
 */
function XH_buildHc()
{
    global $cl, $s, $cf, $si, $hc, $hl;

    $pages = new XH\Pages();
    $si = -1;
    $hc = array();
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i)
            || ($cf['show_hidden']['pages_toc'] == 'true'
            && ($i == $s || in_array($i, $pages->getAncestorsOf($s, false))))
        ) {
            $hc[] = $i;
        }
        if ($i == $s) {
            $si = count($hc);
        }
    }
    $hl = count($hc);
}

/**
 * Returns the search form.
 *
 * @return string HTML
 *
 * @global string The script name.
 * @global array  The localization of the core.
 */
function searchbox()
{
    global $sn, $tx;

       return '<form id="searchbox" action="' . $sn . '" method="post">' . "\n"
        . '<input type="text" id="searchfield" class="text" name="search" title="'
        . $tx['search']['label'] . '" placeholder="' . $tx['search']['label']
        . '" size="12">' . "\n"
        . '<input type="hidden" name="function" value="search">' . "\n" . ' '
        . '<input type="submit" class="submit" value="'
        . $tx['search']['button'] . '">' . "\n"
        . '</form>' . "\n";
}


/**
 * Returns the sitemap link.
 *
 * @return string HTML
 */
function sitemaplink()
{
    return ml('sitemap');
}


/**
 * Returns the link for the print view.
 *
 * @return string HTML
 *
 * @global array  The localization of the core.
 */
function printlink()
{
    global $tx;

    return '<a href="' . XH_printUrl() . '" rel="nofollow">'
        . $tx['menu']['print'] . '</a>';
}

/**
 * Returns the URL of the print view.
 *
 * @return string
 *
 * @global string The requested special function.
 * @global string The current search string.
 * @global string The requested special file.
 * @global string The script name.
 *
 * @since 1.6
 */
function XH_printUrl()
{
    global $f, $search, $file, $sn;

    $t = '&print';
    if ($f == 'search') {
        $t .= '&function=search&search=' . urlencode(stsl($search));
    } elseif ($f == 'file') {
        $t .= '&file=' . $file;
    } elseif ($f != '' && $f != 'save') {
        $t .= '&' . $f;
    } elseif (sv('QUERY_STRING') != '') {
        $t = sv('QUERY_STRING') . $t;
    }
    $t = XH_hsc($t);
    return $sn . '?' . $t;
}

/**
 * Returns the link to the mail form.
 *
 * @return string HTML
 *
 * @global array The configuration of the core.
 */
function mailformlink()
{
    global $cf;

    if ($cf['mailform']['email'] != '') {
        return ml('mailform');
    }
	 return "";
}

/**
 * Returns the link to the login form.
 *
 * @global int    The index of the requested page.
 * @global array  The localization of the core.
 *
 * @return string HTML
 */
function loginlink()
{
    global $s, $tx;

    if (!XH_ADM) {
        return a($s > -1 ? $s : 0, '&amp;login" rel="nofollow')
            . $tx['menu']['login'] . '</a>';
    }
	return "";
}


/**
 * Returns the date of the last update of the site.
 *
 * @param bool $br   Whether to emit a br element between text and date.
 * @param int  $hour The time correction in hours.
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 * @global array The paths of system files and folders.
 */
function lastupdate($br = null, $hour = null)
{
    global $tx, $pth;

    $t = $tx['lastupdate']['text'] . ':';
    if (!(isset($br))) {
        $t .= '<br>';
    } else {
        $t .= ' ';
    }
    $time = filemtime($pth['file']['content']) + (isset($hour) ? $hour * 3600 : 0);
    return $t . '<time datetime="' . date('c', $time) . '">'
        . XH_formatDate($time)
        . '</time>';
}

/**
 * Returns the locator (breadcrumb navigation).
 *
 * @return string HTML
 */
function locator($separator = '&gt;')
{
    $breadcrumbs = XH_getLocatorModel();
    $last = count($breadcrumbs) - 1;
    $html = '<span itemscope itemtype="https://schema.org/BreadcrumbList">';
    foreach ($breadcrumbs as $i => $breadcrumb) {
        list($title, $url) = $breadcrumb;
        if ($i > 0) {
            $html .= ' ' . $separator . ' ';
        }
        $html .= '<span itemprop="itemListElement" '
                . 'itemscope itemtype="https://schema.org/ListItem">';
        $inner = '<span itemprop="name">' . $title . '</span>';
        if (isset($url) && $i < $last) {
            $html .= '<a itemprop="item" href="' . $url . '">'
                    . $inner . '</a>';
        } else {
            $html .= $inner;
        }
        $html .= '<meta itemprop="position" content="'. ($i + 1) . '"></span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Returns the admin menu.
 *
 * Returns an empty string since XH 1.5,
 * as the admin menu is automatically inserted to the template.
 *
 * @return string HTML
 *
 * @see XH_adminMenu()
 *
 * @deprecated since 1.7. Just remove from the template.
 */
function editmenu()
{
    trigger_error('Function editmenu() is deprecated', E_USER_DEPRECATED);

    return '';
}

/**
 * Returns the contents area.
 *
 * @return string HTML
 *
 * @global int    The index of the current page.
 * @global string The output of the contents area.
 * @global array  The content of the pages.
 * @global bool   Whether edit mode is active.
 */
function content()
{
    global $s, $o, $c, $edit;

    if (!($edit && XH_ADM) && $s > -1) {
        if (isset($_GET['search'])) {
            $search = XH_hsc(trim(preg_replace('/\s+/u', ' ', $_GET['search'])));
            $words = explode(' ', $search);
            $c[$s] = XH_highlightSearchWords($words, $c[$s]);
        }
        $o .= preg_replace('/#CMSimple (.*?)#/is', '', $c[$s]);
    }
    return  preg_replace('/<!--XH_ml[1-9]:.*?-->/is', '', $o);
}


/**
 * Returns the submenu of a page.
 *
 * @param string $html Optional markup to wrap the heading.
 *
 * @return string HTML
 *
 * @global int   The index of the current page.
 * @global int   The number of pages.
 * @global array The menu levels of the pages.
 * @global array The localization of the core.
 * @global array The configuration of the core.
 */
function submenu($html = '')
{
    global $s, $cl, $l, $tx, $cf;

    $ta = array();
    if ($s > -1) {
        $tl = $l[$s] + 1 + $cf['menu']['levelcatch'];
        for ($i = $s + 1; $i < $cl; $i++) {
            if ($l[$i] <= $l[$s]) {
                break;
            }
            if ($l[$i] <= $tl) {
                if (!hide($i)) {
                    $ta[] = $i;
                }
            }
            if ($l[$i] < $tl) {
                $tl = $l[$i];
            }
        }
        if (count($ta) != 0) {
            if ($html == '') {
                $level = min($cf['menu']['levels'] + 1, 6);
                return '<h' . $level . '>' . $tx['submenu']['heading']
                    . '</h' . $level . '>'
                    . li($ta, 'submenu');
            } else {
                return sprintf($html, $tx['submenu']['heading'])
                    . li($ta, 'submenu');
            }
        }
    }
	return "";
}

/**
 * Returns a link to the previous page.
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 *
 * @see nextpage()
 */
function previouspage()
{
    global $tx;

    $index = XH_findPreviousPage();
    if ($index !== false) {
        return a($index, '" rel="prev') . $tx['navigator']['previous'] . '</a>';
    }
	return "";
}

/**
 * Returns a link to the next page
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 *
 * @see previouspage()
 */
function nextpage()
{
    global $tx;

    $index = XH_findNextPage();
    if ($index !== false) {
        return a($index, '" rel="next') . $tx['navigator']['next'] . '</a>';
    }
	return "";
}

/**
 * Returns a link to the top of the page.
 *
 * To work, an appropriate ID has to be defined in the template.
 *
 * @param string $id An (X)HTML ID.
 *
 * @return string HTML
 *
 * @global array The localization of the core.
 */
function top($id = 'TOP')
{
    global $tx;

    return '<a href="#' . $id . '">' . $tx['navigator']['top'] . '</a>';
}


/**
 * Returns the language menu.
 *
 * @return string HTML
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global string The current language.
 */
function languagemenu()
{
    global $pth, $cf, $sl;

    $r = XH_secondLanguages();
    array_unshift($r, $cf['language']['default']);
    $i = array_search($sl, $r);
    unset($r[$i]);
    $langName = [];
    $langNames = explode(';', $cf['language']['2nd_lang_names']);
    foreach ($langNames as $value) {
        $langName[substr($value, 0, 2)] = substr($value, 3);
    }

    $t = '';
    foreach ($r as $lang) {
        $url = $pth['folder']['base']
            . ($lang == $cf['language']['default'] ? '' : $lang . '/');
        $img = $pth['folder']['templateflags'] . $lang . '.gif';
        if (!file_exists($img)) {
            $img = $pth['folder']['flags'] . $lang . '.gif';
        }

        $title = isset($langName[$lang])
            ? $langName[$lang]
            : $lang;

        $el = file_exists($img)
            ? '<img src="' . $img . '" alt="' . $title . '" title="'
                . $title . '" class="flag">'
            : $title;
        $t .= '<a href="' . $url . '">' . $el . '</a> ';
    }
    return $t;
}


/**
 * Provides a minimal template (in case template isn't found).
 *
 * @return void
 *
 * @since 1.6.3
 */
function XH_emergencyTemplate()
{
    header('HTTP/1.0 503 Service Unavailable');
    header('Content-Type: text/html;charset=UTF-8');
    echo '<!DOCTYPE html><head>'
    . head()
    . '</head><body '
    . onload()
    . '>'
    . sitename()
    . toc()
    . content()
    . loginlink()
    . '</body></html>';
    XH_exit();
}

/**
 * Creates the link to the generated page "Site/CMS Info".
 *
 * One of the 3 functions to create "Site/CMS Info".
 *
 * @param string $linktext The text to be displayed as the link in the template.
 *
 * @return string The link.
 *
 * @global string The site (script) name.
 *
 * @since 1.7
 */
function poweredByLink($linktext = '')
{
    global $sn;

    $linktext = $linktext ? $linktext : 'Site/CMS Info';
    return '<a href="' . $sn . '?' . uenc('site/cms info') . '">'
        . $linktext . '</a>';
}
