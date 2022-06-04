<?php
declare(strict_types=1);

namespace Dotclear\Plugin\SysInfo\Common;

use Dotclear\App;
use Dotclear\Core\Url\Url;

class SysInfoUrl extends Url
{
    public function __construct()
    {
        App::core()->url()->register('sysinfo', 'sysinfo', '^sysinfo(?:/(.+))?$', [$this, 'sysInfo']);
    }

    public function sysInfo($args)
    {
        if (in_array($args, ['behaviours', 'templatetags'])) {
            $this->sysInfoServeDocument($args);
        }

        App::core()->url()->p404();
        exit;
    }

    private function sysInfoServeDocument(string $doc): void
    {
            $module = App::core()->themes()->getModule(App::core()->blog()->settings()->getGroup('system')->getSetting('theme'));
            $tplset = $module ? $module->templateset() : null;
            if (!empty($tplset) && is_dir(__DIR__ . '/../templates/' . $tplset)) {
                App::core()->template()->setPath(App::core()->template()->getPath(), __DIR__ . '/../templates/' . $tplset);
            } else {
                App::core()->template()->setPath(App::core()->template()->getPath(), __DIR__ . '/../templates/' . App::core()->config()->get('template_default'));
            }
            App::core()->url()->serveDocument($doc . '.html');
            exit;
    }
}