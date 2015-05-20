(function () {
    "use strict";
    jQuery(document).ready(function () {
        jQuery('*[placeholder]').each(function (i, el) {
            el = jQuery(el);

            if (el.val() === '') {
                el.val(el.attr('placeholder'));
            }

            el.focus(function () {
                if (el.val() === el.attr('placeholder')) {
                    el.val('');
                }
            });

            el.blur(function () {
                if (el.val() === '') {
                    el.val(el.attr('placeholder'));
                }
            });
        });
    });
})();