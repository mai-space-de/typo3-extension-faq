<?php

declare(strict_types=1);

use Maispace\MaiBase\Icon\StreamlineSvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$allIcons = 'EXT:mai_base/Resources/Public/Icons/all-icons/';

return [
    'ext-maispace-mai_faq' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:mai_faq/Resources/Public/Icons/Extension.svg',
    ],
    'mai-faq' => [
        'provider' => StreamlineSvgIconProvider::class,
        'source' => $allIcons . 'interface-help-question-circle--circle-faq-frame-help-info-mark-more-query-question--Streamline-Core.svg',
    ],
];
