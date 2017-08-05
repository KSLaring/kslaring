/*global define: false */
define(['jquery', 'core/log', 'theme_bootstrapbase/bootstrap'], function ($, log) {
    "use strict";

    var $ele = null,
        $that = null,
        $taggroup = null,
        $tags = null,
        issingleselect = 0;

    log.debug('AMD block_course_tags/singleselect loaded');

    return {
        init: function () {
            log.debug('AMD block_course_tags/singleselect init');

            $('.taggroups').on('updated', '.inplaceeditable', function () {
                $ele = $(this);

                // If the change is triggered by the script remove the data »byscript« and return;
                if ($ele.parent().data('byscript')) {
                    $ele.parent().removeData('byscript');
                    $ele.find('a').blur();
                    return;
                }

                $taggroup = $ele.parents('.taggroup');
                issingleselect = $taggroup.data('singleselect');

                if (issingleselect) {
                    $tags = $taggroup.find('.inplaceeditable');
                    $tags.each(function () {
                        $that = $(this);
                        // Deselect the element when it is selected and it is not the just updated element.
                        if ($that.data('value') && $that.data('itemid') !== $ele.data('itemid')) {
                            // Add data »byscript« to the parent element to mark the element triggered by the script.
                            $that.parent().data('byscript', 1);
                            $that.find('a').trigger('click');
                        }
                    });
                }
            });
        }
    };
});
