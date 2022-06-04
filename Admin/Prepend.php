<?php
/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\SysInfo\Admin;

use ArrayObject;

use Dotclear\App;
use Dotclear\Modules\ModulePrepend;
use Dotclear\Plugin\SysInfo\Admin\SysInfoRest;
use Dotclear\Plugin\SysInfo\Common\SysInfoUrl;

class Prepend extends ModulePrepend
{
    public function loadModule(): void
    {
        # dead but useful code, in order to have translations
        __('sysInfo') . __('System Information');

        # Add menu & fav
        $this->addStandardMenu('System');
        $this->addStandardFavorites();

        # Register rest methods
        new SysInfoRest();
        new SysInfoUrl();
    }

    public function installModule(): ?bool
    {
        App::core()->blog()->settings()->getGroup('sysinfo')->putSetting('http_cache', true, 'boolean', 'HTTP cache', false, true);

        return true;
    }
}
