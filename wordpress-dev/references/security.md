# WordPress Security Standards

Always prioritize security when developing for WordPress. Follow these mandatory practices.

## 1. Nonce Verification
Use nonces to prevent CSRF. 
- **Generate:** `wp_create_nonce('action_name')`
- **Verify (AJAX):** `check_ajax_referer('action_name', 'nonce_key')`
- **Verify (POST):** `check_admin_referer('action_name', 'nonce_key')`

## 2. Input Sanitization
Sanitize all user-provided data before storing it.
- **Text:** `sanitize_text_field($data)`
- **Email:** `sanitize_email($data)`
- **TextArea:** `sanitize_textarea_field($data)`
- **Key:** `sanitize_key($data)`

## 3. Data Validation
Validate data for expected types and formats.
- **Numbers:** `is_numeric()`, `intval()`, `absint()`
- **URLs:** `esc_url_raw()`
- **Dates:** `wp_date()` or regex validation.

## 4. Output Escaping
Escape all data immediately before outputting to the browser.
- **HTML:** `esc_html($data)`
- **Attributes:** `esc_attr($data)`
- **URLs:** `esc_url($data)`
- **JS:** `esc_js($data)`
- **Translate & Escape:** `esc_html__('Text', 'domain')`, `esc_attr_e('Text', 'domain')`

## 5. Database Safety
Use `$wpdb` with `prepare()` for all custom queries to prevent SQL injection.
```php
$wpdb->get_results( $wpdb->prepare( 
    "SELECT * FROM {$wpdb->prefix}table WHERE id = %d", 
    $id 
) );
```

## 6. Permissions
Check capabilities before executing privileged actions.
- `if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Access Denied' ); }`
- Use specific capabilities instead of roles (e.g., `edit_posts` instead of `editor`).
