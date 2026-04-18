<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Maispace\MaiBase\TableConfigurationArray\CType;
use Maispace\MaiBase\TableConfigurationArray\Helper;

$lang = Helper::localLangHelperFactory('mai_faq', 'Default/locallang_tca.xlf');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'MaiFaq',
    'List',
    $lang('plugin.list.title'),
    'ext-maispace-mai_faq',
    'maispace_feature',
);

(new CType('maispace_faq_list', $lang('ctype.faq_list'), 'ext-maispace-mai_faq'))
    ->addDefaultHeaderPalette()
    ->addCustomFields('pi_flexform')
    ->addDefaultLanguageTab()
    ->addDefaultAccessTab()
    ->setGroup('maispace_feature')
    ->register();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:mai_faq/Configuration/FlexForms/FaqPlugin.xml',
    'maispace_faq_list',
);
