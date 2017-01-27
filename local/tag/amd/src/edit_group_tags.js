/*global define: false, require: false, M: true */
define(['core/ajax', 'theme_bootstrapbase/bootstrap', 'jquery', 'jqueryui', 'core/url', 'core/log'],
    function (ajax, bst, $, ui, url, log) {
        "use strict";

        log.debug('local_tag - Drag&drop AMD');

        // Add jQuery to the global space - for development only.
        window.$ = $;

        var strreadydragdrop = M.str.local_tag.readydragdrop;
        var $ddLists = null,
            $itemList0 = null,
            $itemLists = null,
            $addtagBtn = null,
            $addfield = null,
            $filtertagBtn = null,
            $filterfield = null,
            ddResultBuffer = {
                sourcegroupid: null,
                sortablelist: {},
                dropablelist: {}
            },
            sortorder = {};

        /**
         * Set up drag & drop for the tag lists.
         */
        var setupDragDrop = function () {
            require(['local_tag/Xloader'], function () {
                $itemList0.sortable({
                    items: '> li',
                    cursor: 'move',
                    connectWith: '.itemList',
                    helper: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - helper');

                        var $uiclone = $(ui).clone(),
                            $selectedSiblings = $(ui).siblings(".selected").not(".ui-sortable-placeholder");

                        if ($selectedSiblings.length) {
                            $selectedSiblings.clone().appendTo($uiclone);
                        }

                        return $uiclone.appendTo('body').show();
                    },
                    create: function () {
                        $(this).addClass('dd-initialized');
                        $('#user-notifications')
                            .find('.alert')
                            .append(' <span>' + strreadydragdrop + '</span>');
                    },
                    start: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - start');
                        ddOnStart($(this).attr('id'));

                        // ui.item.siblings(".selected").not(".ui-sortable-placeholder").appendTo(ui.item);
                        var selectedSiblings = ui.item.siblings(".selected").not(".ui-sortable-placeholder");
                        if (selectedSiblings.length) {
                            selectedSiblings.clone().appendTo(ui.item);
                            selectedSiblings.remove();
                        }
                    },
                    stop: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - stop');

                        ui.item.after(ui.item.find("li"));
                        ddOnStop($(this).attr('id'));
                    },
                    update: function () {
                        sortorder = '';
                        $(this).find('.selected').removeClass('selected');
                        $(this).find('.item').each(function (index, elem) {
                            var $listItem = $(elem),
                                id = $listItem.attr('id');

                            if (id !== undefined) {
                                sortorder += ' ' + getIntFromID(id);
                            }
                        });

                        sortorder = sortorder.trim();

                        log.debug($(this).attr('id') + ' sortable - update');
                        // log.debug($(this).attr('id') + ' newIndices: ' + sortorder);
                        ddOnSortableUpdate($(this).attr('id'), sortorder);
                    }
                }).disableSelection();

                $itemLists.sortable({
                    items: '> li',
                    cursor: 'move',
                    connectWith: '.itemList',
                    helper: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - helper');

                        var $uiclone = $(ui).clone(),
                            $selectedSiblings = $(ui).siblings(".selected").not(".ui-sortable-placeholder");

                        if ($selectedSiblings.length) {
                            $selectedSiblings.clone().appendTo($uiclone);
                        }

                        return $uiclone.appendTo('body').show();
                    },
                    create: function () {
                        $(this).addClass('dd-initialized');
                    },
                    start: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - start');
                        ddOnStart($(this).attr('id'));

                        // ui.item.siblings(".selected").not(".ui-sortable-placeholder").appendTo(ui.item);
                        var selectedSiblings = ui.item.siblings(".selected").not(".ui-sortable-placeholder");
                        if (selectedSiblings.length) {
                            selectedSiblings.clone().appendTo(ui.item);
                            selectedSiblings.remove();
                        }
                    },
                    stop: function (e, ui) {
                        log.debug($(this).attr('id') + ' sortable - stop');

                        ui.item.after(ui.item.find("li"));
                        ddOnStop($(this).attr('id'));
                    },
                    update: function () {
                        sortorder = '';
                        $(this).find('.selected').removeClass('selected');
                        $(this).find('.item').each(function (index, elem) {
                            var id = $(elem).attr('id');

                            if (id !== undefined) {
                                sortorder += ' ' + getIntFromID(id);
                            }
                        });

                        sortorder = sortorder.trim();

                        log.debug($(this).attr('id') + ' sortable - update');
                        // log.debug($(this).attr('id') + ' newIndices: ' + sortorder);
                        ddOnSortableUpdate($(this).attr('id'), sortorder);
                    }
                }).disableSelection();

                // Disable sortable to avoid drops into areas hidden in closed accordion sections.
                $itemLists.sortable('disable');

                $(".accordion-toggle").droppable({
                    hoverClass: 'mx-content-hover',
                    drop: function (e, ui) {
                        var $target = $(e.target),
                            containerid = $target.attr('href'),
                            $container = $(containerid),
                            $draggableClone = ui.draggable.clone(),
                            $items = $draggableClone.find("li").detach(),
                            $targetList = $container.find('.itemList').eq(0);

                        log.debug($targetList.attr('id') + ' droppable - drop');

                        $draggableClone
                            .removeClass('ui-sortable-helper')
                            .removeClass('selected')
                            .attr('style', '');
                        $items
                            .removeClass('ui-sortable-helper')
                            .removeClass('selected')
                            .attr('style', '');

                        $targetList.append($draggableClone);
                        $draggableClone.after($items);

                        setTimeout(function () {
                            var $parent = ui.draggable.parent();
                            ui.draggable.remove();
                            $items.each(function () {
                                var id = $(this).attr('id');
                                $parent.find('#' + id).remove();
                            });
                        }, 0);

                        if (!$container.hasClass('in')) {
                            $target.trigger('click');
                        }

                        sortorder = '';
                        $targetList.find('.item').each(function (index, elem) {
                            var id = $(elem).attr('id');

                            if (id !== undefined) {
                                sortorder += ' ' + getIntFromID(id);
                            }
                        });

                        sortorder = sortorder.trim();

                        ddOnDropableUpdate($targetList.attr('id'), sortorder);
                    }
                });

                // When an accordion section is opened enable sortable for that list.
                $('#dd-lists')
                    .on('shown', '.accordion-body', function (e) {
                        $(e.target).find('.itemList').sortable('enable');
                    })
                    .on('hidden', '.accordion-body', function (e) {
                        $(e.target).find('.itemList').sortable('disable');
                    });
            });
        };

        /**
         * Filter in all tag lists.
         */
        var handleFilterList = function () {
            var filtertext = $filterfield.val(),
                $taggroupaccordion = $('#taggroup-accordion');

            log.debug('»' + filtertext + '«');

            $itemList0.find('.hidden').removeClass('hidden');
            $taggroupaccordion.find('.hidden').removeClass('hidden');

            if (filtertext !== undefined && filtertext !== '') {
                $itemList0.find('li:not(:contains("' + filtertext + '"))').addClass('hidden');
                $taggroupaccordion.find('li:not(:contains("' + filtertext + '"))').addClass('hidden');
            }
        };

        /**
         * Add the user entered list of new tags.
         */
        var handleAddTags = function () {
            var newtags = $addfield.val();

            if (newtags !== '') {
                addNewTags(newtags);
            }

            $addfield.val('');
        };

        /**
         * Send the new tags via AJAX and show the added tags in the list.
         *
         * @param {string} newtags The user entered list of new tags.
         */
        var addNewTags = function (newtags) {
            $.when(
                ajax.call([
                    {
                        methodname: 'local_tag_add_course_tags',
                        args: {
                            taglist: newtags
                        }
                    }
                ])[0]
            ).then(function (response) {
                var taglist = JSON.parse(response.tagarray);

                $.each(taglist, function (i, item) {
                    // If the returned tag is not listed add it.
                    if ($ddLists.find('#' + item.id).length === 0) {
                        $itemList0.append('<li id="tagid-' + item.id + '" class="item ui-sortable ui-sortable-handle">' +
                            '<img class="smallicon" src="' + url.imageUrl('i/move_2d', 'core') + '"> ' +
                            item.name + '</li>');
                    }
                });
            });
        };

        /**
         * Prepare the result buffers.
         *
         * @param {string} id The CSS id string
         */
        var ddOnStart = function (id) {
            ddResultBuffer.sourcegroupid = id;
            ddResultBuffer.sortablelist = {};
            ddResultBuffer.dropablelist = {};
        };

        /**
         * Collect the item lists from the sortable lists.
         *
         * @param {string} id The CSS id string
         * @param {string} list The list of item ids as string
         */
        var ddOnSortableUpdate = function (id, list) {
            if (id !== ddResultBuffer.sourcegroupid) {
                ddResultBuffer.sortablelist[id] = list;
            }
        };

        /**
         * Collect the item list from the dropable lists.
         *
         * @param {string} id The CSS id string
         * @param {string} list The list of item ids as string
         */
        var ddOnDropableUpdate = function (id, list) {
            ddResultBuffer.dropablelist[id] = list;
        };

        /**
         * Handle the drag & drop results.
         *
         * @param {string} id The CSS id string
         */
        var ddOnStop = function (id) {
            log.debug(id + ' ddOnStop');
            log.debug(ddResultBuffer);
            // log.debug('sortablelist: ' + ($.isEmptyObject(ddResultBuffer.sortablelist) ? 'empty' : 'has data'));
            // log.debug('dropablelist: ' + ($.isEmptyObject(ddResultBuffer.dropablelist) ? 'empty' : 'has data'));
            var groupid = '',
                taglist = '';

            // If dropablelist contains data use it, eles check if the sortable list contains data.
            if (!$.isEmptyObject(ddResultBuffer.dropablelist)) {
                for (groupid in ddResultBuffer.dropablelist) {
                    if (ddResultBuffer.dropablelist.hasOwnProperty(groupid)) {
                        taglist = ddResultBuffer.dropablelist[groupid];
                        break;
                    }
                }
            } else if (!$.isEmptyObject(ddResultBuffer.sortablelist)) {
                for (groupid in ddResultBuffer.sortablelist) {
                    if (ddResultBuffer.sortablelist.hasOwnProperty(groupid)) {
                        taglist = ddResultBuffer.sortablelist[groupid];
                        break;
                    }
                }
            }

            if (taglist.length) {
                sendTagsInGroup(getIntFromID(groupid), taglist);
            }
        };

        /**
         * Get the id number from the CSS id string.
         * Form »name-id«.
         *
         * @param {string} id The CSS id string
         *
         * @return {int} The id number
         */
        var getIntFromID = function (id) {
            if (id !== undefined && id.indexOf('-') !== false) {
                return id.split('-')[1];
            } else {
                log.debug('getIntFromID missing »-« for: ' + id);
                return -1;
            }
        };

        /**
         * Send the tags in the droped group via AJAX.
         *
         * @param {int} groupid The group id
         * @param {string} taglist The list of tag ids as string
         */
        var sendTagsInGroup = function (groupid, taglist) {
            ajax.call([
                {
                    methodname: 'local_tag_group_tags',
                    args: {
                        groupid: groupid,
                        taglist: taglist
                    },
                    done: function (response) {
                        log.debug('local_tag_group_tags response:');
                        log.debug(response.result);
                    },
                    fail: function (response) {
                        log.debug(response);
                        window.location.reload(true);
                    }
                }
            ]);
        };

        return {
            init: function () {
                $ddLists = $('#dd-lists');
                $itemList0 = $('#draggableItemList-0');
                $itemLists = $('.group-list');
                $addtagBtn = $('#add-tag');
                $addfield = $('#add-field');
                $filtertagBtn = $('#filter-tag');
                $filterfield = $('#filter-field');
                sortorder = {};

                log.debug('local_tag - Drag&drop init');

                $itemList0.on('click', 'li', function () {
                    $(this).toggleClass("selected");
                });

                $itemLists.on('click', 'li', function () {
                    $(this).toggleClass("selected");
                });

                setupDragDrop();

                $filtertagBtn.on('click', handleFilterList);

                $filterfield.keyup(function (e) {
                    if (e.keyCode === 13) {
                        handleFilterList();
                    }
                });

                $addtagBtn.on('click', handleAddTags);

                $addfield.keyup(function (e) {
                    if (e.keyCode === 13) {
                        handleAddTags();
                    }
                });
            }
        };
    }
);
