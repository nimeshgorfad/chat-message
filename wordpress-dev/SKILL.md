---
name: wordpress-dev
description: Specialized workflows for WordPress development. Use when working on plugins, themes, blocks, or custom integrations. Covers Hooks, Security, REST API, and modern block development.
---

# WordPress Development Skill

This skill provides expert guidance for building and maintaining WordPress projects using modern standards.

## Core Workflows

### 1. Project Triage
Before starting, detect the context:
- **Plugin:** Check for a PHP file with `Plugin Name:`.
- **Theme:** Check for `style.css` with `Theme Name:`.
- **Block:** Check for `block.json`.
- **Tooling:** Check for `wp-cli.yml`, `package.json` (wp-scripts), or `blueprint.json`.

### 2. Implementation Guide
Always follow these procedural steps:
1. **Scaffold:** Use `scripts/init_plugin.cjs` for new plugins or `wp scaffold` for CLI-based projects.
2. **Hooks:** Use `references/hooks.md` to identify the correct hook for your task.
3. **Security:** Implement mandatory checks from `references/security.md` (Nonces, Sanitization, Escaping).
4. **Data:** Use `$wpdb->prepare()` for custom SQL.
5. **Assets:** Enqueue scripts/styles correctly via `wp_enqueue_scripts`.

## Detailed References

- **Hooks & Lifecycle:** See [hooks.md](references/hooks.md) for action/filter maps.
- **Security Standards:** See [security.md](references/security.md) for nonces and escaping rules.
- **Block Development:** See [blocks.md](references/blocks.md) for Gutenberg and Interactivity API.
- **Ops & Performance:** See [ops.md](references/ops.md) for WP-CLI and profiling.

## Common Tasks

### Creating a Plugin
```bash
node wordpress-dev/scripts/init_plugin.cjs "My New Plugin"
```

### Adding a Hook
1. Identify if it's an action (event) or filter (data modification).
2. Use a unique prefix for your callback function.
3. Check `references/hooks.md` for the correct hook name.

### AJAX Handler Pattern
1. Localize script with `ajax_url` and `nonce`.
2. Register `wp_ajax_{action}` hook.
3. Use `check_ajax_referer()` and `current_user_can()`.
4. Sanitize input and escape output.

## Code Standards
- **Prefixing:** Always prefix all globals, functions, and classes.
- **Documentation:** Use JSDoc/PHPDoc for all functions.
- **Localization:** Use `__()`, `_e()`, and `_x()` with the correct text domain.
