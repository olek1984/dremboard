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