<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_faq', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maifaq_faq')))
    ->setDefaultConfig()
    ->setLabel('question')
    ->setSearchFields('question, answer')
    ->setIconFile('EXT:mai_faq/Resources/Public/Icons/tx_maifaq_faq.svg')
    ->setSortingField()
    ->addColumn(
        'question',
        $lang('tx_maifaq_faq.question'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'answer',
        $lang('tx_maifaq_faq.answer'),
        [
            'type' => 'text',
            'rows' => 10,
            'cols' => 50,
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
            'eval' => 'required',
        ]
    )
    ->addColumn(
        'categories',
        $lang('tx_maifaq_faq.categories'),
        ['type' => 'category']
    )
    ->addTypeShowItem(
        '0',
        'question, answer, categories,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
