<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'FAQ',
    'description' => 'FAQ extension providing accordion view, category tabs and search filter widget.',
    'category' => 'plugin',
    'author' => 'mai.space',
    'author_email' => 'info@mai.space',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.99.99',
            'extbase' => '12.4.0-13.99.99',
            'fluid' => '12.4.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
