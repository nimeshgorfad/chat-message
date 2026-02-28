<?php 
function wpchat_send_otp(){		
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;		
	$email = sanitize_email($_POST['email']);    
	$name = sanitize_text_field($_POST['name']);		
	$otp = rand(100000, 999999);		
	wp_mail($email, 'Your Code', "Your Code is $otp");	

	$data = array( 'name' => $name, 'otp' => $otp,        'verified' => 0  );
	
	///$wpdb->replace($wpdb->prefix . 'chat_users', $data);
	
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE email = %s", $email));	    
	if ($user) {
		 
		$wpdb->update($wpdb->prefix . 'chat_users', $data , ['email' => $email]);	
		
		//wp_send_json_success();		    
	} else {	
			
		$data['email'] = $email;
	
		$wpdb->insert($wpdb->prefix . 'chat_users', $data );	
		
		     
	}
	
		
	wp_send_json_success();    
	die();
}
add_action('wp_ajax_send_otp', 'wpchat_send_otp');
add_action('wp_ajax_nopriv_send_otp', 'wpchat_send_otp');

function wpchat_verify_otp() {	  
  
	check_ajax_referer('wpchat_nonce', 'nonce');
	global $wpdb;    
	
	session_start();


	$email = sanitize_email($_POST['email']);    
	
	$otp = sanitize_text_field($_POST['otp']);    
	
	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE email = %s AND otp = %s", $email, $otp));	
	//$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE email = %s ", $email));	
    
	if ($user) {	

		$_SESSION["wp_chat_id"] = $user->id;
		
		setcookie('wp_chat_id', $user->id, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);


		
		$wpdb->update($wpdb->prefix . 'chat_users', ['verified' => 1,'last_activity' => current_time('mysql') ], ['email' => $email] );	
		
		//wp_send_json_success($user);	
		
		
		$data = handle_get_messages(0); 
		wp_send_json_success($data);	
			
	    
	} else {		  
	
		wp_send_json_error('Invalid OTP');	
	    
	}
}

add_action('wp_ajax_verify_otp', 'wpchat_verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'wpchat_verify_otp'); 



// AJAX handler for Online user 
function update_online_users(){
	
	check_ajax_referer('wpchat_nonce', 'nonce');
	if( isset( $_SESSION["wp_chat_id"]  ) ){
		
		$wp_chat_id = $_SESSION["wp_chat_id"];
		
		$wpdb->update($wpdb->prefix . 'chat_users', ['last_activity' => current_time('mysql') ], ['id' => $wp_chat_id] );	
		
		
	}
	
	wp_send_json_success();
}
add_action('wp_ajax_update_online_users', 'update_online_users');
add_action('wp_ajax_nopriv_update_online_users', 'update_online_users'); 

// AJAX handler for sending messages
add_action('wp_ajax_send_message', 'handle_send_message');
add_action('wp_ajax_nopriv_send_message', 'handle_send_message');

function handle_send_message() {
    check_ajax_referer('wpchat_nonce', 'nonce');
    global $wpdb;
	//session_start();
	if( isset( $_SESSION["wp_chat_id"]  ) ){
		
		$wp_chat_id = $_SESSION["wp_chat_id"] ;
		
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %s and block ='1' ", $wp_chat_id));
		 
		if($user){
			wp_send_json_error('You are blocked by administrator .');			
		}
		
		$message =  sanitize_text_field( $_POST['message'] );  
		$sender = 'user' ;
		
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
			
		 $wpdb->update($wpdb->prefix . 'chat_users', ['last_activity' => current_time('mysql') ], ['id' => $wp_chat_id] );	
			
	
		if ($result) {
			
			$last_message_id = $_POST['last_message_id'];
			$data = handle_get_messages($last_message_id); 
			
			wp_send_json_success($data);
			
		} else {
			wp_send_json_error('Failed to send message.');
		}
		
		
	}else{
		
		wp_send_json_error('Please verify your email address');	
	}
	
}


function user_upload_chat_image(){
	
	check_ajax_referer('wpchat_nonce', 'nonce');
	if( isset( $_SESSION["wp_chat_id"]  ) ){
		$wp_chat_id = $_SESSION['wp_chat_id'];
		$last_message_id = $_POST['last_message_id'];
	
		if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
			wp_send_json_error('No image file uploaded.');
		}
		
		$file = $_FILES['image'];

		// Validate file type
		$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
		if (!in_array($file['type'], $allowed_types)) {
			wp_send_json_error('Only image files are allowed (JPEG, PNG, GIF).');
		}
		
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		// Handle the upload
		$upload = wp_handle_upload($file, ['test_form' => false]);

		if (isset($upload['error'])) {
			wp_send_json_error($upload['error']);
		}
		
		$image_url = $upload['url']; // URL of the uploaded image
		
		 global $wpdb;
			
		$result = $wpdb->insert(
			"{$wpdb->prefix}chat_messages",
			[
				'user_id'   => $wp_chat_id,
				'sender'    => 'user',
				'message'   => '',
				'image' => $image_url,
				'sent_at'   => current_time('mysql')
			],
			['%d', '%s', '%s', '%s', '%s']
		);
			 
		
		$last_message_id = $_POST['last_message_id'];
		$data = handle_get_messages($last_message_id);  
		
		wp_send_json_success($data); 
	
	}
	
}
add_action('wp_ajax_user_upload_chat_image', 'user_upload_chat_image');
add_action('wp_ajax_nopriv_user_upload_chat_image', 'user_upload_chat_image');


// AJAX handler for retrieving messages

function handle_get_messages($last_message_id = 0) {
    global $wpdb;
	$html = "";
	if( isset( $_SESSION["wp_chat_id"]  ) ){
	
		$wp_chat_id = $_SESSION["wp_chat_id"];		 
		
		//var_dump( $last_message_id );
		
		if( $last_message_id > 0){
			
			$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}chat_messages WHERE  id > %d and user_id = %d ORDER BY id DESC", 
				$last_message_id , $wp_chat_id
			));
			
			//var_dump($messages );
			
			
		}else{
			
			$messages = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_messages WHERE user_id = %d ORDER BY id DESC LIMIT 1000",
					$wp_chat_id
				));
				
			 
		}
		

		if( !empty( $messages ) ){
			
			$messages = array_reverse( $messages );
			//$last_message_id = $messages[0]->id;
			
			foreach($messages as $da){
				$last_message_id = $da->id;
				
				$date_time = '<p>' .wp_date("M d, Y, H:i A",strtotime($da->sent_at)). ' </p>';
				
				$img_html = "";
				
				$reply_text = '<div class="tyn-reply-text">' . $da->message . ''. $date_time .'</div>';
				
				if( !empty( $da->image ) ){
					$reply_text = '<div class="tyn-reply-media" > <img src="'.$da->image.'" class="tyn-image" alt=""> '. $date_time .' </div>'; 
				}
				
				
				if( 'admin' == $da->sender ){
					
					$html .= '<div class="tyn-reply-item incoming">
					<div class="tyn-reply-avatar">
					  <div class="tyn-media tyn-size-md tyn-circle">
						<img src="'.plugins_url('images/avatar/2.jpg', __FILE__).'" alt="" />
					  </div>
					</div> 
					<div class="tyn-reply-group">
					  <div class="tyn-reply-bubble">
						 '.$reply_text.'
					  </div>                   
					</div> 
				  </div>';
					
				}else{
					
					$html .= '<div class="tyn-reply-item outgoing">
						<div class="tyn-reply-group">
						  <div class="tyn-reply-bubble">
							 '.$reply_text.'
						  </div> 
						</div> 
					  </div>';
							
				}
				
			 
			}
			
		}
		
		/* echo $html;
		
		print_r($messages);	 */

		
	
	
	}else{
		
		 
	}
	
	return array("html"=>$html,'last_message_id'=>$last_message_id); 
}

// 
// AJAX handler for Fetch new messages

function fetch_new_messages(){
	check_ajax_referer('wpchat_nonce', 'nonce');
	$last_message_id = $_POST['last_message_id'];
	$data = handle_get_messages($last_message_id);
	 
	wp_send_json_success($data);
}
add_action('wp_ajax_fetch_new_messages', 'fetch_new_messages');
add_action('wp_ajax_nopriv_fetch_new_messages', 'fetch_new_messages');


function fetch_new_messages_count(){
	 check_ajax_referer('wpchat_nonce', 'nonce');
	 global $wpdb;
	$total = 0;
	if( isset( $_SESSION["wp_chat_id"]  ) ){
		$wp_chat_id = $_SESSION["wp_chat_id"];		
		$last_message_id = $_POST["last_message_id"];		
	 
		 $messages = $wpdb->get_results($wpdb->prepare(
				"SELECT count(*) as total_msg FROM {$wpdb->prefix}chat_messages WHERE  id > %d and user_id = %d and sender = %s", 
				$last_message_id, $wp_chat_id, 'admin'
			)); 
		 
		 
		$total = $messages[0]->total_msg;
	}
	
	wp_send_json_success( array( "count" =>  $total ) );
}
add_action('wp_ajax_fetch_new_messages_count', 'fetch_new_messages_count');
add_action('wp_ajax_nopriv_fetch_new_messages_count', 'fetch_new_messages_count');


/**** Admin ****/
// AJAX handler for sending messages
add_action('wp_ajax_admin_send_message', 'handle_admin_send_message');
add_action('wp_ajax_nopriv_admin_send_message', 'handle_admin_send_message');

function handle_admin_send_message() {
    global $wpdb;
	//session_start();
	if( isset( $_POST["wp_chat_id"]  ) && !empty( $_POST["wp_chat_id"] ) ){
		
		$wp_chat_id = $_POST["wp_chat_id"] ;
		$message =  sanitize_text_field( $_POST['message'] );  
		$sender = 'admin' ;
		
		$data = array(
					'user_id' => $wp_chat_id ,
					'sender'  => 'admin',
					'message' => $message,
					'sent_at' => current_time('mysql')
					);
					
		  $result = $wpdb->insert(
				"{$wpdb->prefix}chat_messages",
				$data,
				['%d', '%s', '%s', '%s']
			);
	
		if ($result) {
			
			$last_message_id = $_POST['last_message_id'];
			$data = admin_handle_get_messages($last_message_id);
	
			wp_send_json_success($data);
		} else {
			wp_send_json_error('Failed to send message.');
		}
		
		
	}else{
		
		wp_send_json_error('Please select user');	
	}
	
}

function admin_handle_get_messages($last_message_id = 0) {
    global $wpdb;
	$html = "";
	$images = "";
	if( isset( $_POST["wp_chat_id"]  ) ){
	
		$wp_chat_id = $_POST["wp_chat_id"];		 
		 
		if( $last_message_id > 0){
			
			$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}chat_messages WHERE  id > %d and user_id = %d ORDER BY id DESC", 
				$last_message_id , $wp_chat_id
			));
			
			//var_dump($messages );
			
			
		}else{
			
			$messages = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_messages WHERE user_id = %d ORDER BY id DESC LIMIT 1000",
					$wp_chat_id
				));
				
			 
		}
		
		// update un read message
		$wpdb->update(
            "{$wpdb->prefix}chat_messages",
            ['read_status' => 1],
            ['user_id' => $wp_chat_id, 'read_status' => 0],
            ['%d'],
            ['%d', '%d']
        );
		
		
		if( !empty( $messages ) ){
			
			$messages = array_reverse( $messages );
			//$last_message_id = $messages[0]->id;
			
			foreach($messages as $da){
				$last_message_id = $da->id;
				
				$img_html ="";
				$date_time ='<p>' .wp_date("M d, Y, H:i A",strtotime($da->sent_at)). ' </p>';
				
				$reply_text = '<div class="tyn-reply-text">' . $da->message . ''.$date_time.'</div>';
				
				if( !empty( $da->image ) ){
					$reply_text = '<div class="tyn-reply-media" > <img src="'.$da->image.'" class="tyn-image" alt=""> '.$date_time.' </div>';

					$images .='<div class="col-4" > <img src="'.$da->image.'" class="tyn-image" alt="">  </div>';	
					$date_time ='';
				} 
					
				
				if( 'admin' == $da->sender ){
 				
					
					$html .= '<div class="tyn-reply-item outgoing">
						<div class="tyn-reply-group">
						  <div class="tyn-reply-bubble">
							 '.$reply_text.'					 
						  </div> 
						</div> 
					  </div>';
					
				}else{
					
					
					$html .= '<div class="tyn-reply-item incoming">
					<div class="tyn-reply-group">
					  <div class="tyn-reply-bubble">
						  '.$reply_text.'						 
					  </div>                   
					</div> 
				  </div>';
							
				}			 
			}
			
		}
		 
	} 
	 	
	return array("html"=>$html,'last_message_id'=>$last_message_id,'images'=>$images); 
}


function admin_fetch_new_messages(){
	$last_message_id = $_POST['last_message_id'];
	$data = admin_handle_get_messages($last_message_id);
	 
	wp_send_json_success($data);
}
add_action('wp_ajax_admin_fetch_new_messages', 'admin_fetch_new_messages');
add_action('wp_ajax_nopriv_admin_fetch_new_messages', 'admin_fetch_new_messages');

function admin_fetch_new_chat(){
	
	global $wpdb;
	
	$last_chat_id = $_POST['last_chat_id'];
	
	$chat_users = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}chat_users where id > $last_chat_id ORDER BY id DESC"));
					
	$html = "";			
	
	if( !empty( $chat_users )){
		
		  foreach( $chat_users as $chat_user){
			   
			 $html .= '<li class="tyn-aside-item js-toggle-main chat_list" data-id="'.$chat_user->id .'" data-block="'.$chat_user->block.'" id="chat_id_'.$chat_user->id.'"  >
				<div class="tyn-media-group">
					<div class="tyn-media tyn-size-lg">
						<img src="https://gowibble.com/wp-content/plugins/chat-message/images/avatar/1.jpg" alt="" />
					 </div>
					 
					 <div class="tyn-media-col">
						<div class="tyn-media-row">
						  <h6 class="name"> ' . $chat_user->name .' </h6>
						  <span class="typing"></span>
						</div>
						<div class="tyn-media-row has-dot-sap">
						  <p class="content"> '. $chat_user->email .' </p>
						  <span class="meta"></span>
						</div>
					  </div>				
				</div>							  
			  </li>';
			   
						  
		  } 
	  }
	  
	  
	  $unread_counts = $wpdb->get_results(
        "SELECT user_id, COUNT(*) as unread_count
         FROM {$wpdb->prefix}chat_messages
         WHERE read_status = 0 and sender = 'user'
         GROUP BY user_id",
        ARRAY_A
    );

 
					  
	wp_send_json_success(array( "html" => $html ,'unread_counts' => $unread_counts ));
}
add_action('wp_ajax_admin_fetch_new_chat', 'admin_fetch_new_chat');
add_action('wp_ajax_nopriv_admin_fetch_new_chat', 'admin_fetch_new_chat');




function admin_get_online_users() {
    global $wpdb;

    // Define the online threshold (e.g., last 5 minutes)
  //  $threshold = current_time('mysql', 1) - 5 * MINUTE_IN_SECONDS;
    $threshold = strtotime("-5 minutes");
	 
    $online_users = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name, email FROM {$wpdb->prefix}chat_users WHERE last_activity >= %s",
            date('Y-m-d H:i:s', $threshold)
        )
    );
	
	 
	wp_send_json_success($online_users);
   
}

add_action('wp_ajax_admin_get_online_users', 'admin_get_online_users');
add_action('wp_ajax_nopriv_admin_get_online_users', 'admin_get_online_users');

function admin_upload_chat_image(){
	
	$wp_chat_id = $_POST['wp_chat_id'];
	$last_message_id = $_POST['last_message_id'];
	
	if (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
        wp_send_json_error('No image file uploaded.');
    }
	
	$file = $_FILES['image'];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error('Only image files are allowed (JPEG, PNG, GIF).');
    }
	
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	// Handle the upload
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) {
        wp_send_json_error($upload['error']);
    }
	
	$image_url = $upload['url']; // URL of the uploaded image
	
	 global $wpdb;
    	
	$result = $wpdb->insert(
        "{$wpdb->prefix}chat_messages",
        [
            'user_id'   => $wp_chat_id,
            'sender'    => 'admin',
            'message'   => '',
            'image' => $image_url,
            'sent_at'   => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s', '%s']
    );
	
	 
	
	$data = admin_handle_get_messages($last_message_id);
	
	wp_send_json_success($data); 
}
add_action('wp_ajax_admin_upload_chat_image', 'admin_upload_chat_image');
add_action('wp_ajax_nopriv_admin_upload_chat_image', 'admin_upload_chat_image');
 
 
 
function admin_block_user(){

	 global $wpdb;
	//session_start();
	if( isset( $_POST["wp_chat_id"]  ) && !empty( $_POST["wp_chat_id"] ) ){
		$wp_chat_id = $_POST["wp_chat_id"];
		
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %s", $wp_chat_id));
		
		$block = 0;
		if($user->block == 0 ){
			$block = 1;
		}
		/* var_dump($user->block);
		var_dump($block); */  
		 
		$wpdb->update($wpdb->prefix . 'chat_users', ['block' => $block] , ['id' => $wp_chat_id]);	
		
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_users WHERE id = %s", $wp_chat_id));
		
		wp_send_json_success($user); 
	
	}

}
add_action('wp_ajax_admin_block_user', 'admin_block_user');
add_action('wp_ajax_nopriv_admin_block_user', 'admin_block_user');
