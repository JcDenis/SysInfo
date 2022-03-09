<?php
declare(strict_types=1);

namespace Dotclear\Plugin\SysInfo\Common;

use Dotclear\Core\Url\Url;

class SysInfoUrl extends Url
{
    public function __construct()
    {
        dotclear()->url()->register('sysinfo', 'sysinfo', '^sysinfo(?:/(.+))?$', [$this, 'sysInfo']);
    }

    public function sysInfo($args)
    {
        if (in_array($args, ['behaviours', 'templatetags'])) {
            $this->sysInfoServeDocument($args);
        }

        dotclear()->url()->p404();
        exit;
    }

    private function sysInfoServeDocument(string $doc): void
    {
            $module = dotclear()->themes->getModule(dotclear()->blog()->settings()->system->theme);
            $tplset = $module ? $module->templateset() : null;
            if (!empty($tplset) && is_dir(__DIR__ . '/../templates/' . $tplset)) {
                dotclear()->template()->setPath(dotclear()->template()->getPath(), __DIR__ . '/../templates/' . $tplset);
            } else {
                dotclear()->template()->setPath(dotclear()->template()->getPath(), __DIR__ . '/../templates/' . dotclear()->config()->template_default);
            }
            dotclear()->url()->serveDocument($doc . '.html');
            exit;
    }
}