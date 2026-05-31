<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_menu', 'wpchat_register_chat_admin_page');

function wpchat_register_chat_admin_page() {
    $menu = add_menu_page(__('Chat', 'chat-message'), __('Chat', 'chat-message'), 'manage_options', 'chat', 'wpchat_chat_admin_page_callback', 'dashicons-admin-comments');
	
	 add_menu_page(
        __('Chat Settings', 'chat-message'),       
        __('Chat Settings', 'chat-message'),       
        'manage_options',      
        'chat-settings',       
        'wpchat_chat_settings_page',  
        'dashicons-format-chat',  
    );
	
	add_action('load-'.$menu, 'wpchat_enqueue_admin_chat_scripts');
}

function wpchat_chat_settings_page() {
    if (isset($_POST['chat_settings_submit'])) {
        // Verify nonce
        if (!isset($_POST['chat_settings_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_POST['chat_settings_nonce'])), 'chat_settings_action')) {
            echo '<div class="error"><p>' . esc_html__('Security check failed. Please try again.', 'chat-message') . '</p></div>';
            return;
        }

        // Save the uploaded image
        if (isset($_FILES['chat_logo']) && !empty($_FILES['chat_logo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $uploaded = media_handle_upload('chat_logo', 0);
            if (!is_wp_error($uploaded)) {
                update_option('chat_logo', $uploaded);
            } else {
                echo '<div class="error"><p>' . esc_html__('Failed to upload image.', 'chat-message') . '</p></div>';
            }
        }

        // Save the welcome message
        if (isset($_POST['welcome_message'])) {
            update_option('welcome_message', sanitize_textarea_field(wp_unslash($_POST['welcome_message'])));
        }

        // Save global enable option
        $enable_chat = isset($_POST['enable_chat_box']) ? 'yes' : 'no';
        update_option('enable_chat_box', $enable_chat);

        echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'chat-message') . '</p></div>';
    }

    $chat_logo_id = get_option('chat_logo');
    $chat_logo_url = $chat_logo_id ? wp_get_attachment_url($chat_logo_id) : '';
    $welcome_message = get_option('welcome_message', '');
    $enable_chat_box = get_option('enable_chat_box', 'no');

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Chat Settings', 'chat-message'); ?></h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('chat_settings_action', 'chat_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Chat Box', 'chat-message'); ?></th>
                    <td>
                        <label for="enable_chat_box">
                            <input type="checkbox" name="enable_chat_box" id="enable_chat_box" value="yes" <?php checked('yes', $enable_chat_box); ?> />
                            <?php esc_html_e('Show chat box automatically on all frontend pages.', 'chat-message'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="chat_logo"><?php esc_html_e('Chat Logo', 'chat-message'); ?></label></th>
                    <td>
                        <input type="file" name="chat_logo" id="chat_logo" />
                        <?php if ($chat_logo_url): ?>
                            <p><img src="<?php echo esc_url($chat_logo_url); ?>" alt="<?php esc_attr_e('Chat Logo', 'chat-message'); ?>" style="max-width: 150px;" /></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome_message"><?php esc_html_e('Welcome Message', 'chat-message'); ?></label></th>
                    <td>
                        <textarea name="welcome_message" id="welcome_message" rows="5" cols="50" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Settings', 'chat-message'), 'primary', 'chat_settings_submit'); ?>
        </form>
    </div>
    <?php
}

function wpchat_chat_admin_page_callback() {
	global $wpdb;
	
	 $chat_users = wp_cache_get( 'wpchat_admin_users', 'wpchat' );
	 if ( false === $chat_users ) {
		 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		 $chat_users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}chat_users ORDER BY id DESC"); 	
		 wp_cache_set( 'wpchat_admin_users', $chat_users, 'wpchat', 1 * MINUTE_IN_SECONDS );
	 }
    ?>
    <div class="wrap">
        <div class="tyn-root">
      <nav class="tyn-appbar">
        <div class="tyn-appbar-wrap">
          <div class="tyn-appbar-logo">
            <a class="tyn-logo" >
              <svg viewBox="0 0 43 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M37.2654 14.793C37.2654 14.793 45.0771 20.3653 41.9525 29.5311C41.9525 29.5311 41.3796 31.1976 39.0361 34.4264L42.4732 37.9677C42.4732 37.9677 43.3065 39.478 41.5879 39.9987H24.9229C24.9229 39.9987 19.611 40.155 14.8198 36.9782C14.8198 36.9782 12.1638 35.2076 9.76825 31.9787L18.6215 32.0308C18.6215 32.0308 24.298 31.9787 29.7662 28.3333C35.2344 24.6878 37.4217 18.6988 37.2654 14.793Z" fill="#60A5FA" />
                <path d="M34.5053 12.814C32.2659 1.04441 19.3506 0.0549276 19.3506 0.0549276C8.31004 -0.674164 3.31055 6.09597 3.31055 6.09597C-4.24076 15.2617 3.6751 23.6983 3.6751 23.6983C3.6751 23.6983 2.99808 24.6357 0.862884 26.5105C-1.27231 28.3854 1.22743 29.3748 1.22743 29.3748H17.3404C23.4543 28.7499 25.9124 27.3959 25.9124 27.3959C36.328 22.0318 34.5053 12.814 34.5053 12.814ZM19.9963 18.7301H9.16412C8.41419 18.7301 7.81009 18.126 7.81009 17.3761C7.81009 16.6261 8.41419 16.022 9.16412 16.022H19.9963C20.7463 16.022 21.3504 16.6261 21.3504 17.3761C21.3504 18.126 20.7358 18.7301 19.9963 18.7301ZM25.3708 13.314H9.12245C8.37253 13.314 7.76843 12.7099 7.76843 11.96C7.76843 11.21 8.37253 10.6059 9.12245 10.6059H25.3708C26.1207 10.6059 26.7248 11.21 26.7248 11.96C26.7248 12.7099 26.1103 13.314 25.3708 13.314Z" fill="#2563EB" />
              </svg>
            </a>
          </div>
          <div class="tyn-appbar-content">
            <ul class="tyn-appbar-nav tyn-appbar-nav-start">
            </ul>
            <ul class="tyn-appbar-nav tyn-appbar-nav-end">
              <li class="tyn-appbar-item">
                <a class="d-inline-flex dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown" href="#" data-bs-offset="0,10">
                  <div class="tyn-media tyn-size-lg tyn-circle">
                    <img src="<?php echo esc_url(get_avatar_url(get_current_user_id())); ?>" alt="" />
                  </div> </a>
                <div class="dropdown-menu dropdown-menu-end">
                  <div class="dropdown-gap">
                    <div class="tyn-media-group">
                      <div class="tyn-media tyn-size-lg">
                        <img src="<?php echo esc_url(get_avatar_url(get_current_user_id())); ?>" alt="" />
                      </div>
                      <div class="tyn-media-col">
                        <div class="tyn-media-row">
                          <?php $current_user = wp_get_current_user(); ?>
                          <h6 class="name"><?php echo esc_html($current_user->display_name); ?></h6>
                          <div class="indicator varified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                              <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                            </svg>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="dropdown-gap">
                    <div class="d-flex gap gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16">
                        <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                      </svg>
                      <div>
                        <h6><?php esc_html_e('Darkmode', 'chat-message'); ?></h6>
                        <ul class="d-flex align-items-center gap gap-3">
                          <li class="inline-flex">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="themeMode" id="dark" value="dark" />
                              <label class="form-check-label small" for="dark"> <?php esc_html_e('On', 'chat-message'); ?> </label>
                            </div>
                          </li>
                          <li class="inline-flex">
                            <div class="form-check">
                              <input class="form-check-input" type="radio" name="themeMode" id="light" value="light" checked />
                              <label class="form-check-label small" for="light"> <?php esc_html_e('Off', 'chat-message'); ?> </label>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <div class="tyn-content tyn-content-full-height tyn-chat has-aside-base">
        <div class="tyn-aside tyn-aside-base">
          <div class="tyn-aside-head">
            <div class="tyn-aside-head-text">
              <h3 class="tyn-aside-title"><?php esc_html_e('Chats', 'chat-message'); ?></h3>
            </div>
          </div>
          <div class="tyn-aside-body" data-simplebar>
            <div class="tyn-aside-search">
              <div class="form-group tyn-pill">
                <div class="form-control-wrap">
                  <div class="form-control-icon start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                  </div>
                  <input type="text" class="form-control form-control-solid" id="search_chat_user" placeholder="<?php esc_attr_e('Search contact / chat', 'chat-message'); ?>" />
                </div>
              </div>
            </div>
            <div class="tab-content">
              <div class="tab-pane show active" id="all-chats" tabindex="0" role="tabpanel">
                <ul class="tyn-aside-list" id="chat_list_ul" >
					<?php 
					  if( !empty( $chat_users )){
						  foreach( $chat_users as $chat_user){
                              $nkg_last_chat_id = 0;
							  
							     $cache_key = 'wpchat_unread_' . $chat_user->id;
								 $unread_count_val = wp_cache_get( $cache_key, 'wpchat' );
								 
								 if ( false === $unread_count_val ) {
									 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
									 $unread_results = $wpdb->get_results(
										$wpdb->prepare(
											"SELECT COUNT(*) as unread_count FROM {$wpdb->prefix}chat_messages WHERE read_status = 0 AND sender = 'user' AND user_id = %d",
											$chat_user->id
										),
										ARRAY_A
									);
									$unread_count_val = !empty( $unread_results[0]['unread_count'] ) ? intval( $unread_results[0]['unread_count'] ) : 0;
									wp_cache_set( $cache_key, $unread_count_val, 'wpchat', 30 );
								 }

                                 $unread_count = ( $unread_count_val > 0 ) ? "(".$unread_count_val.")" :'';
                                 $user_avatar_url = wpchat_get_avatar_url_by_email($chat_user->email);
							  ?>
							  <li class="tyn-aside-item js-toggle-main chat_list" id="chat_id_<?php echo esc_attr($chat_user->id);?>" data-id="<?php echo esc_attr($chat_user->id);?>" data-avatar="<?php echo esc_url($user_avatar_url); ?>" data-last="<?php echo esc_attr($nkg_last_chat_id);?>" data-block="<?php echo esc_attr($chat_user->block); ?>"  >
							  <div class="tyn-media-group">
							  <div class="tyn-media tyn-size-lg">
							  <img src="<?php echo esc_url($user_avatar_url); ?>" alt="" class="tyn-image" /> 
							  </div>
									 <div class="tyn-media-col">
										<div class="tyn-media-row">
										  <h6 class="name"> <?php echo esc_html($chat_user->name); ?> </h6>
										  <span class="typing"> </span>
										</div>
										<div class="tyn-media-row has-dot-sap nkg_active_div" id="active_<?php echo esc_attr($chat_user->id); ?>" >
										  <p class="content"><?php echo esc_html($chat_user->email); ?></p>
										  <span class="meta"></span>
										  <span class="unread_count" id="unread_count_<?php echo esc_attr($chat_user->id); ?>" > <?php echo esc_html($unread_count); ?>  </span>
										</div> 
									  </div>
								</div>							  
							  </li>
							  <?php 
						  } 
					  }
					  ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="tyn-main tyn-chat-content" id="tynMain">
          <div class="tyn-chat-head">
            <ul class="tyn-list-inline d-md-none ms-n1">
              <li>
                <button class="btn btn-icon btn-md btn-pill btn-transparent js-toggle-main">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                  </svg>
                </button>
              </li>
            </ul>
            <div class="tyn-media-group">
              <div class="tyn-media tyn-size-lg d-none d-sm-inline-flex">
                <img src="<?php echo esc_url(plugins_url('images/avatar/user.png', __FILE__)); ?>" alt="" class="start_c_avatar" />
              </div>
              <div class="tyn-media tyn-size-rg d-sm-none">
                <img src="<?php echo esc_url(plugins_url('images/avatar/user.png', __FILE__)); ?>" alt="" class="start_c_avatar" />
              </div>
              <div class="tyn-media-col">
                <div class="tyn-media-row">
                  <h6 class="name start_c_name"> </h6>
                </div> 
              </div>
            </div>
            <ul class="tyn-list-inline gap gap-3 ms-auto">
              <li>
                <button class="btn btn-icon btn-light js-toggle-chat-options">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-layout-sidebar-inset-reverse" viewBox="0 0 16 16">
                    <path d="M2 2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zm12-1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2z" />
                    <path d="M13 4a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1z" />
                  </svg>
                </button> 
              </li>
            </ul>
            <div class="tyn-chat-search" id="tynChatSearch">
              <div class="flex-grow-1">
                <div class="form-group">
                  <div class="form-control-wrap form-control-plaintext-wrap">
                    <div class="form-control-icon start">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                      </svg>
                    </div>
                    <input type="text" class="form-control form-control-plaintext" id="searchInThisChat" placeholder="<?php esc_attr_e('Search in this chat', 'chat-message'); ?>" />
                  </div>
                </div>
              </div>
              <div class="d-flex align-items-center gap gap-3">
                <ul class="tyn-list-inline">
                  <li>
                    <button class="btn btn-icon btn-sm btn-transparent">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-up" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z" />
                      </svg>
                    </button>
                  </li>
                  <li>
                    <button class="btn btn-icon btn-sm btn-transparent">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                      </svg>
                    </button>
                  </li>
                </ul>
                <ul class="tyn-list-inline">
                  <li>
                    <button class="btn btn-icon btn-md btn-light js-toggle-chat-search">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
                      </svg>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="tyn-chat-body js-scroll-to-end" id="tynChatBody">
            <div class="tyn-reply tyn-reply-quick" id="tynReply">
                <span id="chat-messages"></span>
            </div>
          </div>
          <div class="tyn-chat-form">
            <div class="tyn-chat-form-insert">
              <ul class="tyn-list-inline gap gap-3">
                <li class="d-none d-sm-block">
                  <input type="file" id="tynChatfileInput" style="display:none" accept="image/png, image/gif, image/jpeg">  
                  <button class="btn btn-icon btn-light btn-md btn-pill" id="but_chat_file">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-image" viewBox="0 0 16 16">
                      <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                      <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v.54L1 12.5v-9a.5.5 0 0 1 .5-.5z" />
                    </svg>
                  </button>
                </li>                
              </ul>
            </div>
            <div class="tyn-chat-form-enter">
              <div class="tyn-chat-form-input" id="tynChatInput" contenteditable></div>              
              <ul class="tyn-list-inline me-n2 my-1">
                <li>
                  <button class="btn btn-icon btn-white btn-md btn-pill" id="send-message-admin" data-chat_id="0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                      <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z" />
                    </svg>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="tyn-chat-content-aside" id="tynChatAside" data-simplebar>
            <div class="tyn-chat-cover">
              <img src="<?php echo esc_url(plugins_url('images/cover/1.png', __FILE__)); ?>" alt="" />
            </div>
            <div class="tyn-media-group tyn-media-vr tyn-media-center mt-n4">
              <div class="tyn-media tyn-size-xl border border-2 border-white">
                <img src="<?php echo esc_url(plugins_url('images/avatar/user.png', __FILE__)); ?>" alt="" class="start_c_avatar" />
              </div>
              <div class="tyn-media-col">
                <div class="tyn-media-row">
                  <h6 class="name start_c_name"></h6>
                </div>
                <div class="tyn-media-row has-dot-sap">
                  <span class="meta"><?php esc_html_e('Active Now', 'chat-message'); ?></span>
                </div>
              </div>
            </div>
            <div class="tyn-aside-row">
              <ul class="nav nav-btns nav-btns-stretch nav-btns-light">
                <li class="nav-item">
                  <button class="nav-link tyn-chat-mute chat_block" type="button"> 
                    <span class="icon unmuted-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" />
                      </svg>
                    </span>
                    <span class="unmuted-icon"><?php esc_html_e('Block', 'chat-message'); ?></span>
                    <span class="icon muted-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-slash-fill" viewBox="0 0 16 16">
                        <path d="M5.164 14H15c-1.5-1-2-5.902-2-7q0-.396-.06-.776zm6.288-10.617A5 5 0 0 0 8.995 2.1a1 1 0 1 0-1.99 0A5 5 0 0 0 3 7c0 .898-.335 4.342-1.278 6.113zM10 15a2 2 0 1 1-4 0zm-9.375.625a.53.53 0 0 0 .75.75l14.75-14.75a.53.53 0 0 0-.75-.75z" />
                      </svg>
                    </span>
                    <span class="muted-icon"><?php esc_html_e('Blocked', 'chat-message'); ?></span>
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chat-media" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
                      <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                      <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z" />
                    </svg>
                    <span><?php esc_html_e('Media', 'chat-message'); ?></span>
                  </button>
                </li>
              </ul>
            </div>
            <div class="tab-content">
              <div class="tab-pane active show" id="chat-media" tabindex="0">
                <div class="tyn-aside-row py-0">
                  <ul class="nav nav-tabs nav-tabs-line">
                    <li class="nav-item">
                      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chat-media-images" type="button"><?php esc_html_e('Images', 'chat-message'); ?></button>
                    </li>
                  </ul>
                </div>
                <div class="tyn-aside-row">
                  <div class="tab-content">
                    <div class="tab-pane show active" id="chat-media-images" tabindex="0">
                      <div class="row g-3" id="chat_images_div"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" tabindex="-1" id="deleteChat">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
          <div class="modal-body">
            <div class="py-4 px-4 text-center">
              <h3><?php esc_html_e('Delete chat', 'chat-message'); ?></h3>
              <p class="small"><?php esc_html_e('Once you delete your copy of this conversation, it cannot be undone.', 'chat-message'); ?></p>
              <ul class="tyn-list-inline gap gap-3 pt-1 justify-content-center">
                <li>
                  <button class="btn btn-danger" data-bs-dismiss="modal"><?php esc_html_e('Delete', 'chat-message'); ?></button>
                </li>
                <li>
                  <button class="btn btn-light" data-bs-dismiss="modal"><?php esc_html_e('No', 'chat-message'); ?></button>
                </li>
              </ul>
            </div>
          </div>
          <button class="btn btn-md btn-icon btn-pill btn-white shadow position-absolute top-0 end-0 mt-n3 me-n3" data-bs-dismiss="modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    </div>
    <?php
}
