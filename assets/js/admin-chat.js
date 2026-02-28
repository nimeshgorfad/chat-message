jQuery(document).ready(function($){
	
	var chatget = null; 
	var activeRequest  = false; 
	
	jQuery(".chat_list").click(function(){
		
		jQuery(".chat_list").removeClass('active');
		jQuery(this).addClass('active');
		
		//jQuery("#tynReply").html("");
		 var start_c_name = jQuery(this).find('.name').text();
		jQuery(".start_c_name").html(start_c_name);
		jQuery("#chat_images_div").html('');
		jQuery("#chat-messages").html("");
		var chat_id = jQuery(this).attr("data-id");
		var block = jQuery(this).attr("data-block");
		jQuery("#send-message-admin").attr("data-chat_id", chat_id);		
		jQuery("#unread_count_"+chat_id).html("");		
		jQuery("#send-message-admin").attr("data-last", 0);	
		
		if( block  == 1){
			
			jQuery(".muted-icon").show();
			jQuery(".unmuted-icon").hide();
			
		}else{
			jQuery(".unmuted-icon").show();
			jQuery(".muted-icon").hide();						
		}
		
		
		if (chatget != null){
			chatget.abort();			
		}

		fetchNewMessages(0,chat_id);
		 
		
	});	
	 
	$(window).on('load', function() {
	   
	  $('.chat_list').first().click(); 
	});

	 setInterval(function() {
		var lastMessageId = jQuery("#send-message-admin").attr("data-last");
		var chat_id = jQuery("#send-message-admin").attr("data-chat_id");
		
		//console.log( "activeRequest == " + activeRequest );
		if(  chat_id > 0 && activeRequest){
			
			 fetchNewMessages(lastMessageId, chat_id);			 
			
		}
	  }, 10000);
		  
	 // Send Message
    $("#send-message-admin").on("click", function () {
		
       // const message 	= jQuery("#chat-message").val();
        var message 	= jQuery("#tynChatInput").text();
		var chat_id		= jQuery("#send-message-admin").attr("data-chat_id");
		var last_message_id		= jQuery("#send-message-admin").attr("data-last");
		 
        if ( 0 == chat_id ) {
            alert("Please select a user.");
            return;
        }  
		
		if ( !message ) {
            alert("Please enter a message.");
            return;
        } 

		chatget.abort();
		
        $.ajax({
            url: chatAjax.ajaxurl,
            method: "POST",
			dataType: "json",   
            data: {
                action: "admin_send_message", 
                message: message,
                wp_chat_id: chat_id,
                last_message_id: last_message_id,
            },
            success: function (response) {
                if (response.success) {
                    $("#tynChatInput").text("");
					
					activeRequest = true;	 			
					
					jQuery('#chat-messages').append(response.data.html);
					lastMessageId = response.data.last_message_id;					
					jQuery("#send-message-admin").attr("data-last", lastMessageId );
					
                    //loadMessages();
                } else {
                    alert(response.data || "Failed to send message.");
                }
            },
        });    
		
		
    });
	
	$('#tynChatInput').keydown(function(event){ 
		 
		if ( event.key === "Enter" && !event.shiftKey ) {
			event.preventDefault()
			$('#send-message-admin').trigger('click');
		}
	}); 

	// Image add 
	jQuery("#but_chat_file").click(function(){
		
		jQuery("#tynChatfileInput").trigger("click");
		
	});
	
	
	jQuery("#tynChatfileInput").on("change", function () {
		
		const imageInput = this.files[0];

		if (!imageInput) {
			alert("Please select an image.");
			return;
		}

		// Validate file type (optional)
		const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
		if (!allowedTypes.includes(imageInput.type)) {
			alert("Only JPEG, PNG, and GIF images are allowed.");
			return;
		}

		// Validate file size (optional, e.g., max 2MB)
		const maxSize = 2 * 1024 * 1024; // 2MB in bytes
		if (imageInput.size > maxSize) {
			alert("Image size exceeds 2MB.");
			return;
		}

		var chat_id		= jQuery("#send-message-admin").attr("data-chat_id");
		var last_message_id		= jQuery("#send-message-admin").attr("data-last");
		 
        if ( 0 == chat_id ) {
            alert("Please select a user.");
            return;
        } 
		
		const formData = new FormData();
		formData.append("action", "admin_upload_chat_image");
		formData.append("image", imageInput);
		 
		formData.append("sender", "user"); // Set sender (user or admin)
		formData.append("last_message_id", last_message_id);  
		formData.append("wp_chat_id", chat_id);  
		 
		chatget.abort();

		$.ajax({
			url: ajaxurl, // WordPress AJAX URL
			method: "POST",
			data: formData,
			contentType: false,
			processData: false,
			success: function (response) {
				
				  jQuery('#chat-messages').append(response.data.html);					 				
					lastMessageId = response.data.last_message_id;					
					jQuery("#send-message-admin").attr("data-last", lastMessageId );
					if( response.data.images != "" ){
						jQuery("#chat_images_div").append( response.data.images );
					}
					//console.log( ' ajsx ' +  lastMessageId );
					
					activeRequest  = true;
				 
			},
			error: function () {
				//alert("Error while uploading the image.");
			},
		});
		
	});
	
	
	function fetchNewMessages(lastMessageId,chat_id) {
		 
        chatget = $.ajax({
            url: chatAjax.ajaxurl,
            method: 'POST',
			dataType: "json",   
            data: {
                action: 'admin_fetch_new_messages', 
                last_message_id: lastMessageId,
                wp_chat_id: chat_id 
            },
            success: function(response) {
                if (response) {
					
					
                    jQuery('#chat-messages').append(response.data.html);
					 				
					lastMessageId = response.data.last_message_id;					
					jQuery("#send-message-admin").attr("data-last", lastMessageId );
					
					if( response.data.images != "" ){
						jQuery("#chat_images_div").append( response.data.images );
					}
					
					activeRequest  = true;
					 
                }
				
            }
        });
    }
	 
	 
	// Search chat User
	 jQuery('#search_chat_user').on('input', function() {
		 
		var searchText = jQuery(this).val().toLowerCase();
		
		jQuery('.chat_list').each(function() {
			
			//var text = $(this).text().toLowerCase();
			
			var text_a = jQuery(this).find('.name').text().toLowerCase();
			var text_b = jQuery(this).find('.content').text().toLowerCase();
			
			if ( text_a.includes(searchText) || text_b.includes(searchText) ) {
				$(this).removeClass('hidden');
			} else {
				$(this).addClass('hidden');
			}
			
		});
		
	});
	
	// Get new chat user every 15 second 
	setInterval(function() {
		
		var last_chatid = $('.chat_list').first().attr('data-id');
		  
		 $.ajax({
            url: chatAjax.ajaxurl,
            method: 'POST',
			dataType: "json",   
            data: {
                action: 'admin_fetch_new_chat',  
                last_chat_id: last_chatid 
            },
            success: function(response) {
                
				if (response) {
									
                    jQuery('#chat_list_ul').prepend(response.data.html); 	//jQuery('#chat_list_ul').append(response.data.html);

				 	// unread_counts
					
					jQuery.each( response.data.unread_counts ,function(key, udata){			
						jQuery("#unread_count_"+udata.user_id).html("("+ udata.unread_count +")");
					});
						
					
                }
				
            }
        });
		
		
		
	},15000);		
		
	// Get Live user every minute	
	
	setInterval(function() {
		
	 
		 $.ajax({
            url: chatAjax.ajaxurl,
            method: 'POST',
			dataType: "json",   
            data: {
                action: 'admin_get_online_users',  
            },
            success: function(response) {
                
				if (response) { 
					jQuery(".nkg_active_div").removeClass('active');				
                    console.log( response.data );
					if(  response.data ){
						jQuery.each( response.data ,function(key, udata){
							
							jQuery("#active_"+udata.id).addClass("active");
						});
					}
					
                }
				
            }
        });
		
		
		
	},60000);
	
	
	// Block unblock user 
	
	jQuery(".chat_block").click(function(){
		
		
		var chat_id		= jQuery("#send-message-admin").attr("data-chat_id");
		//var block		= jQuery("this").attr("data-block");
		 
        if ( 0 == chat_id ) {
            alert("Please select a user.");
            return;
        }  
		  
        $.ajax({
            url: chatAjax.ajaxurl,
            method: "POST",
			dataType: "json",   
            data: {
                action: "admin_block_user",  
                wp_chat_id: chat_id,
            },
            success: function (response) {
                if (response.success) {
                   
					if( response.data.block  == 1){
						jQuery(".muted-icon").show();
						jQuery(".unmuted-icon").hide();
						jQuery("#chat_id_"+chat_id).attr("data-block",1);	
					}else{
						jQuery(".unmuted-icon").show();
						jQuery(".muted-icon").hide();	
						jQuery("#chat_id_"+chat_id).attr("data-block",0);	
								
					}
                    //loadMessages();
                } else {
                    alert(response.data || "Failed to send message.");
                }
            },
        });  
		
		
	});

	
})