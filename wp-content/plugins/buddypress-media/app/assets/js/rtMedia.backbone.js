var galleryObj;
var nextpage = 2;
var upload_sync = false;
var activity_id = -1;
var uploaderObj;
var objUploadView ;
var rtmedia_load_template_flag = true;

jQuery(function($) {

    var o_is_album, o_is_edit_allowed;
    if (typeof(is_album) == "undefined") {
        o_is_album = new Array("");
    } else {
        o_is_album = is_album
    }
    if (typeof(is_edit_allowed) == "undefined") {
        o_is_edit_allowed = new Array("")
    } else {
        o_is_edit_allowed = is_edit_allowed;
    }

    rtMedia = window.rtMedia || {};

    rtMedia = window.rtMedia || {};

    rtMedia.Context = Backbone.Model.extend({
        url: function() {
            var url = rtmedia_media_slug + "/";
            if (!upload_sync && nextpage > 0)
                url += 'pg/' + nextpage + '/'
            return url;
        },
        defaults: {
            "context": "post",
            "context_id": false
        }
    });

    rtMedia.Media = Backbone.Model.extend({
        defaults: {
            "id": 0,
            "blog_id": false,
            "media_id": false,
            "media_author": false,
            "media_title": false,
            "album_id": false,
            "media_type": "photo",
            "activity_id": false,
            "privacy": 0,
            "views": 0,
            "downloads": 0,
            "ratings_average": 0,
            "ratings_total": 0,
            "ratings_count": 0,
            "likes": 0,
            "dislikes": 0,
            "guid": false,
            "width": 0,
            "height": 0,
            "rt_permalink": false,
            "media_category": "Uncategorized",
            "activity_like": "like"
                    //			"next"			: -1,
                    //			"prev"			: -1
        }

    });

    rtMedia.Gallery = Backbone.Collection.extend({
        model: rtMedia.Media,
        url: function() {
            var temp = window.location.pathname;
            var url = '';
            if (temp.indexOf("/" + rtmedia_media_slug + "/") == -1) {
                url = rtmedia_media_slug + '/';
            } else {
                if (temp.indexOf('pg/') == -1)
                    url = temp;
                else
                    url = window.location.pathname.substr(0, window.location.pathname.lastIndexOf("pg/"));
            }
            if (!upload_sync && nextpage > 1) {
                if (url.substr(url.length - 1) != "/")
                    url += "/"
                url += 'pg/' + nextpage + '/';
            }
            return url;
        },
        getNext: function(page, el, element) {
	    that = this;
	    if( rtmedia_load_template_flag == true ) {
		jQuery("#rtmedia-gallery-item-template").load(template_url, {
		    backbone: true,
		    is_album: o_is_album,
		    is_edit_allowed: o_is_edit_allowed
		    },function(){
		    rtmedia_load_template_flag = false;
		    that.getNext();
		});
	    }
	    if( !rtmedia_load_template_flag ) {
		var query = {
                json: true,
                rtmedia_page: nextpage
		};
		if (el == undefined){
		    el = jQuery(".rtmedia-list").parent().parent();
		}
		if (el != undefined) {
		    if(element != undefined) {
			jQuery(element).parent().parent().prevAll("input[type=hidden]").each(function(e) {
			    query[jQuery(this).attr("name")] = jQuery(this).val();
			});
		    } else {
			jQuery(el).find("input[type=hidden]").each(function(e) {
			    query[jQuery(this).attr("name")] = jQuery(this).val();
			});
		    }

		}
		this.fetch({
		    data: query,
		    success: function(model, response) {
                        jQuery('.rtm-media-loading').hide();
			var list_el = "";
			//if(typeof(element) === "undefined" )
			    list_el = (jQuery(".rtmedia-list.col4").length != 0) ? jQuery(".rtmedia-list.col4:last") : jQuery(".rtmedia-list:last");
			//else
			//    list_el = element.parent().siblings('.rtmedia-list');
			nextpage = response.next;
			var galleryViewObj = new rtMedia.GalleryView({
			    collection: new rtMedia.Gallery(response.data),
			    el: list_el
			});
			//element.show();
		    }
		});
	    }
        },
        reloadView: function() {
            upload_sync = true;
            nextpage = 1;
            this.getNext();
        }


    });

    rtMedia.MediaView = Backbone.View.extend({
        tagName: 'li',
        className: 'rtmedia-list-item',
        initialize: function() {
        	t = jQuery("#rtmedia-gallery-item-template").html();
        	template = t.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            this.template = _.template(template);
            //alert('123333333333');
            this.model.bind('change', this.render);
            this.model.bind('remove', this.unrender);
            this.render();
        },
        render: function() {
            jQuery(this.el).html(this.template(this.model.toJSON()));
            return this.el;
        },
        unrender: function() {
            jQuery(this.el).remove();
        },
        remove: function() {
            this.model.destroy();
        }
    });

    rtMedia.GalleryView = Backbone.View.extend({
        tagName: 'ul',
        className: 'rtmedia-list',
        initialize: function() {
        	t = jQuery("#rtmedia-gallery-item-template").html();
        	template = t.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            this.template = _.template(template);
            this.render();
        },
        render: function() {

            that = this;

            if (upload_sync) {
                jQuery(that.el).html('');
            }

            $.each(this.collection.toJSON(), function(key, media) {

                var media_type = jQuery(this).attr('media_type');
                if (media_type == "photo") {
                    var nowidappend = jQuery(this).attr('id');
                    var jsappend = "<script type='text/javascript'>toolTips('#" + nowidappend + "',\"Add to my <a href='http://dremboard.com/members/admin/media/photo/?dremboard=" + nowidappend + "?keepThis=true&TB_iframe=true&height=600&width=500' class='thickbox' alt='Add to my drēmboard'>drēmboard</a>\");</script>";
                    var newhtml = jQuery(that.template(media));
                    var cover_art = jQuery(this).attr('cover_art');

                    var activity_like = jQuery(this).attr('activity_like');

                    /*if (media_type != "photo"){
                     jQuery(".rtmedia-item-category" , newhtml).remove();                
                     }*/
                    jQuery(".template." + media_type, newhtml).removeClass("template").removeClass(media_type);
                    jQuery(".template", newhtml).remove();
                    newhtml.css("width", "25%");
                    jQuery(that.el).append(newhtml);
                    jQuery('#' + nowidappend, newhtml).append(jsappend);
                    //jQuery(that.el).append(jsappend);
                    //alert(jQuery(that.template(media)).html());
                    //alert(jQuery(that.el).attr('id'))
                    //jQuery(that.el).append(jsappend);
                    if (cover_art > 0){
                        var cover_btn = jQuery(".cover-media-"+jQuery(this).attr('id'), newhtml);
                        if (cover_btn.length != 0){
                            cover_btn.removeClass("no-cover");
                            cover_btn.addClass("cover");
                            cover_btn.html("Cover");
                            cover_btn.attr("title", "Not cover this item");
                        }
                    }
                    jQuery(".compare_group", newhtml).each(function() {
                        var rid = jQuery(".rid", this).val();
                        var ridd = jQuery(".ridd", this).val();
                        var ert = JSON.parse(jQuery(".ert", this).val());
                        var erth = JSON.parse(jQuery(".erth", this).val());

                        if (ert != null && ert.indexOf(rid) === -1) {
                            jQuery(".contain_rid_ert", this).remove();
                        } else {
                            jQuery(".uncontain_rid_ert", this).remove();
                        }

                        if (erth != null && erth.indexOf(ridd) === -1) {
                            jQuery(".contain_ridd_erth", this).remove();
                        } else {
                            jQuery(".uncontain_ridd_erth", this).remove();
                        }

                        if (rid === 0) {
                            jQuery(".unzero_rid", this).remove();
                        }

                        jQuery(this).removeClass("compare_group");

                        var id = "like-activity-" + rid;
                        var likebtn = jQuery("#" + id, this);
                        likebtn.hide();

                        var r;
                        if (activity_like) {
                            var cmp = activity_like.toLowerCase();
                            likebtn.html(activity_like);

                            if (cmp.search("unlike") !== -1) {
                                r = id.replace("like", "unlike");
                                likebtn.removeClass("like").addClass("unlike").attr("title", bp_like_terms_unlike_message).attr("id", r)
                            } else {

                            }
                            likebtn.show();
                        }
                    });
                }else if(media_type == "album"){
                    var newhtml = jQuery(that.template(media));
                    jQuery(that.el).append(newhtml);
                }
            });
            if (upload_sync) {
                upload_sync = false;
            }
            if (nextpage > 1) {
		jQuery(that.el).siblings('.rtmedia_next_prev').children('#rtMedia-galary-next').show();
		//jQuery("#rtMedia-galary-next").show();
            }


        },
        appendTo: function(media) {
            //console.log("append");
            var mediaView = new rtMedia.MediaView({
                model: media
            });
            jQuery(this.el).append(mediaView.render().el);
            //alert(jQuery(this.el).html());
        }
    });


    galleryObj = new rtMedia.Gallery();

    jQuery("body").append('<script id="rtmedia-gallery-item-template" type="text/template"></script>');
	//!!!!!!!!! jQuery("body").append('<div id="rtmedia-gallery-item-template" type="text/template"></div>');

    //jQuery(document).on("click", "#rtMedia-galary-next", function(e) {
    auto_load_more_rtmedia_gallery();
    function auto_load_more_rtmedia_gallery() {

        if (jQuery("#rtMedia-galary-next").length == 0)
            return;
        
        jQuery(window).scroll(function() {
            load_more_rtmedia_gallery_action();
        })
        jQuery(window).resize(function() {
            load_more_rtmedia_gallery_action();
        })
        load_more_rtmedia_gallery_action();
    }
    
    function load_more_rtmedia_gallery_action(){
        if(is_load_more_rtmedia_gallery_shown()){
            load_more_rtmedia_gallery();
        }
    }
    
    function is_load_more_rtmedia_gallery_shown() {
        var load_more = jQuery("#rtMedia-galary-next");
        if (load_more.length == 0 || load_more.css('display') == "none")
            return false;

        var win_btm = window.pageYOffset + jQuery(window).height();
        var load_more_top = load_more.offset().top;

        if (load_more_top + 50 < win_btm) {
            return true;
        }
        return false;
    }
    
    function load_more_rtmedia_gallery(){
        var load_more = jQuery("#rtMedia-galary-next");
        if (load_more.length == 0)
            return;
        load_more.before( "<div class='rtm-media-loading'><img src='" + rMedia_loading_media + "' /></div>" );
	load_more.hide();
	galleryObj.getNext(nextpage, jQuery(this).parent().parent().parent(), load_more);
    }

    if (window.location.pathname.indexOf(rtmedia_media_slug) != -1) {
        var tempNext = window.location.pathname.substring(window.location.pathname.lastIndexOf("pg/") + 5, window.location.pathname.lastIndexOf("/"));
        if (isNaN(tempNext) === false) {
            nextpage = parseInt(tempNext) + 1;
        }
    }



    window.UploadView = Backbone.View.extend({
        events: {
            "click #rtMedia-start-upload": "uploadFiles"
        },
        initialize: function(config) {
            this.uploader = new plupload.Uploader(config);
        },
        render: function() {

        },
        initUploader: function(a) {

            if(typeof(a)!=="undefined") a=false;// if rtmediapro widget calls the function, dont show max size note.
            this.uploader.init();
            //The plupload HTML5 code gives a negative z-index making add files button unclickable
            jQuery(".plupload.html5").css({
                zIndex: 0
            });
            jQuery("#rtMedia-upload-button").css({
                zIndex: 2
            });
            if(a!==false){
                window.file_size_info = rtmedia_max_file_msg + " : " + this.uploader.settings.max_file_size_msg ;
                window.file_extn_info = rtmedia_allowed_file_formats + " : " + this.uploader.settings.filters[0].extensions;
                var info = window.file_size_info + ", " + window.file_extn_info;
                jQuery(".rtm-file-size-limit").attr('title', info);
 //jQuery("#rtMedia-upload-button").after("<span>( <strong>" + rtmedia_max_file_msg + "</strong> "+ this.uploader.settings.max_file_size_msg + ")</span>");
            }

            return this;
        },
        uploadFiles: function(e) {
            if (e != undefined)
                e.preventDefault();
            this.uploader.start();
            return false;
        }

    });



    if (jQuery("#rtMedia-upload-button").length > 0) {
	if( typeof rtmedia_upload_type_filter == "object" && rtmedia_upload_type_filter.length > 0 ) {
	    rtMedia_plupload_config.filters[0].extensions = rtmedia_upload_type_filter.join();
	}
	
	var title_desc_modal = jQuery('<div id="title_desc_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
            + '<div class="rtmedia-edit-title" style="border-radius: 8px; background: #fff; padding: 20px; padding-bottom: 50px;">'
            + '<label>Title : </label>'
            + '<input type="text" class="rtmedia-title-editor" id="memory_title" value="" style="width:100%;">'
        	+ '<label>Description : </label>'
        	+ '<textarea class="rtmedia-desc-textarea" id="memory_desc" style="width: 100%; margin-bottom:20px;"></textarea>'
            + '<input class="okbtn" type="button" value="Ok"/>'
            + '<input class="cancelbtn" type="button" value="Cancel"/>'
            + '</div>'
            + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
            + '</div>');
    var memory_title = null;
    var memory_desc = null;
    title_desc_modal.dialog({
        dialogClass:'wp-dialog',
        title: "Edit Media Title",
        autoOpen:false,
        closeOnEscape: false,
        open: function(event, ui) { 
                jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
                jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
                jQuery(this).parent().css("background", "none");
                jQuery(this).parent().css("border", "none");
        },
        width:500,
        modal:true,
    });
    jQuery(".closeicon", title_desc_modal).click(function(){
        title_desc_modal.dialog("close");
    });
    jQuery(".cancelbtn", title_desc_modal).click(function(){
        title_desc_modal.dialog("close");
    });
    jQuery(".okbtn", title_desc_modal).click(function(){
        if(jQuery("#memory_title", title_desc_modal).val() !== ""){
            memory_title.innerHTML = jQuery("#memory_title", title_desc_modal).val();
        }
        if(jQuery("#memory_desc", title_desc_modal).val() !== ""){
            memory_desc.innerHTML = jQuery("#memory_desc", title_desc_modal).val();
        }
        jQuery("#memory_title", title_desc_modal).val("");
        memory_title = null;
        jQuery("#memory_desc", title_desc_modal).val("");
        memory_desc = null;
        title_desc_modal.dialog("close");
    });
	
	uploaderObj = new UploadView(rtMedia_plupload_config);

        uploaderObj.initUploader();


        uploaderObj.uploader.bind('UploadComplete', function(up, files) {
	    activity_id = -1;
            if( typeof rtmedia_gallery_reload_on_upload != "undefined" && rtmedia_gallery_reload_on_upload =='1'){ //reload gallery view when upload completes if enabled( by default enabled)
                galleryObj.reloadView();
            }
            jQuery('.start-media-upload').hide();
        });

        uploaderObj.uploader.bind('FilesAdded', function(up, files) {
            var upload_size_error = false;
            var upload_error = "";
            var upload_error_sep = "";
            var upload_remove_array= [];
            $.each(files, function(i, file) {
                var hook_respo = rtMediaHook.call('rtmedia_js_file_added', [up,file, "#rtMedia-queue-list tbody"]);
                if( hook_respo == false){
                    file.status = -1;
                    upload_remove_array.push(file.id);
                    return true;
                }
                jQuery('.rtmedia-upload-input').attr('value', rtmedia_add_more_files_msg);
                jQuery('.start-media-upload').show();
                if (uploaderObj.uploader.settings.max_file_size < file.size) {
//                    upload_size_error = true
//                    upload_error += upload_error_sep + file.name;
//                    upload_error_sep = ",";
//                    var tr = "<tr style='background-color:lightpink;color:black' id='" + file.id + "'><td>" + file.name + "(" + plupload.formatSize(file.size) + ")" + "</td><td colspan='4'> " + rtmedia_max_file_msg + plupload.formatSize(uploaderObj.uploader.settings.max_file_size) + "</td></tr>"
//                    jQuery("#rtMedia-queue-list tbody").append(tr);
                    return true;
                }
                var tmp_array =  file.name.split(".");
                var ext_array = uploaderObj.uploader.settings.filters[0].extensions.split(',');
                if(tmp_array.length > 1){
                    var ext= tmp_array[tmp_array.length - 1];
		    ext = ext.toLowerCase();
                    if( jQuery.inArray( ext ,ext_array) === -1){
                        return true;
                    }
                }else{
                    return true;
                }

                uploaderObj.uploader.settings.filters[0].title;
                tdName = document.createElement("td");
                tdName.innerHTML = file.name;
                tdName.className = "plupload_file_name";
                tdDesc = document.createElement("td");
                tdDesc.innerHTML = "";
                tdDesc.className = "plupload_file_desc";
                jQuery(tdDesc).css('display', 'none');
                tdStatus = document.createElement("td");
                tdStatus.className = "plupload_file_status";
                tdStatus.innerHTML = rtmedia_waiting_msg;
                tdSize = document.createElement("td");
                tdSize.className = "plupload_file_size";
                tdSize.innerHTML = plupload.formatSize(file.size);
                tdDelete = document.createElement("td");
                tdDelete.innerHTML = "<span class='remove-from-queue'>&times;</span>";
                tdDelete.title = rtmedia_close;
                tdDelete.className = "close plupload_delete";
                tdEdit = document.createElement("td");
                tdEdit.innerHTML = "";
                tdEdit.className = "plupload_media_edit";
                tr = document.createElement("tr");
                tr.className = 'upload-waiting';
                tr.id = file.id;
                tr.appendChild(tdName);
                tr.appendChild(tdDesc);
                tr.appendChild(tdStatus);
                tr.appendChild(tdSize);
                tr.appendChild(tdEdit);
                tr.appendChild(tdDelete);
                jQuery("#rtMedia-queue-list").append(tr);
                
                memory_title = tdName;
                memory_desc = tdDesc
                title_desc_modal.dialog("open");
                
                //Delete Function
                jQuery("#" + file.id + " td.plupload_delete .remove-from-queue").click(function(e) {
                    e.preventDefault();
                    uploaderObj.uploader.removeFile(up.getFile(file.id));
                    jQuery("#" + file.id).remove();
                    return false;
                });

            });
            $.each(upload_remove_array, function(i, rfile) {
               if(up.getFile(rfile))
                    up.removeFile(up.getFile(rfile));
            });

//            if (upload_size_error) {
//                // alert(upload_error + " because max file size is " + plupload.formatSize(uploaderObj.uploader.settings.max_file_size) );
//            }
        });

        uploaderObj.uploader.bind('Error', function(up, err) {

            if(err.code== -600){ //file size error // if file size is greater than server's max allowed size
                var tmp_array;
                var ext = tr = '';
                tmp_array =  err.file.name.split(".");
                if(tmp_array.length > 1){
                    ext= tmp_array[tmp_array.length - 1];
                    if( !(typeof(up.settings.upload_size) != "undefined" && typeof(up.settings.upload_size[ext]) != "undefined" &&  typeof(up.settings.upload_size[ext]['size']) )){
                        tr = "<tr class='upload-error'><td>" + err.file.name + "</td><td> " + rtmedia_max_file_msg + plupload.formatSize( up.settings.max_file_size / 1024 * 1024) + " <i class='rtmicon-info-circle' title='" + window.file_size_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td></td><td class='close error_delete'>&times;</td></tr>";
                    }
                }
                //append the message to the file queue
                jQuery("#rtMedia-queue-list tbody").append(tr);
            }
            else {

                if( err.code == -601) { // file extension error
                    err.message = rtmedia_file_extension_error_msg;
                }
                var tr = "<tr class='upload-error'><td>" + (err.file ? err.file.name : "") + "</td><td>" + err.message + " <i class='rtmicon-info-circle' title='" + window.file_extn_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td></td><td class='close error_delete'>&times;</td></tr>";
                jQuery("#rtMedia-queue-list tbody").append(tr);
            }

            jQuery('.error_delete').on('click',function(e){
                e.preventDefault();
                jQuery(this).parent('tr').remove();
            });
            return false;

        });

        jQuery('.start-media-upload').on('click', function(e){
            e.preventDefault();
            uploaderObj.uploadFiles();
        });

        uploaderObj.uploader.bind('QueueChanged', function(up) {

//            jQuery('.rtmedia-upload-input').attr('value','Add more files');
//            jQuery('.start-media-upload').show();

        });

        uploaderObj.uploader.bind('UploadProgress', function(up, file) {
            //jQuery("#" + file.id + " .plupload_file_status").html(file.percent + "%");
            jQuery("#" + file.id + " .plupload_file_status").html( rtmedia_uploading_msg + '( ' + file.percent + '% )');
            jQuery("#" + file.id).addClass('upload-progress');
            jQuery("#" + file.id).removeClass('upload-waiting');
            if (file.percent == 100) {
                 jQuery("#" + file.id).toggleClass('upload-success');
            }
        });
        uploaderObj.uploader.bind('BeforeUpload', function(up, file) {
            var privacy = jQuery("#rtm-file_upload-ui select.privacy").val();
            if(privacy !== undefined) {
                up.settings.multipart_params.privacy = jQuery("#rtm-file_upload-ui select.privacy").val();
            }
            if (jQuery("#rt_upload_hf_redirect").length > 0)
                up.settings.multipart_params.redirect = up.files.length;
            jQuery("#rtmedia-uploader-form input[type=hidden]").each(function() {
                up.settings.multipart_params[jQuery(this).attr("name")] = jQuery(this).val();
            });
            up.settings.multipart_params.activity_id = activity_id;
            if (jQuery('#rtmedia-uploader-form .rtmedia-user-album-list').length > 0)
                up.settings.multipart_params.album_id = jQuery('#rtmedia-uploader-form .rtmedia-user-album-list').find(":selected").val();
            else if (jQuery('#rtmedia-uploader-form .rtmedia-current-album').length > 0)
                up.settings.multipart_params.album_id = jQuery('#rtmedia-uploader-form .rtmedia-current-album').val();
            
            up.settings.multipart_params.title = jQuery(".upload-waiting .plupload_file_name").html();
        	up.settings.multipart_params.description = jQuery(".upload-waiting .plupload_file_desc").html();
        });

        uploaderObj.uploader.bind('FileUploaded', function(up, file, res) {
            if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) { //test for MSIE x.x;
                var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number

                   if(ieversion <10) {
                           if( typeof res.response !== "undefined" )
                               res.status = 200;
                   }
            }
            var rtnObj;
             try {

                rtnObj = JSON.parse(res.response);
                uploaderObj.uploader.settings.multipart_params.activity_id = rtnObj.activity_id;
                activity_id = rtnObj.activity_id;
                if(rtnObj.permalink != ''){
                	origin_name = jQuery("#" + file.id + " .plupload_file_name").html();
                    jQuery("#" + file.id + " .plupload_file_name").html("<a href='" + rtnObj.permalink + "' target='_blank' title='" + rtnObj.permalink + "'>" + origin_name + "</a>");
                    //!!!!!!
                    rtmedia_edit = '<font color=\'red\'>Edit Description</font>';
                    jQuery("#" + file.id + " .plupload_media_edit").html("<a href='" + rtnObj.permalink + "edit' target='_blank'><span title='" + rtmedia_edit_media + "'><i class='rtmicon-edit'></i> " + rtmedia_edit + "</span></a>");
                    jQuery("#" + file.id + " .plupload_delete").html("<span id='" + rtnObj.media_id + "' class='rtmedia-delete-uploaded-media' title='" + rtmedia_delete + "'>&times;</span>");
                    //!!!!!!
                    jQuery('.plupload_media_edit').fadeIn();
                    jQuery('.plupload_media_edit').fadeOut();
                    jQuery('.plupload_media_edit').fadeIn();
                }
                else
                {
                	//!!!!!!
                	var str1=location.href;
                	var num1=str1.indexOf("media/photo");
                	if (num1 != -1)
                	{
                		jQuery("#" + file.id + " .plupload_file_name").html("<font color='color'>Error, You can only upload photo file in Drēms panel</font>");
                	}
                	
                	return;
                }

            } catch (e) {
                // console.log('Invalid Activity ID');
            }
            if (res.status == 200 || res.status == 302) {
                if (uploaderObj.upload_count == undefined)
                    uploaderObj.upload_count = 1;
                else
                    uploaderObj.upload_count++;

                if (uploaderObj.upload_count == up.files.length && jQuery("#rt_upload_hf_redirect").length > 0 && jQuery.trim(rtnObj.redirect_url.indexOf("http") == 0)) {
                    window.location = rtnObj.redirect_url;
                }

                jQuery("#" + file.id + " .plupload_file_status").html( rtmedia_uploaded_msg);

            }else {
                jQuery("#" + file.id + " .plupload_file_status").html( rtmedia_upload_failed_msg );
            }

            files = up.files;
            lastfile = files[files.length - 1];


        });

        uploaderObj.uploader.refresh();//refresh the uploader for opera/IE fix on media page

        jQuery("#rtMedia-start-upload").click(function(e) {
            uploaderObj.uploadFiles(e);
        });
        jQuery("#rtMedia-start-upload").hide();

        jQuery(document).on('click', '#rtm_show_upload_ui', function(){
            jQuery('#rtm-media-gallery-uploader').slideToggle();
            uploaderObj.uploader.refresh();//refresh the uploader for opera/IE fix on media page
            jQuery('#rtm_show_upload_ui').toggleClass('primary');
        });
    } else {
	jQuery(document).on('click', '#rtm_show_upload_ui', function(){
            jQuery('#rtm-media-gallery-uploader').slideToggle();
            jQuery('#rtm_show_upload_ui').toggleClass('primary');
	});
    }

    jQuery(document).on( 'click','.plupload_delete .rtmedia-delete-uploaded-media',function(){
        var that = jQuery(this);
        if(confirm(rtmedia_delete_uploaded_media)){
            var nonce = jQuery('#rtmedia-upload-container #rtmedia_media_delete_nonce').val();
            var media_id = jQuery(this).attr('id');
            var data = {
                action : 'delete_uploaded_media',
                nonce : nonce,
                media_id : media_id
            }

            $.post( ajaxurl, data, function(response){
                if(response == '1'){
                    that.closest('tr').remove();
                    jQuery('#'+media_id).remove();
                }
            });
        }
    });


});
/** History Code for route

 var rtMediaRouter = Backbone.Router.extend({
 routes: {
 "media/*": "getMedia"
 }
 });
 var app_router = new rtMediaRouter;
 app_router.on('route:getMedia', function() {
 // Note the variable in the route definition being passed in here
 });
 Backbone.history.start({pushState: true});

 **/


/** Activity Update Js **/
// make modal for flag and submit activity.
    var activity_flag_html = '<div id="activity_flag_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
        + '<div class="modal_container" style="border-radius: 8px; background: #fff;">'
        + "<div class='title'>Report Drēm</div>"
        + "<div class='divider'></div>"
        + "<div class='description'>Why are you reporting this drēm?</div>"
        + "<div class='condition-container'>"
        + "<input type='radio' name='flag_option' id='annoy' value='annoy'/><label for='annoy'>It's annoying or not interesting</label><br>"
        + "<input type='radio' name='flag_option' id='nudity' value='nudity'/><label for='nudity'>Nudity or Pornography</label><br>"
        + "<input type='radio' name='flag_option' id='graphic' value='graphic'/><label for='graphic'>Graphic Violence</label><br>"
        + "<input type='radio' name='flag_option' id='attack' value='attack'/><label for='attack'>Attacks a group or individual</label><br>"
        + "<input type='radio' name='flag_option' id='improper' value='improper'/><label for='improper'>I think it shouldn’t be on Drēmboard</label><br>"
        + "<input type='radio' name='flag_option' id='spam' value='spam'/><label for='spam'>It's Spam</label><br>"
        + "</div>" //condition-container
        + "<div class='footer'>" 
        + "<form action='/copyright-complaint/' method='post'>"
        + "<input name='show_id' type='hidden' value='' id='show_id'/>"
        + "<input name='activity_id' type='hidden' value='' id='activity_id'/>"
        + "<div class='copy-right'>Is this your <br><a href='' class='submit'>Intellectual Property</a>?</div>"
        + "</form>"
        + "<div class='button-container'>" 
        + "<input class='cancelbtn' type='button' value='Cancel'>"
        + "<input class='reportbtn submit' type='button' value='Report Drēm'>"
        + "</div>" //button-container
        + "</div>" //footer
        + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
        + '</div>'; //modal container.
        + '</div>'; //activity_flag_modal
    
    var activity_flag_modal = jQuery(activity_flag_html);
    activity_flag_modal.dialog({
        dialogClass:'wp-dialog',
        title: "Flag Media",
        autoOpen:false,
        closeOnEscape: false,
        open: function(event, ui) { 
                jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
                jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
                jQuery(this).parent().css("background", "none");
                jQuery(this).parent().css("border", "none");
                reset_activity_flag_modal();
        },
        width:500,
        modal:true,
    });
    
    jQuery(".closeicon", activity_flag_modal).click(function(){
        activity_flag_modal.dialog("close");
    });
    jQuery(".cancelbtn", activity_flag_modal).click(function(){
        activity_flag_modal.dialog("close");
    });
    jQuery("a.submit", activity_flag_modal).click(function(){
        var form = jQuery(this).closest("form");
        form.submit();
        return false;
    });
    jQuery(".reportbtn", activity_flag_modal).click(function(){
        var activity_id = jQuery("#activity_id", activity_flag_modal).val();
        var show_id = jQuery("#show_id", activity_flag_modal).val();
        var checked = jQuery(".condition-container input:checked", activity_flag_modal).val();
        if (checked != null && checked != '') {
            do_flag_activity(activity_id, show_id, checked);
            activity_flag_modal.dialog("close");
        }
    });

function do_flag_activity(activity_id, showid, checked) {
    var dataString = 'activity_action=flag&activity_id=' + activity_id + '&flag_slug=' + checked;
    jQuery.ajax({
        type: "POST",
        url: "/wp-content/plugins/buddypress/bp-themes/bp-default/activity/submit.php",
        data: dataString,
        success: function(msg) {
            msg = msg.replace(/(^\s*)|(\s*$)/g, "");
            result = JSON.parse(msg);

            if (result.status == '0') {
                jQuery("#hexd-" + showid).html(result.msg).show({duration: 500, queue: true})
                        .delay(2000)
                        .hide({duration: 500, queue: true});
            }
            if (result.status == '1') {
                jQuery("#hexd-" + showid).html(result.msg).show("slow");
            }
        }
    });

}
    function reset_activity_flag_modal(){
        jQuery(".condition-container input:checked", activity_flag_modal).removeAttr("checked");
    }
    function flag_activity(activity_id, show_id){
        jQuery("#activity_id", activity_flag_modal).val(activity_id);
        jQuery("#show_id", activity_flag_modal).val(show_id);
        activity_flag_modal.dialog("open");
    }
    /////////////////// flag modal on pop up modal //////////////////////////
var flag_activity_on_popup = null;
function ready_for_flag_on_pop_up() {
    var activity_flag_modal = jQuery(activity_flag_html);
    var reset_activity_flag_modal_popup = function() {
        jQuery(".condition-container input:checked", activity_flag_modal).removeAttr("checked");
    }
    activity_flag_modal_ctr = modal_Controller(activity_flag_modal, {width: '500px', height: 'auto', open_func: reset_activity_flag_modal_popup});
    jQuery(".closeicon", activity_flag_modal).click(function() {
        activity_flag_modal_ctr.close();
    });
    jQuery(".cancelbtn", activity_flag_modal).click(function() {
        activity_flag_modal_ctr.close();
    });
    jQuery("a.submit", activity_flag_modal).click(function(){
        var form = jQuery(this).closest("form");
        form.submit();
        return false;
    });
    jQuery(".reportbtn", activity_flag_modal).click(function() {
        var activity_id = jQuery("#activity_id", activity_flag_modal).val();
        var show_id = jQuery("#show_id", activity_flag_modal).val();
        var checked = jQuery(".condition-container input:checked", activity_flag_modal).val();
        if (checked != null && checked != '') {
            do_flag_activity(activity_id, show_id, checked);
            activity_flag_modal_ctr.close();
        }
    });

    flag_activity_on_popup = function(activity_id, show_id) {
        jQuery("#activity_id", activity_flag_modal).val(activity_id);
        jQuery("#show_id", activity_flag_modal).val(show_id);
        activity_flag_modal_ctr.open();
    }
}
// make modal for share and submit activity.

    	var activity_share_list = jQuery("<select id='activity_share_list' name='activity_share_list' class='activity_share_list'></select>");
    	//var acitivity_share_wall = jQuery("<option val='activity_wall' selected>On your wall Timeline</option>");
    	var acitivity_share_own = jQuery("<option val='activity_own'>On your own Timeline</option>");
    	var acitivity_share_friend = jQuery("<option val='activity_friend'>Share with friend</option>");
    	var acitivity_share_group = jQuery("<option val='activity_group'>In a group</option>");
    	var acitivity_share_message = jQuery("<option val='activity_private_message'>In a private message</option>");
    	activity_share_list//.append(acitivity_share_wall)
    		.append(acitivity_share_own)
    		.append(acitivity_share_friend)
    		.append(acitivity_share_group)
    		.append(acitivity_share_message);
    	var activity_share_info = jQuery("<div class='activity_share_info'><input type='text' name='send-to-input' class='send-to-input helper-input-box only_one' id='send-to-input' autocomplete='off'></div>");
    	var activity_share_inform = jQuery("<div class='activity_share_inform'></div>");
    	activity_share_info.hide();
    	activity_share_inform.hide();
    	var activity_share_content = jQuery('<div id="content" style="margin-top: 10px;">'
                +'<label style="display: block;line-height: 24px;">Description: </label>'
                +'<textarea  id="desc" class="helper-input-box has_mark" style="display: inline-block; width: 500px; height:100px; margin-bottom: 10px;"></textarea>'
                +'</div>');
    	
        var  activity_share_modal = jQuery('<div id="activity_share_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
            + '<div class="modal_container" style="border-radius: 8px; background: #fff; padding: 20px; padding-bottom: 50px;">'
            + '<input type="hidden" id="show_id" name="show_id"/>'
            + '<input type="hidden" id="activity_id" name="activity_id"/>'
            + '<input class="okbtn" type="button" value="Share"/>'
            + '<input class="cancelbtn" type="button" value="Cancel"/>'
            + '</div>'
                + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
            + '</div>');
        jQuery(".modal_container", activity_share_modal).prepend(activity_share_content).prepend(activity_share_inform).prepend(activity_share_info).prepend(activity_share_list);
        
    activity_share_modal.dialog({
        dialogClass:'wp-dialog',
        title: "Share Media",
        autoOpen:false,
        closeOnEscape: false,
        open: function(event, ui) { 
                jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
                jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
                jQuery(this).parent().css("background", "none");
                jQuery(this).parent().css("border", "none");
                reset_activity_share_modal();
        },
        width:500,
        modal:true,
    });
    activity_share_list.change(function(){
    	var share_mode = jQuery(this).val();
    	if (share_mode === "Share with friend"){
    		jQuery("input", activity_share_info).val("").attr("usertype", "friend").attr("placeholder", "@friend name");
    		activity_share_info.show();
    	}else if(share_mode === "In a group"){
    		jQuery("input", activity_share_info).val("").attr("usertype", "group").attr("placeholder", "@group name");
    		activity_share_info.show();
    	}else if(share_mode === "In a private message"){
    		jQuery("input", activity_share_info).val("").attr("usertype", "user").attr("placeholder", "@user name");
    		activity_share_info.show();
    	}else{
    		jQuery("input", activity_share_info).val("");
    		activity_share_info.hide();
    	}
    });
    jQuery(".closeicon", activity_share_modal).click(function(){
        activity_share_modal.dialog("close");
    });
    jQuery(".cancelbtn", activity_share_modal).click(function(){
        activity_share_modal.dialog("close");
    });
    jQuery(".okbtn", activity_share_modal).click(function(){
    	var id = jQuery("#activity_id", activity_share_modal).val();
    	var showid = jQuery("#show_id", activity_share_modal).val();
    	var desc = jQuery("#desc", activity_share_content).val();
    	var share_mode = activity_share_list.val();
    	var share_user = jQuery.trim(jQuery("input", activity_share_info).val());
    	
    	var dataString = 'activity_action=share&id=' + id +'&desc=' + desc + '&share_mode=' + share_mode + '&share_user=' + share_user;
    	
    	if (activity_share_info.css("display") !== "none" && (share_user === "" || share_user.indexOf("@") != -1)){
    		activity_share_inform.html("You must provide a recipient.").show({duration: 500, queue: true}).delay(2000).hide({duration: 500, queue: true});
    		return;
    	}
    	
    	if (id != null) {
            jQuery.ajax({
                type: "POST",
                url: "/wp-content/plugins/buddypress/bp-themes/bp-default/activity/submit.php",
                data: dataString,
                success: function(msg) {
                    msg = msg.replace(/(^\s*)|(\s*$)/g, "");
                    result = JSON.parse(msg);
                    
                    if (result.status == '0') {
                        jQuery("#hexd-" + showid).html(result.msg).show({duration: 500, queue: true}).delay(2000).hide({duration: 500, queue: true});
                    }
                    if (result.status == '1') {
                        jQuery("#hexd-" + showid).html(result.msg).show("slow");
                    }
                }
            });
    	}
    	activity_share_modal.dialog("close");
    });
    add_input_for_bp_user(jQuery("textarea",activity_share_content));
    add_input_for_bp_user(jQuery("input",activity_share_info));

function reset_activity_share_modal(){
	jQuery("option:first", activity_share_list).attr("selected", "selected")
	jQuery("input", activity_share_info).val("");
	activity_share_info.hide();
	activity_share_inform.html("").hide();
}

function share_activity(showid, id, desc){
	jQuery("#desc", activity_share_content).val(desc);
	jQuery("#show_id", activity_share_modal).val(showid);
	jQuery("#activity_id", activity_share_modal).val(id);
	activity_share_modal.dialog("open");
}
/////////////////// share modal on pop up modal //////////////////////////
var share_activity_on_popup;
function ready_for_share_on_pop_up() {
    var activity_share_list = jQuery("<select id='activity_share_list' name='activity_share_list' class='activity_share_list'></select>");
    //var acitivity_share_wall = jQuery("<option val='activity_wall' selected>On your wall Timeline</option>");
    var acitivity_share_own = jQuery("<option val='activity_own'>On your own Timeline</option>");
    var acitivity_share_friend = jQuery("<option val='activity_friend'>Share with friend</option>");
    var acitivity_share_group = jQuery("<option val='activity_group'>In a group</option>");
    var acitivity_share_message = jQuery("<option val='activity_private_message'>In a private message</option>");
    activity_share_list//.append(acitivity_share_wall)
            .append(acitivity_share_own)
            .append(acitivity_share_friend)
            .append(acitivity_share_group)
            .append(acitivity_share_message);
    var activity_share_info = jQuery("<div class='activity_share_info'><input type='text' name='send-to-input' class='send-to-input helper-input-box only_one' id='send-to-input' autocomplete='off'></div>");
    var activity_share_inform = jQuery("<div class='activity_share_inform'></div>");
    activity_share_info.hide();
    activity_share_inform.hide();
    var activity_share_content = jQuery('<div id="content" style="margin-top: 10px;">'
            + '<label style="display: block;line-height: 24px;">Description: </label>'
            + '<textarea  id="desc" class="helper-input-box has_mark" style="display: inline-block; width: 500px; height:100px; margin-bottom: 10px;"></textarea>'
            + '</div>');

    var activity_share_modal = jQuery('<div id="activity_share_modal">'
            + '<div class="modal_container" style="padding-bottom: 30px;">'
            + '<input type="hidden" id="show_id" name="show_id"/>'
            + '<input type="hidden" id="activity_id" name="activity_id"/>'
            + '<input class="okbtn" type="button" value="Share"/>'
            + '<input class="cancelbtn" type="button" value="Cancel"/>'
            + '</div>'
            + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
            + '</div>');
    jQuery(".modal_container", activity_share_modal).prepend(activity_share_content).prepend(activity_share_inform).prepend(activity_share_info).prepend(activity_share_list);

    activity_share_modal_ctr = modal_Controller(activity_share_modal, {width: '500px', height: 'auto'});

    activity_share_list.change(function() {
        var share_mode = jQuery(this).val();
        if (share_mode === "Share with friend") {
            jQuery("input", activity_share_info).val("").attr("usertype", "friend").attr("placeholder", "@friend name");
            activity_share_info.show();
        } else if (share_mode === "In a group") {
            jQuery("input", activity_share_info).val("").attr("usertype", "group").attr("placeholder", "@group name");
            activity_share_info.show();
        } else if (share_mode === "In a private message") {
            jQuery("input", activity_share_info).val("").attr("usertype", "user").attr("placeholder", "@user name");
            activity_share_info.show();
        } else {
            jQuery("input", activity_share_info).val("");
            activity_share_info.hide();
        }
    });

    jQuery(".closeicon", activity_share_modal).click(function() {
        activity_share_modal_ctr.close();
    });
    jQuery(".cancelbtn", activity_share_modal).click(function() {
        activity_share_modal_ctr.close();
    });
    jQuery(".okbtn", activity_share_modal).click(function() {
        var id = jQuery("#activity_id", activity_share_modal).val();
        var showid = jQuery("#show_id", activity_share_modal).val();
        var desc = jQuery("#desc", activity_share_content).val();
        var share_mode = activity_share_list.val();
        var share_user = jQuery.trim(jQuery("input", activity_share_info).val());

        var dataString = 'activity_action=share&id=' + id + '&desc=' + desc + '&share_mode=' + share_mode + '&share_user=' + share_user;

        if (activity_share_info.css("display") !== "none" && (share_user === "" || share_user.indexOf("@") != -1)) {
            activity_share_inform.html("You must provide a recipient.").show({duration: 500, queue: true})
                    .delay(2000)
                    .hide({duration: 500, queue: true});
            return;
        }

        if (id != null) {
            jQuery.ajax({
                type: "POST",
                url: "/wp-content/plugins/buddypress/bp-themes/bp-default/activity/submit.php",
                data: dataString,
                success: function(msg) {
                    msg = msg.replace(/(^\s*)|(\s*$)/g, "");
                    result = JSON.parse(msg);

                    if (result.status == '0') {
                        jQuery("#hexd-" + showid).html(result.msg).show({duration: 500, queue: true})
                                .delay(2000)
                                .hide({duration: 500, queue: true});
                    }
                    if (result.status == '1') {
                        jQuery("#hexd-" + showid).html(result.msg).show("slow");
                    }
                }
            });
        }
        activity_share_modal_ctr.close();
    });
    add_input_for_bp_user(jQuery("textarea", activity_share_content));
    add_input_for_bp_user(jQuery("input", activity_share_info));

    function reset_activity_share_modal() {
        jQuery("option:first", activity_share_list).attr("selected", "selected")
        jQuery("input", activity_share_info).val("");
        activity_share_info.hide();
        activity_share_inform.html("").hide();
    }

    share_activity_on_popup = function(showid, id, desc) {
        jQuery("#desc", activity_share_content).val(desc);
        jQuery("#show_id", activity_share_modal).val(showid);
        jQuery("#activity_id", activity_share_modal).val(id);
        activity_share_modal_ctr.open();
    };
// activity share modal not using ui jquery 
}
function modal_Controller(contents, settings) {
    var modal, overlay, content, close;
    var method = {};

    // Generate the HTML and add it to the document

    modal = jQuery('<div id="custom_modal"></div>');
    content = jQuery('<div id="custom_modal_content"></div>');
    close = jQuery('<a id="custom_modal_close" href="#">close</a>');
    overlay = jQuery('<div id="custom_modal_overlay"></div>');

    content.append(contents);
    modal.append(content, close);

    modal.hide();
    overlay.hide();

    jQuery(".rtmedia-single-container").append(overlay, modal);

    close.click(function(e) {
        e.preventDefault();
        method.close();
    });

    // Center the modal in the viewport
    method.center = function() {
        var top, left;

        top = Math.max(jQuery(window).height() - modal.outerHeight(), 0) / 2;
        left = Math.max(jQuery(window).width() - modal.outerWidth(), 0) / 2;

        modal.css({
            top: top,
            left: left// + jQuery(window).scrollLeft()
        });
    };

    // Open the modal
    method.open = function(arg) {
        if (settings != null && settings.open_func != null && jQuery.isFunction(settings.open_func)) {
            settings.open_func(arg);
        }

        modal.css({
            width: settings.width || 'auto',
            height: settings.height || 'auto'
        });

        method.center();
        jQuery(window).bind('resize.modal', method.center);
        modal.show();
        overlay.show();
    };

    // Close the modal
    method.close = function(arg) {
        if (settings != null && settings.close_func != null && jQuery.isFunction(settings.close_func)) {
            settings.close_func(arg);
        }

        modal.hide();
        overlay.hide();
        jQuery(window).unbind('resize.modal');
    };

    return method;
}
// make modal for share and submit activity.

var member_block_list = jQuery("<div id='member_block_list' class='member_block_list'></div>");
var member_block_label = jQuery("<label>Block Type: </label>");
var member_block_friend = jQuery("<div><label><input type='checkbox' name='block_friend' id='block_friend'/> Block user from being friends</label></div>");
var member_block_following = jQuery("<div><label><input type='checkbox' name='block_following' id='block_following'/> Blocked user from following</label></div>");
var member_block_message = jQuery("<div><label><input type='checkbox' name='block_message' id='block_message'/> Block user from sending messages</label></div>");
    member_block_list.append(member_block_friend)
        .append(member_block_following)
        .append(member_block_message);

var  member_block_modal = jQuery('<div id="member_block_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
    + '<div class="modal_container" style="border-radius: 8px; background: #fff; padding: 20px; padding-bottom: 50px;">'
    + '<input type="hidden" id="user_id"/>'
    + '<input type="hidden" id="member_id"/>'
    + '<input type="hidden" id="block_token"/>'
    + "<div class='select_all'><label><input type='checkbox' name='select_all'/> Select All.</label></div>"
    + '<input class="okbtn" type="button" value="block"/>'
    + '<input class="cancelbtn" type="button" value="Cancel"/>'
    + '</div>'
        + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
    + '</div>');
jQuery(".modal_container", member_block_modal).prepend(member_block_list).prepend(member_block_label);

member_block_modal.dialog({
    dialogClass:'wp-dialog',
    title: "Block this member",
    autoOpen:false,
    closeOnEscape: false,
    open: function(event, ui) { 
            jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
            jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
            jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
            jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
            jQuery(this).parent().css("background", "none");
            jQuery(this).parent().css("border", "none");
    },
    width:350,
    modal:true,
});

jQuery(".select_all input", member_block_modal).change(function(){
	if(jQuery(this).attr("checked") === "checked"){
		jQuery("input", member_block_list).each(function(){
	        jQuery(this).attr("checked", "checked");
	    });
	}else{
		jQuery("input", member_block_list).each(function(){
	        jQuery(this).removeAttr("checked");
	    });
	}
});

jQuery(".closeicon", member_block_modal).click(function(){
    member_block_modal.dialog("close");
});

jQuery(".cancelbtn", member_block_modal).click(function(){
    member_block_modal.dialog("close");
});

jQuery(".okbtn", member_block_modal).click(function(){
	var block_type=1;
	var block_type_friend = (jQuery("input", member_block_friend).attr("checked") == "checked") ? 2 : 1;
	var block_type_following = (jQuery("input", member_block_following).attr("checked") == "checked") ? 3 : 1;
	var block_type_message = (jQuery("input", member_block_message).attr("checked") == "checked") ? 5 : 1;
	block_type = block_type_friend * block_type_following * block_type_message;
	
	if (block_type == 1)
		return;
	
	var user_id = jQuery("#user_id", member_block_modal).val();
	var member_id = jQuery("#member_id", member_block_modal).val();
	var block_token = jQuery("#block_token", member_block_modal).val();
	var block_button = jQuery("#block-"+member_id);
	
	var dataString = "action=block_member"
				+"&user_id=" + user_id
				+"&member_id=" + member_id
				+"&block_token=" + block_token
				+"&block_type=" + block_type;
	
	jQuery.ajax({
        type: "POST",
        data: dataString,
        success: function(msg) {
        	var result = JSON.parse(msg);
        	var type_friend = jQuery(".type_friend" ,block_button.parent().parent());
        	var type_follow = jQuery(".type_follow" ,block_button.parent().parent());
        	var type_message = jQuery(".type_message" ,block_button.parent().parent());
        	
        	if(type_friend.length != 0){
        		if (block_type_friend != 1){
    				type_friend.addClass("blocked");
    				type_friend.html("blocked");
        		}else{
        			type_friend.removeClass("blocked");
        			type_friend.html("");
        		}
        	}
        	
        	if(type_follow.length != 0){
        		if (block_type_following != 1){
    				type_follow.addClass("blocked");
    				type_follow.html("blocked");
        		}else{
        			type_follow.removeClass("blocked");
        			type_follow.html("");
        		}
        	}
        	
        	if(type_message.length != 0){
        		if (block_type_message != 1){
    				type_message.addClass("blocked");
    				type_message.html("blocked");
        		}else{
        			type_message.removeClass("blocked");
        			type_message.html("");
        		}
        	}
        	
        	block_token = result.unblock_token;
        	block_button.removeClass("unblocked");
        	block_button.addClass("blocked");
        	block_button.attr("onclick", "");
        	jQuery("a", block_button).html("Unblock");
        	block_button.unbind('click');
        	block_button.click(function(){
        		member_unblock_action(user_id, member_id, block_token)
        	});
        	
        }
    });
    member_block_modal.dialog("close");
});

function change_cover(media_id) {
    jq.post(ajaxurl, {
        action: 'change_media_cover',
        'cookie': bp_get_cookies(),
        data: {media_id : media_id}
    },
    function(response) {
        var res = response.toLocaleLowerCase();
        var btn_class = "cover-media-"+media_id;
        var cover_btn = jQuery("."+btn_class);
        cover_btn.removeClass("cover").removeClass("no-cover");
        if (res == 'cover'){
            cover_btn.addClass("cover");
            cover_btn.html("Cover");
            cover_btn.attr("title", "Not cover this item");
        }else{
            cover_btn.addClass("no-cover");
            cover_btn.html("Not Cover");
            cover_btn.attr("title", "Cover this item");
        }
    }, 'json');
    return false;
}

function reset_member_block_modal(user_id, member_id, block_token){
    jQuery("#user_id", member_block_modal).val(user_id);
    jQuery("#member_id", member_block_modal).val(member_id);
    jQuery("#block_token", member_block_modal).val(block_token);
    
    jQuery(".select_all input", member_block_modal).removeAttr("checked");
    jQuery("input", member_block_list).each(function(){
        jQuery(this).removeAttr("checked");
    });
}

function member_block_action(user_id, member_id, block_token){
    reset_member_block_modal(user_id, member_id, block_token);
	member_block_modal.dialog("open");
}

function member_unblock_action(user_id, member_id, token){
	var block_type = 1;
	var block_button = jQuery("#block-"+member_id);
	var block_token = token;
	
	var dataString = "action=unblock_member"
				+"&user_id=" + user_id
				+"&member_id=" + member_id
				+"&block_token=" + block_token
				+"&block_type=" + block_type;
	
	jQuery.ajax({
        type: "POST",
        data: dataString,
        success: function(msg) {
        	var result = JSON.parse(msg);
        	var type_friend = jQuery(".type_friend" ,block_button.parent().parent());
        	var type_follow = jQuery(".type_follow" ,block_button.parent().parent());
        	var type_message = jQuery(".type_message" ,block_button.parent().parent());
        	
        	if(type_friend.length != 0){
        			type_friend.removeClass("blocked");
        			type_friend.html("");
        	}
        	
        	if(type_follow.length != 0){
        			type_follow.removeClass("blocked");
        			type_follow.html("");
        	}
        	
        	if(type_message.length != 0){
        			type_message.removeClass("blocked");
        			type_message.html("");
        	}
        	
        	block_token = result.block_token;
        	block_button.removeClass("blocked");
        	block_button.addClass("unblocked");
        	block_button.attr("onclick", "");
        	jQuery("a", block_button).html("Block");
        	block_button.unbind('click');
        	block_button.click(function(){
        		member_block_action(user_id, member_id, block_token);
        	});
        }
    });
}

jQuery(document).ready(function($) {

    //handling the "post update: button on activity page
        jQuery('#aw-whats-new-submit').removeAttr('disabled');
        jQuery(document).on( "blur",'#whats-new', function(){
            setTimeout(function(){ jQuery('#aw-whats-new-submit').removeAttr('disabled'); },100);
        });
        jQuery('#aw-whats-new-submit').on('click', function(e){
            setTimeout(function(){ jQuery('#aw-whats-new-submit').removeAttr('disabled'); },100);
        });
		jQuery('#aw-whats-new-btn').on('click', function(e){
        	jQuery('#aw-whats-new-submit').click();
        });
    // when user changes the value in activity "post in" dropdown, hide the privacy dropdown and show when posting in profile.
    jQuery('#whats-new-post-in').on('change', function(e){
        if( jQuery(this).val() == '0' ){
            jQuery("#rtmedia-action-update .privacy").prop('disabled',false).show();
        }else{
            jQuery("#rtmedia-action-update .privacy").prop('disabled',true).hide();
        }
    });

    if (typeof rtMedia_update_plupload_config == 'undefined') {
        return false;
    }
    var activity_attachemnt_ids = [];
    if (jQuery("#rtmedia-add-media-button-post-update").length > 0) {
        jQuery("#whats-new-options").prepend(jQuery("#rtmedia-action-update"));
        if (jQuery("#rtm-file_upload-ui .privacy").length > 0) {
            jQuery("#rtmedia-action-update").append(jQuery("#rtm-file_upload-ui .privacy"));
        }
    }
    objUploadView = new UploadView(rtMedia_update_plupload_config);
    var title_modal = jQuery('<div id="title_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
            + '<div class="rtmedia-edit-title" style="border-radius: 8px; background: #fff; padding: 20px; padding-bottom: 50px;">'
            + '<label>Title : </label>'
            + '<input type="text" class="rtmedia-title-editor" id="media_title" value="" style="width:100%; margin-bottom:20px;">'
            + '<input class="okbtn" type="button" value="Ok"/>'
            + '<input class="cancelbtn" type="button" value="Cancel"/>'
            + '</div>'
                + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
            + '</div>');
    var media_title = null;
    title_modal.dialog({
        dialogClass:'wp-dialog',
        title: "Media Title",
        autoOpen:false,
        closeOnEscape: false,
        open: function(event, ui) { 
                jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
                jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
                jQuery(this).parent().css("background", "none");
                jQuery(this).parent().css("border", "none");

        },
        width:400,
        modal:true,
    });
    jQuery(".closeicon", title_modal).click(function(){
        title_modal.dialog("close");
    });
    jQuery(".cancelbtn", title_modal).click(function(){
        title_modal.dialog("close");
    });
    jQuery(".okbtn", title_modal).click(function(){
        if(jQuery("#media_title", title_modal).val() !== ""){
            media_title.innerHTML = jQuery("#media_title", title_modal).val();
            jQuery("#media_title", title_modal).val("");
            media_title = null;
            title_modal.dialog("close");
        }
    });
    
    //// category for post ///
    	var category_html = jQuery("#post-category-list").html();
        var category_modal = jQuery('<div id="category_modal" style="background: rgba(0,0,0,0.2); border-radius: 14px !important; padding: 8px;">'
            + '<div class="rtmedia-edit-title" style="border-radius: 8px; background: #fff; padding: 20px; padding-bottom: 50px;">'
            + '<label>Category : </label>'
            + '<select class="category-list" style="font-size: 12px; display: block; margin-bottom: 22px; width: 100%;"></select>'
            + '<input class="okbtn" type="button" value="Ok"/>'
            + '<input class="cancelbtn" type="button" value="Cancel"/>'
            + '</div>'
                + '<a class="closeicon" href="#" style="position: absolute; background: url(/wp-content/themes/Msocial/images/close.png) 0 0 no-repeat;width: 24px;height: 27px;display: block;text-indent: -9999px;top: -7px;right: -7px;">close</a>'
            + '</div>');
        jQuery(".category-list", category_modal).html(category_html);
        var media_categorys = new Array();
    category_modal.dialog({
        dialogClass:'wp-dialog',
        title: "Media Category",
        autoOpen:false,
        closeOnEscape: false,
        open: function(event, ui) { 
                jQuery(".ui-dialog-titlebar", jQuery(this).parent()).hide(); 
                jQuery(".ui-resizable-handle", jQuery(this).parent()).hide();
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("background", "#000");
                jQuery(".ui-widget-overlay", jQuery(this).parent()).css("opacity", "0.5");
                jQuery(this).parent().css("background", "none");
                jQuery(this).parent().css("border", "none");
        },
        width:300,
        modal:true,
    });
    jQuery(".closeicon", category_modal).click(function(){
        category_modal.dialog("close");
    });
    jQuery(".cancelbtn", category_modal).click(function(){
        category_modal.dialog("close");
    });
    jQuery(".okbtn", category_modal).click(function(){
    	/*jQuery("#post-category-value").val(jQuery(".category-list", category_modal).val());
    	jQuery(".category-list", category_modal).val(1);
        category_modal.dialog("close");
        jQuery('#aw-whats-new-submit').click();*/
        if (media_categorys != null && media_categorys.length != 0){
            for (var key in media_categorys){
                var media_category = media_categorys[key];
	        media_category.innerHTML = jQuery(".category-list", category_modal).val();
            }
            jQuery(".category-list", category_modal).val(7);
            media_categorys.length = 0;
    	}
        category_modal.dialog("close");
    });
    ////////////////////////////
    //jQuery("#whats-new-form").append(jQuery(title_modal));
    jQuery("#whats-new-form").on('click', '#rtmedia-add-media-button-post-update', function(e) {
        objUploadView.uploader.refresh();
    });
    //whats-new-post-in

    objUploadView.upload_remove_array = [];
    objUploadView.uploader.bind('FilesAdded', function(upl, rfiles) {
        //jQuery("#aw-whats-new-submit").attr('disabled', 'disabled');

        $.each(rfiles, function(i, file) {
            var hook_respo = rtMediaHook.call('rtmedia_js_file_added', [upl,file, "#rtMedia-queue-list tbody"]);
            if( hook_respo == false){
                    file.status = -1;
                    objUploadView.upload_remove_array.push(file.id);
                    return true;
            }
            if (objUploadView.uploader.settings.max_file_size < file.size) {
                return true;
            }
            var tmp_array =  file.name.split(".");
            var ext_array = objUploadView.uploader.settings.filters[0].extensions.split(',');
            var ext;
            if(tmp_array.length > 1){
                ext= tmp_array[tmp_array.length - 1];
		ext = ext.toLowerCase();
                if( jQuery.inArray( ext ,ext_array) === -1){
                    return true;
                }
            }else{
                return true;
            }
            tdName = document.createElement("td");
            tdName.innerHTML = file.name;
            tdName.className = "plupload_file_name"; //!!!!!!
            tdCategory = document.createElement("td");
            tdCategory.innerHTML = "-1";
            tdCategory.className = "plupload_file_category";
            jQuery(tdCategory).css("display", "none");
            tdStatus = document.createElement("td");
            tdStatus.className = "plupload_file_status";
            tdStatus.innerHTML = rtmedia_waiting_msg;
            tdSize = document.createElement("td");
            tdSize.className = "plupload_file_size";
            tdSize.innerHTML = plupload.formatSize(file.size);
            tdDelete = document.createElement("td");
            tdDelete.innerHTML = "&times;";
            tdDelete.title = rtmedia_remove_from_queue;
            tdDelete.className = "close plupload_delete";
            tdEdit = document.createElement("td");
            tdEdit.innerHTML = "";
            tr = document.createElement("tr");
            tr.className = 'upload-waiting';
            tr.id = file.id;
            tr.appendChild(tdName);
            tr.appendChild(tdCategory);
            tr.appendChild(tdStatus);
            tr.appendChild(tdSize);
            tr.appendChild(tdEdit);
            tr.appendChild(tdDelete);
            jQuery('#whats-new-content').css('padding-bottom','0px');
            jQuery("#rtm-upload-start-notice").css('display','block'); // show the file upload notice to the user
            jQuery("#rtMedia-queue-list").append(tr);
            jQuery("#" + file.id + " td.plupload_delete").click(function(e) {
                    e.preventDefault();
                    objUploadView.uploader.removeFile(upl.getFile(file.id));
                    jQuery("#" + file.id).remove();
                    return false;
                });
            if (ext === "mp4" || ext === "mov"){
                media_title = tdName;
                title_modal.dialog("open");
            }else{
            	media_categorys.push(tdCategory);
            	category_modal.dialog("open");
            }
        });

         $.each(objUploadView.upload_remove_array, function(i, rfile) {
                if(upl.getFile(rfile))
                    upl.removeFile(upl.getFile(rfile));
            });
    });

    objUploadView.uploader.bind('FileUploaded', function(up, file, res) {
        if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) { //test for MSIE x.x;
         var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number

            if(ieversion <10) {
                try {
                    if( typeof JSON.parse(res.response) !== "undefined" )
                        res.status = 200;
                }
                catch(e){}
            }
        }

        if (res.status == 200) {
            try {
                var objIds = JSON.parse(res.response);
                $.each(objIds, function(key, val) {
                    activity_attachemnt_ids.push(val);
                    if (jQuery("#whats-new-form").find("#rtmedia_attached_id_" + val).length < 1) {
                        jQuery("#whats-new-form").append("<input type='hidden' name='rtMedia_attached_files[]' data-mode='rtMedia-update' id='rtmedia_attached_id_" + val + "' value='"
                                + val + "' />");
                    }
                });
            } catch (e) {

            }
        }
    });

    objUploadView.uploader.bind('Error', function(up, err) {

                    if(err.code== -600){ //file size error // if file size is greater than server's max allowed size
                       var tmp_array;
                       var ext = tr = '';
                       tmp_array =  err.file.name.split(".");
                       if(tmp_array.length > 1){

                           ext= tmp_array[tmp_array.length - 1];
                           if( !(typeof(up.settings.upload_size) != "undefined" && typeof(up.settings.upload_size[ext]) != "undefined" && (up.settings.upload_size[ext]["size"] <  1 || (up.settings.upload_size[ext]["size"] * 1024 * 1024) >= err.file.size ))){
                               tr = "<tr class='upload-error'><td>" + err.file.name + "(" + plupload.formatSize(err.file.size) + ")" + "</td><td> " + rtmedia_max_file_msg + plupload.formatSize( up.settings.max_file_size / 1024 * 1024) + " <i class='rtmicon-info-circled' title='" + window.file_size_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td></td><td class='close error_delete'>&times;</td></tr>";
                           }
                       }
                       //append the message to the file queue
                       jQuery("#rtMedia-queue-list tbody").append(tr);
                   }
                   else {
                       if( err.code == -601) { // file extension error
                           err.message = rtmedia_file_extension_error_msg;
                       }
                       var tr = "<tr class='upload-error'><td>" + (err.file ? err.file.name : "") + "</td><td>" + err.message + " <i class='rtmicon-info-circled' title='" + window.file_extn_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td></td><td class='close error_delete'>&times;</td></tr>";
                       jQuery("#rtMedia-queue-list tbody").append(tr);
                   }

                jQuery('.error_delete').on('click',function(e){
                    e.preventDefault();
                    jQuery(this).parent('tr').remove();
                });
                jQuery("#rtm-upload-start-notice").css('display','block'); // show the file upload notice to the user
                return false;

        });

    objUploadView.uploader.bind('BeforeUpload', function(up, files) {

        $.each(objUploadView.upload_remove_array, function(i, rfile) {
                if(up.getFile(rfile))
                    up.removeFile(up.getFile(rfile));
            });

        var object = '';
        var item_id = jq("#whats-new-post-in").val();
        if (item_id == undefined)
            item_id = 0;
        if (item_id > 0) {
            object = "group";
        } else {
            object = "profile";
        }

        up.settings.multipart_params.context = object;
        up.settings.multipart_params.context_id = item_id;
        // if privacy dropdown is not disabled, then get the privacy value of the update
        if( jQuery("select.privacy").prop('disabled') === false ){
            up.settings.multipart_params.privacy = jQuery("select.privacy").val();
        }
        up.settings.multipart_params.title = jQuery(".upload-waiting .plupload_file_name").html();
        up.settings.multipart_params.description = jQuery("#whats-new-textarea textarea").val();
        if (jQuery(".upload-waiting .plupload_file_category").html() != "-1"){
        	up.settings.multipart_params.category = jQuery(".upload-waiting .plupload_file_category").html();
    	}
    });
    objUploadView.uploader.bind('UploadComplete', function(up, files) {
        media_uploading = true;
        jQuery("#aw-whats-new-submit").click();
        //remove the current file list
        jQuery("#rtMedia-queue-list tr").remove();
        jQuery("#rtm-upload-start-notice").hide();
        //jQuery("#aw-whats-new-submit").removeAttr('disabled');
    });
    objUploadView.uploader.bind('UploadProgress', function(up, file) {
        jQuery("#" + file.id + " .plupload_file_status").html( rtmedia_uploading_msg + '( ' + file.percent + '% )');
        jQuery("#" + file.id).addClass('upload-progress');
        jQuery("#" + file.id).removeClass('upload-waiting');
        if (file.percent == 100) {
                jQuery("#" + file.id).toggleClass('upload-success');
            }

    });

    jQuery("#rtMedia-start-upload").hide();

    objUploadView.initUploader();
    var change_flag = false
    var media_uploading = false;
    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
        // Modify options, control originalOptions, store jqXHR, etc
        try{
            if (originalOptions.data == null || typeof(originalOptions.data) == "undefined" || typeof(originalOptions.data.action) == "undefined" ) {
                return true;
            }
        }catch(e){
            return true;
        }
        if (originalOptions.data.action == 'post_update' || originalOptions.data.action == 'activity_widget_filter') {
            var temp = activity_attachemnt_ids;
            while (activity_attachemnt_ids.length > 0) {
                options.data += "&rtMedia_attached_files[]=" + activity_attachemnt_ids.pop();
            }
            options.data += "&rtmedia-privacy=" + jQuery("select.privacy").val();
            activity_attachemnt_ids = temp;
            var orignalSuccess = originalOptions.success;
            options.beforeSend = function () {
                if (originalOptions.data.action == 'post_update') {
                    if ($.trim(jQuery("#whats-new").val()) == "") {
                        var newname1 = jQuery('.plupload_file_name').html();
                        //alert(newname1);
//		    	jQuery("#whats-new").val(newname1);
                        jQuery("#whats-new").val("");
                        //!!!!!! alert(rtmedia_empty_activity_msg);
                        // jQuery("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
                        //return false;
                    }
                    if (!media_uploading && objUploadView.uploader.files.length > 0) {
                        jQuery("#whats-new-post-in").attr('disabled', 'disabled');
                        jQuery("#rtmedia-add-media-button-post-update").attr('disabled', 'disabled');
                        objUploadView.uploadFiles()
                        media_uploading = true;
                        return false;
                    } else {
                        media_uploading = false;
                        return true;
                    }
                }



            }
            options.success = function(response) {
                orignalSuccess(response);
                if (response[0] + response[1] == '-1') {
                    //Error

                } else {
                    if (originalOptions.data.action == 'activity_widget_filter') {
                        jQuery("div.activity").bind("fadeIn", function () {
                            apply_rtMagnificPopup(jQuery('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.widget-item-listing,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content'));
                            rtMediaHook.call('rtmedia_js_after_activity_added', []);
                        });
                        jQuery("div.activity").fadeIn(100);
                    } else {
                        jQuery("input[data-mode=rtMedia-update]").remove();
                        while (objUploadView.uploader.files.pop() != undefined) {
                        }
                        objUploadView.uploader.refresh();
                        jQuery('#rtMedia-update-queue-list').html('');
                        //jQuery("#div-attache-rtmedia").hide();
                        apply_rtMagnificPopup(jQuery('.rtmedia-list-media, .rtmedia-activity-container ul.rtmedia-list, #bp-media-list,.widget-item-listing,.bp-media-sc-list, li.media.album_updated ul,ul.bp-media-list-media, li.activity-item div.activity-content div.activity-inner div.bp_media_content'));
                        jQuery('ul.activity-list li.rtmedia_update:first-child .wp-audio-shortcode, ul.activity-list li.rtmedia_update:first-child .wp-video-shortcode').mediaelementplayer({
                            // if the <video width> is not specified, this is the default
                            defaultVideoWidth: 480,
                            // if the <video height> is not specified, this is the default
                            defaultVideoHeight: 270,
                            // if set, overrides <video width>
                            //videoWidth: 1,
                            // if set, overrides <video height>
                            //videoHeight: 1
                        });
                        rtMediaHook.call('rtmedia_js_after_activity_added', []);
                    }
                    jQuery("#whats-new-post-in").removeAttr('disabled');
                    jQuery("#rtmedia-add-media-button-post-update").removeAttr('disabled');
                }
            }
        }
    });
});
/**
 * rtMedia Comment Js
 */
jQuery(document).ready(function($) {
    jQuery(document).on("click", "#rt_media_comment_form #rt_media_comment_submit", function(e) {
        e.preventDefault();
        jQuery('.rtmedia-comments-container').show();
        var confirm_flag = false;
        /*
        if(confirm("Want to check comments?")){
			confirm_flag = true;
		}else{
                */
	        if ($.trim(jQuery("#comment_content").val()) == "" && jQuery("#rt_media_comment_form .ac-input-file").val() == "") {
	            alert(rtmedia_empty_comment_msg);
	            return false;
	        }
    	//}

        jQuery(this).attr('disabled', 'disabled');
		
		var form = jQuery("#rt_media_comment_form");
		var form_data = new FormData();
		var file_data = jQuery(".ac-input-file", form).prop("files")[0];
		
		
		form_data.append("comment_content", jQuery("#comment_content", form).val());
		form_data.append('cookie', bp_get_cookies());
		form_data.append('rtmedia_comment_nonce', jQuery("#rtmedia_comment_nonce", form).val());
		form_data.append('_wp_http_referer', jQuery("input[name='_wp_http_referer']", form).val());
		if (jQuery(".ac-input-file", form).val() != ""){
			form_data.append('file_name', jQuery(".ac-input-file", form).prop("files")[0].name);
		}
		
		form_data.append("file", file_data);
		form_data.append("confirm_flag", confirm_flag)
		form_data.append("rtajax", true);
		
        $.ajax({
            url: jQuery("#rt_media_comment_form").attr("action"),
            type: 'post',
            datatype:'script',
		    cache:false,
		    contentType:false,
		    processData:false,
		    data:form_data,
            success: function(data) {
            	jQuery('#rtmedia-no-comments').remove();
            	jQuery("#rt_media_comment_form #rt_media_comment_submit").removeAttr('disabled');
            	if(confirm_flag){
            		var new_command = jQuery(data);
            		jQuery("#rtmedia_comment_ul").html(new_command.html());
            	}else{
                jQuery("#rtmedia_comment_ul").append(data);
                jQuery("#comment_content").val("");
                var content_body = jQuery(".rtm-single-meta-contents.logged-in");
				var comment_body = jQuery(".rtm-media-single-comments");
				if (content_body.length == 1 && comment_body.length == 1){
					if (jq("#rt_media_comment_form .ac-file-view").css("display") != "none"){
						content_body.height(content_body.height() + 100);
						comment_body.height(comment_body.height() - 100);
					}
				}
				jq("#rt_media_comment_form .ac-file-view img").attr("src", "");
                jq("#rt_media_comment_form .ac-input-file").val("");
                jq("#rt_media_comment_form .ac-file").show();
                jq("#rt_media_comment_form .ac-file-view").hide();
            	}
            	rtMediaHook.call('rtmedia_js_after_comment_added', []);
            }
        });

        return false;
    });

    //Delete comment
    jQuery(document).on('click', '.rtmedia-delete-comment', function(e){
       e.preventDefault();
       var ask_confirmation = true
       ask_confirmation = rtMediaHook.call('rtmedia_js_delete_comment_confirmation', [ask_confirmation]);
       if(ask_confirmation && !confirm( rtmedia_media_comment_delete_confirmation ))
           return false;
       var current_comment = jQuery(this);
       var current_comment_parent = current_comment.parent();
       var comment_id = current_comment.data('id');
       current_comment_parent.css('opacity', '0.4');
       if(comment_id == '' || isNaN(comment_id)){
           return false;
       }
       var action = current_comment.closest('ul').data("action");

       jQuery.ajax({
           url: action,
           type: 'post',
           data: { comment_id : comment_id },
           success: function(res) {
            if(res !='undefined' && res == 1){
                current_comment.closest('li').hide(1000, function(){ current_comment.closest('li').remove(); });
            }else{
                current_comment.css('opacity', '1');
            }
            rtMediaHook.call('rtmedia_js_after_comment_deleted', []);
           }
       });

    });

    jQuery(document).on("click", '.rtmedia-like', function(e) {
        e.preventDefault();
        var that = this;
        jQuery(this).attr('disabled', 'disabled');
        var url = jQuery(this).parent().attr("action");
        jQuery(that).prepend("<img class='rtm-like-loading' src='" + rMedia_loading_file + "' style='width:10px' />");
        $.ajax({
            url: url,
            type: 'post',
            data: "json=true",
            success: function(data) {
                try {
                    data = JSON.parse(data);
                } catch (e) {

                }
                jQuery('.rtmedia-like span').html(data.next);
                jQuery('.rtm-like-loading').remove();
                jQuery(that).removeAttr('disabled');
                //update the like counter
                jQuery('.rtmedia-like-counter').html(data.count);
                if(data.count > 0){
                    jQuery('.rtmedia-like-info').removeClass('hide');
                    //!!!!!
   checknow = jQuery(".my-like");
    jQuery(checknow).removeClass('my-like');
    jQuery(checknow).addClass('my-unlike');
    jQuery(checknow).removeAttr('title');
    jQuery(checknow).attr('title','Unlike this');
                }else {
                    jQuery('.rtmedia-like-info').addClass('hide');
                    //!!!!!
   checknow = jQuery(".my-unlike");
    jQuery(checknow).removeClass('my-unlike');
    jQuery(checknow).addClass('my-like');
    jQuery(checknow).removeAttr('title');
    jQuery(checknow).attr('title','Like this');    
                }
            }
        });


    });
    jQuery(document).on("click", '.rtmedia-featured', function(e) {
        e.preventDefault();
        var that = this;
        jQuery(this).attr('disabled', 'disabled');
        var url = jQuery(this).parent().attr("action");
        jQuery(that).prepend("<img class='rtm-featured-loading' src='" + rMedia_loading_file + "' />");
        $.ajax({
            url: url,
            type: 'post',
            data: "json=true",
            success: function(data) {
                try {
                    data = JSON.parse(data);
                } catch (e) {

                }
                jQuery(that).find('span').html(data.next);
                jQuery('.rtm-featured-loading').remove();
                jQuery(that).removeAttr('disabled');
            }
        });


    });
    jQuery("#div-attache-rtmedia").find("input[type=file]").each(function() {
        //jQuery(this).attr("capture", "camera");
        // jQuery(this).attr("accept", jQuery(this).attr("accept") + ';capture=camera');

    });

    // manually trigger fadein event so that we can bind some function on this event. It is used in activity when content getting load via ajax
    var _old_fadein = $.fn.fadeIn;
    jQuery.fn.fadeIn = function(){
	return _old_fadein.apply(this,arguments).trigger("fadeIn");
    };
});
//!!!!!!!!!
jQuery(document).ready(function($) {
jQuery("#buddypress .item-list-tabs .feed").css('display','none');
})
