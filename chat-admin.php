<?php 

// In your plugin file.
add_action('admin_menu', 'register_chat_admin_page');

function register_chat_admin_page() {
    $menu = add_menu_page('Chat', 'Chat', 'manage_options', 'chat', 'chat_admin_page_callback', 'dashicons-admin-comments');
	
	 add_menu_page(
        'Chat Settings',       
        'Chat Settings',       
        'manage_options',      
        'chat-settings',       
        'chat_settings_page',  
        'dashicons-format-chat',  
    );
	
	add_action('load-'.$menu,'load_admin_js_css');
	
}

// Display the admin page
function chat_settings_page() {
    // Handle form submission
    if (isset($_POST['chat_settings_submit'])) {
        // Verify nonce
        if (!isset($_POST['chat_settings_nonce']) || !wp_verify_nonce($_POST['chat_settings_nonce'], 'chat_settings_action')) {
            echo '<div class="error"><p>Security check failed. Please try again.</p></div>';
            return;
        }

        // Save the uploaded image
        if (!empty($_FILES['chat_logo']['name'])) {
            $uploaded = media_handle_upload('chat_logo', 0);
            if (!is_wp_error($uploaded)) {
                update_option('chat_logo', $uploaded);
            } else {
                echo '<div class="error"><p>Failed to upload image.</p></div>';
            }
        }

        // Save the welcome message
        if (isset($_POST['welcome_message'])) {
            update_option('welcome_message', sanitize_textarea_field($_POST['welcome_message']));
        }

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Get saved values
    $chat_logo_id = get_option('chat_logo');
    $chat_logo_url = $chat_logo_id ? wp_get_attachment_url($chat_logo_id) : '';
    $welcome_message = get_option('welcome_message', '');

    ?>
    <div class="wrap">
        <h1>Chat Settings</h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('chat_settings_action', 'chat_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="chat_logo">Chat Logo</label></th>
                    <td>
                        <input type="file" name="chat_logo" id="chat_logo" />
                        <?php if ($chat_logo_url): ?>
                            <p><img src="<?php echo esc_url($chat_logo_url); ?>" alt="Chat Logo" style="max-width: 150px;" /></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome_message">Welcome Message</label></th>
                    <td>
                        <textarea name="welcome_message" id="welcome_message" rows="5" cols="50" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Settings', 'primary', 'chat_settings_submit'); ?>
        </form>
    </div>
    <?php
}


function chat_admin_page_callback() {
	
	global $wpdb;
	
	 $chat_users = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_users ORDER BY id DESC")); 	
    ?>
    <div class="wrap">
        <div class="tyn-root">
      <nav class="tyn-appbar">
        <div class="tyn-appbar-wrap">
          <div class="tyn-appbar-logo">
            <a class="tyn-logo" >
              <svg viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M37.2654 14.793C37.2654 14.793 45.0771 20.3653 41.9525 29.5311C41.9525 29.5311 41.3796 31.1976 39.0361 34.4264L42.4732 37.9677C42.4732 37.9677 43.3065 39.478 41.5879 39.9987H24.9229C24.9229 39.9987 19.611 40.155 14.8198 36.9782C14.8198 36.9782 12.1638 35.2076 9.76825 31.9787L18.6215 32.0308C18.6215 32.0308 24.298 31.9787 29.7662 28.3333C35.2344 24.6878 37.4217 18.6988 37.2654 14.793Z"
                  fill="#60A5FA"
                />
                <path
                  d="M34.5053 12.814C32.2659 1.04441 19.3506 0.0549276 19.3506 0.0549276C8.31004 -0.674164 3.31055 6.09597 3.31055 6.09597C-4.24076 15.2617 3.6751 23.6983 3.6751 23.6983C3.6751 23.6983 2.99808 24.6357 0.862884 26.5105C-1.27231 28.3854 1.22743 29.3748 1.22743 29.3748H17.3404C23.4543 28.7499 25.9124 27.3959 25.9124 27.3959C36.328 22.0318 34.5053 12.814 34.5053 12.814ZM19.9963 18.7301H9.16412C8.41419 18.7301 7.81009 18.126 7.81009 17.3761C7.81009 16.6261 8.41419 16.022 9.16412 16.022H19.9963C20.7463 16.022 21.3504 16.6261 21.3504 17.3761C21.3504 18.126 20.7358 18.7301 19.9963 18.7301ZM25.3708 13.314H9.12245C8.37253 13.314 7.76843 12.7099 7.76843 11.96C7.76843 11.21 8.37253 10.6059 9.12245 10.6059H25.3708C26.1207 10.6059 26.7248 11.21 26.7248 11.96C26.7248 12.7099 26.1103 13.314 25.3708 13.314Z"
                  fill="#2563EB"
                />
              </svg>
            </a>
          </div>
          <!-- .tyn-appbar-logo -->
          <div class="tyn-appbar-content">
            <ul class="tyn-appbar-nav tyn-appbar-nav-start">
              <img src="https://gowibble.com/wp-content/uploads/2024/10/logo-white.svg" alt="" />
            </ul>
            <!-- .tyn-appbar-nav -->
            <ul class="tyn-appbar-nav tyn-appbar-nav-end">
            
              <!-- .tyn-appbar-item -->
              <?php /* ?>
              <li class="tyn-appbar-item">
                <a class="tyn-appbar-link dropdown-toggle" data-bs-toggle="dropdown" href="#" data-bs-offset="0,10" data-bs-auto-close="outside">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-app-indicator" viewBox="0 0 16 16">
                    <path
                      d="M5.5 2A3.5 3.5 0 0 0 2 5.5v5A3.5 3.5 0 0 0 5.5 14h5a3.5 3.5 0 0 0 3.5-3.5V8a.5.5 0 0 1 1 0v2.5a4.5 4.5 0 0 1-4.5 4.5h-5A4.5 4.5 0 0 1 1 10.5v-5A4.5 4.5 0 0 1 5.5 1H8a.5.5 0 0 1 0 1z"
                    />
                    <path d="M16 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0" /></svg
                  ><!-- app-indicator -->
                  <span class="d-none">Notifications</span> </a
                ><!-- .dropdown-toggle -->
                <div class="dropdown-menu dropdown-menu-rg dropdown-menu-end">
                  <div class="dropdown-head">
                    <div class="title">
                      <h6>Notifications</h6>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-line">
                      <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications-unread" type="button">Unread</button>
                      </li>
                      <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#notifications-all" type="button">All</button>
                      </li>
                    </ul>
                  </div>
                  <!-- .dropdown-head -->
                  <div class="dropdown-gap">
                    <ul class="tyn-media-list gap gap-3">
                      <li>
                        <div class="tyn-media-group">
                          <div class="tyn-media tyn-circle">
                            <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
                          </div>
                          <div class="tyn-media-col">
                            <div class="tyn-media-row">
                              <span class="message"><strong>Phillip Burke</strong> Sent message</span>
                            </div>
                            <div class="tyn-media-row">
                              <span class="meta">10 Hours ago</span>
                            </div>
                          </div>
                        </div>
                        <!-- .tyn-media-group -->
                      </li>
                      <!-- li -->
                      <li>
                        <div class="tyn-media-group align-items-start">
                          <div class="tyn-media tyn-circle">
                            <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/2.jpg" alt="" />
                          </div>
                          <div class="tyn-media-col">
                            <div class="tyn-media-row">
                              <span class="message">Missed call from <strong>Romy Schulte</strong></span>
                            </div>
                            <div class="tyn-media-row has-dot-sap">
                              <span class="meta">2 days ago</span>
                            </div>
                            <div class="tyn-media-row">
                              <ul class="tyn-btn-inline gap gap-2 pt-1">
                                <li>
                                  <button class="btn btn-md btn-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Call Back</span>
                                  </button>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>
                        <!-- .tyn-media-group -->
                      </li>
                      <!-- li -->
                      <li>
                        <div class="tyn-media-group align-items-start">
                          <div class="tyn-media tyn-circle">
                            <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/3.jpg" alt="" />
                          </div>
                          <div class="tyn-media-col">
                            <div class="tyn-media-row">
                              <span class="message"><strong>Thomas Poulain</strong> Added You</span>
                            </div>
                            <div class="tyn-media-row has-dot-sap">
                              <span class="meta">1 weeks ago</span>
                            </div>
                            <div class="tyn-media-row">
                              <ul class="tyn-btn-inline gap gap-3 pt-1">
                                <li>
                                  <button class="btn btn-md btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16">
                                      <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0" />
                                      <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z" /></svg
                                    ><!-- check2-circle -->
                                    <span>Accept</span>
                                  </button>
                                </li>
                                <li>
                                  <button class="btn btn-md btn-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                      <path
                                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"
                                      /></svg
                                    ><!-- x-circle -->
                                    <span>Reject</span>
                                  </button>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>
                        <!-- .tyn-media-group -->
                      </li>
                      <!-- li -->
                      <li>
                        <div class="tyn-media-group">
                          <div class="tyn-media tyn-circle">
                            <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/4.jpg" alt="" />
                          </div>
                          <div class="tyn-media-col">
                            <div class="tyn-media-row">
                              <span class="message"><strong>Gabriel Schmitz</strong> Sent message</span>
                            </div>
                            <div class="tyn-media-row has-dot-sap">
                              <span class="meta">1 Months ago</span>
                            </div>
                          </div>
                        </div>
                        <!-- .tyn-media-group -->
                      </li>
                      <!-- li -->
                    </ul>
                    <!-- .tyn-media-list -->
                  </div>
                  <!-- .dropdown-gap -->
                </div>
                <!-- .dropdown-menu -->
              </li>
              <?php */ ?>
              <!-- .tyn-appbar-item -->
              <li class="tyn-appbar-item">
                <a class="d-inline-flex dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown" href="#" data-bs-offset="0,10">
                  <div class="tyn-media tyn-size-lg tyn-circle">
                    <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/3.jpg" alt="" />
                  </div> </a
                ><!-- .dropdown-toggle -->
                <div class="dropdown-menu dropdown-menu-end">
                  <div class="dropdown-gap">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/3.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Marie George</h6>
                          <div class="indicator varified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                              <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                              /></svg
                            ><!-- check-circle-fill -->
                          </div>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content"> </p> 
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                    </div>
                    <!-- .tyn-media-group -->
                  </div>
                  <!-- .dropdown-gap -->
                  <div class="dropdown-gap">
                    <div class="d-flex gap gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16">
                        <path
                          d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"
                        /></svg
                      ><!-- moon-fill -->
                      <div>
                        <h6>Darkmode</h6>
                        <ul class="d-flex align-items-center gap gap-3">
                          <li class="inline-flex">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="themeMode" id="dark" value="dark" />
                              <label class="form-check-label small" for="dark"> On </label>
                            </div>
                          </li>
                          <!-- li -->
                          <li class="inline-flex">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="themeMode" id="light" value="light" checked />
                              <label class="form-check-label small" for="light"> Off </label>
                            </div>
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- ul -->
                      </div>
                      <!-- div -->
                    </div>
                    <!-- .d-flex -->
                  </div>
                  <!-- .dropdown-gap -->
                  
                  <!-- .tyn-list-links -->
                </div>
                <!-- .dropdown-menu -->
              </li>
              <!-- .tyn-appbar-item -->
            </ul>
            <!-- .tyn-appbar-nav -->
          </div>
          <!-- .tyn-appbar-content -->
        </div>
        <!-- .tyn-appbar-wrap -->
      </nav>
      <!-- .tyn-appbar -->
      <div class="tyn-content tyn-content-full-height tyn-chat has-aside-base">
        <div class="tyn-aside tyn-aside-base">
          <div class="tyn-aside-head">
            <div class="tyn-aside-head-text">
              <h3 class="tyn-aside-title">Chats</h3>
            </div>
            <!-- .tyn-aside-head-text -->
            <?php /* ?>
            <div class="tyn-aside-head-tools">
              <ul class="link-group gap gx-3">
                
                <!-- li -->
                <li class="dropdown">
                  <button class="link dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter" viewBox="0 0 16 16">
                      <path
                        d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"
                      /></svg
                    ><!-- filter -->
                    <span>Filter</span>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <ul class="tyn-list-links nav nav-tabs border-0">
                      <li>
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-chats">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-square-text" viewBox="0 0 16 16">
                            <path
                              d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"
                            />
                            <path
                              d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6m0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"
                            /></svg
                          ><!-- chat-square-text -->
                          <span>All Chats</span>
                        </button>
                      </li>
                      <!-- li -->
                      <li>
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#active-contacts">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                            <path
                              d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"
                            />
                            <path
                              d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"
                            /></svg
                          ><!-- person-check -->
                          <span>Active Contacts</span>
                        </button>
                      </li>
                      <!-- li -->
                      <li>
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#archived-chats">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                            <path
                              d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"
                            /></svg
                          ><!-- archive -->
                          <span>Archived Chats</span>
                        </button>
                      </li>
                      <!-- li -->
                      <li>
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#spam-messages">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark-x" viewBox="0 0 16 16">
                            <path
                              fill-rule="evenodd"
                              d="M6.146 5.146a.5.5 0 0 1 .708 0L8 6.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 7l1.147 1.146a.5.5 0 0 1-.708.708L8 7.707 6.854 8.854a.5.5 0 1 1-.708-.708L7.293 7 6.146 5.854a.5.5 0 0 1 0-.708"
                            />
                            <path
                              d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"
                            /></svg
                          ><!-- bookmark-x -->
                          <span>Spam Messages</span>
                        </button>
                      </li>
                      <!-- li -->
                      <li>
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#trash-bin">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                            <path
                              d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                            /></svg
                          ><!-- trash -->
                          <span>Trash Bin</span>
                        </button>
                      </li>
                      <!-- li -->
                    </ul>
                    <!-- .nav -->
                  </div>
                  <!-- .dropdown-menu -->
                </li>
                <!-- li -->
              </ul>
              <!-- .link-group -->
            </div>
            
            <?php 
            */ ?> 
            <!-- .tyn-aside-head-tools -->
          </div>
          <!-- .tyn-aside-head -->
          <div class="tyn-aside-body" data-simplebar>
            <div class="tyn-aside-search">
              <div class="form-group tyn-pill">
                <div class="form-control-wrap">
                  <div class="form-control-icon start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                      <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"
                      /></svg
                    ><!-- search -->
                  </div>
                  <input type="text" class="form-control form-control-solid" id="search_chat_user" placeholder="Search contact / chat" />
                </div>
              </div>
            </div>
            <!-- .tyn-aside-search -->
            <div class="tab-content">
              <div class="tab-pane show active" id="all-chats" tabindex="0" role="tabpanel">
                <ul class="tyn-aside-list" id="chat_list_ul" >
				
					<?php 
					   
					  if( !empty( $chat_users )){
						  foreach( $chat_users as $chat_user){
                              $nkg_last_chat_id = 0;
                             /*  $messages = $wpdb->get_results($wpdb->prepare(
                                        "SELECT id FROM {$wpdb->prefix}chat_messages WHERE  user_id = %d   ORDER BY id DESC limit 1", 
                                         $chat_user->id, 
                                    )); 
                                    
                                if( !empty( $messages ) ){
                                    $nkg_last_chat_id = $messages[0]->id;			
                                } */
                                
                                 $unread_counts = $wpdb->get_results(
                                    "SELECT COUNT(*) as unread_count
                                     FROM {$wpdb->prefix}chat_messages
                                     WHERE read_status = 0 and sender = 'user' and user_id = '$chat_user->id' ",
                                    ARRAY_A
                                );
                                
                                 $unread_count = !empty( $unread_counts[0]['unread_count'] ) ? "(".$unread_counts[0]['unread_count'].")" :'';
                                // print_r($unread_counts);
                                 
							  ?>
							  <li class="tyn-aside-item js-toggle-main chat_list" id="chat_id_<?php echo $chat_user->id;?>" data-id="<?php echo $chat_user->id;?>" data-last="<?php echo $nkg_last_chat_id;?>" data-block="<?php echo $chat_user->block; ?>"  >
								<div class="tyn-media-group">
									<div class="tyn-media tyn-size-lg">
										<img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" /> 
									 </div>
									 
									 <div class="tyn-media-col">
										<div class="tyn-media-row">
										  <h6 class="name"> <?php echo $chat_user->name; ?> </h6>
										  <span class="typing"> </span>
										</div>
										<div class="tyn-media-row has-dot-sap nkg_active_div" id="active_<?php echo $chat_user->id; ?>" >
										  <p class="content"><?php echo $chat_user->email; ?></p>
										  <span class="meta"></span>
										  <span class="unread_count" id="unread_count_<?php echo $chat_user->id; ?>" > <?php echo $unread_count; ?>  </span>
										</div> 
									  </div>
								
								</div>							  
							  </li>
							  
							  <?php 
										  
						  } 
					  }
				
					  
					  ?>
					  
					
                <?php /* ?> 
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/2.jpg" alt="" />
                      </div>
                      <!-- .tyn-media -->
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Konstantin Frank</h6>
                          <div class="indicator varified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                              <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                              /></svg
                            ><!-- check-circle-fill -->
                          </div>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Liked that disco music</p>
                          <span class="meta">1 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  
                  <?php */ ?> 
                  <!-- .tyn-aside-item -->
                </ul>
                <!-- .tyn-aside-list -->
              </div>
              <!-- .tab-pane -->
              <div class="tab-pane" id="active-contacts" tabindex="0" role="tabpanel">
                <ul class="tyn-aside-list">
                  <li class="tyn-aside-item js-toggle-main active">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Jasmine Thompson</h6>
                          <span class="typing">typing ...</span>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Had they visited Rome before</p>
                          <span class="meta">45 min</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/3.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Mathias Devos</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Hey, how&#39;s it going?</p>
                          <span class="meta">2 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/4.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Marie George</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Same here. I&#39;ve been trying to keep myself occupied</p>
                          <span class="meta">2 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/5.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Phillip Burke</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">It&#39;s been really fun so far</p>
                          <span class="meta">4 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/6.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Romy Schulte</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">That&#39;s cool!</p>
                          <span class="meta">1 week</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/10.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Maxim Werner</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Definitely, let&#39;s plan on it</p>
                          <span class="meta">1 year</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                </ul>
                <!-- .tyn-aside-list -->
              </div>
              <!-- .tab-pane -->
              <div class="tab-pane" id="archived-chats" tabindex="0" role="tabpanel">
                <ul class="tyn-aside-list">
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/2.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Konstantin Frank</h6>
                          <div class="indicator varified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                              <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                              /></svg
                            ><!-- check-circle-fill -->
                          </div>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Liked that disco music</p>
                          <span class="meta">1 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/4.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Marie George</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Same here. I&#39;ve been trying to keep myself occupied</p>
                          <span class="meta">2 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/5.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Phillip Burke</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">It&#39;s been really fun so far</p>
                          <span class="meta">4 days</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/9.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Albert Henderson</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Sounds good to me</p>
                          <span class="meta">3 months</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/10.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Maxim Werner</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Definitely, let&#39;s plan on it</p>
                          <span class="meta">1 year</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                </ul>
                <!-- .tyn-aside-list -->
              </div>
              <!-- .tab-pane -->
              <div class="tab-pane" id="spam-messages" tabindex="0" role="tabpanel">
                <ul class="tyn-aside-list">
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/9.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Albert Henderson</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Sounds good to me</p>
                          <span class="meta">3 months</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                  <li class="tyn-aside-item js-toggle-main">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/10.jpg" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <h6 class="name">Maxim Werner</h6>
                        </div>
                        <div class="tyn-media-row has-dot-sap">
                          <p class="content">Definitely, let&#39;s plan on it</p>
                          <span class="meta">1 year</span>
                        </div>
                      </div>
                      <!-- .tyn-media-col -->
                      <div class="tyn-media-option tyn-aside-item-option">
                        <ul class="tyn-media-option-list">
                          <li class="dropdown">
                            <div class="btn btn-icon btn-white btn-pill dropdown-toggle" data-bs-toggle="dropdown" data-bs-offset="0,0" data-bs-auto-close="outside">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" /></svg
                              ><!-- three-dots -->
                            </div>
                            <!-- .dropdown-toggle -->
                            <div class="dropdown-menu dropdown-menu-end">
                              <ul class="tyn-list-links">
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                      <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" /></svg
                                    ><!-- check -->
                                    <span>Mark as Read</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                      <path
                                        d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"
                                      /></svg
                                    ><!-- bell -->
                                    <span>Mute Notifications</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="contacts.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                      <path
                                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"
                                      /></svg
                                    ><!-- person -->
                                    <span>View Profile</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#callingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                      <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                      /></svg
                                    ><!-- telephone -->
                                    <span>Audio Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#videoCallingScreen" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video" viewBox="0 0 16 16">
                                      <path
                                        fill-rule="evenodd"
                                        d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1z"
                                      /></svg
                                    ><!-- camera-video -->
                                    <span>Video Call</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li class="dropdown-divider"></li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                                      <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                      <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"
                                      /></svg
                                    ><!-- file-earmark-arrow-down -->
                                    <span>Archive</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#deleteChat" data-bs-toggle="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                      <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"
                                      />
                                      <path
                                        d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"
                                      /></svg
                                    ><!-- trash -->
                                    <span>Delete</span></a
                                  >
                                </li>
                                <!-- li -->
                                <li>
                                  <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                      <path
                                        d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"
                                      />
                                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" /></svg
                                    ><!-- exclamation-triangle -->
                                    <span>Report</span></a
                                  >
                                </li>
                                <!-- li -->
                              </ul>
                              <!-- .tyn-list-links -->
                            </div>
                            <!-- .dropdown-menu -->
                          </li>
                          <!-- li -->
                        </ul>
                        <!-- .tyn-media-option-list -->
                      </div>
                      <!-- .tyn-media-option -->
                    </div>
                    <!-- .tyn-media-group -->
                  </li>
                  <!-- .tyn-aside-item -->
                </ul>
                <!-- .tyn-aside-list -->
              </div>
              <!-- .tab-pane -->
              <div class="tab-pane" id="trash-bin" tabindex="0" role="tabpanel">
                <div class="tyn-aside-row text-center">
                  <h6>Nothing in trash</h6>
                  <p>Lets delete someting to test it.</p>
                </div>
                <!-- .tyn-aside-row -->
              </div>
              <!-- .tab-pane -->
            </div>
            <!-- .tab-content -->
          </div>
          <!-- .tyn-aside-body -->
        </div>
        <!-- .tyn-aside -->
        <div class="tyn-main tyn-chat-content" id="tynMain">
          <div class="tyn-chat-head">
            <ul class="tyn-list-inline d-md-none ms-n1">
              <li>
                <button class="btn btn-icon btn-md btn-pill btn-transparent js-toggle-main">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" /></svg
                  ><!-- arrow-left -->
                </button>
              </li>
            </ul>
            <div class="tyn-media-group">
              <div class="tyn-media tyn-size-lg d-none d-sm-inline-flex">
                <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
              </div>
              <!-- .tyn-media -->
              <div class="tyn-media tyn-size-rg d-sm-none">
                <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
              </div>
              <!-- .tyn-media -->
              <div class="tyn-media-col">
                <div class="tyn-media-row">
                  <h6 class="name start_c_name" id="" > </h6>
                </div> 
                <?php /* ?>
                <div class="tyn-media-row has-dot-sap">
                  <span class="meta">Active</span>
                </div>
                <?php */ ?>
              </div>
              <!-- .tyn-media-col -->
            </div>
            <!-- .tyn-media-group -->
            <ul class="tyn-list-inline gap gap-3 ms-auto">
               
               
              <li>
                <button class="btn btn-icon btn-light js-toggle-chat-options">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-layout-sidebar-inset-reverse" viewBox="0 0 16 16">
                    <path d="M2 2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zm12-1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2z" />
                    <path d="M13 4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1z" /></svg
                  ><!-- layout-sidebar-inset-reverse -->
                </button> 
              </li>
            </ul>
            <!-- .tyn-list-inline -->
            <div class="tyn-chat-search" id="tynChatSearch">
              <div class="flex-grow-1">
                <div class="form-group">
                  <div class="form-control-wrap form-control-plaintext-wrap">
                    <div class="form-control-icon start">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path
                          d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"
                        /></svg
                      ><!-- search -->
                    </div>
                    <input type="text" class="form-control form-control-plaintext" id="searchInThisChat" placeholder="Search in this chat" />
                  </div>
                </div>
              </div>
              <div class="d-flex align-items-center gap gap-3">
                <ul class="tyn-list-inline">
                  <li>
                    <button class="btn btn-icon btn-sm btn-transparent">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z" /></svg
                      ><!-- chevron-up -->
                    </button>
                  </li>
                  <li>
                    <button class="btn btn-icon btn-sm btn-transparent">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" /></svg
                      ><!-- chevron-down -->
                    </button>
                  </li>
                </ul>
                <ul class="tyn-list-inline">
                  <li>
                    <button class="btn btn-icon btn-md btn-light js-toggle-chat-search">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path
                          d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"
                        /></svg
                      ><!-- x-lg -->
                    </button>
                  </li>
                </ul>
              </div>
            </div>
            <!-- .tyn-chat-search -->
          </div>
          <!-- .tyn-chat-head -->
          <div class="tyn-chat-body js-scroll-to-end" id="tynChatBody">
            <div class="tyn-reply tyn-reply-quick " id="tynReply">
               
                <span id="chat-messages" >
                
                </span>
              <!-- .tyn-reply-item -->
            </div>
            <!-- .tyn-reply -->
          </div>
          <!-- .tyn-chat-body -->
          <div class="tyn-chat-form">
            <div class="tyn-chat-form-insert">
              <ul class="tyn-list-inline gap gap-3">
                
                <!-- li -->
                <li class="d-none d-sm-block">
                  <input type="file" id="tynChatfileInput" style="display:none" accept="image/png, image/gif, image/jpeg"  >  
                  <button class="btn btn-icon btn-light btn-md btn-pill" id="but_chat_file" >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-image" viewBox="0 0 16 16">
                      <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                      <path
                        d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v.54L1 12.5v-9a.5.5 0 0 1 .5-.5z"
                      /></svg
                    ><!-- card-image -->
                  </button>
                </li>                
                <!-- li -->
              </ul>
            </div>
            <!-- .tyn-chat-form-insert -->
            <div class="tyn-chat-form-enter">
              <div class="tyn-chat-form-input" id="tynChatInput" contenteditable></div>              
              
              <ul class="tyn-list-inline me-n2 my-1">
                
                <li>
                  <button class="btn btn-icon btn-white btn-md btn-pill" id="send-message-admin"  data-chat_id="0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                      <path
                        d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"
                      /></svg
                    ><!-- send-fill -->
                  </button>
                </li>
              </ul>
            </div>
            <!-- .tyn-chat-form-enter -->
          </div>
          <!-- .tyn-chat-form -->
          <div class="tyn-chat-content-aside" id="tynChatAside" data-simplebar>
            <div class="tyn-chat-cover">
              <img src="https://gowibble.com/wp-content/plugins/chat-message/images/cover/1.jpg" alt="" />
            </div>
            <!-- .tyn-chat-cover -->
            <div class="tyn-media-group tyn-media-vr tyn-media-center mt-n4">
              <div class="tyn-media tyn-size-xl border border-2 border-white">
                <img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
              </div>
              <div class="tyn-media-col">
                <div class="tyn-media-row">
                  <h6 class="name start_c_name "></h6>
                </div>
                <div class="tyn-media-row has-dot-sap">
                  <span class="meta">Active Now</span>
                </div>
              </div>
              <!-- .tyn-media-col -->
            </div>
            <!-- .tyn-media-group -->
            <div class="tyn-aside-row">
              <ul class="nav nav-btns nav-btns-stretch nav-btns-light">
                <li class="nav-item">
                  <button class="nav-link js-chat-mute-toggle__  tyn-chat-mute chat_block" type="button"> 
                    <span class="icon unmuted-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" /></svg
                      ><!-- bell-fill -->
                    </span>
                    <span class="unmuted-icon">Block</span>
                    <span class="icon muted-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-slash-fill" viewBox="0 0 16 16">
                        <path
                          d="M5.164 14H15c-1.5-1-2-5.902-2-7q0-.396-.06-.776zm6.288-10.617A5 5 0 0 0 8.995 2.1a1 1 0 1 0-1.99 0A5 5 0 0 0 3 7c0 .898-.335 4.342-1.278 6.113zM10 15a2 2 0 1 1-4 0zm-9.375.625a.53.53 0 0 0 .75.75l14.75-14.75a.53.53 0 0 0-.75-.75z"
                        /></svg
                      ><!-- bell-slash-fill -->
                    </span>
                    <span class="muted-icon">Blocked</span>
                  </button>
                </li>
                <!-- .nav-item -->
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chat-media" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
                      <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                      <path
                        d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"
                      /></svg
                    ><!-- images -->
                    <span>Media</span>
                  </button>
                </li>
                
              </ul>
              <!-- .nav-btns -->
            </div>
            <!-- .tyn-aside-row -->
            <div class="tab-content">
              <div class="tab-pane active show"" id="chat-media" tabindex="0">
                <div class="tyn-aside-row py-0">
                  <ul class="nav nav-tabs nav-tabs-line">
                    <li class="nav-item">
                      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chat-media-images" type="button">Images</button>
                    </li>
                    <!-- .nav-item --> 
                  </ul>
                  <!-- .nav-tabs -->
                </div>
                <!-- .tyn-aside-row -->
                <div class="tyn-aside-row">
                  <div class="tab-content">
                    <div class="tab-pane show active" id="chat-media-images" tabindex="0">
                      <div class="row g-3" id="chat_images_div" >
                         
                      </div>
                      <!-- .row -->
                    </div>
                    
                  </div>
                  <!-- .tab-content -->
                </div>
                <!-- .tyn-aside-row -->
              </div>
              <!-- .tab-pane -->
           
              <!-- .tab-pane -->
            </div>
            <!-- .tab-content -->
          </div>
          <!-- .tyn-chat-content-aside -->
        </div>
        <!-- .tyn-main -->
      </div>
      <!-- .tyn-content -->
   
      <!-- .tyn-quick-chat -->
    </div>
    <!-- .tyn-root -->
    
    
    <!-- .modal -->
      <?php /* ?>
    <div class="modal fade" tabindex="-1" id="muteOptions">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
          <div class="modal-body p-4">
            <h4 class="pb-2">Mute conversation</h4>
            <ul class="tyn-media-list gap gap-2">
              <li>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="muteFor" id="muteFor15min" />
                  <label class="form-check-label" for="muteFor15min"> For 15 minutes </label>
                </div>
              </li>
              <!-- li -->
              <li>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="muteFor" id="muteFor1Hour" checked />
                  <label class="form-check-label" for="muteFor1Hour"> For 1 Hours </label>
                </div>
              </li>
              <!-- li -->
              <li>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="muteFor" id="muteFor1Days" checked />
                  <label class="form-check-label" for="muteFor1Days"> For 1 Days </label>
                </div>
              </li>
              <!-- li -->
              <li>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="muteFor" id="muteForInfinity" checked />
                  <label class="form-check-label" for="muteForInfinity"> Until I turn back On </label>
                </div>
              </li>
              <!-- li -->
            </ul>
            <!-- .tyn-media-list -->
            <ul class="tyn-list-inline gap gap-3 pt-3">
              <li>
                <button class="btn btn-md btn-danger js-chat-mute">Mute</button>
              </li>
              <li>
                <button class="btn btn-md btn-light" data-bs-dismiss="modal">Close</button>
              </li>
            </ul>
            <!-- .tyn-list-inline -->
          </div>
          <!-- .modal-body -->
          <button class="btn btn-md btn-icon btn-pill btn-white shadow position-absolute top-0 end-0 mt-n3 me-n3" data-bs-dismiss="modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" /></svg
            ><!-- x-lg --></button
          ><!-- modal-close -->
        </div>
        <!-- .modal-content -->
      </div>
      <!-- .modal-dialog -->
    </div>
    
  
    <?php */ ?>
    <!-- .modal -->
     
     
    <!-- .modal -->
    <div class="modal fade" tabindex="-1" id="deleteChat">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
          <div class="modal-body">
            <div class="py-4 px-4 text-center">
              <h3>Delete chat</h3>
              <p class="small">Once you delete your copy of this conversation, it cannot be undone.</p>
              <ul class="tyn-list-inline gap gap-3 pt-1 justify-content-center">
                <li>
                  <button class="btn btn-danger" data-bs-dismiss="modal">Delete</button>
                </li>
                <li>
                  <button class="btn btn-light" data-bs-dismiss="modal">No</button>
                </li>
              </ul>
              <!-- .tyn-list-inline -->
            </div>
          </div>
          <!-- .modal-body -->
          <button class="btn btn-md btn-icon btn-pill btn-white shadow position-absolute top-0 end-0 mt-n3 me-n3" data-bs-dismiss="modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" /></svg
            ><!-- x-lg --></button
          ><!-- modal-close -->
        </div>
        <!-- .modal-content -->
      </div>
      <!-- .modal-dialog -->
    </div>
		
		
    </div>
 
    <?php
}


?>