# Modern WordPress Block Development

Building for the Block Editor (Gutenberg) involves both PHP and JavaScript (React).

## 1. Block Scaffolding
- Use `wp-scripts` for building.
- **block.json:** The single source of truth for block metadata.

## 2. The Interactivity API
Modern alternative to manual jQuery/Vanilla JS for frontend interactivity.
- Use `data-wp-interactive`, `data-wp-context`, `data-wp-bind`, and `data-wp-on`.
- Register the store in JavaScript using `store()`.

## 3. Block Attributes
Define data structure in `block.json`.
- `type`: String, Number, Boolean, Object, Array.
- `source`: html, text, attribute, query.
- `selector`: CSS selector to extract data from content.

## 4. Server-Side Rendering (Dynamic Blocks)
Use `render_callback` in PHP for blocks that need to be updated dynamically or rely on database queries.
```php
register_block_type( __DIR__ . '/build', array(
    'render_callback' => 'my_plugin_render_block',
) );
```

## 5. Patterns and Templates
- **Block Patterns:** Reusable layouts registered via `register_block_pattern`.
- **Block Templates:** JSON-based layouts for entire page types in Block Themes (`theme.json`).
