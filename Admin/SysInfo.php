<?php
/*
 * @class Dotclear\Plugin\SysInfo\Admin\SysInfo
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

use Dotclear\App;
use Dotclear\Helper\Html\Form;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\GPC\GPC;
use Dotclear\Helper\Network\Http;
use Dotclear\Helper\Lexical;
use Dotclear\Modules\Repository\RepositoryReader;
use Dotclear\Process\Public\Template\Template;

class SysInfo
{
    public static $template;

    /**
     * Return list of registered permissions
     *
     * @return     string
     */
    public static function permissions(): string
    {
        $permissions = App::core()->user()->getPermissionsTypes();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Types of permission') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Type') . '</th>' .
            '<th scope="col" class="maximal">' . __('Label') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($permissions as $n => $l) {
            $str .= '<tr>' .
                '<td class="nowrap">' . $n . '</td>' .
                '<td class="maximal">' . __($l) . '</td>' .
                '</tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of REST methods
     *
     * @return     string
     */
    public static function restMethods(): string
    {
        $methods = App::core()->rest()->dump();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('REST methods') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Method') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($methods as $method => $callback) {
            $str .= '<tr><td class="nowrap">' . $method . '</td><td class="maximal"><code>';
            if (is_array($callback)) {
                if (count($callback) > 1) {
                    if (is_string($callback[0])) {
                        $str .= $callback[0] . '::' . $callback[1];
                    } else {
                        $str .=  get_class($callback[0]) . '->' . $callback[1];
                    }
                } else {
                    $str .= $callback[0];
                }
            } else {
                $str .= $callback;
            }
            $str .= '()</code></td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of plugins
     *
     * @return     string
     */
    public static function plugins(): string
    {
        // Affichage de la liste des plugins (et de leurs propriétés)
        $plugins = App::core()->plugins()->getModules();

        $thead = 
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Plugin id') . '</th>' .
            '<th scope="col" class="minimal">' . __('Properties') . '</th>' .
            '<th scope="col" class="maximal">' . __('Values') . '</th>' .
            '</tr>';

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Plugins (in loading order)') . '</caption>' .
            '<thead>' . $thead . '</thead>' .
            '<tbody>';
        $old_id = '';
        foreach ($plugins as $id => $m) {
            $old_k = '';
            foreach($m->properties() as $k => $v) {
                $str .= '<tr>';
                $str .= '<td class="nowrap">' . ($old_id != $id ? $id : ' ') . '</td>';
                $str .= '<td class="minimal">' . ($old_k != $k ? $k : ' ') . '</td>';
                $str .= '<td class="maximal"><pre class="sysinfo">';

                if (is_array($v)) {
                    foreach($v as $kk => $vv) {
                        if (is_array($vv)) {
                            $vv = implode(' => ', $vv);
                        }
                        $v[$kk] = $vv;
                    }
                    $v = implode(', ', $v);
                } elseif (is_bool($v)) {
                    $v = '<strong>' . ($v ? __('yes') : __('no')) . '</strong>';
                } elseif (is_string($v) && 0 === strpos($v, 'http')) {
                    $v = sprintf('<a href="%1$s">%1$s</a>', $v);
                }

                $str .= $v . '</pre></td>';
                $str .= '</tr>';

                $old_k = $k;
                $old_id = $id;
            }
            $str .=  $thead;
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of formaters (syntaxes coped by installed editors)
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function formaters(): string
    {
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        $formaters = App::core()->formater()->getFormaters();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Editors and their supported syntaxes') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Editor') . '</th>' .
            '<th scope="col" class="maximal">' . __('Syntax') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($formaters as $e => $s) {
            $str .= '<tr><td class="nowrap">' . $e . '</td>';
            $newline = false;
            if (is_array($s)) {
                foreach ($s as $f) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal">' . $f . '</td>';
                    $newline = true;
                }
            }
            $str .= '</tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of Dotclear constants
     *
     * @return     string
     */
    public static function dcConfig(): string
    {
        // Affichage des constantes remarquables de Dotclear
        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Dotclear configuration') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Parameter') . '</th>' .
            '<th scope="col" class="minimal">' . __('Type') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach (App::core()->config()->dump() as $c => $v) {
            $t = gettype($v);
            if (is_array($v)) {
                $v = implode(', ', $v);
            } elseif (is_bool($v)) {
                $v = '<strong>' . ($v ? __('yes') : __('no')) . '</strong>';
            } elseif (strpos($c, 'dir') !== false) {
                $v = Path::real($v);
            } elseif (strpos($c, 'password') !== false) {
                $v = '*****';
            }
            $str .= 
                '<tr><td class="nowrap">' .
                '<img src="?df=images/' . ($v === null || $v === '' ? 'check-off.png' : 'check-on.png') . '" /> <code>' . $c . '</code></td>' .
                '<td class="minimal">' . $t . '</td><td class="maximal">' . $v . '</td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of Dotclear constants
     *
     * @return     string
     */
    public static function dcConstants(): string
    {
        $constants = self::getConstants($undefined);

        // Affichage des constantes remarquables de Dotclear
        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Dotclear constants') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Constant') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($constants as $c => $v) {
            $str .= '<tr><td class="nowrap">' .
                '<img src="?df=images/' . ($v != $undefined ? 'check-on.png' : 'check-off.png') . '" /> <code>' . $c . '</code></td>' .
                '<td class="maximal">';
            if ($v != $undefined) {
                $str .= $v;
            }
            $str .= '</td></tr>';
        }
        $str .= '</tbody></table>';

        return $str;
    }

    /**
     * Return list of registered behaviours
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function behaviours(): string
    {
        // Affichage de la liste des behaviours inscrits
        $bl = App::core()->behavior()->dump();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Behaviours list') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Behavior') . '</th>' .
            '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($bl as $b => $f) {
            $str .= '<tr><td class="nowrap">' . $b . '</td>';
            $newline = false;
            if (is_array($f)) {
                foreach ($f as $fi) {
                    $str .= ($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal"><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            $str .= get_class($fi[0]) . '-&gt;' . $fi[1];
                        } else {
                            $str .= $fi[0] . '::' . $fi[1];
                        }
                    } else {
                        if ($fi instanceof \Closure) {
                            $str .= '__closure__';
                        } else {
                            $str .= $fi;
                        }
                    }
                    $str .= '()</code></td>';
                    $newline = true;
                }
            } else {
                $str .= '<td><code>' . $f . '()</code></td>';
            }
            $str .= '</tr>';
        }
        $str .= '</tbody></table>';

        $str .= '<p><a id="sysinfo-preview" href="' . App::core()->blog()->url . App::core()->url()->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

        return $str;
    }

    /**
     * Return list of registered URLs
     *
     * @return     string
     */
    public static function URLHandlers(): string
    {
        // Récupération des types d'URL enregistrées
        $urls = App::core()->url()->getHandlers();

        // Tables des URLs non gérées par le menu
        //$excluded = ['rsd','xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed'];
        $excluded = [];

        $str = '<table id="urls" class="sysinfo"><caption>' . __('List of known URLs') . '</caption>' .
            '<thead><tr><th scope="col">' . __('Type') . '</th>' .
            '<th scope="col">' . __('base URL') . '</th>' .
            '<th scope="col" class="maximal">' . __('Regular expression') . '</th></tr></thead>' .
            '<tbody>' .
            '<tr>' .
            '<td scope="row">' . 'home' . '</td>' .
            '<td>' . '' . '</td>' .
            '<td class="maximal"><code>' . '^$' . '</code></td>' .
            '</tr>';
        foreach ($urls as $handler) {
            if (!in_array($handler->type, $excluded)) {
                $str .= '<tr>' .
                    '<td scope="row">' . $handler->type . '</td>' .
                    '<td>' . $handler->url . '</td>' .
                    '<td class="maximal"><code>' . $handler->representation . '</code></td>' .
                    '</tr>';
            }
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /**
     * Return list of admin registered URLs
     *
     * @return     string
     */
    public static function adminURLs(): string
    {
        // Récupération de la liste des URLs d'admin enregistrées
        $urls = App::core()->adminurl()->dump();

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Admin registered URLs') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Class') . '</th>' .
            '<th scope="col" class="maximal">' . __('Query string') . '</th></tr></thead>' .
            '<tbody>';
        foreach ($urls as $handler => $v) {
            $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $handler . '</td>' .
                '<td><code>' . $v['class'] . '</code></td>' .
                '<td class="maximal"><code>' . http_build_query($v['qs']) . '</code></td>' .
                '</tr>';
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /**
     * Return PHP info
     *
     * @return     string
     */
    public static function phpInfo(): string
    {
        ob_start();
        phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES + INFO_ENVIRONMENT + INFO_VARIABLES);
        $phpinfo = ['phpinfo' => []];
        if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $keys = array_keys($phpinfo);
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    @$phpinfo[end($keys)][$match[2]] = isset($match[4]) ? [$match[3], $match[4]] : $match[3];
                } else {
                    @$phpinfo[end($keys)][] = $match[2];
                }
            }
        }
        $str = '';
        foreach ($phpinfo as $name => $section) {
            $str .= "<h3>$name</h3>\n<table class=\"sysinfo\">\n";
            foreach ($section as $key => $val) {
                if (is_array($val)) {
                    $str .= "<tr><td>$key</td><td>$val[0]</td><td>$val[1]</td></tr>\n";
                } elseif (is_string($key)) {
                    $str .= "<tr><td>$key</td><td>$val</td></tr>\n";
                } else {
                    $str .= "<tr><td>$val</td></tr>\n";
                }
            }
            $str .= "</table>\n";
        }

        return $str;
    }

    /**
     * Return list of compiled template's files
     *
     * @return     string
     */
    public static function templates(): string
    {
        $tplset = self::publicPrepend();

        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        $cache_path    = Path::real(App::core()->config()->get('cache_dir'));
        if (substr($cache_path, 0, strlen($document_root)) == $document_root) {
            $cache_path = substr($cache_path, strlen($document_root));
        } elseif (substr($cache_path, 0, strlen(App::core()->config()->get('root_dir'))) == App::core()->config()->get('root_dir')) {
            $cache_path = substr($cache_path, strlen(App::core()->config()->get('root_dir')));
        }
        $blog_host = App::core()->blog()->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $blog_url = App::core()->blog()->url;
        if (substr($blog_url, 0, strlen($blog_host)) == $blog_host) {
            $blog_url = substr($blog_url, strlen($blog_host));
        }

        $paths = self::$template->getPath();

        $str = '<form action="' . App::core()->adminurl()->get('admin.plugin.SysInfo') . '" method="post" id="tplform">' .
            '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of compiled templates in cache') . ' ' . $cache_path . '/cbtpl' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col">' . __('Template path') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Template file') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Cache subpath') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        // Template stack
        $stack = [];
        // Loop on template paths
        foreach ($paths as $path) {
            $sub_path = Path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(App::core()->config()->get('root_dir'))) == App::core()->config()->get('root_dir')) {
                $sub_path = substr($sub_path, strlen(App::core()->config()->get('root_dir')));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $path_displayed = false;
            // Don't know exactly why but need to cope with */default-templates !
            $md5_path = (!strstr($path, '/default-templates/' . $tplset) ? $path : path::real($path));
            $files    = Files::scandir($path);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches)) {
                        if (isset($matches[1])) {
                            if (!in_array($file, $stack)) {
                                $stack[]        = $file;
                                $cache_file     = md5($md5_path . '/' . $file) . '.php';
                                $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                                $cache_fullpath = Path::real(App::core()->config()->get('cache_dir')) . '/cbtpl/' . $cache_subpath;
                                $file_check     = $cache_fullpath . '/' . $cache_file;
                                $file_exists    = File_exists($file_check);
                                // $file_url       = Http::getHost() . $cache_path . '/cbtpl/' . $cache_subpath . '/' . $cache_file;
                                $str .= '<tr>' .
                                    '<td>' . ($path_displayed ? '' : $sub_path) . '</td>' .
                                    '<td scope="row" class="nowrap">' . $file . '</td>' .
                                    '<td class="nowrap">' . '<img src="?df=images/' . ($file_exists ? 'check-on.png' : 'check-off.png') . '" /> ' . $cache_subpath . '</td>' .
                                    '<td class="nowrap">' .
                                    Form::checkbox(
                                        ['tpl[]'],
                                        $cache_file,
                                        false,
                                        ($file_exists) ? 'tpl_compiled' : '',
                                        '',
                                        !($file_exists)
                                    ) . ' ' .
                                    '<label class="classic">' .
                                    ($file_exists ? '<a class="tpl_compiled" href="' . '#' . '">' : '') .
                                    $cache_file .
                                    ($file_exists ? '</a>' : '') .
                                    '</label></td>' .
                                    '</tr>';
                                $path_displayed = true;
                            }
                        }
                    }
                }
            }
        }
        $str .= '</tbody></table>' .
            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' . App::core()->nonce()->form() . '<input type="submit" class="delete" id="deltplaction" name="deltplaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with form templates action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function doFormTemplates(string &$checklist)
    {
        if (!GPC::post()->empty('deltplaction')) {
            // Cope with cache file deletion
            try {
                if (GPC::post()->empty('tpl')) {
                    throw new \Exception(__('No cache file selected'));
                }
                $root_cache = Path::real(App::core()->config()->get('cache_dir')) . '/cbtpl/';
                foreach (GPC::post()->array('tpl') as $k => $v) {
                    $cache_file = $root_cache . sprintf('%s/%s', substr($v, 0, 2), substr($v, 2, 2)) . '/' . $v;
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (\Exception $e) {
                $checklist = 'templates';
                App::core()->error()->add($e->getMessage());
            }
            if (!App::core()->error()->flag()) {
                App::core()->notice()->addSuccessNotice(__('Selected cache files have been deleted.'));
                App::core()->adminurl()->redirect('admin.plugin.SysInfo', ['tpl' => 1]);
            }
        }
    }

    public static function doCheckTemplates(string &$checklist)
    {
        if (!GPC::get()->empty('tpl')) {
            $checklist = 'templates';
        }
    }

    /**
     * Return list of template paths
     *
     * @return     string
     */
    public static function tplPaths(): string
    {
        self::publicPrepend();

        $paths         = self::$template->getPath();
        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of template paths') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($paths as $path) {
            $sub_path = Path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(App::core()->config()->get('root_dir'))) == App::core()->config()->get('root_dir')) {
                $sub_path = substr($sub_path, strlen(App::core()->config()->get('root_dir')));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $str .= '<tr><td>' . $sub_path . '</td><tr>';
        }
        $str .= '</tbody></table>';

        $str .= '<p><a id="sysinfo-preview" href="' . App::core()->blog()->url . App::core()->url()->getURLFor('sysinfo') . '/templatetags' . '">' . __('Display template tags') . '</a></p>';

        return $str;
    }

    /**
     * Return list of available modules
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     * @param      string  $title      The title
     * @param      string  $label      The label
     *
     * @return     string
     */
    private static function repoModules(bool $use_cache, string $url, string $title, string $label): string
    {
        $cache_path = Path::real(App::core()->config()->get('cache_dir'));
        $xml_url    = $url;
        $in_cache   = false;

        if ($use_cache) {
            // Get XML cache file for modules
            $ser_file = sprintf(
                '%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($xml_url), 0, 2),
                substr(md5($xml_url), 2, 2),
                md5($xml_url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }
        $parser    = RepositoryReader::quickParse($xml_url, App::core()->config()->get('cache_dir'), !$in_cache);
        $raw_datas = !$parser ? [] : $parser->getModules();
        Lexical::lexicalKeySort($raw_datas);

        $str = '<h3>' . $title . __(' from: ') . ($in_cache ? __('cache') : $xml_url) . '</h3>';
        if (!$parser) {     // @phpstan-ignore-line
            $str .= '<p>' . __('Repository is unreachable') . '</p>';
        } else {
            $str .= '<details id="expand-all"><summary>' . $label . '</summary></details>';
            $url_fmt = '<a href="%1$s">%1$s</a>';
            foreach ($raw_datas as $id => $infos) {
                $str .= '<details><summary>' . $id . '</summary>';
                $str .= '<ul>';
                foreach ($infos as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $val = (in_array($key, ['file', 'details', 'support', 'sshot']) && $value ? sprintf($url_fmt, $value) : $value);
                    $str .= '<li>' . $key . ' = ' . $val . '</li>';
                }
                $str .= '</ul>';
                $str .= '</details>';
            }
        }

        return $str;
    }

    /**
     * Return list of available plugins
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function repoPlugins(bool $use_cache = false): string
    {
        return self::repoModules(
            $use_cache,
            App::core()->blog()->settings()->getGroup('system')->getSetting('store_plugin_url'),
            __('Repository plugins list'),
            __('Plugin ID')
        );
    }

    /**
     * Return list of available themes
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function repoThemes(bool $use_cache = false): string
    {
        return self::repoModules(
            $use_cache,
            App::core()->blog()->settings()->getGroup('system')->getSetting('store_theme_url'),
            __('Repository themes list'),
            __('Theme ID')
        );
    }

    /**
     * Return a quote and PHP and DB driver version
     *
     * @return     string
     */
    public static function quoteVersions(): string
    {
        // Display a quote and PHP and DB version
        $quotes = [
            __('Live long and prosper.'),
            __('To infinity and beyond.'),
            __('So long, and thanks for all the fish.'),
            __('Find a needle in a haystack.'),
            __('A clever person solves a problem. A wise person avoids it.'),
            __('I\'m sorry, Dave. I\'m afraid I can\'t do that.'),
            __('With great power there must also come great responsibility.'),
            __('It\'s great, we have to do it all over again!'),
            __('Have You Tried Turning It Off And On Again?'),
        ];
        $q = rand(0, count($quotes) - 1);

        // Server info
        $server = '<blockquote class="sysinfo"><p>' . $quotes[$q] . '</p></blockquote>' .
            '<details open><summary>' . __('System info') . '</summary>' .
            '<ul>' .
            '<li>' . __('PHP Version: ') . '<strong>' . phpversion() . '</strong></li>' .
            '<li>' .
                __('DB driver: ') . '<strong>' . App::core()->con()->driver() . '</strong> ' .
                __('version') . ' <strong>' . App::core()->con()->version() . '</strong> ' .
                sprintf(__('using <strong>%s</strong> syntax'), App::core()->con()->syntax()) . '</li>' .
            '</ul>' .
            '</details>';

        // Dotclear info
        $dotclear = '<details open><summary>' . __('Dotclear info') . '</summary>' .
            '<ul>' .
            '<li>' . __('Dotclear version: ') . '<strong>' . App::core()->config()->get('core_version') . '</strong></li>' .
            '</ul>' .
            '</details>';

        // Update info

        $versions = '';
        $path     = Path::real(App::core()->config()->get('cache_dir') . '/versions');
        if (is_dir($path)) {
            $channels = ['stable', 'testing', 'unstable'];
            foreach ($channels as $channel) {
                $file = $path . '/dotclear-' . $channel;
                if (file_exists($file)) {
                    if ($content = @unserialize(@file_get_contents($file))) {
                        if (is_array($content)) {
                            $versions .= '<li>' . __('Channel: ') . '<strong>' . $channel . '</strong>' .
                                ' (' . date(DATE_ATOM, filemtime($file)) . ')' .
                                '<ul>' .
                                '<li>' . __('version: ') . '<strong>' . $content['version'] . '</strong></li>' .
                                '<li>' . __('href: ') . '<a href="' . $content['href'] . '">' . $content['href'] . '</a></li>' .
                                '<li>' . __('checksum: ') . '<code>' . $content['checksum'] . '</code></li>' .
                                '<li>' . __('info: ') . '<a href="' . $content['info'] . '">' . $content['info'] . '</a></li>' .
                                '<li>' . __('PHP min: ') . '<strong>' . $content['php'] . '</strong></li>' .
                                '</ul></li>';
                        }
                    }
                }
            }
        }
        if ($versions !== '') {
            $versions = '<details open><summary>' . __('Update info') . '</summary><ul>' . $versions . '</ul></details>';
        }

        return $server . $dotclear . $versions;
    }

    /* --- helpers --- */

    /**
     * Emulate public prepend
     *
     * @return     string  template set name
     */
    private static function publicPrepend(): string
    {
        $path = App::core()->themes()->getThemePath('templates/tpl');

        self::$template    = new Template(App::core()->config()->get('cache_dir'), __CLASS__ . '::$template');

        # Check templateset and add all path to tpl
        $tplset = App::core()->themes()->getModule(array_key_last($path))->templateset();
        if (!empty($tplset)) {
            $tplset_dir = Path::implodeSrc('Process', 'Public', 'templates', $tplset);
            if (is_dir($tplset_dir)) {
                self::$template->setPath($path, $tplset_dir, self::$template->getPath());
            } else {
                $tplset = null;
            }
        }
        if (empty($tplset)) {
            self::$template->setPath($path, self::$template->getPath());
        }

        // Looking for default-templates in each plugin's dir
        foreach (App::core()->plugins()->getModules() as $id => $module) {
            $plugin_tpl = Path::real($module->root() . '/templates/' . $tplset);
            if (!empty($plugin_tpl) && is_dir($plugin_tpl)) {
                self::$template->setPath(self::$template->getPath(), $plugin_tpl);
            }
        }

        return $tplset;
    }

    /**
     * Get current list of Dotclear constants and their values
     *
     * @return     array  list of constants
     */
    private static function getConstants(?string &$undefined): array
    {
        $undefined = '<!-- undefined -->';
        $constants = [
            'DOTCLEAR_ERROR_FILE' => defined('DOTCLEAR_ERROR_FILE') ? DOTCLEAR_ERROR_FILE : $undefined, 
            'DOTCLEAR_AUTH_SESS_UID' => defined('DOTCLEAR_AUTH_SESS_UID') ? DOTCLEAR_AUTH_SESS_UID : $undefined,
            'DOTCLEAR_SCH_CLASS' => defined('DOTCLEAR_SCH_CLASS') ? DOTCLEAR_SCH_CLASS : $undefined,
            'DOTCLEAR_CON_CLASS' => defined('DOTCLEAR_CON_CLASS') ? DOTCLEAR_CON_CLASS : $undefined,
            'DOTCLEAR_USER_CLASS' => defined('DOTCLEAR_USER_CLASS') ? DOTCLEAR_USER_CLASS : $undefined,
            'DC_FAIRTRACKBACKS_FORCE' => defined('DC_FAIRTRACKBACKS_FORCE') ? (DC_FAIRTRACKBACKS_FORCE ? __('yes') : __('no')) : $undefined,
            'DC_DNSBL_SUPER' => defined('DC_DNSBL_SUPER') ? (DC_DNSBL_SUPER ? __('yes') : __('no')) : $undefined,
            'DC_ANTISPAM_CONF_SUPER' => defined('DC_ANTISPAM_CONF_SUPER') ? (DC_ANTISPAM_CONF_SUPER ? __('yes') : __('no')) : $undefined,
            'DC_AKISMET_SUPER' => defined('DC_AKISMET_SUPER') ? (DC_AKISMET_SUPER ? __('yes') : __('no')) : $undefined,

        ];

        if (App::core()->plugins()->hasModule('StaticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = defined('DC_SC_CACHE_ENABLE') ? (DC_SC_CACHE_ENABLE ? __('yes') : __('no')) : $undefined;
            $constants['DC_SC_CACHE_DIR']       = defined('DC_SC_CACHE_DIR') ? DC_SC_CACHE_DIR : $undefined;
            $constants['DC_SC_CACHE_BLOGS_ON']  = defined('DC_SC_CACHE_BLOGS_ON') ? DC_SC_CACHE_BLOGS_ON : $undefined;
            $constants['DC_SC_CACHE_BLOGS_OFF'] = defined('DC_SC_CACHE_BLOGS_OFF') ? DC_SC_CACHE_BLOGS_OFF : $undefined;
            $constants['DC_SC_EXCLUDED_URL']    = defined('DC_SC_EXCLUDED_URL') ? DC_SC_EXCLUDED_URL : $undefined;
        }

        return $constants;
    }

    public static function folders()
    {
        // Check generic Dotclear folders
        $folders = [
            'root'     => Path::implodeBase(),
            'config'   => Path::implodeBase(),
            'cache'    => [
                App::core()->config()->get('cache_dir'),
                App::core()->config()->get('cache_dir') . '/cbfeed',
                App::core()->config()->get('cache_dir') . '/cbtpl',
                App::core()->config()->get('cache_dir') . '/dcrepo',
                App::core()->config()->get('cache_dir') . '/versions',
            ],
            'digest'   => App::core()->config()->get('digests_dir'),
            'l10n'     => App::core()->config()->get('l10n_dir'),
            'plugins'  => App::core()->plugins()->getPaths() ?? [],
            'themes'   => App::core()->themes()->getPaths() ?? [],
            'public'   => App::core()->blog()->public_path,
            'var'      => App::core()->config()->get('var_dir'),
        ];

        if (defined('DC_SC_CACHE_DIR')) {
            $folders += ['static' => DC_SC_CACHE_DIR];
        }

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Dotclear folders and files') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '<th scope="col" class="maximal">' . __('Status') . '</th></tr></thead>' .
            '<tbody>';

        foreach ($folders as $name => $subfolder) {
            if (!is_array($subfolder)) {
                $subfolder = [$subfolder];
            }
            foreach ($subfolder as $folder) {
                $path     = Path::real($folder);
                $writable = $path && is_writable($path);
                $touch    = true;
                $err      = [];
                $void     = '';
                if ($writable && is_dir($path)) {
                    // Try to create a file, inherit dir perms and then delete it
                    try {
                        $void  = $path . (substr($path, -1) === '/' ? '' : '/') . 'tmp-' . str_shuffle(MD5(microtime()));
                        $touch = false;
                        Files::putContent($void, '');
                        if (file_exists($void)) {
                            Files::inheritChmod($void);
                            unlink($void);
                            $touch = true;
                        }
                    } catch (\Exception $e) {
                        $err[] = $void . ' : ' . $e->getMessage();
                    }
                }
                if ($path) {
                    $status = $writable && $touch ?
                    '<img src="?df=images/check-on.png" alt="" /> ' . __('Writable') :
                    '<img src="?df=images/check-wrn.png" alt="" /> ' . __('Readonly');
                } else {
                    $status = '<img src="?df=images/check-off.png" alt="" /> ' . __('Unknown');
                }
                if (count($err) > 0) {
                    $status .= '<div style="display: none;"><p>' . implode('<br />', $err) . '</p></div>';
                }
/*
                if (substr($folder, 0, strlen(App::core()->config()->get('root_dir'))) === App::core()->config()->get('root_dir')) {
                    $folder = substr_replace($folder, '<code>DOTCLEAR_ROOT_DIR</code> ', 0, strlen(App::core()->config()->get('root_dir')));
                }
*/
                $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $name . '</td>' .
                '<td class="maximal">' . Path::real($folder, false) . '</td>' .
                '<td class="nowrap">' . $status . '</td>' .
                '</tr>';

                $name = '';     // Avoid repeating it if multiple lines
            }
        }

        $str .= '</tbody>' .
            '</table>';

        return $str;
    }

    /* --- 3rd party plugins specific --- */

    /**
     * Return list of files in static cache
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function staticCache()
    {
        $blog_host = App::core()->blog()->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(Http::getHostFromURL($blog_host));
        $cache     = new StaticCache(DC_SC_CACHE_DIR, $cache_key);

        if (!is_dir($cache_dir)) {
            return '<p>' . __('Static cache directory does not exists') . '</p>';
        }
        if (!is_readable($cache_dir)) {
            return '<p>' . __('Static cache directory is not readable') . '</p>';
        }
        $k          = str_split($cache_key, 2);
        $cache_root = $cache_dir;
        $cache_dir  = sprintf('%s/%s/%s/%s/%s', $cache_dir, $k[0], $k[1], $k[2], $cache_key);

        // Add a static cache URL convertor
        $str = '<p class="fieldset">' .
            '<label for="sccalc_url" class="classic">' . __('URL:') . '</label>' . ' ' .
            Form::field('sccalc_url', 50, 255, Html::escapeHTML(App::core()->blog()->url)) . ' ' .
            '<input type="button" id="getscaction" name="getscaction" value="' . __(' → ') . '" />' .
            ' <span id="sccalc_res"></span><a id="sccalc_preview" href="#" data-dir="' . $cache_dir . '"></a>' .
            '</p>';

        // List of existing cache files
        $str .= '<form action="' . App::core()->adminurl()->get('admin.plugin.SysInfo') . '" method="post" id="scform">';

        $str .= '<table id="chk-table-result" class="sysinfo">';
        $str .= '<caption>' . __('List of static cache files in') . ' ' . substr($cache_dir, strlen($cache_root)) .
           ', ' . __('last update:') . ' ' . date('Y-m-d H:i:s', $cache->getMtime()) . '</caption>';
        $str .= '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap" colspan="3">' . __('Cache subpath') . '</th>' .
            '<th scope="col" class="nowrap maximal">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>';
        $str .= '<tbody>';

        $files = files::scandir($cache_dir);
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                    $cache_fullpath = $cache_dir . '/' . $file;
                    if (is_dir($cache_fullpath)) {
                        $str .= '<tr>' .
                            '<td class="nowrap">' .
                            '<a class="sc_dir" href="#">' . $file . '</a>' .
                            '</td>' .                                     // 1st level
                            '<td class="nowrap">' . __('…') . '</td>' . // 2nd level (loaded via getStaticCacheDir REST)
                            '<td class="nowrap"></td>' .                  // 3rd level (loaded via getStaticCacheList REST)
                            '<td class="nowrap maximal"></td>' .          // cache file (loaded via getStaticCacheList REST too)
                            '</tr>' . "\n";
                    }
                }
            }
        }

        $str .= '</tbody></table>';
        $str .= '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' . App::core()->nonce()->form() . '<input type="submit" class="delete" id="delscaction" name="delscaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with static cache form action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception  (description)
     */
    public static function doFormStaticCache(string &$checklist)
    {
        if (!GPC::post()->empty('delscaction')) {
            // Cope with static cache file deletion
            try {
                if (GPC::post()->empty('sc')) {
                    throw new \Exception(__('No cache file selected'));
                }
                foreach (GPC::post()->array('sc') as $k => $cache_file) {
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (\Exception $e) {
                $checklist = 'sc';
                App::core()->error()->add($e->getMessage());
            }
            if (!App::core()->error()->flag()) {
                App::core()->notice()->addSuccessNotice(__('Selected cache files have been deleted.'));
                App::core()->adminurl()->redirect('admin.plugin.SysInfo', ['sc' => 1]);
            }
        }
    }

    public static function doCheckStaticCache(string &$checklist)
    {
        if (!GPC::get()->empty('sc')) {
            $checklist = 'sc';
        }
    }
}
