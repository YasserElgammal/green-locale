# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-07-14

### Added
* Added explicit `yasser-elgammal/green-core` dependency for the Green service provider integration.

### Changed
* Updated `LocaleServiceProvider` to extend Green's `ServiceProvider` and register `LocaleManager` plus the `LocaleRepository` contract in the application container.
* Updated locale manager creation to load configuration from Green's config repository first, with a `config/locale.php` fallback for older bootstraps.
* Replaced the old manual `LocaleServiceProvider::boot()` setup with `LocaleServiceProvider::make()` for scripts, tests, and legacy bootstraps.
* Updated README setup instructions for provider registration, resolver configuration, and manual manager creation.

## [1.0.0] - 2026-05-31

We are thrilled to announce the first official release of Green Locale! This package makes it easy and flexible to add multi-language support to Green Framework applications.

### Core Features
* **Localization Support for Green Framework Models**: Store translated attributes like `name`, `description`, and `slug` in a single JSON column, keeping your database schema clean.
* **`HasLocales` Trait**: Read and write translated fields using model methods like `localeValue()`, `putLocaleValue()`, and `syncLocaleValues()`.
* **Smart Locale Resolution**: Detect the current locale through an adjustable priority chain based on URL query strings, session data, request headers, and default configuration fallback.

### Database & Querying
* **Dedicated `LocaleQueryBuilder`**: Filter, search, and sort records based on values inside JSON columns with `whereLocale()`, `whereLocaleLike()`, and `orderByLocale()`.

### Validation
* **Custom Respect Validation Rules**: Validate localized payloads with `LocaleArrayRule`, `LocaleRequiredRule`, and `LocaleExistsRule`.

### Flexibility & Control
* **Robust Fallback System**: Display a configured fallback language when the translation for the current locale is missing.
* **Strict Mode**: Optionally throw `MissingLocaleValueException` when a translation is missing instead of returning `null` or a fallback.
* **Event Dispatching**: Track and react to translation changes via model events: `LocaleValueStored`, `LocaleValueForgotten`, and `LocaleValuesSynced`.
* **Global Helpers**: Access localization state with `current_locale()`, `fallback_locale()`, and `locale_manager()`.