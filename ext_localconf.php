<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'MaiFaq',
    'List',
    [
        \Maispace\MaiFaq\Controller\FaqController::class => 'list',
    ],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['mai_faq']
    = \Maispace\MaiFaq\Hook\FaqCacheInvalidationHook::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['mai_faq']
    = \Maispace\MaiFaq\Hook\FaqCacheInvalidationHook::class;
