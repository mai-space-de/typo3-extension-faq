<?php

declare(strict_types=1);

return [
    "ctrl" => [
        "title" =>
            "LLL:EXT:mai_faq/Resources/Private/Language/locallang_db.xlf:tx_maifaq_domain_model_faqentry",
        "label" => "question",
        "tstamp" => "tstamp",
        "crdate" => "crdate",
        "sortby" => "sorting",
        "delete" => "deleted",
        "enablecolumns" => [
            "disabled" => "hidden",
        ],
        "searchFields" => "question,answer",
        "iconfile" =>
            "EXT:mai_faq/Resources/Public/Icons/tx_maifaq_domain_model_faqentry.svg",
    ],
    "types" => [
        "1" => [
            "showitem" => "hidden,question,answer,category",
        ],
    ],
    "columns" => [
        "hidden" => [
            "exclude" => true,
            "label" =>
                "LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible",
            "config" => [
                "type" => "check",
                "renderType" => "checkboxToggle",
                "items" => [
                    [
                        "label" => "",
                        "invertStateDisplay" => true,
                    ],
                ],
            ],
        ],
        "question" => [
            "exclude" => false,
            "label" =>
                "LLL:EXT:mai_faq/Resources/Private/Language/locallang_db.xlf:tx_maifaq_domain_model_faqentry.question",
            "config" => [
                "type" => "input",
                "size" => 50,
                "eval" => "trim",
                "required" => true,
            ],
        ],
        "answer" => [
            "exclude" => false,
            "label" =>
                "LLL:EXT:mai_faq/Resources/Private/Language/locallang_db.xlf:tx_maifaq_domain_model_faqentry.answer",
            "config" => [
                "type" => "text",
                "enableRichtext" => true,
                "richtextConfiguration" => "default",
                "rows" => 15,
                "cols" => 48,
            ],
        ],
        "category" => [
            "exclude" => false,
            "label" =>
                "LLL:EXT:mai_faq/Resources/Private/Language/locallang_db.xlf:tx_maifaq_domain_model_faqentry.category",
            "config" => [
                "type" => "select",
                "renderType" => "selectSingle",
                "foreign_table" => "tx_maifaq_domain_model_faqcategory",
                "foreign_table_where" =>
                    "ORDER BY tx_maifaq_domain_model_faqcategory.title ASC",
                "items" => [
                    [
                        "label" => "",
                        "value" => 0,
                    ],
                ],
                "minitems" => 0,
                "maxitems" => 1,
            ],
        ],
    ],
];
