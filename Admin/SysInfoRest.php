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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

class sysInfoRest
{
    public static function getCompiledTemplate($core, $get)
    {
        // Return compiled template file content
        $file    = !empty($get['file']) ? $get['file'] : '';
        $rsp     = new xmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($file != '') {
            // Load content of compiled template file (if exist and if is readable)
            $subpath  = sprintf('%s/%s', substr($file, 0, 2), substr($file, 2, 2));
            $fullpath = path::real(DC_TPL_CACHE) . '/cbtpl/' . $subpath . '/' . $file;
            if (file_exists($fullpath) && is_readable($fullpath)) {
                $content = file_get_contents($fullpath);
                $ret     = true;
            }
        }

        $rsp->ret = $ret;
        // Escape file content (in order to avoid further parsing error)
        // JSON encode to preserve UTF-8 encoding
        // Base 64 encoding to preserve line breaks
        $rsp->msg = base64_encode(json_encode(html::escapeHTML($content)));

        return $rsp;
    }

    public static function getStaticCacheDir($core, $get)
    {
        // Return list of folders in a given cache folder
        $root    = !empty($get['root']) ? $get['root'] : '';
        $rsp     = new xmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($root != '') {
            $blog_host = $core->blog->host;
            if (substr($blog_host, -1) != '/') {
                $blog_host .= '/';
            }
            $cache_dir = path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(http::getHostFromURL($blog_host));

            $k         = str_split($cache_key, 2);
            $cache_dir = sprintf('%s/%s/%s/%s/%s', $cache_dir, $k[0], $k[1], $k[2], $cache_key);

            if (is_dir($cache_dir) && is_readable($cache_dir)) {
                $files = files::scandir($cache_dir . '/' . $root);
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                            $cache_fullpath = $cache_dir . '/' . $root . '/' . $file;
                            if (is_dir($cache_fullpath)) {
                                $content .= '<tr>' .
                                '<td class="nowrap">' . $root . '</td>' . // 1st level
                                '<td class="nowrap">' .
                                '<a class="sc_subdir" href="#">' . $file . '</a>' .
                                '</td>' .                                     // 2nd level
                                '<td class="nowrap">' . __('…') . '</td>' . // 3rd level
                                '<td class="nowrap maximal"></td>' .          // cache file
                                '</tr>' . "\n";
                            }
                        }
                    }
                }
                $ret = true;
            }
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    public static function getStaticCacheList($core, $get)
    {
        // Return list of folders and files in a given folder
        $root    = !empty($get['root']) ? $get['root'] : '';
        $rsp     = new xmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($root != '') {
            $blog_host = $core->blog->host;
            if (substr($blog_host, -1) != '/') {
                $blog_host .= '/';
            }
            $cache_dir = path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(http::getHostFromURL($blog_host));

            if (is_dir($cache_dir) && is_readable($cache_dir)) {
                $k         = str_split($cache_key, 2);
                $cache_dir = sprintf('%s/%s/%s/%s/%s', $cache_dir, $k[0], $k[1], $k[2], $cache_key);

                $dirs = [$cache_dir . '/' . $root];
                do {
                    $dir   = array_shift($dirs);
                    $files = files::scandir($dir);
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                                $cache_fullpath = $dir . '/' . $file;
                                if (is_file($cache_fullpath)) {
                                    $k = str_split($file, 2);
                                    $content .= '<tr>' .
                                    '<td class="nowrap">' . $k[0] . '</td>' . // 1st level
                                    '<td class="nowrap">' . $k[1] . '</td>' . // 2nd level
                                    '<td class="nowrap">' . $k[2] . '</td>' . // 3rd level
                                    '<td class="nowrap maximal">' .
                                    form::checkbox(['sc[]'], $cache_fullpath, false) . ' ' .
                                    '<label class="classic">' .
                                    '<a class="sc_compiled" href="#" data-file="' . $cache_fullpath . '">' . $file . '</a>' .
                                    '</label>' .
                                    '</td>' . // cache file
                                    '</tr>' . "\n";
                                } else {
                                    $dirs[] = $dir . '/' . $file;
                                }
                            }
                        }
                    }
                } while (count($dirs));
                if ($content == '') {
                    // No more dirs and files → send an empty raw
                    $k = explode('/', $root);
                    $content .= '<tr>' .
                    '<td class="nowrap">' . $k[0] . '</td>' .         // 1st level
                    '<td class="nowrap">' . $k[1] . '</td>' .         // 2nd level
                    '<td class="nowrap">' . __('(empty)') . '</td>' . // 3rd level (empty)
                    '<td class="nowrap maximal"></td>' .              // cache file (empty)
                    '</tr>' . "\n";
                }
                $ret = true;
            }
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    public static function getStaticCacheName($core, $get)
    {
        // Return static cache filename from a given URL
        $url     = !empty($get['url']) ? $get['url'] : '';
        $rsp     = new xmlTag('sysinfo');
        $ret     = false;
        $content = '';

        // Extract REQUEST_URI from URL if possible
        $blog_host = $core->blog->host;
        if (substr($url, 0, strlen($blog_host)) == $blog_host) {
            $url = substr($url, strlen($blog_host));
        }

        if ($url != '') {
            $content = md5($url);
            $ret     = true;
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    public static function getStaticCacheFile($core, $get)
    {
        // Return compiled static cache file content
        $file    = !empty($get['file']) ? $get['file'] : '';
        $rsp     = new xmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($file != '') {
            if (file_exists($file) && is_readable($file)) {
                $content = file_get_contents($file);
                $ret     = true;
            }
        }

        $rsp->ret = $ret;
        // Escape file content (in order to avoid further parsing error)
        // JSON encode to preserve UTF-8 encoding
        // Base 64 encoding to preserve line breaks
        $rsp->msg = base64_encode(json_encode(html::escapeHTML($content)));

        return $rsp;
    }
}
