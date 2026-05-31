<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpchat_send_otp(){		
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;		
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';    
	$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';		
	
    if ( empty($email) || empty($name) ) {
        wp_send_json_error(__('Missing required fields.', 'chat-message'));
    }

    $otp = wp_rand(100000, 999999);		
	
	$otp = 123456; // For testing only, remove this line in production
	/* translators: %s: OTP code */
	$body = sprintf(__('Your Code is %s', 'chat-message'), $otp);
	wp_mail($email, __('Your Code', 'chat-message'), $body);	

	$data = array( 'name' => $name, 'otp' => $otp, 'verified' => 0 );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE email = %s", $email));	    
	if ($user) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update($wpdb->prefix . 'chat_users', $data , ['email' => $email]);	
	} else {	
		$data['email'] = $email;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert($wpdb->prefix . 'chat_users', $data );	
		wp_cache_delete( 'wpchat_admin_users', 'wpchat' );
	}
	
	wp_send_json_success();    
}
add_action('wp_ajax_wpchat_send_otp', 'wpchat_send_otp');
add_action('wp_ajax_nopriv_wpchat_send_otp', 'wpchat_send_otp');

function wpchat_verify_otp() {	  
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;    
	
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';    
	$otp = isset($_POST['otp']) ? sanitize_text_field(wp_unslash($_POST['otp'])) : '';    
	
    if ( empty($email) || empty($otp) ) {
        wp_send_json_error(__('Missing required fields.', 'chat-message'));
    }

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE email = %s AND otp = %s", $email, $otp));	
    
	if ($user) {	
		setcookie('wp_chat_id', $user->id, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update($wpdb->prefix . 'chat_users', ['verified' => 1,'last_activity' => current_time('mysql') ], ['email' => $email] );	
		
		$data = wpchat_handle_get_messages(0, $user->id); 
		wp_send_json_success($data);	
	} else {		  
		wp_send_json_error(__('Invalid OTP', 'chat-message'));	
	}
}
add_action('wp_ajax_wpchat_verify_otp', 'wpchat_verify_otp');
add_action('wp_ajax_nopriv_wpchat_verify_otp', 'wpchat_verify_otp'); 

function wpchat_update_online_users(){
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;
	
    $wp_chat_id = isset($_COOKIE["wp_chat_id"]) ? absint($_COOKIE["wp_chat_id"]) : 0;

	if( $wp_chat_id ){
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update($wpdb->prefix . 'chat_users', ['last_activity' => current_time('mysql') ], ['id' => $wp_chat_id] );	
	}
	
	wp_send_json_success();
}
add_action('wp_ajax_wpchat_update_online_users', 'wpchat_update_online_users');
add_action('wp_ajax_nopriv_wpchat_update_online_users', 'wpchat_update_online_users');

function wpchat_handle_send_message() {
    check_ajax_referer('wpchat_nonce', 'nonce');
    global $wpdb;

    $wp_chat_id = isset($_COOKIE["wp_chat_id"]) ? absint($_COOKIE["wp_chat_id"]) : 0;

	if( $wp_chat_id ){
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %d AND block = 1", $wp_chat_id));
		 
		if($user){
			wp_send_json_error(__('You are blocked by administrator.', 'chat-message'));			
		}
		
		$message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';  
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert(
			"{$wpdb->prefix}chat_messages",
			[
				'user_id' => $wp_chat_id ,
				'sender'  => 'user',
				'message' => $message,
				'sent_at' => current_time('mysql')
			],
			['%d', '%s', '%s', '%s']
		);
			
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update($wpdb->prefix . 'chat_users', ['last_activity' => current_time('mysql') ], ['id' => $wp_chat_id] );	
			
		if ($result) {
			wp_cache_delete( 'wpchat_last_msg_id_' . $wp_chat_id, 'wpchat' );
			$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
			$data = wpchat_handle_get_messages($last_message_id, $wp_chat_id); 
			wp_send_json_success($data);
		} else {
			wp_send_json_error(__('Failed to send message.', 'chat-message'));
		}
	} else {
		wp_send_json_error(__('Please verify your email address', 'chat-message'));	
	}
}
add_action('wp_ajax_wpchat_send_message', 'wpchat_handle_send_message');
add_action('wp_ajax_nopriv_wpchat_send_message', 'wpchat_handle_send_message');

function wpchat_user_upload_chat_image(){
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;

    $wp_chat_id = isset($_COOKIE["wp_chat_id"]) ? absint($_COOKIE["wp_chat_id"]) : 0;

	if( $wp_chat_id ){
		$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
	
		if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
			wp_send_json_error(__('No image file uploaded.', 'chat-message'));
		}
		
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file = $_FILES['image'];
		$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
		if (!in_array($file['type'], $allowed_types)) {
			wp_send_json_error(__('Only image files are allowed (JPEG, PNG, GIF).', 'chat-message'));
		}
		
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$upload = wp_handle_upload($file, ['test_form' => false]);
		if (isset($upload['error'])) {
			wp_send_json_error($upload['error']);
		}
		
		$image_url = $upload['url']; 
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			"{$wpdb->prefix}chat_messages",
			[
				'user_id'   => $wp_chat_id,
				'sender'    => 'user',
				'message'   => '',
				'image'     => $image_url,
				'sent_at'   => current_time('mysql')
			],
			['%d', '%s', '%s', '%s', '%s']
		);
		wp_cache_delete( 'wpchat_last_msg_id_' . $wp_chat_id, 'wpchat' );
		
		$data = wpchat_handle_get_messages($last_message_id, $wp_chat_id);  
		wp_send_json_success($data); 
	}
}
add_action('wp_ajax_wpchat_user_upload_chat_image', 'wpchat_user_upload_chat_image');
add_action('wp_ajax_nopriv_wpchat_user_upload_chat_image', 'wpchat_user_upload_chat_image');

function wpchat_handle_get_messages($last_message_id = 0, $wp_chat_id = 0) {
    global $wpdb;
	$html = "";

	if( $wp_chat_id ){
		if( $last_message_id > 0){
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}chat_messages WHERE id > %d AND user_id = %d ORDER BY id DESC", 
				$last_message_id , $wp_chat_id
			));
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}chat_messages WHERE user_id = %d ORDER BY id DESC LIMIT 1000",
				$wp_chat_id
			));
		}

		if( !empty( $messages ) ){
			$messages = array_reverse( $messages );
			foreach($messages as $da){
				$last_message_id = $da->id;
				$date_time = '<p>' . esc_html(wpchat_date("M d, Y, H:i A", strtotime($da->sent_at))) . ' </p>';
				$reply_text = '<div class="tyn-reply-text">' . esc_html($da->message) . '' . $date_time . '</div>';
				
				if( !empty( $da->image ) ){
					$reply_text = '<div class="tyn-reply-media"> <img src="' . esc_url($da->image) . '" class="tyn-image" alt=""> ' . $date_time . ' </div>'; 
				}
				
				if( 'admin' == $da->sender ){
					$html .= '<div class="tyn-reply-item incoming">
					<div class="tyn-reply-avatar">
					  <div class="tyn-media tyn-size-md tyn-circle">
						<img src="' . esc_url(plugins_url('images/avatar/2.jpg', __FILE__)) . '" alt="" />
					  </div>
					</div> 
					<div class="tyn-reply-group">
					  <div class="tyn-reply-bubble">
						 ' . $reply_text . '
					  </div>                   
					</div> 
				  </div>';
				} else {
					$html .= '<div class="tyn-reply-item outgoing">
						<div class="tyn-reply-group">
						  <div class="tyn-reply-bubble">
							 ' . $reply_text . '
						  </div> 
						</div> 
					  </div>';
				}
			}
		}
	}
	
	return array("html" => $html, 'last_message_id' => $last_message_id); 
}

function wpchat_fetch_new_messages(){
	check_ajax_referer('wpchat_nonce', 'nonce');
    $wp_chat_id = isset($_COOKIE["wp_chat_id"]) ? absint($_COOKIE["wp_chat_id"]) : 0;
	$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
	$data = wpchat_handle_get_messages($last_message_id, $wp_chat_id);
	wp_send_json_success($data);
}
add_action('wp_ajax_wpchat_fetch_new_messages', 'wpchat_fetch_new_messages');
add_action('wp_ajax_nopriv_wpchat_fetch_new_messages', 'wpchat_fetch_new_messages');

function wpchat_fetch_new_messages_count(){
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;
	$total = 0;
    $wp_chat_id = isset($_COOKIE["wp_chat_id"]) ? absint($_COOKIE["wp_chat_id"]) : 0;

	if( $wp_chat_id ){
		$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$messages = $wpdb->get_results($wpdb->prepare(
			"SELECT count(*) as total_msg FROM {$wpdb->prefix}chat_messages WHERE id > %d AND user_id = %d AND sender = %s", 
			$last_message_id, $wp_chat_id, 'admin'
		)); 
		$total = !empty($messages) ? $messages[0]->total_msg : 0;
	}
	
	wp_send_json_success( array( "count" => $total ) );
}
add_action('wp_ajax_wpchat_fetch_new_messages_count', 'wpchat_fetch_new_messages_count');
add_action('wp_ajax_nopriv_wpchat_fetch_new_messages_count', 'wpchat_fetch_new_messages_count');

/**** Admin ****/
function wpchat_handle_admin_send_message() {
    check_ajax_referer('wpchat_nonce', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(__('Unauthorized', 'chat-message'));
    }

    global $wpdb;
	if( isset( $_POST["wp_chat_id"]  ) && !empty( $_POST["wp_chat_id"] ) ){
		$wp_chat_id = absint(wp_unslash($_POST["wp_chat_id"]));
		$message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';  
		$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
		
		$data = array(
					'user_id' => $wp_chat_id ,
					'sender'  => 'admin',
					'message' => $message,
					'sent_at' => current_time('mysql')
					);
					
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert(
				"{$wpdb->prefix}chat_messages",
				$data,
				['%d', '%s', '%s', '%s']
			);
	
		if ($result) {
			wp_cache_delete( 'wpchat_last_msg_id_' . $wp_chat_id, 'wpchat' );
			$data = wpchat_admin_handle_get_messages($last_message_id, $wp_chat_id);
			wp_send_json_success($data);
		} else {
			wp_send_json_error(__('Failed to send message.', 'chat-message'));
		}
	} else {
		wp_send_json_error(__('Please select user', 'chat-message'));	
	}
}
add_action('wp_ajax_wpchat_admin_send_message', 'wpchat_handle_admin_send_message');
add_action('wp_ajax_nopriv_wpchat_admin_send_message', 'wpchat_handle_admin_send_message');

function wpchat_admin_handle_get_messages($last_message_id = 0, $wp_chat_id = 0) {
    global $wpdb;
	$html = "";
	$images = "";
	if( $wp_chat_id ){
		if( $last_message_id > 0){
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}chat_messages WHERE id > %d AND user_id = %d ORDER BY id DESC", 
				$last_message_id , $wp_chat_id
			));
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$messages = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_messages WHERE user_id = %d ORDER BY id DESC LIMIT 1000",
					$wp_chat_id
				));
		}
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
            "{$wpdb->prefix}chat_messages",
            ['read_status' => 1],
            ['user_id' => $wp_chat_id, 'read_status' => 0, 'sender' => 'user'],
            ['%d'],
            ['%d', '%d', '%s']
        );
		
		if( !empty( $messages ) ){
			$messages = array_reverse( $messages );
			foreach($messages as $da){
				$last_message_id = $da->id;
				$date_time = '<p>' . esc_html(wpchat_date("M d, Y, H:i A", strtotime($da->sent_at))) . ' </p>';
				$reply_text = '<div class="tyn-reply-text">' . esc_html($da->message) . '' . $date_time . '</div>';
				
				if( !empty( $da->image ) ){
					$reply_text = '<div class="tyn-reply-media"> <img src="' . esc_url($da->image) . '" class="tyn-image" alt=""> ' . $date_time . ' </div>';
					$images .= '<div class="col-4"> <img src="' . esc_url($da->image) . '" class="tyn-image" alt=""> </div>';	
				} 
					
				if( 'admin' == $da->sender ){
					$html .= '<div class="tyn-reply-item outgoing">
						<div class="tyn-reply-group">
						  <div class="tyn-reply-bubble">
							 ' . $reply_text . '					 
						  </div> 
						</div> 
					  </div>';
				} else {
					$html .= '<div class="tyn-reply-item incoming">
					<div class="tyn-reply-group">
					  <div class="tyn-reply-bubble">
						  ' . $reply_text . '						 
					  </div>                   
					</div> 
				  </div>';
				}			 
			}
		}
	} 
	return array("html" => $html, 'last_message_id' => $last_message_id, 'images' => $images); 
}

function wpchat_admin_fetch_new_messages(){
    check_ajax_referer('wpchat_nonce', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(__('Unauthorized', 'chat-message'));
    }
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$wp_chat_id = isset($_POST['wp_chat_id']) ? absint(wp_unslash($_POST['wp_chat_id'])) : 0;
	$data = wpchat_admin_handle_get_messages($last_message_id, $wp_chat_id);
	wp_send_json_success($data);
}
add_action('wp_ajax_wpchat_admin_fetch_new_messages', 'wpchat_admin_fetch_new_messages');
add_action('wp_ajax_nopriv_wpchat_admin_fetch_new_messages', 'wpchat_admin_fetch_new_messages');

function wpchat_admin_fetch_new_chat(){
	check_ajax_referer('wpchat_nonce', 'nonce');
	if ( ! current_user_can('manage_options') ) {
		wp_send_json_error(__('Unauthorized', 'chat-message'));
	}
	global $wpdb;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$last_chat_id = isset($_POST['last_chat_id']) ? absint(wp_unslash($_POST['last_chat_id'])) : 0;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$chat_users = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_users WHERE id > %d ORDER BY id DESC", $last_chat_id));
	
	if ( ! empty( $chat_users ) ) {
		wp_cache_delete( 'wpchat_admin_users', 'wpchat' );
	}

	$html = "";			
	if( !empty( $chat_users )){
		  foreach( $chat_users as $chat_user){
             $user_avatar_url = wpchat_get_avatar_url_by_email($chat_user->email);
			 $html .= '<li class="tyn-aside-item js-toggle-main chat_list" data-id="'.esc_attr($chat_user->id) .'" data-avatar="'.esc_url($user_avatar_url).'" data-block="'.esc_attr($chat_user->block).'" id="chat_id_'.esc_attr($chat_user->id).'"  >
				<div class="tyn-media-group">
					<div class="tyn-media tyn-size-lg">
						<img src="' . esc_url($user_avatar_url) . '" alt="" class="tyn-image" />
					 </div>
					 <div class="tyn-media-col">
						<div class="tyn-media-row">
						  <h6 class="name"> ' . esc_html($chat_user->name) .' </h6>
						  <span class="typing"></span>
						</div>
						<div class="tyn-media-row has-dot-sap">
						  <p class="content"> '. esc_html($chat_user->email) .' </p>
						  <span class="meta"></span>
						</div>
					  </div>				
				</div>							  
			  </li>';
		  } 
	  }
	  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	  $unread_counts = $wpdb->get_results(
        "SELECT user_id, COUNT(*) as unread_count
         FROM {$wpdb->prefix}chat_messages
         WHERE read_status = 0 AND sender = 'user'
         GROUP BY user_id",
        ARRAY_A
    );
	wp_send_json_success(array( "html" => $html ,'unread_counts' => $unread_counts ));
}
add_action('wp_ajax_wpchat_admin_fetch_new_chat', 'wpchat_admin_fetch_new_chat');
add_action('wp_ajax_nopriv_wpchat_admin_fetch_new_chat', 'wpchat_admin_fetch_new_chat');

function wpchat_admin_get_online_users() {
    check_ajax_referer('wpchat_nonce', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(__('Unauthorized', 'chat-message'));
    }
    global $wpdb;

	$online_users = wp_cache_get( 'wpchat_online_users', 'wpchat' );
	if ( false === $online_users ) {
		$threshold = current_time('timestamp') - (5 * MINUTE_IN_SECONDS);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$online_users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, name, email FROM {$wpdb->prefix}chat_users WHERE last_activity >= %s",
				wpchat_date('Y-m-d H:i:s', $threshold)
			)
		);
		wp_cache_set( 'wpchat_online_users', $online_users, 'wpchat', 30 ); // Cache for 30 seconds
	}

	wp_send_json_success($online_users);
}
add_action('wp_ajax_wpchat_admin_get_online_users', 'wpchat_admin_get_online_users');
add_action('wp_ajax_nopriv_wpchat_admin_get_online_users', 'wpchat_admin_get_online_users');

function wpchat_admin_upload_chat_image(){
    check_ajax_referer('wpchat_nonce', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(__('Unauthorized', 'chat-message'));
    }
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$wp_chat_id = isset($_POST['wp_chat_id']) ? absint(wp_unslash($_POST['wp_chat_id'])) : 0;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$last_message_id = isset($_POST['last_message_id']) ? absint(wp_unslash($_POST['last_message_id'])) : 0;
	if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
        wp_send_json_error(__('No image file uploaded.', 'chat-message'));
    }
	
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error(__('Only image files are allowed (JPEG, PNG, GIF).', 'chat-message'));
    }
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
    $upload = wp_handle_upload($file, ['test_form' => false]);
    if (isset($upload['error'])) {
        wp_send_json_error($upload['error']);
    }
	$image_url = $upload['url']; 
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->insert(
        "{$wpdb->prefix}chat_messages",
        [
            'user_id'   => $wp_chat_id,
            'sender'    => 'admin',
            'message'   => '',
            'image'     => $image_url,
            'sent_at'   => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s', '%s']
    );
	wp_cache_delete( 'wpchat_last_msg_id_' . $wp_chat_id, 'wpchat' );
	$data = wpchat_admin_handle_get_messages($last_message_id, $wp_chat_id);
	wp_send_json_success($data); 
}
add_action('wp_ajax_wpchat_admin_upload_chat_image', 'wpchat_admin_upload_chat_image');
add_action('wp_ajax_nopriv_wpchat_admin_upload_chat_image', 'wpchat_admin_upload_chat_image');
 
function wpchat_admin_block_user(){
    check_ajax_referer('wpchat_nonce', 'nonce');
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error(__('Unauthorized', 'chat-message'));
    }
	 global $wpdb;
	if( isset( $_POST["wp_chat_id"]  ) && !empty( $_POST["wp_chat_id"] ) ){
		$wp_chat_id = absint(wp_unslash($_POST["wp_chat_id"]));
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %d", $wp_chat_id));
		if ($user) {
			$block = ($user->block == 0) ? 1 : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update($wpdb->prefix . 'chat_users', ['block' => $block] , ['id' => $wp_chat_id]);	
			wp_cache_delete( 'wpchat_admin_users', 'wpchat' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %d", $wp_chat_id));
			wp_send_json_success($user); 
		} else {
			wp_send_json_error(__('User not found.', 'chat-message'));
		}
	} else {
		wp_send_json_error(__('Invalid User ID.', 'chat-message'));
	}
}
add_action('wp_ajax_wpchat_admin_block_user', 'wpchat_admin_block_user');
add_action('wp_ajax_nopriv_wpchat_admin_block_user', 'wpchat_admin_block_user');
