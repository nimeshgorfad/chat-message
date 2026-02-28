<?php 
/**
 * Plugin Name: Chat message
 * Description:   Chat on any WordPress website.
 * Version: 1.1.0
 * Author: Nimesh Gorfad
 * Author URI: https://www.upwork.com/fl/~01332645bee1d0c42d?mp_source=share
 * Text Domain: wpchat  
 */
 
 /** definded plugin version */

defined('WPCHAT_VERSION') || define('WPCHAT_VERSION', '1.1.0');   

 /* Add Css and JS */ 
 function enqueue_chat_scripts() {
	 
    wp_enqueue_style('chat-bundle0ae1', plugins_url('assets/css/bundle0ae1.css', __FILE__)); 
    wp_enqueue_style('chat-app0ae1', plugins_url('assets/css/app0ae1.css', __FILE__)); 
	
	 
    wp_enqueue_script('chat-bundle0ae1', plugins_url('assets/js/bundle0ae1.js', __FILE__), array('jquery'), null, true);
	
	
    wp_enqueue_script('chat-app0ae1', plugins_url('assets/js/app0ae1.js', __FILE__), array('jquery'), null, true);
	
    wp_enqueue_script('chat-script', plugins_url('assets/js/chat.js', __FILE__), array('jquery'), WPCHAT_VERSION, true);
	
    wp_localize_script('chat-script', 'chatAjax', array(
        'nkg'=>'123',
        'ajaxurl' => admin_url('admin-ajax.php'),
        'ajax_url'=> admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpchat_nonce')
    ));
	
}

add_action('wp_enqueue_scripts', 'enqueue_chat_scripts',100);

function enqueue_admin_chat_scripts(){
	
	 wp_enqueue_style(
        'admin-chat-bundle',
        plugins_url('assets/css/bundle0ae1.css', __FILE__)
    ); 
	
	wp_enqueue_style(
        'admin-chat-app0ae1',
        plugins_url('assets/css/app0ae1.css', __FILE__)
    );
	
	
	 wp_enqueue_script(
		'admin-chat-bundle0ae1',
		plugins_url('assets/js/bundle0ae1.js', __FILE__), 
		array('jquery'), 
		null, 
		true
	);
	 
	 wp_enqueue_script(
		 'admin-chat-app0ae1', 
		 plugins_url('assets/js/app0ae1.js', __FILE__), 
		 array('jquery'), 
		 null, 
		 true
	 );
	
    wp_enqueue_script(
		'admin-chat-script', 
		plugins_url('assets/js/admin-chat.js', __FILE__), 
		array('jquery'), 
		null, 
		true
	);
	
	
	 wp_localize_script(
		'admin-chat-script', 
		'chatAjax', 
		array('ajaxurl' => admin_url('admin-ajax.php'),
		'ajax_url'=> admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wpchat_nonce'))
     
		);
	 
	
 
}



function load_admin_js_css(){	
	add_action('admin_enqueue_scripts', 'enqueue_admin_chat_scripts'); 
}
 
// Hook for plugin activation
register_activation_hook(__FILE__, 'chat_plugin_activate');


function wpchat_startSession() {
    if(!session_id()) {
        session_start();
    } 	
}

add_action('init', 'wpchat_startSession', 1);


function chat_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Table for chat users
    $table_users = $wpdb->prefix . 'chat_users';
    $sql_users = "CREATE TABLE $table_users (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        name VARCHAR(100) NOT NULL,
        otp VARCHAR(10) DEFAULT NULL,
        verified TINYINT(1) DEFAULT 0,
        block TINYINT(1) DEFAULT 0,
		last_activity DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // Table for chat messages
    $table_messages = $wpdb->prefix . 'chat_messages';
    $sql_messages = "CREATE TABLE $table_messages (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        sender ENUM('user', 'admin') NOT NULL,
        message TEXT DEFAULT NULL,
        image TEXT DEFAULT NULL,
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		read_status TINYINT(1) DEFAULT 0,  
        FOREIGN KEY (user_id) REFERENCES $table_users(id) ON DELETE CASCADE
    ) $charset_collate;";
 
    // Include WordPress upgrade functions
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create or update tables
    dbDelta($sql_users);
    dbDelta($sql_messages);
}

 
function chat_box_shortcode() {
	 $last_chat_id = 0;
	 if (isset( $_COOKIE["wp_chat_id"] )){
		 global $wpdb;
		 
		 $wp_chat_id = $_COOKIE["wp_chat_id"];					  
		 $_SESSION["wp_chat_id"] = $wp_chat_id;				  
		
		$wpdb->update($wpdb->prefix . 'chat_users', ['last_activity' => current_time('mysql') ], ['id' => $wp_chat_id] );			
	 
		$messages = $wpdb->get_results($wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}chat_messages WHERE  user_id = %d   ORDER BY id DESC limit 1", 
				 $wp_chat_id, 
			)); 
			
		if( !empty( $messages ) ){
			$last_chat_id = $messages[0]->id;			
		}
		
			
		 
	 }
	 
	$welcome_message = get_option('welcome_message', '');
	$chat_logo_id = get_option('chat_logo');
    $chat_logo_url = $chat_logo_id ? wp_get_attachment_url($chat_logo_id) : '';
	 
	ob_start();
	
	
	?>
    <div class="tyn-quick-chat" id="tynQuickChat">
        <button class="tyn-quick-chat-toggle js-toggle-quick"  id="stat_chat_nkg">
		<?php 
		if( !empty($chat_logo_url) ){
			echo '<img src="'.$chat_logo_url.'" > ';
		}else{
			?>
			
			
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
		  
		  <?php 
		}
		?>
          <span class="badge bg-primary top-0 end-0 position-absolute rounded-pill new_msg_count ">0</span></button
        ><!-- .tyn-quick-chat-toggle -->
        <div class="tyn-quick-chat-box">	
			<?php 
			 
			/* ?>
          <div class="tyn-quick-chat-head">
            <div class="tyn-media-group">
              <div class="tyn-media tyn-size-rg">
                <img src="images/avatar/1.jpg" alt="" />
              </div>
              <!-- .tyn-media -->
			  
              <div class="tyn-media-col">
                <div class="tyn-media-row">
                  <h6 class="name">Jasmine Thompson</h6>
                </div>
                <div class="tyn-media-row has-dot-sap">
                  <span class="meta">Active</span>
                </div>
              </div>
			
              <!-- .tyn-media-col -->
            </div>
            <!-- .tyn-media-group -->
          </div>
		  
		    <?php */ ?>
          <!-- .tyn-quick-chat-head -->
          <div class="tyn-quick-chat-reply js-scroll-to-end">
            <div class="tyn-reply tyn-reply-quick" id="tynQuickReply">
				<div id="verify_wrap" style="<?php echo (isset( $_SESSION["wp_chat_id"] )) ? 'display:none;' :''; ?> " > 
					<h2 class="letsChat">
						Let's chat
					</h2>
					<p class="wellcomeMessage">
						<?php 
							echo $welcome_message;
						?>
					</p>
					 <div id="user-info">
           <input type="text" id="chat-name" placeholder="Your Name" required>

						<input type="email" id="chat-email" placeholder="Your Email" required>
						
						
						<button id="send-otp">Get Code</button>
					</div>
					<div id="otp-verification" style="display:none;">
						<input type="text" id="chat-otp" placeholder="Enter Code">
						<button id="verify-otp">Verify</button>
					</div>
					
				</div>
				 
			   
			  <span id="chat-messages">
			  </span>
              <!-- .tyn-reply-item -->
            </div>
            <!-- .tyn-reply -->
          </div>
          <!-- .tyn-quick-chat-reply -->
          <div class="tyn-quick-chat-form" id="chat-window" style="<?php echo (isset( $_SESSION["wp_chat_id"] )) ? '' :'display:none;'; ?>"   > 
            <div   contenteditable></div>
			
			<input type="hidden" id="nkg_is_chat_start" value="<?php echo (isset( $_SESSION["wp_chat_id"] )) ? 'yes' :'no'; ?>"  >
		 
			<textarea class="tyn-chat-form-input bg-light" id="chat-message" ></textarea>
			
            <ul class="tyn-list-inline me-n2 my-1">
              <li>
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
              <li>
                <button class="btn btn-icon btn-white btn-sm btn-pill" id="send-message" data-last="<?php echo $last_chat_id; ?>" >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                    <path
                      d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"
                    /></svg
                  ><!-- send-fill -->
                </button>
              </li>
            </ul>
            <!-- .tyn-list-inline -->
          </div>
          <!-- .tyn-quick-chat-form -->
          <button class="btn btn-danger btn-sm btn-icon top-0 end-0 position-absolute rounded-pill translate-middle js-toggle-quick" id="end_chat_nkg" >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
              <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" /></svg
            ><!-- x-lg -->
          </button>
        </div>
        <!-- .tyn-quick-chat-box -->
      </div>
	
	<?php  
	return ob_get_clean();	
        
}         
add_shortcode( 'chat_box', 'chat_box_shortcode' );


include("chat-admin.php");
include("chat-ajax.php");


?>