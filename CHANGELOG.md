# Changelog

All notable changes to `laravel-data-json-schemas` will be documented in this file.

## v1.3.0 - 2026-03-19

### Laravel 13 support

Updates composer.json to support Laravel 13.

## v1.2.0 - 2026-03-19

### Laravel 12 support

Updates `composer.json` to support Laravel 12.

## v1.1.0 - 2025-02-09

### Increased schema strictness

This release adds `"additionalProperties": false` to each Data class schema, invalidating JSON that includes properties which aren't defined in the Data class.

### Support for multiple `not` keywords in a union schema

Before this release, only the most recently-added `not` keyword would be kept in a union schema. Now, `not` subschemas are consolidated into a single `not` keyword.

## v1.0.0 - 2025-02-07

Initial release
