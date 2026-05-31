# GEMINI.md - Chat Message WordPress Plugin

## Project Overview
**Chat Message** is a lightweight, real-time live chat plugin for WordPress. it enables direct communication between site visitors and administrators through a dedicated dashboard and a frontend floating chat widget.

### Main Technologies
- **Backend:** PHP (WordPress Plugin API), MySQL (Custom Tables).
- **Frontend:** HTML5, CSS3 (using Tyn UI Kit), JavaScript (jQuery, AJAX).
- **Authentication:** OTP-based login for visitors via email.
- **Real-time:** AJAX polling for message delivery and status updates.

### Architecture
- `chat-message.php`: Entry point. Handles plugin activation, table creation (`wp_chat_users`, `wp_chat_messages`), script enqueuing, and the `[chat_box]` shortcode.
- `chat-admin.php`: Implements the WordPress admin menu, Chat Settings page, and the main Admin Chat Dashboard.
- `chat-ajax.php`: Central hub for all AJAX requests including OTP generation/verification, sending/receiving messages (user & admin), and file uploads.
- `assets/`: Contains CSS and JS bundles. The naming convention (`bundle0ae1.css`) suggests a pre-compiled UI kit (Tyn).

---

## Building and Running

### Prerequisites
- WordPress installation.
- Mail server configured (for OTP emails).

### Installation
1. Place the `chat-message` directory in `/wp-content/plugins/`.
2. Activate via the WordPress Plugins menu.
3. Use the `[chat_box]` shortcode on any page or post to display the chat widget.

### Development Commands
- **Testing:** No automated test suite is currently implemented. TODO: Add PHPUnit or Cypress tests for chat flow.
- **Assets:** Assets appear to be pre-bundled. If a build system (like Webpack/Vite) is added, document it here.

---

## Development Conventions

### WordPress Standards
- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-php-coding-standards/).
- Use `wp_enqueue_script` and `wp_enqueue_style` for asset loading.
- Always use `check_ajax_referer` with the `wpchat_nonce` for AJAX security.
- Sanitize all inputs using `sanitize_text_field`, `sanitize_email`, etc.

### Database
- Interactions with custom tables (`wp_chat_users`, `wp_chat_messages`) should use the global `$wpdb` object.
- Use `dbDelta` during activation to manage schema changes.

### UI/UX
- The plugin uses the **Tyn UI** framework. Maintain consistency with existing classes (`tyn-media`, `tyn-reply`, etc.) when modifying the chat interface.
- Frontend styles are located in `assets/css/app0ae1.css`.

### AJAX Flow
1. **Frontend:** `assets/js/chat.js` handles visitor interactions.
2. **Backend Admin:** `assets/js/admin-chat.js` handles admin dashboard interactions.
3. **Server:** `chat-ajax.php` processes all requests.
