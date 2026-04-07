# Coding Style Guide

## Purpose

This guide defines the coding conventions for this plugin based on the current codebase and its long-term direction.

It has two goals:

1. Keep new code consistent.
2. Reduce style drift while we gradually refactor legacy code.

## Scope

- PHP source in `plugin/`, `plugin.php`, `oo-updater.php`
- JavaScript in `js/`
- CSS in `css/`
- Tests in `tests/`

## Project Baseline (Audit Snapshot)

These observations come from the current repository state:

- Tabs are the project default (`.editorconfig`).
- Both strict and non-strict PHP typing styles exist.
- Legacy naming (`$_property`, `$pVariable`) and modern naming coexist.
- jQuery-heavy and modern DOM JavaScript coexist.

Because of this, **new code should follow the target conventions below**, while touched legacy code should be improved opportunistically.

## PHP Conventions

### 1) File setup

- Start files with `<?php`.
- Add `declare(strict_types=1);` in new PHP files.
- Keep one class/interface/trait per file.
- Use tabs for indentation.

### 2) Namespaces and imports

- Use `onOffice\WPlugin\...` namespaces matching folder structure.
- Import classes with `use` statements; avoid fully qualified names in method bodies unless clarity requires it.

### 3) Typing

- Add parameter and return types on all new/changed methods when practical.
- Prefer typed properties over docblock-only types for new code.
- Use precise nullable and union types only when needed.

### 4) Naming

- Classes/interfaces: `PascalCase`.
- Methods/variables: `camelCase`.
- Constants: `UPPER_SNAKE_CASE`.
- New code should avoid underscore-prefixed properties (legacy pattern like `$_pDataView`).

### 5) Comparisons and collections

- Prefer strict comparison (`===`, `!==`) over loose comparison (`==`, `!=`).
- Prefer short array syntax (`[]`) over `array()`.
- Prefer early returns to reduce nested conditionals.

### 6) WordPress input/output safety

- Read request input through a dedicated sanitizer/wrapper when possible.
- If superglobals are accessed directly, sanitize immediately and document why.
- Verify nonces for state-changing operations.
- Escape output at render time (`esc_html`, `esc_attr`, `esc_url`, etc.).

### 7) Dependency management

- Prefer constructor injection over creating dependencies deep in business logic.
- Keep WordPress functions behind wrappers when testability benefits from it.

### 8) Error handling

- Fail with explicit exceptions or typed error states.
- Log actionable errors with context (form id, view id, request context).
- Do not swallow exceptions silently.

## JavaScript Conventions

### 1) Language level and declarations

- Prefer `const` and `let`.
- Avoid `var` in new code.
- End statements consistently with semicolons.

### 2) Structure

- Keep one responsibility per file/module.
- Avoid large monolithic `document.ready` blocks.
- Prefer small named functions over deeply nested callbacks.

### 3) DOM and events

- Use `closest()` instead of long `.parent().parent()` chains.
- Keep selectors local to the relevant container.
- Use event delegation only where dynamic content requires it.

### 4) Global scope

- Avoid creating globals except for explicit plugin namespaces.
- If a global is necessary, attach only one namespace object.

## CSS Conventions

- Use tabs for indentation.
- Prefer class selectors over id selectors.
- Keep component styles grouped by feature.
- Avoid deep selector chains when a single utility/component class can solve it.

## Testing and Quality Gates

For non-trivial changes:

- Add or update PHPUnit tests in `tests/`.
- Run `composer install` and `vendor/bin/phpunit`.
- Run `vendor/bin/phpstan analyse` (project currently uses `phpstan.neon`).
- Build frontend assets with `npm run build` if JS/CSS changed.

## Legacy Migration Rules

When editing old files:

1. Do not rewrite everything in one PR.
2. Keep behavior unchanged first.
3. Apply small style upgrades near touched code:
   - add strict comparison,
   - add missing types,
   - simplify conditionals,
   - reduce direct superglobal access.

This keeps reviews safe while steadily converging on one style.
