jQuery(document).ready(function ($) {
	 
	var lastMessageId = 0;
	  
    let userEmail = "";
    let userName = "";

	var chatget = null; 
	var activeRequest  = false; 
	
	
    // Send OTP
    $("#send-otp").on("click", function () {
        userEmail = $("#chat-email").val();
        userName = $("#chat-name").val();

        if (!userEmail || !userName) {
            alert("Please enter your email and name.");
            return;
        }

        $.ajax({
            url: chatAjax.ajaxurl, // Provided by WordPress
            method: "POST",
			dataType: "json",   
            data: {
                action: "send_otp",
                email: userEmail,
                name: userName,
                nonce: chatAjax.nonce
            },
            beforeSend: function () {
                $("#send-otp").text("Sending...").prop("disabled", true);
            },
            success: function (response) {
                if (response.success) {
                    //alert("OTP sent to your email!");
                    $("#user-info").hide();
                    $("#otp-verification").show();
                } else {
                    alert(response.data || "Failed to send Code.");
                }
            },
            complete: function () {
                $("#send-otp").text("Send OTP").prop("disabled", false);
            },
        });
    });

    // Verify OTP
    $("#verify-otp").on("click", function () {
        const otp = $("#chat-otp").val();

        if (!otp) {
            alert("Please enter the Code.");
            return;
        }

        $.ajax({
            url: chatAjax.ajaxurl,
            method: "POST",
			dataType: "json",   
            data: {
                action: "verify_otp",
                email: userEmail,
                otp: otp,
                nonce: chatAjax.nonce
            },
            beforeSend: function () {
                $("#verify-otp").text("Verifying...").prop("disabled", true);
            },
            success: function (response) {
                if (response.success) {
                   // alert("OTP verified! Start chatting.");
                    $("#otp-verification").hide();
                    $("#verify_wrap").hide();
                    $("#chat-window").show();
					
					//fetchNewMessages(0);	
					
					$('#chat-messages').append(response.data.html);				
					lastMessageId = response.data.last_message_id;
					jQuery("#send-message").data("last", lastMessageId );
					
					
					jQuery("#nkg_is_chat_start").val('yes');					
					activeRequest = true;
                    //loadMessages();
					
					
                } else {
                    alert(response.data || "Failed to verify OTP.");
                }
            },
            complete: function () {
                $("#verify-otp").text("Verify").prop("disabled", false);
            },
        });
    });

     // Send Message
    $("#send-message").on("click", function () {
		
        const message = $("#chat-message").val();
		var lastMessageId = jQuery("#send-message").data("last");		
        if (!message) {
            alert("Please enter a message.");
            return;
        }

		chatget.abort();
		
        $.ajax({
            url: chatAjax.ajaxurl,
            method: "POST",
			dataType: "json",   
            data: {
                action: "send_message", 
                message: message,
				last_message_id: lastMessageId,
                nonce: chatAjax.nonce
            },
            success: function (response) {
                if (response.success) { 
                    $("#chat-message").val("");
                    //loadMessages();					
					$('#chat-messages').append(response.data.html);				
					lastMessageId = response.data.last_message_id;
					jQuery("#send-message").data("last", lastMessageId );
								
                } else {
                    alert(response.data || "Failed to send message.");
                }
            },
        });
    });
	
	$('#chat-message').keydown(function(event){ 
		 
		if ( event.key === "Enter" && !event.shiftKey ) {
			event.preventDefault()
			$('#send-message').trigger('click');
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
		
		var lastMessageId = jQuery("#send-message").data("last");	
		
		const formData = new FormData();
		formData.append("action", "user_upload_chat_image");
		formData.append("image", imageInput);		 
		formData.append("sender", "user");  
		formData.append("last_message_id", lastMessageId);     
		formData.append("nonce", chatAjax.nonce);
		 
		chatget.abort();

		$.ajax({
			url: chatAjax.ajaxurl, // WordPress AJAX URL
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
	
	
	
	
	 function fetchNewMessages(lastMessageId) {
		 console.log(chatAjax);
        chatget = $.ajax({
						url: chatAjax.ajaxurl,
						method: 'POST',
						dataType: "json",   
						data: {
							action: 'fetch_new_messages', 
							last_message_id: lastMessageId,
                            nonce: chatAjax.nonce
						},
						success: function(response) {
							if (response) {
								$('#chat-messages').append(response.data.html); 						
								lastMessageId = response.data.last_message_id;
								
								jQuery("#send-message").data("last", lastMessageId );
								
								console.log( ' ajsx ' +  lastMessageId );
								
								 
							}
							
						}
					});
    }
	
	
	jQuery("#stat_chat_nkg").click(function(){
		activeRequest = true;	 
		var lastId = jQuery("#send-message").data("last");		
		var nkg_is_chat_start = jQuery("#nkg_is_chat_start").val();	
		jQuery("#chat-messages").html("");	
		//if( 'yes'  == nkg_is_chat_start && activeRequest ){		 
		if( activeRequest ){		 
			fetchNewMessages(0);			 
		}
		 
	});
	
	jQuery("#end_chat_nkg").click(function(){
		activeRequest = false;
		//jQuery("#nkg_is_chat_start").val('no'); 
		 jQuery("#chat-messages").html("");	
	});
	
	
	// Fetch new messages every 2 seconds
    setInterval(function() {
		
		var nkg_is_chat_start = jQuery("#nkg_is_chat_start").val();
		var lastId = jQuery("#send-message").data("last");   
		 if( 'yes'  == nkg_is_chat_start && activeRequest ){
		  
			fetchNewMessages(lastId);
			 
		 }
		 
    }, 10000);
	
	
	
	 setInterval(function() {
		
		var nkg_is_chat_start = jQuery("#nkg_is_chat_start").val();
		var lastId = jQuery("#send-message").data("last");   
		 if( 'yes'  == nkg_is_chat_start && !activeRequest ){
		  
			fetchNewMessagesCount(lastId);
			 
		 }
		 
    }, 10000);
	
	function fetchNewMessagesCount( lastId ){
		
		
		$.ajax({
			url: chatAjax.ajaxurl,
			method: 'POST',
			dataType: "json",   
			data: {
				action: 'fetch_new_messages_count', 
				last_message_id: lastId,
                nonce: chatAjax.nonce
			},
			success: function(response) {
				if ( response ) {
					jQuery(".new_msg_count").html( response.data.count );
					 
				}
				
			}
		});
		
	}
	
	// set active user every 4 minute
	
	setInterval(function() {
		
		 
		 $.ajax({
            url: chatAjax.ajaxurl,
            method: 'POST',
			dataType: "json",   
            data: {
                action: 'update_online_users',
                nonce: chatAjax.nonce
            },
            success: function(response) {
                 
				
            }
        }); 
		
		 
    }, 240000);
	
	
	
});
