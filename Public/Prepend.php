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

use Dotclear\Module\AbstractPrepend;
use Dotclear\Module\TraitPrependPublic;
use Dotclear\Plugin\SysInfo\Public\SysInfoTemplate;
use Dotclear\Plugin\SysInfo\Common\SysInfoUrl;

class Prepend extends AbstractPrepend
{
    use TraitPrependPublic;

    public function loadModule(): void
    {
        dotclear()->behavior()->add('publicBreadcrumb', function ($context, $separator): string {
            return $context == 'sysinfo' ? __('System Information') : '';
        });

        dotclear()->behavior()->add('urlHandlerBeforeGetData', function ($ctx): void {
                $ctx->http_cache = (bool) dotclear()->blog()->settings()->get('sysinfo')->get('http_cache');
        });

        new SysInfoTemplate();
        new SysInfoUrl();
    }
}
