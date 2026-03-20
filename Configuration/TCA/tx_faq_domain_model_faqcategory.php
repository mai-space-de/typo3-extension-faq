<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:tx_faq_domain_model_faqcategory',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title',
        'iconfile' => 'EXT:faq/Resources/Public/Icons/tx_faq_domain_model_faqcategory.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title',
        ],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:faq/Resources/Private/Language/locallang_db.xlf:tx_faq_domain_model_faqcategory.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
    ],
];
