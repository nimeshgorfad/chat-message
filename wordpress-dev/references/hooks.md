# WordPress Hook Lifecycle

WordPress uses a Hook system (Actions and Filters) to allow modification of core, plugins, and themes.

## 1. Essential Action Hooks
- `plugins_loaded`: Earliest point to check for other plugins.
- `init`: Best for registering Custom Post Types, Taxonomies, and Shortcodes.
- `wp_enqueue_scripts`: Use for loading CSS/JS on the frontend.
- `admin_enqueue_scripts`: Use for loading CSS/JS in the admin dashboard.
- `admin_menu`: Use for adding pages to the admin menu.
- `wp_ajax_{action}` / `wp_ajax_nopriv_{action}`: Handle AJAX requests.

## 2. Essential Filter Hooks
- `the_content`: Modify post/page content.
- `body_class`: Add custom classes to the `<body>` tag.
- `plugin_action_links_{plugin_file}`: Add custom links to the Plugins page.

## 3. Best Practices
- **Prefix Everything:** Always prefix functions and classes to avoid collisions (e.g., `my_plugin_register_cpt`).
- **Callback Style:** Prefer static methods or instances of classes for larger plugins.
- **Hook Priority:** Default is 10. Lower numbers run earlier. Use `10, 2` to specify priority and number of arguments.

```php
add_action( 'wp_enqueue_scripts', 'my_plugin_scripts' );
function my_plugin_scripts() {
    wp_enqueue_script( 'my-script', plugin_dir_url( __FILE__ ) . 'js/app.js', array( 'jquery' ), '1.0.0', true );
}
```
