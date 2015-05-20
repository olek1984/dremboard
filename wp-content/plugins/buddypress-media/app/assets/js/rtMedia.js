var rtMagnificPopup;
function apply_rtMagnificPopup(selector){
    jQuery('document').ready(function($) {
	var rt_load_more = "";
	if(typeof(rtmedia_load_more) === "undefined") {
	    rt_load_more = "Loading media";
	} else {
	    rt_load_more = rtmedia_load_more;
	}
       if( rtmedia_lightbox_enabled == '1'){ // if lightbox is enabled.

            rtMagnificPopup = jQuery(selector).magnificPopup({
                delegate: 'a.mfp-popup, .rtmedia-list-item > a',
                type: 'ajax',
                tLoading: rt_load_more + ' #%curr%...',
                mainClass: 'mfp-img-mobile',
                preload: [1, 3],
                closeOnBgClick: false,
                gallery: {
                    enabled: true,
                    navigateByImgClick: true,
                    arrowMarkup: '',// disabled default arrows
                    preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
                },
                image: {
                    tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
                    titleSrc: function(item) {
                        return item.el.attr('title') + '<small>by Marsel Van Oosten</small>';
                    }
                },
                disableOn: function() {
                    if (jQuery(window).width() < 600) {
                        return false;
                    }
                    return true;
                },
                callbacks: {
                    ajaxContentAdded: function() {

                        // When last second media is encountered in lightbox, load more medias if available
                        var mfp = jQuery.magnificPopup.instance;
                        var current_media = mfp.currItem.el;
                        var li = current_media.parent();
                        if(li.is(':nth-last-child(2)')){ // if its last second media
                            var last_li = li.next();
                            if(jQuery('#rtMedia-galary-next').css('display') == 'block'){ // if more medias are available
                               //jQuery('#rtMedia-galary-next').click(); // load more
                               // var new_items = last_li.nextAll();
                               // console.log(new_items);
                               // new_items.each(function(index){
                                   // console.log(index);
                                   // mfp.items.push({
                                     //  src: jQuery(this).children('a')
                                    //});
                                    //console.log(jQuery('>a'));
                              //  });
                                //apply_rtMagnificPopup(selector);
                                //mfp.updateItemHTML();
                            }
                        }

                        var items = mfp.items.length;
                        /*
                        if(mfp.index == (items -1) && !(li.is(":last-child"))){
                            current_media.click();
                            return;
                        }
                        */

                        $container = this.content.find('.tagcontainer');
                        if ($container.length > 0) {
                            $context = $container.find('img');
                            $container.find('.tagcontainer').css(
                                    {
                                        'height': $context.css('height'),
                                        'width': $context.css('width')
                                    });

                        }
                        var settings = {};

                        if (typeof _wpmejsSettings !== 'undefined')
                            settings.pluginPath = _wpmejsSettings.pluginPath;
                        $('.mfp-content .wp-audio-shortcode,.mfp-content .wp-video-shortcode,.mfp-content .bp_media_content video').mediaelementplayer({
                            // if the <video width> is not specified, this is the default
                            defaultVideoWidth: 480,
                            // if the <video height> is not specified, this is the default
                            defaultVideoHeight: 270,
                            // if set, overrides <video width>
                            //videoWidth: 1,
                            // if set, overrides <video height>
                            //videoHeight: 1
                        });
                        $('.mfp-content .mejs-audio .mejs-controls').css('position', 'relative');
                        rtMediaHook.call('rtmedia_js_popup_after_content_added', []);
                    },
                    close: function(e) {
                        //console.log(e);
			rtmedia_init_action_dropdown();
                    },
                    BeforeChange: function(e) {
                        //console.log(e);
                    }
                }
            });
        }

	if (jQuery(window).width() < 600) {
	    jQuery('#whats-new').focus( function(){
		jQuery("#whats-new-options").animate({
		    height:'100px'
		});
	    });
	    jQuery('#whats-new').blur( function(){
		jQuery("#whats-new-options").animate({
		    height:'100px'
		});
	    });
	}
    });
}
var rtMediaHook = {
    hooks: [],
    is_break : false,
    register: function(name, callback) {
        if ('undefined' == typeof(rtMediaHook.hooks[name]))
            rtMediaHook.hooks[name] = []
        rtMediaHook.hooks[name].push(callback)
    },
    call: function(name, arguments) {
        if ('undefined' != typeof(rtMediaHook.hooks[name]))
            for (i = 0; i < rtMediaHook.hooks[name].length; ++i){
                if (true != rtMediaHook.hooks[name][i](arguments)) {
                    rtMediaHook.is_break=true;
                    return false;
                    break;
                }
            }
            return true;
    }
}

//drop-down js
function rtmedia_init_action_dropdown() {
    jQuery('.click-nav > span').toggleClass('no-js js');
    jQuery('.click-nav .js ul').hide();
    jQuery('.click-nav .clicker').click(function(e) {
	jQuery(this).next('ul').toggle();
	//$('.click-nav ul').toggle();
	e.stopPropagation();
    });
}

jQuery('document').ready(function($) {

    // open magnific popup as modal for create album/playlist
    if( jQuery('.rtmedia-modal-link').length > 0 ){
	$('.rtmedia-modal-link').magnificPopup({
	    type:'inline',
	    midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href
	    closeBtnInside:true,

	  });
    }

    if( jQuery('.rtmedia-media-edit').length > 0 ){
	//for foundation tabs on single media edit.
        jQuery('.rtmedia-media-edit').foundation();
    }

    $("#rt_media_comment_form").submit(function(e) {
        if ($.trim($("#comment_content").val()) == "") {
            //alert( rtmedia_empty_comment_msg );
            return false;
        } else {
            return true;
        }

    })

   //rtmedia_lightbox_enabled from setting
    if (typeof(rtmedia_lightbox_enabled) != 'undefined' && rtmedia_lightbox_enabled == "1") {
        apply_rtMagnificPopup('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.widget-item-listing,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content, .rtm-bbp-container');
    }

    jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR) {
	try{
            if (originalOptions.data == null || typeof(originalOptions.data) == "undefined" || typeof(originalOptions.data.action) == "undefined" ) {
                return true;
            }
        }catch(e){
            return true;
        }
	if (originalOptions.data.action == 'activity_get_older_updates') {
	    var orignalSuccess = originalOptions.success;
	    options.success = function(response) {
		orignalSuccess(response);
		apply_rtMagnificPopup('.rtmedia-activity-container ul.rtmedia-list, #bp-media-list, .bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content');
		rtMediaHook.call('rtmedia_js_after_activity_added', []);
	    }
	}
    });

    jQuery('.rtmedia-container').on('click', '.select-all', function(e) {
        e.preventDefault();
        jQuery(this).toggleClass('unselect-all').toggleClass('select-all');
        jQuery(this).attr('title', rtmedia_unselect_all_visible);
        jQuery(this).html('<i class="rtmicon-check-square-o"></i>');
        jQuery('.rtmedia-list input').each(function() {
            jQuery(this).prop('checked', true);
        });
	jQuery('.rtmedia-list-item').addClass('bulk-selected');
    });

    jQuery('.rtmedia-container').on('click', '.unselect-all', function(e) {
        e.preventDefault();
        jQuery(this).toggleClass('select-all').toggleClass('unselect-all');
        jQuery(this).attr('title', rtmedia_select_all_visible);
        jQuery(this).html('<i class="rtmicon-square-o"></i>');
        jQuery('.rtmedia-list input').each(function() {
            jQuery(this).prop('checked', false);
        });
	jQuery('.rtmedia-list-item').removeClass('bulk-selected');
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-move', function(e) {
        jQuery('.rtmedia-delete-container').slideUp();
        jQuery('.rtmedia-move-container').slideToggle();
    });

//    jQuery('.rtmedia-container').on('click', '.rtmedia-merge', function(e) {
//        jQuery('.rtmedia-merge-container').slideToggle();
//    });

//    jQuery('.rtmedia-container').on('click', '.rtmedia-create-new-album-button', function(e) {
//        jQuery('.rtmedia-create-new-album-container').slideToggle();
//    });

    //!!!!! jQuery('#rtmedia-create-album-modal').on('click', '#rtmedia_create_new_album', function(e) {
    jQuery('#rtmedia-create-album-modal,#rtmedia-create-dremboard-modal,.rtmedia-create-dremboard-modal').on('click', '#rtmedia_create_new_album', function(e) {
        $albumname = jQuery.trim(jQuery('#rtmedia_album_name').val());
        $context = jQuery.trim(jQuery('#rtmedia_album_context').val());
        $context_id = jQuery.trim(jQuery('#rtmedia_album_context_id').val());
	//!!!!! $privacy = jQuery.trim(jQuery('#rtmedia_select_album_privacy').val());
	$privacy = jQuery.trim(jQuery('#rtmedia_select_album_privacy').attr("checked"));
	if ($privacy == 'checked') $privacy = 'YES';
	if ($privacy == false) $privacy = 'NO';
	
	if (jQuery.trim(jQuery('.rtmedia_select_dremboard_privacy:checked').val()) !== ""){
		$privacy = jQuery.trim(jQuery('.rtmedia_select_dremboard_privacy:checked').val());
	}
	//alert($privacy);
	//!!!!!
	$creat_album_des = jQuery.trim(jQuery('.creat_album_des').val());
	$simplifyCategoryCheck = jQuery.trim(jQuery('#simplifyCategoryCheck').val());
	//alert($simplifyCategoryCheck);
	$creat_album_dremboard_type = jQuery.trim(jQuery('#creat_album_dremboard_type').val());
        if ($albumname != '') {
            var data = {
                action: 'rtmedia_create_album',
                name: $albumname,
                //!!!!!
                description: $creat_album_des,
                category: $simplifyCategoryCheck,
                type: $creat_album_dremboard_type,
                context: $context,
                context_id: $context_id
            };
	   if($privacy !== "") {
	       data['privacy'] = $privacy;
	   }
            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $("#rtmedia_create_new_album").attr('disabled', 'disabled');
            var old_val = $("#rtmedia_create_new_album").html();
            $("#rtmedia_create_new_album").prepend("<img src='" + rMedia_loading_file + "'/>");
            jQuery.post(rtmedia_ajax_url, data, function(response) {
		response = response.trim();
                if (response) {
		    response = response.trim();
		    var flag = true;
		    jQuery('.rtmedia-user-album-list').each(function() {
			jQuery(this).children('optgroup').each(function(){
			    if(jQuery(this).attr('value') === $context) {
				flag = false;
				jQuery(this).append('<option value="' + response + '">' + $albumname + '</option>');
				return;
			    }
			});
			if(flag) {
			    var label = $context.charAt(0).toUpperCase() + $context	.slice(1);
			    var opt_html = '<optgroup value="' + $context + '" label="' + label + ' Albums"><option value="' + response + '">' + $albumname + '</option></optgroup>';
			    jQuery(this).append(opt_html);
			}

		    });
                    jQuery('select.rtmedia-user-album-list option[value="' + response + '"]').prop('selected', true);
                    jQuery('.rtmedia-create-new-album-container').slideToggle();
                    jQuery('#rtmedia_album_name').val("");
                    //!!!!! jQuery("#rtmedia-create-album-modal").append("<span class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b>" + rtmedia_album_created_msg + "</span>");
                    //!!!!!!! jQuery("#rtmedia-create-album-modal,#rtmedia-create-dremboard-modal,.rtmedia-create-dremboard-modal").append("<span class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b>" + rtmedia_album_created_msg + "</span>");
                    //!!!!!!!
                    jQuery("#rtmedia-create-album-modal,#rtmedia-create-dremboard-modal,.rtmedia-create-dremboard-modal").append("<span class='rtmedia-success rtmedia-create-album-alert'><b>" + $albumname + "</b>" + rtmedia_album_created_msg + " Please wait a while for page refreshing..." + "</span>");
                    //alert(jQuery(this).attr('href'));
        //var usernameNum999 = $(this).attr('href').match(/members\/([0-9a-zA-Z]+)\/media/)[1];
        //usernameNumClass = "need-popup-"+usernameNum;
        //$(this).attr('id', usernameNum);
        //$(this).addClass(usernameNumClass);
                    //top.location.reload();
                    setTimeout(function() {
                        jQuery(".rtmedia-create-album-alert").remove();
                        //!!!!!!!
                        //!!!!!!! var usernameNum999 = location.href.match(/members\/([0-9a-zA-Z]+)\/media/)[1];
                        //var usernameNum999 = location.href.match("/media/photo/?dremboard=");
                        //jQuery.load(location.href)
                        location.reload();
                    }, 4000);
                    setTimeout(function() {
                        galleryObj.reloadView();
                        jQuery(".close-reveal-modal").click();
                    }, 2000);

                } else {
                    alert(rtmedia_something_wrong_msg);
                }
                $("#rtmedia_create_new_album").removeAttr('disabled');
                $("#rtmedia_create_new_album").html(old_val);
            });
        } else {
            alert(rtmedia_empty_album_name_msg);
        }
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-delete-selected', function(e) {
        if( jQuery('.rtmedia-list :checkbox:checked').length > 0 ){
            if(confirm(rtmedia_selected_media_delete_confirmation)){
                if (document.URL.indexOf("/show/") != -1){
                    jQuery(this).closest('form').attr('action', '../../../../media/delete').submit();
                }else{
                    jQuery(this).closest('form').attr('action', '../../../media/delete').submit();
                }
            }
        }else{
            alert(rtmedia_no_media_selected);
        }
    });

    jQuery('.rtmedia-container').on('click', '.rtmedia-move-selected', function(e) {
        if( jQuery('.rtmedia-list :checkbox:checked').length > 0 ){
            if(confirm(rtmedia_selected_media_move_confirmation)){
                var form = jQuery(this).closest('form');
                form.append("<input type='hidden' name='media_action' value='move'/>");
                form.attr('action', '').submit();
            }
        }else{
            alert(rtmedia_no_media_selected);
        }

    });
    
    jQuery(".mejs-overlay-play").unbind('click');

    function rtmedia_media_view_counts() {
	//var view_count_action = jQuery('#rtmedia-media-view-form').attr("action");
	if(jQuery('#rtmedia-media-view-form').length > 0 ) {
	    var url = jQuery('#rtmedia-media-view-form').attr("action");
	    jQuery.post(url,
		{

		},function(data){

		});
	}
    }

    rtMediaHook.register('rtmedia_js_popup_after_content_added',
	    function() {
		//rtmedia_media_view_counts();
                rtmedia_init_media_deleting();
                rtmedia_init_popup_navigation();
                var height = $(window).height() ;
                //console.log( height );
                //   , .mfp-content #buddypress .rtmedia-container,
                jQuery('.mfp-content .rtm-lightbox-container .rtmedia-single-meta, .mfp-content .rtm-lightbox-container #rtmedia-single-media-container .rtmedia-media, .rtm-lightbox-container .mejs-video').css({ 'height' : height*0.8, 'max-height' : height*0.8, 'over-flow' : 'hidden' });
                //mejs-video
                //init the options dropdown menu
                rtmedia_init_action_dropdown();
                //get focus on comment textarea when comment-link is clicked
                jQuery('.rtmedia-comment-link').on('click', function(e){
                    e.preventDefault();
                    jQuery('#comment_content').focus();
                });

                jQuery(".rtm-more").shorten({ // shorten the media description to 100 characters
                    "showChars" : 130
                });

                //show gallery title in lightbox at bottom
                var gal_title = $('.rtm-gallery-title'), title = "";
                if(! $.isEmptyObject(gal_title) ){
                    title = gal_title.html();
                }else {
                    title = $('#subnav.item-list-tabs li.selected ').html();
                }
                if( title != ""){
//                    $('.rtm-ltb-gallery-title .ltb-title').html(title);
                }

                //show the index of the current image
//                var index = jQuery.magnificPopup.instance.index;
//                $('.media-index').html(index+1);

                //show image counts
                var counts = $('#subnav.item-list-tabs li.selected span').html();
                $('li.total').html(counts);

		return true;
	    }
    );

   function rtmedia_init_popup_navigation() {
        var rtm_mfp = jQuery.magnificPopup.instance;
        jQuery('.mfp-arrow-right').on('click', function(e) {
            rtm_mfp.next();
        });
        jQuery('.mfp-arrow-left').on('click', function(e) {
            rtm_mfp.prev();
        });
   }
   var dragArea = jQuery("#drag-drop-area");
   var activityArea = jQuery('#whats-new');
   var content = dragArea.html();
   jQuery('#rtmedia-upload-container').after("<h2 id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</h2>");
   jQuery('#whats-new-textarea').after("<h2 id='rtm-drop-files-title'>" + rtmedia_drop_media_msg + "</h2>");
   jQuery(document)
           .on('dragover', function(e) {
               jQuery('#rtm-media-gallery-uploader').show();
                activityArea.addClass('rtm-drag-drop-active');
                activityArea.css('height','150px');
                dragArea.addClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').css('display', 'block');
                })
           .on("dragleave", function(e){
               e.preventDefault();
               activityArea.removeClass('rtm-drag-drop-active');
               activityArea.removeAttr('style');
               dragArea.removeClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').hide();

                })
           .on("drop", function(e){
                e.preventDefault();
                 activityArea.removeClass('rtm-drag-drop-active');
                 activityArea.removeAttr('style');
                 dragArea.removeClass('rtm-drag-drop-active');
                jQuery('#rtm-drop-files-title').hide();
                });


    function rtmedia_init_media_deleting() {
        jQuery('.rtmedia-container').on('click', '.rtmedia-delete-media', function(e) {
            e.preventDefault();
            if(confirm(rtmedia_media_delete_confirmation)) {
                jQuery(this).closest('form').submit();
            }
        });
       }

       jQuery('.rtmedia-container').on('click', '.rtmedia-delete-album' , function(e) {
        e.preventDefault();
        if(confirm(rtmedia_album_delete_confirmation)) {
            jQuery(this).closest('form').submit();
        }
       });

       jQuery('.rtmedia-container').on('click', '.rtmedia-delete-media', function(e) {
        e.preventDefault();
        if(confirm(rtmedia_media_delete_confirmation)) {
            jQuery(this).closest('form').submit();
        }
    });


//    jQuery(document).on('click', '#rtm_show_upload_ui', function(){
//        jQuery('#rtm-media-gallery-uploader').slideToggle();
//    });

    rtmedia_init_action_dropdown();
    $(document).click(function() {
        if ($('.click-nav ul').is(':visible')) {
            $('.click-nav ul', this).hide();
        }
    });

    //get focus on comment textarea when comment-link is clicked
    jQuery('.rtmedia-comment-link').on('click', function(e){
        e.preventDefault();
        jQuery('.rtmedia-comments-container').show();
        jQuery('#comment_content').focus();
    });

    if( jQuery('.rtm-more').length > 0 ){
	$(".rtm-more").shorten({ // shorten the media description to 100 characters
	    "showChars" : 200
	});
    }
});



//Legacy media element for old activities
function bp_media_create_element(id) {
    return false;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function($) {
    $.fn.shorten = function (settings) {

        var config = {
            showChars: 100,
            ellipsesText: "...",
            moreText: "more",
            lessText: "less"
        };

        if (settings) {
            $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on({click: function () {

                var $this = $(this);
                if ($this.hasClass('less')) {
                    $this.removeClass('less');
                    $this.html(config.moreText);
                } else {
                    $this.addClass('less');
                    $this.html(config.lessText);
                }
                $this.parent().prev().toggle();
                $this.prev().toggle();
                return false;
            }
        }, '.morelink');

        return this.each(function () {
            var $this = $(this);
            if($this.hasClass("shortened")) return;

            $this.addClass("shortened");
            var content = $this.html();
            if (content.length > config.showChars) {
                var c = content.substr(0, config.showChars);
                var h = content.substr(config.showChars, content.length - config.showChars);
                var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
                $this.html(html);
                $(".morecontent span").hide();
            }
        });

    };

 })(jQuery);


var processed_query_word = ""; 	// setted word
var bad_query_word = "";		// don't have return
var query_process_flag = false;  // searching is now.
var	last_input = null;
var	last_helper = null;
var	last_query_word = "";

function set_helper_val(input, helper){
	processed_query_word = jQuery(".selected .user_nicename", helper).html();
	var ori_val = input.val();
	var cur_pos = input.prop("selectionStart");
	var prev_val = ori_val.substr(0, cur_pos);
	prev_val = prev_val.replace(/@[^@]*$/,"");
	var next_val = ori_val.substr(cur_pos);
	var last_pos = prev_val.length + processed_query_word.length;
	if (input.hasClass("only_one")){
		if (input.hasClass("has_mark")){
			input.val("@"+processed_query_word);
		}else{
			input.val(processed_query_word);
		}
	}else{
		if (input.hasClass("has_mark")){
			input.val(prev_val + "@"+processed_query_word + " " + next_val);
			input.prop("selectionStart", last_pos + 2);
			input.prop("selectionEnd", last_pos + 2);
		}else{
			input.val(prev_val + processed_query_word + " " + next_val);
			input.prop("selectionStart", last_pos + 1);
			input.prop("selectionEnd", last_pos + 1);
		}
	}
	helper.html("");
	helper.hide();
}

function prev_helper_selected(helper){
	var helper_items = jQuery(".user_item" ,helper);
	var count = helper_items.length;
	var selected_item_index = jQuery(".selected" ,helper).index();
	var new_index = selected_item_index - 1;
	if (new_index < 0)
		new_index = count - 1;
	set_helper_selected(jQuery(helper_items[new_index]));
}

function next_helper_selected(helper){
	var helper_items = jQuery(".user_item" ,helper);
	var count = helper_items.length;
	var selected_item_index = jQuery(".selected" ,helper).index();
	var new_index = selected_item_index + 1;
	if (new_index >= count)
		new_index = 0;
	set_helper_selected(jQuery(helper_items[new_index]));
}

function set_helper_selected(user_item){
	user_item.siblings().removeClass("selected");
	user_item.addClass("selected");
}

function show_helper_screen(input, helper, users_list){
	if(processed_query_word != "")
		return;
	helper.show();
	helper.html("");
	selected_flag = false;
	users_list.forEach(function(user_data){
		var user_id = jQuery("<div class='user_id'>"+user_data.user_id+"</div>");
		var user_nicename = jQuery("<div class='user_nicename'>"+user_data.user_nicename+"</div>");
		var user_fullname = jQuery("<div class='user_fullname'>"+user_data.user_fullname+"</div>");
		var user_email = jQuery("<div class='user_email'>"+user_data.user_email+"</div>");
		var user_avatar = jQuery("<div class='user_avatar'><img src='"+user_data.user_avatar
		+"' width='40' height='40' alt='Profile picture of "+user_data.user_fullname+"'/></div>");
		var user_item = jQuery("<div class='user_item'></div>");
		var sub_content_head = jQuery("<div class='sub_content avatar'></div>");
		var sub_content_tail = jQuery("<div class='sub_content tail'></div>");
		sub_content_head.append(user_avatar);
		sub_content_tail.append(user_id).append(user_fullname).append(user_nicename).append(user_email);
		user_item.append(sub_content_head).append(sub_content_tail);
		user_item.attr("id", "user-"+user_data.user_id);
		if (selected_flag !== true){
			user_item.addClass("selected");
			selected_flag = true;
		}
		helper.append(user_item);
		user_item.mouseenter(function(){
			set_helper_selected(jQuery(this));
		});
		user_item.click(function(){
			set_helper_val(input, helper);
		});
	});
}

function clear_and_hide_helper_screen(helper){
	helper.html("");
	helper.hide();
}

function search_user_list(input, helper, query_word){
	// using ajax, get the dremer's list and show.
	last_input = null;
	var user_type = input.attr("usertype");
	if (user_type === null || user_type === undefined){
		user_type = "user";
	}
	var data = {
        action: 'get_dremer_list',
        query: query_word,
        user_type: user_type
    };
    
    if(bad_query_word != "" && query_word.indexOf(bad_query_word) != -1)
    {
    	query_process_flag = false;
    	clear_and_hide_helper_screen(helper);
    	return;
    }
    
	jQuery.post(rtmedia_ajax_url, data, function(response) {
		var result = jQuery.trim(response);
			result = result.replace(/[^\}\]]*$/, "");
		if(result.length === 0){
			clear_and_hide_helper_screen(helper);
			if (bad_query_word != "" && query_word.indexOf(bad_query_word) != -1){
			}else{
				bad_query_word = query_word;
			}
		}else{
			show_helper_screen(input, helper, JSON.parse(result));
		}
		query_process_flag = false;
		if (last_input != null){
			query_process_flag = true;
			search_user_list(last_input, last_helper, last_query_word);
		}
	});
}

function search_user_list_loop(input, helper, query_word){
	if (query_process_flag == true){
		last_input = input;
		last_helper = helper;
		last_query_word = query_word;
	}else{
		query_process_flag = true
		search_user_list(input, helper, query_word);
	}
}

function add_input_for_bp_user(input_container){
	
	var helpers = null;
	
	if(input_container){
		helpers = input_container;
	}else{
		helpers = jQuery(".helper-input");
	}
	
	if (helpers.length == 0)
		return;
	helpers.each(function(){
		var helper_html = "<div class='helper'><div>";
		var inputbox = jQuery(".helper-input-box", this);
		if (jQuery(this).hasClass("helper-input-box")){
			inputbox = jQuery(this);
		}
		
		if (inputbox.length == 0)
			return;
		
		var helper = jQuery(helper_html);
		jQuery(this).after(helper);
		helper.html("");
		helper.hide();
		var old_query_word = "";
		var query_word = "";
		
		inputbox.bind('keydown', function(e){
			var event = window.event ? window.event : e;
			var keyCode = event.keyCode;
			var helper_status = (helper.css("display") === "none")?false : true;
			if (helper_status){
				if (keyCode === 38){
					prev_helper_selected(helper);
					e.preventDefault();
					return false;
				}
				if (keyCode === 40){
					next_helper_selected(helper);
					e.preventDefault();
					return false;
				}
				if (keyCode === 13){
					set_helper_val(jQuery(this), helper);
					e.preventDefault();
					return false;
				}
			}
		});

		inputbox.bind('keyup', function(e){
			var event = window.event ? window.event : e;
			var keyCode = event.keyCode;
			switch (keyCode) {
				case 37, 38, 39, 40, 13: return;
			}
			search_func();
		});
		function search_func(){
			var string = inputbox.val();
			var cur_pos = inputbox.prop("selectionStart");
			string = string.substr(0, cur_pos);
			string = string.match(/@[^@\n]*$/);
			if(string != null){
				query_word = string[0].substr(1);
				query_word = jQuery.trim(query_word);
				if (query_word == ""){
					processed_query_word = " ";
					clear_and_hide_helper_screen(helper);// delete all the helper's content and hide.
					old_query_word = "";
				}
				if (query_word != processed_query_word && query_word != old_query_word){
					processed_query_word = "";
					search_user_list_loop(inputbox, helper, query_word);// search and show the helper screen.
					old_query_word = query_word;
				}
			}else{
				clear_and_hide_helper_screen(helper);// delete all the helper's content and hide.
				old_query_word = "";
				processed_query_word = " ";
			}
		}
	});
}

function resizeGallery(){
	try{
		var maxCount = 5;
		
		if (jQuery("#gk-sidebar").css('display') == "none"){
			maxCount = 0;
		}
		
		var section_size = parseInt(jQuery("#gk-mainbody-columns > section").css("width"));
		var page_size = parseInt(jQuery(".gk-page").css("width"));
		var sidebar_top = jQuery("#gk-sidebar").offset().top;
		var sidebar_bottom = sidebar_top + jQuery("#gk-sidebar").height();
		var col3ul = jQuery(".rtmedia-container ul.rtmedia-list.col3");
		var col4ul = jQuery(".rtmedia-container ul.rtmedia-list.col4");
		var col3_top = col3ul.offset().top;
		var col3_bottom = col3_top + col3ul.height();
		var count = 0;
		if (col3_bottom > sidebar_top && col3_bottom < sidebar_bottom){
			while(col3_bottom < sidebar_bottom){
				count ++;
				if (count > maxCount){
					break;
				}
				//console.log("count :", count);
				var insert_item = jQuery('li:lt(3)' ,col4ul);
				if (insert_item.length === 0){
					break;
				}
				col3ul.append(insert_item);
				//insert_item.remove();
				col3_bottom = col3_top + col3ul.height();
			}
		}
		col4ul.css("width", page_size - 30);
		//jQuery(".rtmedia-container ul.rtmedia-list.col4 li.rtmedia-list-item").css("width", "25%");
		jQuery("#rtMedia-galary-next").css("width", page_size - 30);
	}catch(e){
	}
}

jQuery('document').ready(function($) {
	add_input_for_bp_user();
});