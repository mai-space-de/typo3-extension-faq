<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\Helper;

$lang = Helper::localLangHelperFactory('mai_faq', 'Default/locallang_tca.xlf');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiFaq',
    'List',
    $lang('plugin.list.title'),
    'mai-content',
    'maispace_plugins_lists',
    '',
    'FILE:EXT:mai_faq/Configuration/FlexForms/FaqPlugin.xml',
);
