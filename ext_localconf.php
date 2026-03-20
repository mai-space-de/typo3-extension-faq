<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Accordion',
    [\Maispace\MaiFaq\Controller\FaqController::class => 'accordion'],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Tabs',
    [\Maispace\MaiFaq\Controller\FaqController::class => 'tabs'],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Search',
    [\Maispace\MaiFaq\Controller\FaqController::class => 'search'],
    [\Maispace\MaiFaq\Controller\FaqController::class => 'search'],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
