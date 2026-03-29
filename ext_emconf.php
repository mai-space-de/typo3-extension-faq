<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mai Faq',
    'description' => 'FAQ extension with accordion view, category tabs, and a JavaScript search/filter widget. Categories use TYPO3 `sys_category`, sharing the same tree as `mai_news`, `mai_gallery`, and `mai_timeline`.',
    'category' => 'module',
    'author' => 'Maispace',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
