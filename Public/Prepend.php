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

namespace Dotclear\Plugin\SysInfo\Public;

use Dotclear\App;
use Dotclear\Modules\ModulePrepend;
use Dotclear\Plugin\SysInfo\Public\SysInfoTemplate;
use Dotclear\Plugin\SysInfo\Common\SysInfoUrl;

class Prepend extends ModulePrepend
{
    public function loadModule(): void
    {
        App::core()->behavior('publicBreadcrumb')->add(function ($context, $separator): string {
            return $context == 'sysinfo' ? __('System Information') : '';
        });

        App::core()->behavior('urlHandlerBeforeGetData')->add(function ($ctx): void {
                $ctx->http_cache = (bool) App::core()->blog()->settings()->getGroup('sysinfo')->getSetting('http_cache');
        });

        new SysInfoTemplate();
        new SysInfoUrl();
    }
}
