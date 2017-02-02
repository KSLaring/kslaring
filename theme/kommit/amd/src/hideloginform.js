/*global define: false, YUI: false */
define(['jquery', 'core/log'], function ($, log) {
    "use strict";

    // For debugging.
    // window.$ = $;
    log.debug('KSL hideloginform AMD');

    return {
        init: function () {
            log.debug('KSL hideloginform init');

            var $loginsub = $(".subcontent.loginsub");
            if ($loginsub.length) {
                $loginsub.insertAfter(".subcontent.guestsub");
                $loginsub
                    .attr("id", "collapseloginfields")
                    .addClass("collapse")
                    .wrap("<div style='text-align: center'></div>")
                    .before("<div id='expandlogindfields'><a href=\"#\" data-toggle='collapse' data-target='#collapseloginfields'>"
                        + M.str.theme_kommit.adminlogin + "</a></div>");
                $loginsub.find(".desc").insertBefore("#expandlogindfields");
            }
        }
    };
});
