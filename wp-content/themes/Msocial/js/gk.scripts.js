/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
(function () {
    "use strict";
    jQuery.cookie = function (key, value, options) {

        // key and at least value given, set cookie...
        if (arguments.length > 1 && String(value) !== "[object Object]") {
            options = jQuery.extend({}, options);

            if (value === null || value === undefined) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires,
                    t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=',
                options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var result, decode = options.raw ? function (s) {
                return s;
            } : decodeURIComponent;
        return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
    };

    // Array filter
    if (!Array.prototype.filter) {
        Array.prototype.filter = function (fun /*, thisp */ ) {
            if (this === null) {
                throw new TypeError();
            }

            var t = Object(this);
            var len = t.length >>> 0;
            if (typeof fun !== "function") {
                throw new TypeError();
            }

            var res = [];
            var thisp = arguments[1];

            for (var i = 0; i < len; i++) {
                if (i in t) {
                    var val = t[i]; // in case fun mutates this
                    if (fun.call(thisp, val, i, t))
                        res.push(val);
                }
            }

            return res;
        };
    }

    /**
     *
     * Template scripts
     *
     **/

    // onDOMLoadedContent event
    jQuery(document).ready(function () {
        // SmoothScroll jQUery substitue
        jQuery('a[href^="#"]').click(function (e) {
            e.preventDefault();
            var target = this.hash,
                $target = jQuery(target);

            if ($target.length) {
                jQuery('html, body').stop().animate({
                    'scrollTop': $target.offset().top
                }, 1000, 'swing', function () {
                    window.location.hash = target;
                });
            } else {
                window.location.hash = target;
            }
        });

        // Thickbox use
        jQuery(document).ready(function () {
            if (typeof tb_init !== "undefined") {
                tb_init('div.wp-caption a'); //pass where to apply thickbox
            }
        });
        // style area
        if (jQuery('#gk-style-area')) {
            jQuery('#gk-style-area div').each(function () {
                jQuery(this).find('a').each(function () {
                    jQuery(this).click(function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                        changeStyle(jQuery(this).attr('href').replace('#', ''));
                    });
                });
            });
        }

        if (jQuery('#gk-style-switcher')) {
            jQuery('#gk-style-switcher').find('a').each(function () {
                jQuery(this).click(function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    changeStyle(jQuery(this).attr('href').replace('#', ''));
                });
            });
        }
        // Function to change styles

        function changeStyle(style) {
            var file = $GK_TMPL_URL + '/css/' + style;
            jQuery('head').append('<link rel="stylesheet" href="' + file + '" type="text/css" />');
            jQuery.cookie($GK_TMPL_NAME + '_style', style, {
                expires: 365,
                path: '/'
            });
        }

        // Responsive tables
        jQuery('article section table').each(function (i, table) {
            table = jQuery(table);
            var heads = table.find('thead th');
            var cells = table.find('tbody td');
            var heads_amount = heads.length;
            // if there are the thead cells
            if (heads_amount) {
                var cells_len = cells.length;
                for (var j = 0; j < cells_len; j++) {
                    var head_content = jQuery(heads.get(j % heads_amount)).text();
                    jQuery(cells.get(j)).html('<span class="gk-table-label">' + head_content + '</span>' + jQuery(cells.get(j)).html());
                }
            }
        });        
        // login popup
        if (jQuery('#gk-popup-login')) {
            var popup_overlay = jQuery('#gk-popup-overlay');
            popup_overlay.css({
                'opacity': '0',
                'display': 'block'
            });
            popup_overlay.fadeOut();

            var opened_popup = null;
            var popup_login = null;
            var popup_login_h = null;
            var popup_login_fx = null;

            popup_login = jQuery('#gk-popup-login');
            popup_login.css({
                'opacity': 0,
                'display': 'block'
            });
            popup_login_h = popup_login.find('.gk-popup-wrap').outerHeight();

            popup_login.animate({
                'opacity': 0,
                'height': 0
            }, 200);

            jQuery('#gk-login').click(function (e) {
                e.preventDefault();
                e.stopPropagation();

                popup_overlay.fadeTo(200, 0.45);
                popup_login.animate({
                    'opacity': 1,
                    'height': popup_login_h
                }, 200);
                opened_popup = 'login';
            });

            popup_overlay.click(function () {
                if (opened_popup === 'login') {
                    popup_overlay.fadeOut();
                    popup_login.animate({
                        'opacity': 0,
                        'height': 0
                    }, 200);
                }
            });
        }
        //
    	jQuery(document).find('.gk-grid-title-overlay').each(function(i, module) {
    		module = jQuery(module);
    
    		if(!module.hasClass('active')) {
    			module.addClass('active');
    			gkGridTitleOverlayInit(module);
    		}
    	});
        init_copy_right ();
    });
    //
    var gkGridTitleOverlayInit = function(module) {
    	module = jQuery(module);
    	// add the basic events
    	module.mouseenter(function() {
    		module.addClass('hover');
    	});
    
    	module.mouseleave(function() {
    		module.removeClass('hover');
    	});
    };
    //
    function gkAddClass(element, cssclass, i) {
        var delay = jQuery(element).attr('data-delay') || 0;

        if (!delay) {
            delay = (i !== false) ? i * 150 : 0;
        }

        setTimeout(function () {
            jQuery(element).addClass(cssclass);
        }, delay);
    }
})();

function init_copy_right() {
    var form = jQuery("#copy-right-form");
    if (form.length == 0)
        return;

    var sub_form_template = jQuery(".sub-form-template").clone(true);
    //submit button's action
    jQuery(".submit button").click(function() {
        var err_flag;
        var button = jQuery(this);
        if (button.hasClass("disabled")) {
            return false;
        }
        
        err_flag = check_all_input_values();
        if (!err_flag) {
            // todo: real action for this form.
            
            // remove_all and strike check box value
            jQuery(".remove_all, .strike, .is_accurate, .is_good_faith, .is_authorized_agent").each(function() {
                var check_box = jQuery(this);
                if (check_box.attr("checked") == "checked") {
                    check_box.prev().val("1");
                } else {
                    check_box.prev().val("0");
                }
            });

            var formdata = form.serialize();
            var action = 'submit_copy_right_compliant';
            if ("counter-claim" == jQuery("input[name=action]").val()){
                action = 'submit_copy_right_counter_claim';
            }else if ("dispute-counter" == jQuery("input[name=action]").val()){
                action = 'submit_copy_right_dispute_counter';
            }
            
            button.addClass("disabled");
            //formdata += (formdata!=='')? '&':'';
            jQuery.post(ajaxurl, {
                action: action,
                'cookie': bp_get_cookies(),
                data: formdata,
            },
                    function(response) {
                        button.removeClass("disabled");
                        if (response.status == "false") {
                            alert(response.msg);
                        } else {
                            alert(response.msg);
                            window.history.back();
                        }
                        
                    }, 'json');
        }
        
        return false;
    });

    // remove item button
    jQuery(".removeItemButton").click(function() {
        if (jQuery(".sub-form-template").length > 1) {
            jQuery(".sub-form-template:last").remove();
        }
        return false;
    });

    // add another button
    jQuery(".addAnotherButton").click(function() {
        jQuery(".sub-form-template:last").after(sub_form_template.clone(true));
        return false;
    });

    // same info button
    jQuery(".sameInfoButton").click(function() {
        var agent_infos = jQuery(".agent_info input, .agent_info select");
        var owner_infos = jQuery(".owner_info input, .owner_info select");
        var i;

        for (i = 0; i < agent_infos.length; i++) {
            jQuery(owner_infos[i]).val(jQuery(agent_infos[i]).val());
        }
        return false;
    });

    // check values input.
    jQuery(".required, .phone_number, .url_input, .integer_input, .good_faith, input", form).live('change blur keyup', function() {
        check_input_values(jQuery(this));
    });

    function check_all_input_values() {
        var err_flag = false;
        jQuery(".required, .phone_number, .url_input, .integer_input, .good_faith, input", form).each(function() {
            var item_err_flag = check_input_values(jQuery(this));
            if (item_err_flag) {
                err_flag = true;
            }
        });
        return err_flag;
    }

    function check_input_values(req_input) {
        var req_message = jQuery("<p class='err_msg'>Required</p>");
        var err_flag = false;

        if (req_input.val() == "") {
            if (req_input.hasClass("required")) {
                req_message.html("Required");
                err_flag = true;
            }
        } else if (req_input.hasClass("email_addr")) {
            err_flag = check_email_addr(req_input.val())
            if (err_flag) {
                req_message.html("Not a valid email.");
            }
        } else if (req_input.hasClass("phone_number")) {
            err_flag = check_phone_number(req_input.val())
            if (err_flag) {
                req_message.html("Not a phone number.");
            }
        } else if (req_input.hasClass("url_input")) {
            err_flag = check_url(req_input.val())
            if (err_flag) {
                req_message.html("Not a valid url.");
            }
        } else if (req_input.hasClass("elec_sign")) {
            err_flag = check_sign_name(req_input.val())
            if (err_flag) {
                jQuery(".submit").each(function() {
                    jQuery(this).addClass("disabled");
                });
                req_message.html("Electronic signature doesn't match your name.");
            } else {
                jQuery(".submit").each(function() {
                    jQuery(this).removeClass("disabled");
                });
            }
        } else if (req_input.attr("type") == "checkbox" && req_input.hasClass("required")) {
            if (req_input.attr("checked") !== "checked") {
                req_message.html("Required");
                err_flag = true;
            }
        } else if (req_input.hasClass("integer_input")) {
            err_flag = check_integer(req_input.val())
            if (err_flag) {
                req_message.html("Not a valid value.");
            }
        } else if (req_input.hasClass("good_faith")) {
            err_flag = check_good_faith(req_input.val())
            if (err_flag) {
                req_message.html("Not a valid value.");
            }
        } else if ((req_input.attr("id") == 'fair_use') && (req_input.attr("checked") == "checked")) {
            jQuery('#fair_use_brief').addClass("required");
            jQuery('#appropriate_brief').removeClass("required");
            remove_err_status(jQuery('#appropriate_brief'));

        } else if ((req_input.attr("id") == 'appropriate') && (req_input.attr("checked") == "checked")) {
            jQuery('#fair_use_brief').removeClass("required");
            jQuery('#appropriate_brief').addClass("required");
            remove_err_status(jQuery('#fair_use_brief'));
        }

        remove_err_status(req_input);

        if (err_flag) {
            req_input.addClass("has_error");
            req_input.after(req_message);
            if (req_input.hasClass("float-right"))
                req_message.addClass("float-right");
        }
        return err_flag;
    }

    function remove_err_status(req_input) {
        req_input.removeClass("has_error");
        var msg = req_input.next();
        while (msg.hasClass("err_msg")) {
            var rm_msg = msg;
            msg = rm_msg.next();
            rm_msg.remove();
        }
    }

    function check_email_addr(email_addr) {
        var ret_val = false;
        var regex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;
        if (regex.test(email_addr) === false) {
            ret_val = true;
        }
        return ret_val;
    }
    function check_phone_number(phone_number) {
        var ret_val = false;
        var regex = /^[0-9 \(\)\-]*$/
        if (regex.test(phone_number) === false) {
            ret_val = true;
        }
        return ret_val;
    }
    function check_sign_name(_sign_name) {
        var ret_val = false;
        if (sign_name !== _sign_name) {
            ret_val = true;
        }
        return ret_val;
    }
    function check_url(url) {
        var ret_val = false;
        var regex = new RegExp('^(https?:\\/\\/)?' + // protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
                '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator

        if (regex.test(url) === false) {
            ret_val = true;
        }
        return ret_val;
    }
    function check_integer(integer) {
        var ret_val = false;
        var regex = /^[0-9]*$/
        if (regex.test(integer) === false) {
            ret_val = true;
        }
        return ret_val;
    }
    function check_good_faith(faith) {
        var ret_val = false;
        var faith_copy = jQuery(".good_faith_copy").html();
        if (faith !== faith_copy) {
            ret_val = true;
        }
        return ret_val;
    }
}