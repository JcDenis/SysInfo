<?php
declare(strict_types=1);

namespace Dotclear\Plugin\SysInfo\Public;

class SysInfoTemplate
{
    public function __construct()
    {
        dotclear()->template()->addValue('SysInfoPageTitle', function($attr): string {
            return '<?php echo \'' . __('System Information') . '\'; ?>';
        });
        dotclear()->template()->addValue('SysInfoBehaviours', function($attr): string {
            return 
                '<h3>' . '<?php echo \'' . __('Public behaviours list') . '\'; ?>' . '</h3>' . "\n" .
                '<?php echo ' . __CLASS__ . '::publicBehavioursList(); ?>';
        });
        dotclear()->template()->addValue('SysInfoTemplatetags', function($attr): string {
            return
                '<h3>' . '<?php echo \'' . __('Template tags list') . '\'; ?>' . '</h3>' . "\n" .
                '<?php echo ' . __CLASS__ . '::publicTemplatetagsList(); ?>';
        });
    }

    public static function publicBehavioursList()
    {
        $code = '<ul>' . "\n";

        $bl = dotclear()->behavior()->dump();
        foreach ($bl as $b => $f) {
            $code .= '<li>' . $b . ' : ';
            if (is_array($f)) {
                $code .= "\n" . '<ul>';
                foreach ($f as $fi) {
                    $code .= '<li><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            $code .= get_class($fi[0]) . '-&gt;' . $fi[1] . '()';
                        } else {
                            $code .= $fi[0] . '::' . $fi[1] . '()';
                        }
                    } elseif ($fi instanceof \Closure) {
                        $code .= '__Closure__';
                    } else {
                        $code .= $fi . '()';
                    }
                    $code .= '</code></li>';
                }
                $code .= '</ul>' . "\n";
            } else {
                $code .= $f . '()';
            }
            $code .= '</li>' . "\n";
        }
        $code .= '</ul>' . "\n";

        return $code;
    }

    public static function publicTemplatetagsList()
    {
        $code = '<ul>' . "\n";

        $tplblocks = array_values(dotclear()->template()->getBlockslist());
        $tplvalues = array_values(dotclear()->template()->getValueslist());

        sort($tplblocks, SORT_STRING);
        sort($tplvalues, SORT_STRING);

        $code .= '<li>' . __('Blocks') . '<ul>' . "\n";
        foreach ($tplblocks as $elt) {
            $code .= '<li>' . $elt . '</li>' . "\n";
        }
        $code .= '</ul></li>' . "\n";

        $code .= '<li>' . __('Values') . '<ul>' . "\n";
        foreach ($tplvalues as $elt) {
            $code .= '<li>' . $elt . '</li>' . "\n";
        }
        $code .= '</ul></li>' . "\n";

        $code .= '</ul>' . "\n";

        return $code;
    }
}
