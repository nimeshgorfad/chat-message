# Chat message

Chat on any WordPress website.

## Description

# Operating System Files

.DS_Store
Thumbs.db

# Logs

\*.log

# Dependencies (if you add them later)

node_modules/
vendor/

**Chat message** is a lightweight live chat plugin for WordPress that facilitates direct communication between site visitors and administrators. It features a secure OTP-based login system for visitors and a dedicated dashboard for admins.

### Key Features

- **Secure Visitor Access:** Visitors authenticate via Name and Email using a One-Time Password (OTP) sent to their email.
- **Real-Time Communication:** AJAX-based polling ensures messages are delivered and received instantly.
- **Media Support:** Both users and admins can upload and send images within the chat.
- **Admin Dashboard:** A dedicated "Chat" menu in the WordPress admin area to manage all active conversations.
- **User Management:** Admins can search for users and block/unblock them as needed.
- **Customization:**
  - Upload a custom Chat Logo.
  - Set a custom Welcome Message.
- **Status Indicators:** Real-time online/offline status updates for users.

## Installation

1.  Upload the `chat-message` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Place the shortcode `[chat_box]` where you want the chat widget to appear (e.g., in a footer widget or the main theme footer).

## Configuration

1.  Navigate to **Chat > Chat Settings** in the WordPress admin dashboard.
2.  **Chat Logo:** Upload an image to be displayed in the chat header.
3.  **Welcome Message:** Enter the greeting text displayed to users before they start a chat.

## Usage

### For Visitors

1.  Click the floating chat icon on the frontend.
2.  Enter Name and Email to request an OTP.
3.  Enter the OTP received via email to enter the chat room.

### For Administrators

1.  Navigate to the **Chat** menu in the admin dashboard.
2.  Select a user from the list on the left to view the conversation.
3.  Type messages or upload images to reply.

## Shortcodes

- `[chat_box]` : Displays the floating chat button and interface.
