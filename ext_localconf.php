<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Accordion',
    [\MaiSpace\Faq\Controller\FaqController::class => 'accordion'],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Tabs',
    [\MaiSpace\Faq\Controller\FaqController::class => 'tabs'],
    [],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Faq',
    'Search',
    [\MaiSpace\Faq\Controller\FaqController::class => 'search'],
    [\MaiSpace\Faq\Controller\FaqController::class => 'search'],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
