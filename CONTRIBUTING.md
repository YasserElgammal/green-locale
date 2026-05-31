# Contributing to Green Locale

Thank you for your interest in contributing to **Green Locale**.

This package adds localized JSON attributes for Green Framework models. Changes should stay focused on locale management, localized model attributes, locale-aware queries, validation rules, and package documentation.

## Before Contributing

- Create your branch from `dev`
- Keep changes small and focused
- Follow the existing Green code style
- Prefer explicit APIs over magic behavior
- Update documentation when behavior changes
- Add or update tests for code changes

## Local Setup

Use the package beside the Green skeleton:

```bash
git clone https://github.com/YasserElgammal/green.git
git clone https://github.com/YasserElgammal/green-locale.git
cd green
composer update yasser-elgammal/green-locale --with-dependencies
```

For local path development, the Green skeleton should include:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../green-locale"
        }
    ]
}
```

## Branch Example

```bash
git checkout dev
git pull origin dev
git checkout -b feat/your-change-name
```

## Commit Examples

```bash
feat: add route locale resolver
fix: preserve unicode JSON values
docs: expand model usage guide
test: cover strict missing locale mode
refactor: simplify resolver creation
```

## Testing

From the Green skeleton directory:

```bash
vendor/bin/phpunit -c ../green-locale/phpunit.xml
vendor/bin/phpunit tests
```

Validate Composer metadata:

```bash
composer validate --no-check-publish
cd ../green-locale
composer validate --no-check-publish
```

## Pull Request

Your PR should explain:

- What changed
- Why it changed
- How you tested it
- Any migration or configuration impact

## Checklist

- [ ] Change belongs in `green-locale`
- [ ] Public API remains explicit and predictable
- [ ] Tests pass
- [ ] README updated if needed
- [ ] Composer metadata remains valid

Thanks for helping improve Green Locale.
