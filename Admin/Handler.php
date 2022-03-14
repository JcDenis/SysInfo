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

use Dotclear\Helper\Html\Form;
use Dotclear\Module\AbstractPage;
use Dotclear\Plugin\SysInfo\Admin\SysInfo;

if (!defined('DOTCLEAR_PROCESS') || DOTCLEAR_PROCESS != 'Admin') {
    return;
}

class Handler extends AbstractPage
{
    private $si_checklists = [];
    private $si_checklist  = '';

    protected function getPermissions(): string|null|false
    {
        return null;
    }

    protected function getPagePrepend(): ?bool
    {
        $this->si_checklists = [
            __('System') => [
                __('Versions')     => 'default',
                __('PHP info')     => 'phpinfo',
                __('DC Config')    => 'config',
                __('DC Constants') => 'constants',
                __('Folders')      => 'folders',
            ],

            __('Core') => [
                __('URL handlers')        => 'urlhandlers',
                __('Behaviours')          => 'behaviours',
                __('Admin URLs')          => 'adminurls',
                __('Types of permission') => 'permissions',
            ],

            __('Templates') => [
                __('Compiled templates') => 'templates',
                __('Template paths')     => 'tplpaths',
            ],

            __('Repositories') => [
                __('Plugins repository (cache)') => 'dcrepo-plugins-cache',
                __('Plugins repository')         => 'dcrepo-plugins',
                __('Themes repository (cache)')  => 'dcrepo-themes-cache',
                __('Themes repository')          => 'dcrepo-themes',
            ],

            __('Miscellaneous') => [
                __('Plugins')              => 'plugins',
                __('Editors and Syntaxes') => 'formaters',
                __('REST methods')         => 'rest',
            ],
        ];

        if (dotclear()->plugins && dotclear()->plugins->hasModule('staticCache')) {
            if (defined('DC_SC_CACHE_ENABLE') && DC_SC_CACHE_ENABLE) {
                if (defined('DC_SC_CACHE_DIR')) {
                    if (dcStaticCacheControl::cacheCurrentBlog()) {
                        $this->si_checklists[__('3rd party')] = [
                            __('Static cache') => 'sc',
                        ];
                    }
                }
            }
        }

        $this->si_checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';


        # Page setup
        $this
            ->setPageTitle(__('System Information'))
            ->setPageBreadcrumb([
                __('System')             => '',
                __('System Information') => '',
            ])
            ->setPageHead(
                dotclear()->resource()->load('sysinfo.css', 'Plugin', 'SysInfo', 'screen', dotclear()->version()->get('sysInfo')) .
                dotclear()->resource()->json('sysinfo', [
                    'colorsyntax'       => dotclear()->user()->preference()->interface->user_ui_colorsyntax,
                    'colorsyntax_theme' => dotclear()->user()->preference()->interface->user_ui_colorsyntax_theme,
                    'msg'               => [
                        'confirm_del_tpl' => __('Are you sure you want to remove selected template cache files?'),
                        'confirm_del_sc'  => __('Are you sure you want to remove selected static cache files?'),
                        'tpl_not_found'   => __('Compiled template file not found or unreadable'),
                        'sc_not_found'    => __('Static cache file not found or unreadable'),
                    ],
                ]) .
                dotclear()->resource()->modal() .
                dotclear()->resource()->load('sysinfo.js', 'Plugin', 'SysInfo', null, dotclear()->version()->get('sysInfo'))
            )
        ;

        if (dotclear()->user()->preference()->interface->user_ui_colorsyntax) {
            $this->setPageHead(
                dotclear()->resource()->loadCodeMirror(dotclear()->user()->preference()->interface->user_ui_colorsyntax_theme)
            );
        }

        # Cope with form submit
        SysInfo::doFormTemplates($this->si_checklist);
        SysInfo::doFormStaticCache($this->si_checklist);
        SysInfo::doCheckTemplates($this->si_checklist);
        SysInfo::doCheckStaticCache($this->si_checklist);

        return true;
    }

    protected function getPageContent(): void
    {
        echo
        '<form action="' . dotclear()->adminurl()->get('admin.plugin.SysInfo') . '" method="post">' .
        '<p class="field"><label for="checklist">' . __('Select a checklist:') . '</label> ' .
        form::combo('checklist', $this->si_checklists, $this->si_checklist) . ' ' .
        dotclear()->nonce()->form() . '<input type="submit" value="' . __('Check') . '" /></p>' .
        '</form>';

        # Display required information
        switch ($this->si_checklist) {

            case 'permissions':
                # Affichage de la liste des types de permission enregistrés
                echo SysInfo::permissions();

                break;

            case 'rest':
                # Affichage de la liste des méthodes REST
                echo SysInfo::restMethods();

                break;

            case 'plugins':
                # Affichage de la liste des plugins (et de leurs propriétés)
                echo SysInfo::plugins();

                break;

            case 'formaters':
                # Affichage de la liste des éditeurs et des syntaxes par éditeur
                echo SysInfo::formaters();

                break;

            case 'constants':
                # Affichage des constantes remarquables de Dotclear
                echo SysInfo::dcConstants();

                break;

            case 'config':
                # Affichage de la configuration de base de Dotclear
                echo SysInfo::dcConfig();

                break;

            case 'folders':
                # Affichage des dossiers remarquables de Dotclear
                echo SysInfo::folders();

                break;

            case 'behaviours':
                # Récupération des behaviours enregistrées
                echo SysInfo::behaviours();

                break;

            case 'urlhandlers':
                # Récupération des types d'URL enregistrées
                echo SysInfo::URLHandlers();

                break;

            case 'adminurls':
                # Récupération de la liste des URLs d'admin enregistrées
                echo SysInfo::adminURLs();

                break;

            case 'phpinfo':
                # Get PHP Infos
                echo SysInfo::phpInfo();

                break;

            case 'templates':
                # Get list of compiled template's files
                echo SysInfo::templates();

                break;

            case 'tplpaths':
                # Get list of template's paths
                echo SysInfo::tplPaths();

                break;

            case 'sc':
                # Get list of existing cache files
                echo SysInfo::staticCache();

                break;

            case 'dcrepo-plugins':
            case 'dcrepo-plugins-cache':
                # Get list of available plugins
                echo SysInfo::repoPlugins($this->si_checklist === 'dcrepo-plugins-cache');

                break;

            case 'dcrepo-themes':
            case 'dcrepo-themes-cache':
                # Get list of available themes
                echo SysInfo::repoThemes($this->si_checklist === 'dcrepo-themes-cache');

                break;

            default:
                # Display PHP version and DB version
                echo SysInfo::quoteVersions();

                break;
        }
    }
}
