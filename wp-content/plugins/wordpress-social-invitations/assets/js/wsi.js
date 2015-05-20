wsifbApiInit = false;
window.fbAsyncInit = function() {
	FB.init({
		  appId      : WsiMyAjax.appId,
		  xfbml      : true,
		  version    : 'v2.0'
	});
	wsifbApiInit = true; //init flag
};
(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/"+WsiMyAjax.locale+"/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

(function($){ 
	$(function(){
		if(!wsifbApiInit && typeof(FB) != 'undefined' && FB != null){
			FB.init({
			  appId      : WsiMyAjax.appId,
			  xfbml      : true,
			  version    : 'v2.0'
			});
		}
		$(".service-filters a").unbind('click');
		$(".service-filters a").click(function(){
			popupurl = $("#wsi_base_url").val();
			provider = $(this).attr("data-provider");
			var current_url = $('#wsi_base_url').val();
			var obj_id 		= $('#wsi_obj_id').val();			
			var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
		    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
		    var left = ((screen.width / 2) - (600 / 2)) + dualScreenLeft;
		    var top = ((screen.height / 2) - (640 / 2)) + dualScreenTop;
		    var widget_id = $(this).closest('.wsi-locker').attr('id');
		    var wsi_locker = true;
		    if( !widget_id ) 
		    {
		    	var widget_id = Date.now();
		    	$(this).closest('.service-filter-content').attr('id',widget_id);
				wsi_locker = false;
		    	
		    }	
		    if( 'facebook' == provider) {
		    	var link = WsiMyAjax.site_url;
		    	var	accept_url = '';
		    	
		    	if( 'registration' == WsiMyAjax.fburl ) {	
		    		//ADD to queue and get the registration url
		    		$.ajax({
		    			type: "POST",
		    			url:  WsiMyAjax.admin_url,
		    			data: { nonce: WsiMyAjax.nonce, action: 'get_fb_registration_url', wsi_obj_id : obj_id },
		    			async: false,
		    			success: function(response){

		    				accept_url = response;	
		    				
		    			}
		    		});
		    	}
		    	if( 'current_url' == WsiMyAjax.fburl ) {
		    		link = current_url;
		    	}
	    		if( 'custom_url' == WsiMyAjax.fburl ) {
	    			//ADD to queue (cubepoints)
		    		$.ajax({
		    			type: "POST",
		    			url:  WsiMyAjax.admin_url,
		    			data: { nonce: WsiMyAjax.nonce, action: 'get_fb_registration_url', wsi_obj_id : obj_id },
		    			async: false,
		    			success: function(response){

		    				//accept_url = response;	
		    				//no need to change url here

		    			}
		    		});
	    			link = WsiMyAjax.fbCustomurl;

	    		}
	    		if( accept_url ){
		    		if( link.indexOf("?") == -1 ) {
		    			link = link + "?" + accept_url + "&wsi-fb-registration=true";
		    		} else {
		    			link = link + "&" + accept_url + "&wsi-fb-registration=true";
		    		}
	   	    	} else {
	   	    		// if we are not registering a new user we still need to add wsi-fb-accept-invitation= for acebook og check 
	   	    		if( link.indexOf("?") == -1 ) {
		    			link = link + "?" + "wsi-fb-accept-invitation=true";
		    		} else {
		    			link = link + "&" + "wsi-fb-accept-invitation=true";
		    		}
	   	    	}
	    		if( WsiMyAjax.wsi_token ){
		    		if( link.indexOf("?") == -1 ) {
		    			link = link + "?" + WsiMyAjax.wsi_token ;
		    		} else {
		    			link = link + "&" + WsiMyAjax.wsi_token ;
		    		}
	   	    	}

    			var method = "send";
	    		if( jQuery(window).width() < 768 ) {
	    			method = "share";
	    		}
	    		FB.ui(
				  {
				    method: method,
				    link: link,
				    href: link,
				  },
				  function(response) {
				    if (response && !response.error_code) {
				        $('#'+widget_id+' #facebook-provider').addClass('completed');
						$('#'+widget_id+' #wsi_provider').html(provider);
						$('#'+widget_id+' .wsi_success').fadeIn('slow',function(){
							if( wsi_locker == 'true' ) {
								setCookie("wsi-lock["+widget_id+"]",1,365);
								window.location.reload();
							}
							if( WsiMyAjax.redirect_url != '' ) {	
								window.location.href = WsiMyAjax.redirect_url;
							}
						
				    	});
				  	}
				  }	 
				);
		    } else {
				window.open(
					WsiMyAjax.admin_url+"?action=wsi_authenticate&redirect_to="+encodeURIComponent(popupurl)+"&provider="+provider+ "&widget_id="+widget_id+"&wsi_locker="+wsi_locker+"&current_url="+encodeURIComponent(current_url)+"&wsi_obj_id="+obj_id+"&_ts=" + (new Date()).getTime(),
					"hybridauth_social_sing_on", 
					"directories=no,copyhistory=no,location=0,toolbar=0,location=0,menubar=0,status=0,scrollbars=1,width=600,height=640,top=" + top + ", left=" + left
				); 
			}

		});
	});
})(jQuery);