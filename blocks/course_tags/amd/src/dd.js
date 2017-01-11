/*global define: false, require: false, M: true */
define(['jquery', 'jqueryui', 'core/log', 'theme_bootstrapbase/bootstrap'], function ($, ui, log) {
    "use strict";

    log.debug('block_course_tags drag&drop AMD');

    return {
        init: function () {
            var $itemList = $('#draggableItemList'),
                sortorder = {};

            log.debug('block_course_tags drag&drop init');

            require(['local_tag/Xloader'], function () {
                $itemList.sortable({
                    items: '> li',
                    cursor: "move",
                    create: function () {
                        // $('#user-notifications').find('.alert-info').find('.close').trigger('click');
                        $itemList.addClass('dd-initialized');
                        $('#user-notifications')
                            .find('.alert-info')
                            .append(' <span>' + M.str.block_course_tags.readydragdrop + '</span>');
                        // str.get_string('readydragdrop', 'block_course_tags').done(function (s) {
                        //     $('#user-notifications').find('.alert-info').append(' <span>' + s + '</span>');
                        // });
                    },
                    update: function () {
                        sortorder = '';
                        $('.item', $itemList).each(function (index, elem) {
                            var $listItem = $(elem),
                                newIndex = $listItem.index(),
                                id = $listItem.attr('id');

                            sortorder += ' ' + id;
                        });

                        log.debug('newIndices: ' + sortorder.trim());
                        $('#id_s__block_course_tags_groupsortorder').val(sortorder.trim());
                    }
                }).disableSelection();
            });
        }
    };
});
