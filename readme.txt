=== Chat message ===
Contributors: nimeshgorfad
Donate link: https://www.upwork.com/fl/~01332645bee1d0c42d
Tags: chat, live chat, messenger, support, communication
Requires at least: 5.3.0
Tested up to: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Chat on any WordPress website with this lightweight, real-time live chat plugin.

== Description ==

**Chat message** is a lightweight live chat plugin for WordPress that facilitates direct communication between site visitors and administrators. It features a secure OTP-based login system for visitors and a dedicated dashboard for admins.

=== Key Features ===

* **Secure Visitor Access:** Visitors authenticate via Name and Email using a One-Time Password (OTP) sent to their email.
* **Real-Time Communication:** AJAX-based polling ensures messages are delivered and received instantly.
* **Media Support:** Both users and admins can upload and send images within the chat.
* **Admin Dashboard:** A dedicated "Chat" menu in the WordPress admin area to manage all active conversations.
* **User Management:** Admins can search for users and block/unblock them as needed.
* **Customization:** Upload a custom Chat Logo and set a custom Welcome Message.
* **Status Indicators:** Real-time online/offline status updates for users.

== Installation ==

1. Upload the `chat-message` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Place the shortcode `[chat_box]` where you want the chat widget to appear (e.g., in a footer widget or the main theme footer).

== Frequently Asked Questions ==

= Where do I put the shortcode? =
You can place `[chat_box]` in any page, post, or widget area. It is recommended to place it in your theme's footer so it appears on all pages.

= How do visitors log in? =
Visitors enter their name and email, then receive a 6-digit OTP via email to verify their identity and start chatting.

== Screenshots ==

1. The frontend chat widget.
2. The admin chat dashboard.
3. Chat settings page.

== Changelog ==

= 1.1.0 =
* Improved security: Added nonce verification and capability checks to all AJAX actions.
* Prefixed all functions and hooks to avoid conflicts.
* Replaced PHP sessions with secure cookies.
* Added internationalization (i18n) support.
* Fixed SQL injection vulnerabilities.
* Added Chat Settings page for logo and welcome message.

= 1.0.0 =
* Initial release.
