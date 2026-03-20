<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Accordion',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.accordion.title',
    'EXT:faq/Resources/Public/Icons/plugin_accordion.svg',
    'plugins'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Tabs',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.tabs.title',
    'EXT:faq/Resources/Public/Icons/plugin_tabs.svg',
    'plugins'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Search',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.search.title',
    'EXT:faq/Resources/Public/Icons/plugin_search.svg',
    'plugins'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['faq_accordion'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['faq_tabs'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['faq_search'] = 'layout,select_key,pages,recursive';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['faq_accordion'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['faq_tabs'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['faq_search'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'faq_accordion',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'faq_tabs',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'faq_search',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);
