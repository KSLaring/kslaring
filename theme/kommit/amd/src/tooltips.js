/*global define: false */
define(['jquery', 'theme_bootstrapbase/bootstrap', 'core/log'], function ($, bootstrap, log) {
    "use strict";

    log.debug('KSL tooltips AMD');

    return {
        init: function () {
            log.debug('KSL tooltips init');
            $('#topsearch').tooltip();
        }
    };
});
