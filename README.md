# typo3-extension-faq

A TYPO3 CMS extension that provides a fully-featured FAQ system.

## Features

- **Model: FaqCategory** – Groups FAQ entries into named categories.
- **Model: FaqEntry** – Each entry has a question, an RTE-enabled answer, an optional category and a manual sort order.
- **Plugin: Accordion** – Renders all FAQ entries as an accessible accordion (`Akkordeon-Ansicht`).
- **Plugin: Category Tabs** – Renders FAQ entries grouped into category tabs (`Kategorie-Tabs`).
- **Plugin: Search** – A search-filter widget that queries entries by keyword (`Suchfilter-Widget`).

## Requirements

- TYPO3 CMS 12.4 or 13.x
- PHP 8.1+

## Installation

Install via Composer:

```bash
composer require mai-space-de/faq
```

Or copy the extension into `typo3conf/ext/faq/` and activate it in the Extension Manager.

## Usage

1. Create a **SysFolder** and add FAQ Categories and FAQ Entries there.
2. Add one of the three content elements to a page:
   - **FAQ: Accordion**
   - **FAQ: Category Tabs**
   - **FAQ: Search**
3. Set the *Storage Page* in the plugin FlexForm to the SysFolder created in step 1.

## Running tests

```bash
composer install
./vendor/bin/phpunit
```

## License

GPL-2.0-or-later
