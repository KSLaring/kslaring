/*global define: false */
define(['jquery', 'theme_bootstrapbase/bootstrap', 'core/log'], function ($, bootstrap, log) {
    "use strict";

    log.debug('KSL carousel AMD');

    return {
        init: function (data) {
            log.debug('KSL carousel init');
            $('#kslCarousel').carousel({
                interval: data.slideinterval
            });
        }
    };
});
