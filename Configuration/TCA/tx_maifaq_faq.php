<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\CategoryConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_faq', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maifaq_faq')))
    ->setSearchFields('question,answer')
    ->setDefaultConfig()
    ->setLabel('question')
    ->setIconFile('EXT:mai_faq/Resources/Public/Icons/tx_maifaq_faq.svg')
    ->setSortingField()
    ->addColumn(
        'question',
        $lang('tx_maifaq_faq.question'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'answer',
        $lang('tx_maifaq_faq.answer'),
        (new TextConfig())->setRows(10)->setCols(50)->enableRte()->setRichtextConfiguration('default')->setRequired()
    )
    ->addColumn(
        'categories',
        $lang('tx_maifaq_faq.categories'),
        new CategoryConfig()
    )
    ->addTypeShowItem(
        '0',
        'question, answer, categories,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
