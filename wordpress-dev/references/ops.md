# WordPress Ops & Performance

Tools for managing environments and optimizing code.

## 1. WP-CLI
The command-line interface for WordPress.
- **Core:** `wp core update`, `wp plugin install`.
- **Scaffolding:** `wp scaffold plugin`, `wp scaffold post-type`.
- **Database:** `wp db export`, `wp search-replace 'old.com' 'new.com'`.

## 2. WordPress Playground & Blueprints
Declarative setup for instant WordPress environments.
- Use `blueprint.json` to define steps like installing plugins, creating posts, and running PHP code.

## 3. Performance Optimization
- **Caching:** Use Transients API for expensive database queries.
- **Object Cache:** Leverage `wp_cache_set` and `wp_cache_get`.
- **Profiling:** Use `Server-Timing` headers and Query Monitor.

## 4. Static Analysis
- **PHPStan:** Use `szepeviktor/phpstan-wordpress` for deep analysis of WordPress code.
- **Linting:** Follow `WordPress-Core` and `WordPress-Extra` PHPCS rulesets.
