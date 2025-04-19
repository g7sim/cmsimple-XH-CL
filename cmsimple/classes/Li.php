<?php

declare(strict_types=1); 

namespace XH;

/**
 * The menu renderer.
 *
 * Generates nested <ul><li> HTML structures for menus based on CMSimple_XH page data.
 * Maintains strict output compatibility with the original implementation.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6.3  - modified by github.com/g7sim
 */
class Li
{
    // Constants for menu types and configuration keys to improve readability
    private const TYPE_SUBMENU = 'submenu';
    private const TYPE_SEARCH = 'search'; 
    private const TYPE_MENULEVEL = 'menulevel';
    private const TYPE_SITEMAPLEVEL = 'sitemaplevel';
    private const CONFIG_MENU_SDOC = 'sdoc';
    private const CONFIG_MENU_SDOC_PARENT = 'parent';
    private const CONFIG_URI_SEPARATOR = 'seperator'; // Corrected typo 'seperator' -> 'separator' if applicable in $cf, otherwise keep as is. Check your $cf structure. Assuming 'seperator' is intentional legacy.
    private const CONFIG_MENU_LEVELCATCH = 'levelcatch';
    private const HEADER_LOCATION_NEW_WINDOW = '2'; // Value indicating open in new window

    /**
     * The relevant page indexes provided for the menu.  @var int[]
     */
    protected array $ta;

    /**
     * The menu level to start with (int) or the type of menu (string).  @var int|string
     */
    protected int|string $st;

    /**
     * Flag: Whether the current menu item being processed is NOT the requested page ($s).
     * True if the item is not the current page, false otherwise.  @var bool
     */
    protected bool $tf;

    /**
     * The base menu level (usually $st - 1 for level-based menus, or 0).  @var int
     */
    protected int $b;

    /**
     * Array of flags signalling whether a specific menu level is currently "open"
     * (i.e., waiting for its closing </ul> tag).
     * Keys are levels (int), values are boolean (true = open, false = closed).
     * @var bool[]
     */
    protected array $lf;

    /**
     * Renders a menu structure of certain pages.
     *
     * @param int[] $ta The indexes of the pages. Should be integers.
     * @param int|string $st The menu level to start with or the type of menu.
     * @return string HTML representation of the menu.
     */
    public function render(array $ta, int|string $st): string
    {
        global $s; 

        $this->ta = $ta;
        $this->st = $st;

        $tl = count($this->ta);
        if ($tl < 1) {
            return ''; 
        }

        $t = ''; // Initialize output HTML string

        if ($this->st === self::TYPE_SUBMENU || $this->st === self::TYPE_SEARCH) {
            
            $t .= '<ul class="' . $this->st . '">' . "\n";
        }

        $this->b = 0;
        if (is_int($this->st) && $this->st > 0) {
            $this->b = $this->st - 1;
            $this->st = self::TYPE_MENULEVEL; // Treat as a level-based menu
        } elseif (is_numeric($this->st) && (int) $this->st > 0) {
             // Handle numeric strings representing levels
             $numericSt = (int) $this->st;
             $this->b = $numericSt - 1;
             $this->st = self::TYPE_MENULEVEL;
        }

        $this->lf = []; 

        // --- Main loop iterating through page indexes ---
        for ($i = 0; $i < $tl; $i++) {
            // $s is the global index of the *currently requested* page.
            // $this->ta[$i] is the index of the *menu item* being processed.
            $this->tf = ($s !== $this->ta[$i]); // True if this item is NOT the current page

            if ($this->st === self::TYPE_MENULEVEL || $this->st === self::TYPE_SITEMAPLEVEL) {
                $t .= $this->renderULStartTags($i);
            }

            $t .= '<li class="' . $this->getClassName($i) . '">';

            $t .= $this->renderMenuItem($i);

            // Handle closing tags for level-based menus
            if ($this->st === self::TYPE_MENULEVEL || $this->st === self::TYPE_SITEMAPLEVEL) {
                $currentLevel = $this->getMenuLevel($i);
                $nextLevel = $this->getMenuLevel($i + 1); // Get level of the *next* item

                if ($nextLevel > $currentLevel) {
                   
                    $this->lf[$currentLevel] = true;                    
                } else {                   
                    $t .= '</li>' . "\n";
                    $this->lf[$currentLevel] = false; // Mark level as closed
                }
                
                $t .= $this->renderEndTags($i);
            } else {
               
                $t .= '</li>' . "\n";
            }
        } 

        if ($this->st === self::TYPE_SUBMENU || $this->st === self::TYPE_SEARCH) {
            $t .= '</ul>' . "\n";
        }

        return $t;
    }

    /**
     * Renders the necessary opening <ul> tags when increasing menu depth.
     * Also handles intermediate empty <li> elements if skipping levels.
     * @param int $i The index of the current item in $this->ta.
     * @return string HTML for opening <ul> tags.
     */
    protected function renderULStartTags(int $i): string
    {
        $prevLevel = $this->getMenuLevel($i - 1);
        $currentLevel = $this->getMenuLevel($i);
        $lines = [];

        for ($k = $prevLevel; $k < $currentLevel; $k++) {
             $lines[] = "\n" . '<ul class="' . $this->st . ($k + 1) . '">' . "\n";
        }

        return implode('<li>' . "\n", $lines); // Original logic joins with <li>
    }

    /**
     * Renders the necessary closing </ul> and </li> tags when decreasing menu depth.
     * @param int $i The index of the current item in $this->ta.
     * @return string HTML for closing tags.
     */
    protected function renderEndTags(int $i): string
    {
        $currentLevel = $this->getMenuLevel($i);
        $nextLevel = $this->getMenuLevel($i + 1); // Level of the *next* item
        $html = '';

        for ($k = $currentLevel; $k > $nextLevel; $k--) {
            $html .= '</ul>' . "\n";
            // If the parent level (k-1) was marked as open (had children), close its LI too.
            if (isset($this->lf[$k - 1]) && $this->lf[$k - 1]) {
                $html .= '</li>' . "\n";
                $this->lf[$k - 1] = false; // Mark parent level as closed now
            }
        }
        return $html;
    }

    /**
     * Returns the menu level (depth) of a page item.
     * Uses the global $l array which stores page levels.
     * @param int $i The index in $this->ta for the item. Handles out-of-bounds for next/prev checks.
     * @return int The menu level.
     */
    protected function getMenuLevel(int $i): int
    {
        /** @global int[] $l Array of page levels, indexed by page index. */
        global $l;

        if (isset($this->ta[$i])) {
            $pageIndex = $this->ta[$i];
            
            if (isset($l[$pageIndex])) {
                // --- FIX: Explicitly cast to int ---
                return (int) $l[$pageIndex];
            }
            return $this->b; // Return base level as fallback if index exists in $ta but not $l
        } 
        return $this->b; 
    }

    /**
     * Determines the CSS class name(s) for the current <li> element.
     * Classes indicate selection state ('s') and presence of children ('doc' vs 'docs').
     * @param int $i The index of the current item in $this->ta.
     * @return string CSS class name(s).
     */
    protected function getClassName(int $i): string
    {
        $className = '';
        if ($this->isSelected($i)) {
            $className .= 's'; // Prefix 's' for selected or ancestor of selected
        }
        $className .= 'doc'; // Base class 'doc'
        if ($this->hasChildren($i)) {
            $className .= 's'; // Suffix 's' - if item has children ('docs' or 'sdocs')
        }
        return $className;
    }

    /**
     * Checks if the current menu item should be marked as selected.
     * @param int $i The index of the current item in $this->ta.
     * @return bool True if the item should be marked as selected.
     */
    protected function isSelected(int $i): bool
    {
        global $cf, $s;

        return !$this->tf
            || (isset($cf['menu'][self::CONFIG_MENU_SDOC]) // Check if config key exists
                && $cf['menu'][self::CONFIG_MENU_SDOC] === self::CONFIG_MENU_SDOC_PARENT // Use constant
                && $this->isAncestorOfSelectedPage($i));
    }

    /**
     * Checks if the current menu item is an ancestor of the currently selected page ($s).
     * Compares page levels and URL segments.
     * @param int $i The index of the current item in $this->ta.
     * @return bool True if it's an ancestor.
     */
    protected function isAncestorOfSelectedPage(int $i): bool
    {
        /** @global int $s Index of the currently selected page. */
        /** @global string[] $u Array of page URL segments, indexed by page index. */
        /** @global int[] $l Array of page levels, indexed by page index. */
        /** @global array $cf CMSimple_XH configuration array. */
        global $s, $u, $l, $cf;

        $currentPageIndex = $s;
        $currentItemIndex = $this->ta[$i];

        if ($currentPageIndex < 0 // No page selected
            || !isset($l[$currentItemIndex])
            || !isset($l[$currentPageIndex])
            || !isset($u[$currentItemIndex])
            || !isset($u[$currentPageIndex])
            || !isset($cf['uri'][self::CONFIG_URI_SEPARATOR])) {
            return false; 
        }

        return $l[$currentItemIndex] < $l[$currentPageIndex]
            && str_starts_with( // Use PHP 8 str_starts_with for clarity
                $u[$currentPageIndex],
                $u[$currentItemIndex] . $cf['uri'][self::CONFIG_URI_SEPARATOR]
            );
     }

    /**
     * Checks if the current menu item has any visible child pages within the configured level depth.
     * Relies on global page count ($cl), levels ($l), config ($cf), and hide() function.
     * @param int $i The index of the current item in $this->ta.
     * @return bool True if the item has children.
     */
    protected function hasChildren(int $i): bool
    {
        /** @global int $cl Total number of pages. */
        /** @global int[] $l Array of page levels, indexed by page index. */
        /** @global array $cf CMSimple_XH configuration array. */
        global $cl, $l, $cf;

        // Ensure that hide() function exists 
        if (!function_exists('hide')) {
             error_log("Error: Global function hide() is not available in Li::hasChildren.");
             return false; 
        }

        $currentItemIndex = $this->ta[$i];

        if (!isset($l[$currentItemIndex]) || !isset($cf['menu'][self::CONFIG_MENU_LEVELCATCH])) {
             return false;
        }

        $currentItemLevel = $l[$currentItemIndex];
        $levelCatch = (int) $cf['menu'][self::CONFIG_MENU_LEVELCATCH]; // Max level difference to check

        for ($j = $currentItemIndex + 1; $j < $cl; $j++) {
            if (!isset($l[$j])) {
                continue;
            }

            // Check if page $j is hidden or too deep relative to current item
            if (!hide($j) // Use global hide() function
                && ($l[$j] - $currentItemLevel) < (2 + $levelCatch) // Check level depth constraint
            ) {
                // If we find a page ($j) that is deeper than the current item ($i)...
                if ($l[$j] > $currentItemLevel) {
                    return true; // Found a child
                }
                
                break; // Stop searching further down the page list
            }
        }
        return false; // No children found within constraints
    }

    /**
     * Renders the menu item content, which is either a link (<a>) or a span (<span>).
     * Uses the global $h array for the page title/heading (assumed pre-escaped).
     *
     * @param int $i The index of the current item in $this->ta.
     * @return string HTML for the menu item content.
     */
    protected function renderMenuItem(int $i): string
    {
        /** @global string[] $h Array of page headings/titles, indexed by page index. */
        global $h;

        $pageIndex = $this->ta[$i];

        $itemText = $h[$pageIndex] ?? ''; 

        if ($itemText === '') {
             error_log("Warning: Empty menu item text for page index {$pageIndex}.");
        }

        if ($this->tf) { // If $this->tf is true, it's *not* the current page, so render a link
            $html = $this->renderAnchorStartTag($i);
            $html .= $itemText;
            $html .= '</a>';
        } else { // It *is* the current page, render a span instead of a link
            $html = '<span>';
            $html .= $itemText;
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Renders the opening anchor tag (<a>) for a menu item link.
     * Uses the global a() function to generate the link URL.
     * @param int $i The index of the current item in $this->ta.
     * @return string HTML for the opening anchor tag.
     */
    protected function renderAnchorStartTag(int $i): string
    {
        // Ensure a() function exists
        if (!function_exists('a')) {
             error_log("Error: Global function a() is not available in Li::renderAnchorStartTag.");
             return "<!-- Link generation error -->"; // Prevent fatal error
        }

        $pageIndex = $this->ta[$i];
        $targetAttribute = $this->shallOpenInNewWindow($i) ? ' target="_blank"' : '';

        return a($pageIndex, $targetAttribute); 
    }

    /**
     * Determines if the link for the current menu item should open in a new window/tab.
     * Checks page data via the $pd_router and admin edit state.
     *
     * @param int $i The index of the current item in $this->ta.
     * @return bool True if the link should have target="_blank".
     */
    protected function shallOpenInNewWindow(int $i): bool
    {
        /** @global bool $edit Flag indicating if admin edit mode is active. */
        /** @global \CMSimple_XH\Router $pd_router The page data router object. */
        global $edit, $pd_router;

        // Ensure $pd_router is available
        if (!isset($pd_router) || !is_object($pd_router) || !method_exists($pd_router, 'find_page')) {
            error_log("Error: Global \$pd_router is not available or invalid in Li::shallOpenInNewWindow.");
            return false; 
        }
        
        if (!defined('XH_ADM')) {
             error_log("Warning: Constant XH_ADM is not defined in Li::shallOpenInNewWindow.");
             $is_admin_mode = false;
        } else {
             $is_admin_mode = (bool) XH_ADM;
        }


        $pageIndex = $this->ta[$i];
        $pageData = $pd_router->find_page($pageIndex); // Fetch page data

        if (!is_array($pageData) || !isset($pageData['use_header_location'])) {
             // error_log("Warning: Page data or 'use_header_location' missing for page index {$pageIndex}.");
             return false; // Default if data is missing
        }

        // Logic: Open in new window if NOT in admin edit mode AND page is set to use header location '2'
        return !($is_admin_mode && $edit)
               && $pageData['use_header_location'] === self::HEADER_LOCATION_NEW_WINDOW; // Use constant
    }
}