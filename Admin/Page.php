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

$checklists = [
    __('System') => [
        __('Versions')     => 'default',
        __('PHP info')     => 'phpinfo',
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

if ($core->plugins->moduleExists('staticCache')) {
    if (defined('DC_SC_CACHE_ENABLE') && DC_SC_CACHE_ENABLE) {
        if (defined('DC_SC_CACHE_DIR')) {
            if (dcStaticCacheControl::cacheCurrentBlog()) {
                $checklists[__('3rd party')] = [
                    __('Static cache') => 'sc',
                ];
            }
        }
    }
}

$checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

# Cope with form submit
libSysInfo::doFormTemplates($p_url, $checklist);
libSysInfo::doFormStaticCache($p_url, $checklist);

# Get interface setting
$core->auth->user_prefs->addWorkspace('interface');
$user_ui_colorsyntax       = $core->auth->user_prefs->interface->colorsyntax;
$user_ui_colorsyntax_theme = $core->auth->user_prefs->interface->colorsyntax_theme;

?>
<html>
<head>
  <title><?php echo __('System Information'); ?></title>
<?php
echo
dcPage::cssModuleLoad('sysInfo/sysinfo.css', 'screen', $core->getVersion('sysInfo')) .
dcPage::jsJson('sysinfo', [
    'colorsyntax'       => $user_ui_colorsyntax,
    'colorsyntax_theme' => $user_ui_colorsyntax_theme,
    'msg'               => [
        'confirm_del_tpl' => __('Are you sure you want to remove selected template cache files?'),
        'confirm_del_sc'  => __('Are you sure you want to remove selected static cache files?'),
        'tpl_not_found'   => __('Compiled template file not found or unreadable'),
        'sc_not_found'    => __('Static cache file not found or unreadable'),
    ],
]) .
dcPage::jsModal() .
dcPage::jsModuleLoad('sysInfo/sysinfo.js', $core->getVersion('sysInfo'));
if ($user_ui_colorsyntax) {
    echo
    dcPage::jsLoadCodeMirror($user_ui_colorsyntax_theme);
}
?>
</head>

<body>
<?php
echo
dcPage::breadcrumb(
    [
        __('System')             => '',
        __('System Information') => '',
    ]
);
echo dcPage::notices();

libSysInfo::doCheckTemplates($checklist);
libSysInfo::doCheckStaticCache($checklist);

echo
    '<form action="' . $p_url . '" method="post">' .
    '<p class="field"><label for="checklist">' . __('Select a checklist:') . '</label> ' .
    form::combo('checklist', $checklists, $checklist) . ' ' .
    $core->formNonce() . '<input type="submit" value="' . __('Check') . '" /></p>' .
    '</form>';

// Display required information
switch ($checklist) {

    case 'permissions':
        // Affichage de la liste des types de permission enregistrés
        echo libSysInfo::permissions();

        break;

    case 'rest':
        // Affichage de la liste des méthodes REST
        echo libSysInfo::restMethods();

        break;

    case 'plugins':
        // Affichage de la liste des plugins (et de leurs propriétés)
        echo libSysInfo::plugins();

        break;

    case 'formaters':
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        echo libSysInfo::formaters();

        break;

    case 'constants':
        // Affichage des constantes remarquables de Dotclear
        echo libSysInfo::dcConstants();

        break;

    case 'folders':
        // Affichage des dossiers remarquables de Dotclear
        echo libSysInfo::folders();

        break;

    case 'behaviours':
        // Récupération des behaviours enregistrées
        echo libSysInfo::behaviours();

        break;

    case 'urlhandlers':
        // Récupération des types d'URL enregistrées
        echo libSysInfo::URLHandlers();

        break;

    case 'adminurls':
        // Récupération de la liste des URLs d'admin enregistrées
        echo libSysInfo::adminURLs();

        break;

    case 'phpinfo':
        // Get PHP Infos
        echo libSysInfo::phpInfo();

        break;

    case 'templates':
        // Get list of compiled template's files
        echo libSysInfo::templates($p_url);

        break;

    case 'tplpaths':
        // Get list of template's paths
        echo libSysInfo::tplPaths();

        break;

    case 'sc':
        // Get list of existing cache files
        echo libSysInfo::staticCache($p_url);

        break;

    case 'dcrepo-plugins':
    case 'dcrepo-plugins-cache':
        // Get list of available plugins
        echo libSysInfo::repoPlugins($checklist === 'dcrepo-plugins-cache');

        break;

    case 'dcrepo-themes':
    case 'dcrepo-themes-cache':
        // Get list of available themes
        echo libSysInfo::repoThemes($checklist === 'dcrepo-themes-cache');

        break;

    default:
        // Display PHP version and DB version
        echo libSysInfo::quoteVersions();

        break;
}

?>
</body>
</html>
