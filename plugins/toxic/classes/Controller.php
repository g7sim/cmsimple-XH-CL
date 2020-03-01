<?php

/**
 * The controllers.
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
 * The controllers.
 *
 * @category CMSimple_XH
 * @package  Toxic
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Toxic_XH
 */
class Toxic_Controller
{
    /**
     * The command factory.
     *
     * @var Toxic_CommandFactory
     */
    protected $commandFactory;

    /**
     * Initializes a new instance.
     *
     * @param Toxic_CommandFactory $commandFactory A command factory.
     *
     * @return void
     */
    public function __construct(Toxic_CommandFactory $commandFactory)
    {
        $this->commandFactory = $commandFactory;
    }

    /**
     * Dispatch according to the request.
     *
     * @return void
     */
    public function dispatch()
    {
        $this->registerFields();
        if (XH_ADM) {
            $this->addPageDataTab();
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    /**
     * Registers the page data fields.
     *
     * @return void
     *
     * @global XH_PageDataRouter The page data router.
     */
    protected function registerFields()
    {
        global $pd_router;

        $pd_router->add_interest('toxic_category');
        $pd_router->add_interest('toxic_class');
    }

    /**
     * Adds the page data tab.
     *
     * @return void
     *
     * @global array             The paths of system files and folders.
     * @global XH_PageDataRouter The page data router.
     * @global array             The localization of the plugins.
     */
    protected function addPageDataTab()
    {
        global $pth, $pd_router, $plugin_tx;

        $pd_router->add_tab(
            $plugin_tx['toxic']['label_tab'],
            $pth['folder']['plugins'] . 'toxic/toxic_view.php'
        );
    }

    /**
     * Returns whether the administration is requested.
     *
     * @return bool
     *
     * @global string Whether the toxic administration is requested.
     */
    protected function isAdministrationRequested()
    {
        global $toxic;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('toxic')
            || isset($toxic) && $toxic == 'true';
    }

    /**
     * Handles the administration.
     *
     * @return void
     *
     * @global string The value of the <var>admin</var> GP parameter.
     * @global string The value of the <var>action</var> GP parameter.
     * @global string The HTML for the contents area.
     */
    protected function handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
        case '':
            $o .= $this->commandFactory->makeInfoCommand()->render();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, 'toxic');
        }
    }
}

?>
