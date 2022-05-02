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

use Dotclear\App;

if (!class_exists('Dotclear\App')) {
    exit(1);
}

return [
    'name'       =>__('SysInfo'),
    'description'=>__('System Information'),
    'version'    =>'2.0-dev',
    'author'     =>'Franck Paul',
    'type'       =>'Plugin',
    'priority'   =>99999999999,
    'details'    =>'https://open-time.net/docs/plugins/sysInfo',
    'support'    =>'https://github.com/franck-paul/sysInfo',
    'repository' =>'https://raw.githubusercontent.com/franck-paul/sysInfo/main/dcstore.xml',
    'requires'   => [
        'core' => '3.0-dev',
    ],
];
