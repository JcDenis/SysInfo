<?php
/*
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
if (!defined('DC_RC_PATH')) {
    return;
}

class libSysInfo
{
    /** @var dcCore dcCore instance */
    public static $core;

    /**
     * Return list of registered permissions
     *
     * @return     string
     */
    public static function permissions(): string
    {
        $permissions = self::$core->auth->getPermissionsTypes();

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
        $methods = self::$core->rest->functions;

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
                    $str .= $callback[0] . '::' . $callback[1];
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
        $plugins = self::$core->plugins->getModules();

        $str = '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('Plugins (in loading order)') . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Plugin id') . '</th>' .
            '<th scope="col" class="maximal">' . __('Properties') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        foreach ($plugins as $id => $m) {
            $str .= '<tr><td class="nowrap">' . $id . '</td><td class="maximal">';
            $str .= '<pre class="sysinfo">' . print_r($m, true) . '</pre></td></tr>';
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
        $formaters = self::$core->getFormaters();

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
                '<img src="images/' . ($v != $undefined ? 'check-on.png' : 'check-off.png') . '" /> <code>' . $c . '</code></td>' .
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
        $bl = self::$core->getBehaviors('');

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

        $str .= '<p><a id="sysinfo-preview" href="' . self::$core->blog->url . self::$core->url->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

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
        $urls = self::$core->url->getTypes();

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
        foreach ($urls as $type => $param) {
            if (!in_array($type, $excluded)) {
                $str .= '<tr>' .
                    '<td scope="row">' . $type . '</td>' .
                    '<td>' . $param['url'] . '</td>' .
                    '<td class="maximal"><code>' . $param['representation'] . '</code></td>' .
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
        $urls = self::$core->adminurl->dumpUrls();

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Admin registered URLs') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('URL') . '</th>' .
            '<th scope="col" class="maximal">' . __('Query string') . '</th></tr></thead>' .
            '<tbody>';
        foreach ($urls as $name => $url) {
            $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $name . '</td>' .
                '<td><code>' . $url['url'] . '</code></td>' .
                '<td class="maximal"><code>' . http_build_query($url['qs']) . '</code></td>' .
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
    public static function templates(string $p_url): string
    {
        $tplset = self::publicPrepend();

        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        $cache_path    = path::real(DC_TPL_CACHE);
        if (substr($cache_path, 0, strlen($document_root)) == $document_root) {
            $cache_path = substr($cache_path, strlen($document_root));
        } elseif (substr($cache_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
            $cache_path = substr($cache_path, strlen(DC_ROOT));
        }
        $blog_host = self::$core->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $blog_url = self::$core->blog->url;
        if (substr($blog_url, 0, strlen($blog_host)) == $blog_host) {
            $blog_url = substr($blog_url, strlen($blog_host));
        }

        $paths = self::$core->tpl->getPath();

        $str = '<form action="' . $p_url . '" method="post" id="tplform">' .
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
            $sub_path = path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $path_displayed = false;
            // Don't know exactly why but need to cope with */default-templates !
            $md5_path = (!strstr($path, '/default-templates/' . $tplset) ? $path : path::real($path));
            $files    = files::scandir($path);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches)) {
                        if (isset($matches[1])) {
                            if (!in_array($file, $stack)) {
                                $stack[]        = $file;
                                $cache_file     = md5($md5_path . '/' . $file) . '.php';
                                $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                                $cache_fullpath = path::real(DC_TPL_CACHE) . '/cbtpl/' . $cache_subpath;
                                $file_check     = $cache_fullpath . '/' . $cache_file;
                                $file_exists    = file_exists($file_check);
                                // $file_url       = http::getHost() . $cache_path . '/cbtpl/' . $cache_subpath . '/' . $cache_file;
                                $str .= '<tr>' .
                                    '<td>' . ($path_displayed ? '' : $sub_path) . '</td>' .
                                    '<td scope="row" class="nowrap">' . $file . '</td>' .
                                    '<td class="nowrap">' . '<img src="images/' . ($file_exists ? 'check-on.png' : 'check-off.png') . '" /> ' . $cache_subpath . '</td>' .
                                    '<td class="nowrap">' .
                                    form::checkbox(
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
            '<p class="col right">' . self::$core->formNonce() . '<input type="submit" class="delete" id="deltplaction" name="deltplaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with form templates action.
     *
     * @param      string     $p_url      The p url
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function doFormTemplates(string $p_url, string &$checklist)
    {
        if (!empty($_POST['deltplaction'])) {
            // Cope with cache file deletion
            try {
                if (empty($_POST['tpl'])) {
                    throw new Exception(__('No cache file selected'));
                }
                $root_cache = path::real(DC_TPL_CACHE) . '/cbtpl/';
                foreach ($_POST['tpl'] as $k => $v) {
                    $cache_file = $root_cache . sprintf('%s/%s', substr($v, 0, 2), substr($v, 2, 2)) . '/' . $v;
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $checklist = 'templates';
                self::$core->error->add($e->getMessage());
            }
            if (!self::$core->error->flag()) {
                dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
                http::redirect($p_url . '&tpl=1');
            }
        }
    }

    public static function doCheckTemplates(string &$checklist)
    {
        if (!empty($_GET['tpl'])) {
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
        $paths         = self::$core->tpl->getPath();
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
            $sub_path = path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $str .= '<tr><td>' . $sub_path . '</td><tr>';
        }
        $str .= '</tbody></table>';

        $str .= '<p><a id="sysinfo-preview" href="' . self::$core->blog->url . self::$core->url->getURLFor('sysinfo') . '/templatetags' . '">' . __('Display template tags') . '</a></p>';

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
        $cache_path = path::real(DC_TPL_CACHE);
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
        $parser    = dcStoreReader::quickParse($xml_url, DC_TPL_CACHE, !$in_cache);
        $raw_datas = !$parser ? [] : $parser->getModules();     // @phpstan-ignore-line
        dcUtils::lexicalKeySort($raw_datas);

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
            self::$core->blog->settings->system->store_plugin_url,
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
            self::$core->blog->settings->system->store_theme_url,
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
                __('DB driver: ') . '<strong>' . self::$core->con->driver() . '</strong> ' .
                __('version') . ' <strong>' . self::$core->con->version() . '</strong> ' .
                sprintf(__('using <strong>%s</strong> syntax'), self::$core->con->syntax()) . '</li>' .
            '</ul>' .
            '</details>';

        // Dotclear info
        $dotclear = '<details open><summary>' . __('Dotclear info') . '</summary>' .
            '<ul>' .
            '<li>' . __('Dotclear version: ') . '<strong>' . DC_VERSION . '</strong></li>' .
            '</ul>' .
            '</details>';

        // Update info

        $versions = '';
        $path     = path::real(DC_TPL_CACHE . '/versions');
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
        // Emulate public prepend

        self::$core->tpl    = new dcTemplate(DC_TPL_CACHE, '$core->tpl', self::$core);
        self::$core->themes = new dcThemes(self::$core);
        self::$core->themes->loadModules(self::$core->blog->themes_path);
        if (!isset($__theme)) {     // @phpstan-ignore-line
            $__theme = self::$core->blog->settings->system->theme;
        }
        if (!self::$core->themes->moduleExists($__theme)) {
            $__theme = self::$core->blog->settings->system->theme = 'default';
        }
        $tplset         = self::$core->themes->moduleInfo($__theme, 'tplset');
        $__parent_theme = self::$core->themes->moduleInfo($__theme, 'parent');
        if ($__parent_theme) {
            if (!self::$core->themes->moduleExists($__parent_theme)) {
                $__theme        = self::$core->blog->settings->system->theme        = 'default';
                $__parent_theme = null;
            }
        }
        $__theme_tpl_path = [
            self::$core->blog->themes_path . '/' . $__theme . '/tpl',
        ];
        if ($__parent_theme) {
            $__theme_tpl_path[] = self::$core->blog->themes_path . '/' . $__parent_theme . '/tpl';
            if (empty($tplset)) {
                $tplset = self::$core->themes->moduleInfo($__parent_theme, 'tplset');
            }
        }
        if (empty($tplset)) {
            $tplset = DC_DEFAULT_TPLSET;
        }
        $main_plugins_root = explode(':', DC_PLUGINS_ROOT);
        self::$core->tpl->setPath(
            $__theme_tpl_path,
            $main_plugins_root[0] . '/../inc/public/default-templates/' . $tplset,
            self::$core->tpl->getPath()
        );

        // Looking for default-templates in each plugin's dir
        $plugins = self::$core->plugins->getModules();
        foreach ($plugins as $k => $v) {
            $plugin_root = self::$core->plugins->moduleInfo($k, 'root');
            if ($plugin_root) {
                self::$core->tpl->setPath(self::$core->tpl->getPath(), $plugin_root . '/default-templates/' . $tplset);
                // To be exhaustive add also direct directory (without templateset)
                self::$core->tpl->setPath(self::$core->tpl->getPath(), $plugin_root . '/default-templates');
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
            'DC_ADMIN_CONTEXT'  => defined('DC_ADMIN_CONTEXT') ? (DC_ADMIN_CONTEXT ? 'true' : 'false') : $undefined,
            'DC_ADMIN_MAILFROM' => defined('DC_ADMIN_MAILFROM') ? DC_ADMIN_MAILFROM : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_ADMIN_SSL'     => defined('DC_ADMIN_SSL') ? (DC_ADMIN_SSL ? 'true' : 'false') : $undefined,
            'DC_ADMIN_URL'     => defined('DC_ADMIN_URL') ? DC_ADMIN_URL : $undefined,
            'DC_AKISMET_SUPER' => defined('DC_AKISMET_SUPER') ? (DC_AKISMET_SUPER ? 'true' : 'false') : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_ALLOW_MULTI_MODULES' => defined('DC_ALLOW_MULTI_MODULES') ? (DC_ALLOW_MULTI_MODULES ? 'true' : 'false') : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_ALLOW_REPOSITORIES'  => defined('DC_ALLOW_REPOSITORIES') ? (DC_ALLOW_REPOSITORIES ? 'true' : 'false') : $undefined,
            'DC_ANTISPAM_CONF_SUPER' => defined('DC_ANTISPAM_CONF_SUPER') ? (DC_ANTISPAM_CONF_SUPER ? 'true' : 'false') : $undefined,
            'DC_AUTH_PAGE'           => defined('DC_AUTH_PAGE') ? DC_AUTH_PAGE : $undefined,
            'DC_AUTH_SESS_ID'        => defined('DC_AUTH_SESS_ID') ? DC_AUTH_SESS_ID : $undefined,
            'DC_AUTH_SESS_UID'       => defined('DC_AUTH_SESS_UID') ? DC_AUTH_SESS_UID : $undefined,
            'DC_BACKUP_PATH'         => defined('DC_BACKUP_PATH') ? DC_BACKUP_PATH : $undefined,
            'DC_BLOG_ID'             => defined('DC_BLOG_ID') ? DC_BLOG_ID : $undefined,
            'DC_CONTEXT_ADMIN'       => defined('DC_CONTEXT_ADMIN') ? (DC_CONTEXT_ADMIN ? 'true' : 'false') : $undefined,
            'DC_CONTEXT_MODULE'      => defined('DC_CONTEXT_MODULE') ? (DC_CONTEXT_MODULE ? 'true' : 'false') : $undefined,
            'DC_CRYPT_ALGO'          => defined('DC_CRYPT_ALGO') ? DC_CRYPT_ALGO : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_STORE_NOT_UPDATE' => defined('DC_STORE_NOT_UPDATE') ? (DC_STORE_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_DBDRIVER'         => defined('DC_DBDRIVER') ? DC_DBDRIVER : $undefined,
            'DC_DBHOST'           => defined('DC_DBHOST') ? DC_DBHOST : $undefined,
            'DC_DBNAME'           => defined('DC_DBNAME') ? DC_DBNAME : $undefined,
            'DC_DBPASSWORD'       => defined('DC_DBPASSWORD') ? '********* ' . __('(see inc/config.php)') /* DC_DBPASSWORD */ : $undefined,
            'DC_DBPREFIX'         => defined('DC_DBPREFIX') ? DC_DBPREFIX : $undefined,
            'DC_DBUSER'           => defined('DC_DBUSER') ? DC_DBUSER : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_DEBUG'           => defined('DC_DEBUG') ? (DC_DEBUG ? 'true' : 'false') : $undefined,
            'DC_DEFAULT_JQUERY'  => defined('DC_DEFAULT_JQUERY') ? DC_DEFAULT_JQUERY : $undefined,
            'DC_DEFAULT_TPLSET'  => defined('DC_DEFAULT_TPLSET') ? DC_DEFAULT_TPLSET : $undefined,
            'DC_DEV'             => defined('DC_DEV') ? (DC_DEV ? 'true' : 'false') : $undefined,
            'DC_DIGESTS'         => defined('DC_DIGESTS') ? DC_DIGESTS : $undefined,
            'DC_DISTRIB_PLUGINS' => defined('DC_DISTRIB_PLUGINS') ? DC_DISTRIB_PLUGINS : $undefined,
            'DC_DISTRIB_THEMES'  => defined('DC_DISTRIB_THEMES') ? DC_DISTRIB_THEMES : $undefined,
            'DC_DNSBL_SUPER'     => defined('DC_DNSBL_SUPER') ? (DC_DNSBL_SUPER ? 'true' : 'false') : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_FAIRTRACKBACKS_FORCE' => defined('DC_FAIRTRACKBACKS_FORCE') ? (DC_FAIRTRACKBACKS_FORCE ? 'true' : 'false') : $undefined,
            'DC_FORCE_SCHEME_443'     => defined('DC_FORCE_SCHEME_443') ? (DC_FORCE_SCHEME_443 ? 'true' : 'false') : $undefined,
            'DC_L10N_ROOT'            => defined('DC_L10N_ROOT') ? DC_L10N_ROOT : $undefined,
            'DC_L10N_UPDATE_URL'      => defined('DC_L10N_UPDATE_URL') ? DC_L10N_UPDATE_URL : $undefined,
            'DC_MASTER_KEY'           => defined('DC_MASTER_KEY') ? '********* ' . __('(see inc/config.php)') /* DC_MASTER_KEY */ : $undefined,
            'DC_MAX_UPLOAD_SIZE'      => defined('DC_MAX_UPLOAD_SIZE') ? DC_MAX_UPLOAD_SIZE : $undefined,
            'DC_NEXT_REQUIRED_PHP'    => defined('DC_NEXT_REQUIRED_PHP') ? DC_NEXT_REQUIRED_PHP : $undefined,
            /* @phpstan-ignore-next-line */
            'DC_NOT_UPDATE'       => defined('DC_NOT_UPDATE') ? (DC_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_PLUGINS_ROOT'     => defined('DC_PLUGINS_ROOT') ? DC_PLUGINS_ROOT : $undefined,
            'DC_QUERY_TIMEOUT'    => defined('DC_QUERY_TIMEOUT') ? DC_QUERY_TIMEOUT . ' ' . __('seconds') : $undefined,
            'DC_RC_PATH'          => defined('DC_RC_PATH') ? DC_RC_PATH : $undefined,
            'DC_ROOT'             => defined('DC_ROOT') ? DC_ROOT : $undefined,
            'DC_SESSION_NAME'     => defined('DC_SESSION_NAME') ? DC_SESSION_NAME : $undefined,
            'DC_SESSION_TTL'      => defined('DC_SESSION_TTL') ? DC_SESSION_TTL : $undefined,
            'DC_SHOW_HIDDEN_DIRS' => defined('DC_SHOW_HIDDEN_DIRS') ? (DC_SHOW_HIDDEN_DIRS ? 'true' : 'false') : $undefined,
            'DC_START_TIME'       => defined('DC_START_TIME') ? DC_START_TIME : $undefined,
            'DC_TPL_CACHE'        => defined('DC_TPL_CACHE') ? DC_TPL_CACHE : $undefined,
            'DC_UPDATE_URL'       => defined('DC_UPDATE_URL') ? DC_UPDATE_URL : $undefined,
            'DC_UPDATE_VERSION'   => defined('DC_UPDATE_VERSION') ? DC_UPDATE_VERSION : $undefined,
            'DC_VAR'              => defined('DC_VAR') ? DC_VAR : $undefined,
            'DC_VENDOR_NAME'      => defined('DC_VENDOR_NAME') ? DC_VENDOR_NAME : $undefined,
            'DC_VERSION'          => defined('DC_VERSION') ? DC_VERSION : $undefined,
            'DC_XMLRPC_URL'       => defined('DC_XMLRPC_URL') ? DC_XMLRPC_URL : $undefined,
            'CLEARBRICKS_VERSION' => defined('CLEARBRICKS_VERSION') ? CLEARBRICKS_VERSION : $undefined,
        ];

        if (self::$core->plugins->moduleExists('staticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = defined('DC_SC_CACHE_ENABLE') ? (DC_SC_CACHE_ENABLE ? 'true' : 'false') : $undefined;
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
            'root'   => DC_ROOT,
            'config' => DC_RC_PATH,
            'cache'  => [
                DC_TPL_CACHE,
                DC_TPL_CACHE . '/cbfeed',
                DC_TPL_CACHE . '/cbtpl',
                DC_TPL_CACHE . '/dcrepo',
                DC_TPL_CACHE . '/versions',
            ],
            'digest'  => DC_DIGESTS,
            'l10n'    => DC_L10N_ROOT,
            'plugins' => explode(':', DC_PLUGINS_ROOT),
            'public'  => self::$core->blog->public_path,
            'themes'  => self::$core->blog->themes_path,
            'var'     => DC_VAR,
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
                $path     = path::real($folder);
                $writable = is_writable($path);
                $touch    = true;
                $err      = [];
                $void     = '';
                if ($writable && is_dir($path)) {
                    // Try to create a file, inherit dir perms and then delete it
                    try {
                        $void  = $path . (substr($path, -1) === '/' ? '' : '/') . 'tmp-' . str_shuffle(MD5(microtime()));
                        $touch = false;
                        files::putContent($void, '');
                        if (file_exists($void)) {
                            files::inheritChmod($void);
                            unlink($void);
                            $touch = true;
                        }
                    } catch (Exception $e) {
                        $err[] = $void . ' : ' . $e->getMessage();
                    }
                }
                if ($path) {
                    $status = $writable && $touch ?
                    '<img src="images/check-on.png" alt="" /> ' . __('Writable') :
                    '<img src="images/check-wrn.png" alt="" /> ' . __('Readonly');
                } else {
                    $status = '<img src="images/check-off.png" alt="" /> ' . __('Unknown');
                }
                if (count($err) > 0) {
                    $status .= '<div style="display: none;"><p>' . implode('<br />', $err) . '</p></div>';
                }

                if (substr($folder, 0, strlen(DC_ROOT)) === DC_ROOT) {
                    $folder = substr_replace($folder, '<code>DC_ROOT</code> ', 0, strlen(DC_ROOT));
                }

                $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $name . '</td>' .
                '<td class="maximal">' . $folder . '</td>' .
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
     * @param      string  $p_url  The p url
     *
     * @return     string  ( description_of_the_return_value )
     */
    public static function staticCache(string $p_url)
    {
        $blog_host = self::$core->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $cache_dir = path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(http::getHostFromURL($blog_host));
        $cache     = new dcStaticCache(DC_SC_CACHE_DIR, $cache_key);

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
            form::field('sccalc_url', 50, 255, html::escapeHTML(self::$core->blog->url)) . ' ' .
            '<input type="button" id="getscaction" name="getscaction" value="' . __(' → ') . '" />' .
            ' <span id="sccalc_res"></span><a id="sccalc_preview" href="#" data-dir="' . $cache_dir . '"></a>' .
            '</p>';

        // List of existing cache files
        $str .= '<form action="' . $p_url . '" method="post" id="scform">';

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
            '<p class="col right">' . self::$core->formNonce() . '<input type="submit" class="delete" id="delscaction" name="delscaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with static cache form action.
     *
     * @param      string     $p_url      The p url
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception  (description)
     */
    public static function doFormStaticCache(string $p_url, string &$checklist)
    {
        if (!empty($_POST['delscaction'])) {
            // Cope with static cache file deletion
            try {
                if (empty($_POST['sc'])) {
                    throw new Exception(__('No cache file selected'));
                }
                foreach ($_POST['sc'] as $k => $cache_file) {
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $checklist = 'sc';
                self::$core->error->add($e->getMessage());
            }
            if (!self::$core->error->flag()) {
                dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
                http::redirect($p_url . '&sc=1');
            }
        }
    }

    public static function doCheckStaticCache(string &$checklist)
    {
        if (!empty($_GET['sc'])) {
            $checklist = 'sc';
        }
    }
}

/*
 * Store current dcCore instance
 */
libSysInfo::$core = $GLOBALS['core'];
