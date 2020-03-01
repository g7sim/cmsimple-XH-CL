<?php

/**
 * The command factories.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Toxic
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Toxic_XH
 */

/**
 * The command factories.
 *
 * @category CMSimple_XH
 * @package  Toxic
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Toxic_XH
 */
class Toxic_CommandFactory
{
    /**
     * Makes a tab command.
     *
     * @param array $pageData A page data array.
     *
     * @return Toxic_TabCommand
     */
    public function makeTabCommand($pageData)
    {
        return new Toxic_TabCommand($pageData);
    }

    /**
     * Makes an info command.
     *
     * @return Toxic_InfoCommand
     */
    public function makeInfoCommand()
    {
        return new Toxic_InfoCommand();
    }
}

?>
