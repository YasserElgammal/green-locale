# Green Locale

> This package provides Localization for [Green Framework](https://github.com/YasserElgammal/green).

Localized JSON attributes for Green Framework models.

`green-locale` lets a model store translated attributes such as `name`, `description`, or `slug` in a single JSON column, then read the right value for the current locale with explicit model methods.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Bootstrapping](#bootstrapping)
- [Database columns](#database-columns)
- [Model setup](#model-setup)
- [Reading locale values](#reading-locale-values)
- [Writing locale values](#writing-locale-values)
- [Persisting model changes](#persisting-model-changes)
- [Locale resolution](#locale-resolution)
- [Querying localized values](#querying-localized-values)
- [Validation rules](#validation-rules)
- [Events](#events)
- [Helpers](#helpers)
- [Testing](#testing)
- [Full example](#full-example)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## Requirements

- PHP `^8.2`
- Green Framework skeleton with `yasser-elgammal/green-core`
- Doctrine DBAL, already used by Green
- Respect Validation, used for the package validation rules

## Installation

Install it normally:

```bash
composer require yasser-elgammal/green-locale
```

## Configuration

Create `config/locale.php` in your Green skeleton:

```php
<?php

return [
    'default_locale' => $_ENV['APP_LOCALE'] ?? 'en',
    'fallback_locale' => $_ENV['APP_FALLBACK_LOCALE'] ?? 'en',

    'available_locales' => [
        'en',
        'ar',
    ],

    'resolver' => 'request',
    'query_parameter' => 'locale',
    'session_key' => 'locale',

    'strict' => false,
];
```

### Config Options

`default_locale`

The locale used when no resolver finds a locale.

`fallback_locale`

The locale used when a translated attribute does not have a value for the current locale.

`available_locales`

The list of locales your application supports.

`resolver`

The locale resolver driver or resolver chain. Use a string for one resolver, or an array for a priority chain:

```php
'resolver' => ['query', 'session', 'request', 'config'],
```

Resolvers are executed in the exact order they are defined in the array (from left to right). The first resolver that returns an available locale wins.

Supported drivers (and their typical priority):

1. `query`: Highest priority. Reads the locale from the URL (e.g., `?locale=ar`).
2. `session`: Reads `session()->get('locale')` or `$_SESSION['locale']` if the user changed their preference previously.
3. `request`: Reads the `Accept-Language` HTTP header to detect the browser's preferred language.
4. `config`: Lowest priority. Returns the `default_locale` if no other method succeeds.

`query_parameter`

The query-string key used by the `query` resolver. Defaults to `locale`, so `?locale=ar` resolves Arabic.

`session_key`

The session key used by the `session` resolver. Defaults to `locale`.

`strict`

When `false`, missing locale values return the fallback value or `null`.

When `true`, missing locale values throw `MissingLocaleValueException`.

## Bootstrapping

Green Locale is a Green service provider. Register it in your application `config/app.php`:

```php
<?php

return [
    'providers' => [
        \YasserElgammal\GreenLocale\LocaleServiceProvider::class,
    ],
];
```

Green will call the provider during application startup and bind the locale manager into the application container.

For scripts, tests, or older bootstrap files that do not use Green's provider loader, you can create the manager manually:

```php
use YasserElgammal\GreenLocale\LocaleServiceProvider;

LocaleServiceProvider::make();
```

You may also pass an explicit config path:

```php
use YasserElgammal\GreenLocale\LocaleServiceProvider;

LocaleServiceProvider::make(BASE_PATH . '/config/locale.php');
```

## Database Columns

Localized attributes are stored as JSON strings in normal database columns.

Example stored value for `name`:

```json
{
    "en": "Phone",
    "ar": "هاتف"
}
```

## Model Setup

Use `HasLocales` on any model that has localized JSON attributes.

```php
<?php

namespace App\Models;

use YasserElgammal\Green\Database\Model;
use YasserElgammal\GreenLocale\Concerns\HasLocales;

class Product extends Model
{
    use HasLocales;

    protected string $table = 'products';
    protected string $primaryKey = 'id';

    protected array $localeAttributes = [
        'name',
        'description',
        'slug',
    ];
}
```

Only attributes listed in `$localeAttributes` can be used with locale methods. Calling locale methods on undeclared attributes throws `LocaleAttributeNotDeclared`.

## Reading Locale Values

Read the value for the current locale:

```php
$product->localeValue('name');
```

Read a specific locale:

```php
$product->localeValue('name', 'ar');
```

Read all locale values:

```php
$product->localeValues('name');
```

Check if a locale value exists and is not empty:

```php
$product->hasLocaleValue('name', 'en');
```

Fallback behavior:

```php
// Current locale: ar
// Fallback locale: en
// Stored: {"en": "Phone"}

$product->localeValue('name'); // "Phone"
```

## Writing Locale Values

Set one locale value:

```php
$product->putLocaleValue('name', 'ar', 'هاتف');
```

Set multiple values and replace the whole locale map:

```php
$product->syncLocaleValues('name', [
    'en' => 'Phone',
    'ar' => 'هاتف',
]);
```

Remove one locale value:

```php
$product->forgetLocaleValue('name', 'ar');
```

All writing methods return `$this`, so chaining is supported:

```php
$product
    ->putLocaleValue('name', 'en', 'Phone')
    ->putLocaleValue('name', 'ar', 'هاتف');
```

## Persisting Model Changes

Green models are DTO-style objects. Calling `putLocaleValue()` updates the model attribute in memory. You still need to persist the changed attribute through your table gateway.

```php
$table = new ProductTable();
$product = $table->fetchById($id);

$product->putLocaleValue('name', 'ar', 'هاتف');

$table->update($id, [
    'name' => $product->getAttribute('name'),
]);
```

Full controller example:

```php
use YasserElgammal\Green\Http\Request;
use YasserElgammal\Green\Http\JsonResponse;

#[Route('POST', '/api/products/{id}/locale')]
public function updateLocale(Request $request, int $id): JsonResponse
{
    $table = new ProductTable();
    $product = $table->fetchById($id);

    $product->putLocaleValue('name', $request->input('locale'), $request->input('name'));

    $table->update($id, [
        'name' => $product->getAttribute('name'),
    ]);

    return api()->item($product, new ProductTransformer());
}
```

## Locale Resolution

The current locale is managed by `LocaleManager`.

```php
use YasserElgammal\GreenLocale\LocaleManager;

$manager = LocaleManager::getInstance();

$manager->current();
$manager->fallback();
$manager->available();
$manager->isAvailable('ar');
```

Set the current locale manually:

```php
LocaleManager::getInstance()->setLocale('ar');
```

Set the fallback locale manually:

```php
LocaleManager::getInstance()->setFallbackLocale('en');
```

If you already have an app middleware that stores the locale in the session, sync it into `LocaleManager`:

```php
use YasserElgammal\GreenLocale\LocaleManager;

$locale = session()->get('locale');
$manager = LocaleManager::getInstance();

if (is_string($locale) && $manager->isAvailable($locale)) {
    $manager->setLocale($locale);
}
```

## Querying Localized Values

Use `LocaleQueryBuilder` with Green table builders.

```php
use YasserElgammal\GreenLocale\LocaleQueryBuilder;

$table = new ProductTable();
$qb = $table->builder();

LocaleQueryBuilder::whereLocale($qb, 'name', 'en', 'Phone');

$products = $table->fetchAllFromBuilder($qb);
```

LIKE query:

```php
LocaleQueryBuilder::whereLocaleLike($qb, 'name', 'en', '%Pho%');
```

Order by a locale value:

```php
LocaleQueryBuilder::orderByLocale($qb, 'name', 'ar', 'ASC');
```

Generated SQL uses JSON extraction:

```sql
JSON_UNQUOTE(JSON_EXTRACT(`name`, '$."en"')) = :locale_value
```

## Validation Rules

The package includes Respect Validation rules.

### LocaleArrayRule

Validates that the input is an associative array with valid locale keys.

```php
use YasserElgammal\GreenLocale\Validation\LocaleArrayRule;

$rule = new LocaleArrayRule();

$rule->validate([
    'en' => 'Phone',
    'ar' => 'هاتف',
]); // true
```

Valid locale key examples:

- `en`
- `ar`
- `en_US`

### LocaleRequiredRule

Validates that required locales exist and are not empty.

```php
use YasserElgammal\GreenLocale\Validation\LocaleRequiredRule;

$rule = new LocaleRequiredRule(['en', 'ar']);

$rule->validate([
    'en' => 'Phone',
    'ar' => 'هاتف',
]); // true
```

### LocaleExistsRule

Validates that all locale keys are available locales.

```php
use YasserElgammal\GreenLocale\Validation\LocaleExistsRule;

$rule = new LocaleExistsRule(['en', 'ar']);

$rule->validate([
    'en' => 'Phone',
    'fr' => 'Telephone',
]); // false
```

Example inside a payload:

```php
use Respect\Validation\Validator as v;
use YasserElgammal\Green\Payload\Payload;
use YasserElgammal\GreenLocale\Validation\LocaleArrayRule;
use YasserElgammal\GreenLocale\Validation\LocaleExistsRule;
use YasserElgammal\GreenLocale\Validation\LocaleRequiredRule;

class StoreProductPayload extends Payload
{
    public function rules(): array
    {
        return [
            'name' => v::allOf(
                new LocaleArrayRule(),
                new LocaleRequiredRule(['en']),
                new LocaleExistsRule(['en', 'ar'])
            ),
        ];
    }
}
```

## Events

The package does not use a hidden event bus. Locale events are collected on the model and can be dispatched explicitly.

Events:

- `LocaleValueStored`
- `LocaleValueForgotten`
- `LocaleValuesSynced`

Read pending events:

```php
$events = $product->localeEvents();
```

Dispatch and clear pending events:

```php
$events = $product->dispatchLocaleEvents(function (object $event): void {
    // Send to your logger, queue, or application event system.
});
```

If no callback is passed, `dispatchLocaleEvents()` simply returns the events and clears the pending list.

```php
$events = $product->dispatchLocaleEvents();
```

## Helpers

The package autoloads helper functions.

```php
locale_manager();
current_locale();
fallback_locale();
```

Examples:

```php
$locale = current_locale();
$fallback = fallback_locale();

locale_manager()->setLocale('ar');
```

## Transformers

Localized values are usually resolved in transformers:

```php
use YasserElgammal\Green\Database\Model;

class ProductTransformer
{
    public function transform(Model $model): array
    {
        return [
            'id' => (int) $model->id,
            'name' => $model->localeValue('name'),
            'description' => $model->localeValue('description'),
            'all_names' => $model->localeValues('name'),
        ];
    }
}
```

## Twig Usage

If your model is passed to Twig, call the explicit model method:

```twig
{{ product.localeValue('name') }}
```

To expose the current locale:

```php
return view('products/index', [
    'products' => $products,
    'locale' => current_locale(),
]);
```

## Testing

Run package tests from the Green skeleton:

```bash
vendor/bin/phpunit -c ../green-locale/phpunit.xml
```

Run the Green skeleton tests:

```bash
vendor/bin/phpunit tests
```

Validate Composer files:

```bash
composer validate --no-check-publish
cd ../green-locale
composer validate --no-check-publish
```

## Full Example

### 1. Create Table

Generate a new migration:

```bash
php green make:migration CreateProductsTable
```

```php
namespace Database\Migrations;

use YasserElgammal\Green\Database\Migrations\Migration;
use YasserElgammal\Green\Database\Schema\Blueprint;
use YasserElgammal\Green\Database\Schema\Schema;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('slug')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
```

### 2. Create Model

```php
namespace App\Models;

use YasserElgammal\Green\Database\Model;
use YasserElgammal\GreenLocale\Concerns\HasLocales;

class Product extends Model
{
    use HasLocales;

    protected string $table = 'products';
    protected string $primaryKey = 'id';

    protected array $localeAttributes = [
        'name',
        'description',
    ];
}
```

### 3. Create Table Gateway

```php
namespace App\Tables;

use App\Models\Product;
use YasserElgammal\Green\Database\Table;

class ProductTable extends Table
{
    public function __construct()
    {
        parent::__construct(new Product());
    }
}
```

### 4. Insert Product

```php
$table = new ProductTable();

$product = $table->insert([
    'name' => json_encode([
        'en' => 'Phone',
        'ar' => 'هاتف',
    ], JSON_UNESCAPED_UNICODE),
    'description' => json_encode([
        'en' => 'Smart device',
        'ar' => 'جهاز ذكي',
    ], JSON_UNESCAPED_UNICODE),
]);
```

### 5. Read Product

```php
LocaleManager::getInstance()->setLocale('ar');

echo $product->localeValue('name'); // هاتف
```

### 6. Update Product Locale

```php
$product->putLocaleValue('name', 'ar', 'هاتف جديد');

$table->update($product->id, [
    'name' => $product->getAttribute('name'),
]);
```

### 7. Query Product

```php
$qb = $table->builder();

LocaleQueryBuilder::whereLocale($qb, 'name', 'en', 'Phone');

$products = $table->fetchAllFromBuilder($qb);
```

## Troubleshooting

### Locale Always Falls Back

Check that:

- `LocaleServiceProvider` is registered in `config/app.php`, or `LocaleServiceProvider::make()` is called in manual bootstraps
- The requested locale exists in `available_locales`
- The resolver chain includes the source you are using, such as `query` or `session`

### LocaleAttributeNotDeclared

Add the attribute to `$localeAttributes`:

```php
protected array $localeAttributes = [
    'name',
    'description',
];
```

### Missing Values Return Null

If `strict` is `false`, missing values return fallback or `null`.

If you want missing values to throw exceptions:

```php
'strict' => true,
```

### JSON Query Does Not Work

`LocaleQueryBuilder` uses JSON functions:

```sql
JSON_UNQUOTE(JSON_EXTRACT(...))
```

Make sure your database supports these functions. MySQL supports them. SQLite support depends on the JSON extension being available.

## Contributing

Contributions are welcome!
