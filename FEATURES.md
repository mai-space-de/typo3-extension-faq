# mai_faq — Feature Reference

## FAQ Record

Each FAQ item is stored in `tx_maifaq_faq` and carries three fields:

| Field | Type | Notes |
|---|---|---|
| `question` | `input` (max 255) | Required; used as the record label |
| `answer` | `text` RTE | Required; rendered via RichText configuration `default` |
| `categories` | `category` (manyToMany) | Links to `sys_category` — no custom category table |

Records are sorted by the `sorting` field (TYPO3 manual drag-and-drop ordering).

## sys_category Integration

`mai_faq` follows the project-wide architecture rule: **no custom category table**.

- The `categories` relation uses TYPO3's built-in `CategoryConfig` (TCA field type `category`),
  which maps to the shared `sys_category` table.
- The Extbase domain model uses `TYPO3\CMS\Extbase\Domain\Model\Category` directly.
- The `FaqController` queries `sys_category` rows by UID for tab rendering, reading
  only `uid` and `title` from that table.
- The same `sys_category` tree is shared with `mai_news`, `mai_gallery`, and `mai_timeline`,
  enabling a single category hierarchy across all record types.

## Content Element Plugin

A single Extbase plugin is registered:

| Identifier | Controller action | Plugin type |
|---|---|---|
| `maispace_faq_list` | `FaqController::listAction` | Content element (not USER_INT) |

The content element belongs to the `maispace_feature` group in the backend.

## Frontend Rendering

`listAction` resolves FAQ records via `FaqRepository` using these four priority rules:

1. **Storage pages + category UID** — `findFromPagesByCategoryUid($pageUids, $categoryUid)`
2. **Storage pages only** — `findFromPages($pageUids)`
3. **Category UID only** — `findByCategoryUid($categoryUid)`
4. **Fallback** — `findAll()`

Assigned template variables:

| Variable | Type | Description |
|---|---|---|
| `faqs` | `QueryResultInterface` | Ordered FAQ items |
| `categories` | `array` | Each element: `['uid' => int, 'title' => string]` |
| `activeCategoryUid` | `int` | Selected category (0 = all) |
| `settings` | `array` | FlexForm and TypoScript merged settings |

The action injects `faq.js` via `AssetCollector` to power the client-side accordion and search filter.

## FlexForm Configuration

The `FaqPlugin.xml` FlexForm exposes four editor settings:

| Field | Type | Default | Description |
|---|---|---|---|
| `settings.pages` | `group` (pages) | — | Storage page UIDs (up to 20) |
| `settings.categoryUids` | `category` (manyToMany) | — | Filter categories shown as tabs |
| `settings.showCategoryTabs` | checkbox toggle | `1` | Show/hide the category tab bar |
| `settings.showSearch` | checkbox toggle | `1` | Show/hide the live-search widget |

## TypoScript Configuration

Constants (all optional overrides):

```
plugin.tx_maifaq_list {
    view {
        templateRootPath =
        partialRootPath =
        layoutRootPath =
    }
    persistence {
        storagePid =
    }
}
```

Default view paths (`0` slot) point to `EXT:mai_faq/Resources/Private/`.
Integrators override via slot `10`.

Settings defaults in `setup.typoscript`:

```
plugin.tx_maifaq_list.settings {
    showCategoryTabs = 1
    showSearch = 1
}
```

## Database Tables

### `tx_maifaq_faq`

| Column | SQL type | Notes |
|---|---|---|
| `uid` | `int(11)` | Auto-increment primary key |
| `pid` | `int(11)` | Storage page |
| `question` | `varchar(255)` | Record label |
| `answer` | `mediumtext` | RTE content |
| `categories` | `int(11)` | MM counter (TYPO3 standard) |
| `sorting` | `int(11)` | Manual ordering |
| Standard enableFields | — | `hidden`, `deleted`, `starttime`, `endtime`, `sys_language_uid`, … |

Relations are stored in `sys_category_record_mm` (TYPO3 core table — no custom MM table).

## Architecture Constraints

- **No custom category table.** Category taxonomy is handled exclusively via `sys_category`.
  Never add a `tx_maifaq_category` table or a custom MM join.
- **Read-only categories.** `FaqController` reads category titles directly from `sys_category`
  for tab labels; it never writes to or modifies that table.
- **Single action.** The `list` action is the only controller action and it is cacheable
  (registered as a non-uncached plugin action).
