<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Accordion',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.accordion.title',
    'EXT:faq/Resources/Public/Icons/plugin_accordion.svg',
    'plugins',
    '',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Tabs',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.tabs.title',
    'EXT:faq/Resources/Public/Icons/plugin_tabs.svg',
    'plugins',
    '',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Faq',
    'Search',
    'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:plugin.search.title',
    'EXT:faq/Resources/Public/Icons/plugin_search.svg',
    'plugins',
    '',
    'FILE:EXT:faq/Configuration/FlexForms/faq.xml'
);
