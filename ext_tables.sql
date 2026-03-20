CREATE TABLE tx_maifaq_domain_model_faqcategory (
    title varchar(255) DEFAULT '' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_maifaq_domain_model_faqentry (
    question varchar(255) DEFAULT '' NOT NULL,
    answer text,
    category int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL
);
