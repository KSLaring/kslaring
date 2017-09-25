/*global define: false, M: true, console: false */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates',
        'theme_bootstrapbase/bootstrap',
        'local_course_search/is_loader',
        'local_course_search/dp_loader'
    ],
    function ($, notification, log, ajax, templates, bootstrap, InfiniteScroll) {
        "use strict";

        // Add the jQuery object globaly or debugging.
        // window.$ = $;

        log.debug('AMD module loaded.');

        var sortbystate = 'name',
            sortascstate = true,
            showtagliststate = false,
            courses = {},
            courseids = [],
            cardsfirst = 12,
            cardsset = 6,
            listfirst = 30,
            listset = 15,
            cardsrendered = false,
            listrendered = false,
            cardcourseidsremaining = [],
            listcourseidsremaining = [],
            userid = 0,
            cardsInfScroll = null,
            listInfScroll = null,
            $catalogarea = null,
            $searcharea = null,
            $coursesearchform = null,
            $coursesearchfield = null,
            $resultarea = null,
            $cardsarea = null,
            $listarea = null,
            $coursecardsul = null,
            $courselisttable = null,
            $navtabs = null,
            $tagpreselectarea = null,
            $formsearch = null,
            $selectsort = null,
            $tagarea = null,
            $selectedCourseTags = null,
            $switchDisplayBtn = null,
            preselecttagsloaded = false,
            hideitemClass = 'hide-item',
            tagContextObj = {id: 0, type: null, group: null, name: null},
            activityContextObj = {id: 0, type: null, group: null, name: null, sort: null, pretext: null, posttext: null, remove: 0},
            col2sortfieldmap = {
                'name': 'sortorder',
                'date': 'sortdate',
                'availseats': 'availnumber',
                'deadline': 'deadline',
                'municipality': 'municipality',
                'location': 'location'
            },
            sortstrmapping = {
                'name': 'course_name',
                'date': 'course_date',
                'availseats': 'course_seats',
                'deadline': 'course_deadline',
                'municipality': 'course_municipality',
                'location': 'course_location'
            };

        /**
         * Render a search tag item with a template with the given data.
         *
         * @param {Array} $where The jQuery node to which the tag shall be appended
         * @param {Array|object} context The data for the template
         */
        var renderSearchTagItem = function ($where, context) {
            templates
                .render('local_course_search/course_search_region_search_groups_item', context)
                .done(function (html) {
                    $where.append(html);
                });
        };

        /**
         * Render the display area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderDisplayArea = function (context) {
            templates
                .render('local_course_search/course_search_result_area', {})
                .done(function (html) {
                    $resultarea.html(html);
                    $cardsarea = $resultarea.find('#tabcards');
                    $listarea = $resultarea.find('#tablist');

                    renderCardsArea(context);
                });
        };

        /**
         * Render the cards area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderCardsArea = function (context) {
            console.log('cards context', context);
            templates
                .render('local_course_search/course_search_course_cards', context)
                .done(function (html) {
                    $cardsarea.html(html);

                    $coursecardsul = $resultarea.find('#course-cards');
                    cardsrendered = true;

                    // Init infinite scroll.
                    cardsInfScroll = new InfiniteScroll($coursecardsul.get(0), {
                        path: 'page{{#}}', // hack
                        loadOnScroll: false, // disable loading
                        history: false,
                        onInit: function () {
                            console.log('cardsInfScroll init');
                        }
                    });

                    cardsScrollEventHandlerOn(true);

                    $navtabs = $('.nav-tabs').eq(0);
                    $('a[data-toggle="tab"]').on('shown', tabChangeHandler);

                    updateCourseDisplay();
                });
        };

        /**
         * Render the list area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderListArea = function (context) {
            console.log('list context', context);
            templates
                .render('local_course_search/course_search_course_list', context)
                .done(function (html) {
                    $listarea.html(html);

                    $courselisttable = $resultarea.find('#course-list');
                    listrendered = true;

                    // Init infinite scroll.
                    listInfScroll = new InfiniteScroll($courselisttable.get(0), {
                        path: 'page{{#}}', // hack
                        loadOnScroll: false, // disable loading
                        history: false,
                        onInit: function () {
                            console.log('listInfScroll init');
                        }
                    });

                    listScrollEventHandlerOn(true);

                    updateCourseDisplay();
                });
        };

        /**
         * Turn the card scroll event handler on/off.
         *
         * param {bool} state The desired state
         */
        var cardsScrollEventHandlerOn = function (state) {
            if (!cardsInfScroll) {
                return;
            }

            if (state) {
                cardsInfScroll.on('scrollThreshold', cardsScrollThresholdHandler);
            } else {
                cardsInfScroll.off('scrollThreshold', cardsScrollThresholdHandler);
            }
        };

        /**
         * Turn the list scroll event handler on/off.
         *
         * param {bool} state The desired state
         */
        var listScrollEventHandlerOn = function (state) {
            if (!listInfScroll) {
                return;
            }

            if (state) {
                listInfScroll.on('scrollThreshold', listScrollThresholdHandler);
            } else {
                listInfScroll.off('scrollThreshold', listScrollThresholdHandler);
            }
        };

        /**
         * Render the next set of cards.
         *
         * @param {Array|object} context The data for the template
         */
        var renderDynamicLoadedCards = function (context) {

        };

        /**
         * Render the tag preselection area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderTagPreselectArea = function (context) {
            templates
                .render('local_course_search/course_search_tag_preselect_area', context)
                .done(function (html) {
                    $tagpreselectarea.html(html);

                    preselecttagsloaded = true;

                    setCheckboxForPreselectedTags();
                });
        };

        /**
         * Handle the InfinitScroll scrollThreshold event.
         *
         * Add content if there is more.
         */
        var cardsScrollThresholdHandler = function () {
            // console.log('Scroll at bottom.', this);
            if (cardcourseidsremaining.length) {
                console.log('card more courses');
            } else {
                console.log('card all shown');
                cardsScrollEventHandlerOn(false);
                return;
            }

            var context = {'courses': []},
                nextids = cardcourseidsremaining.splice(0, cardsset);

            context.courses = nextids.map(function (k) {
                return courses[k];
            });

            if (context.courses.length) {
                templates
                    .render('local_course_search/course_search_course_card_set', context)
                    .done(function (html) {
                        $coursecardsul.append(html);
                    });
            }
        };

        /**
         * Handle the InfinitScroll scrollThreshold event.
         *
         * Add content if there is more.
         */
        var listScrollThresholdHandler = function () {
            // console.log('Scroll at bottom.', this);
            if (listcourseidsremaining.length) {
                console.log('list more courses');
            } else {
                console.log('list all shown');
                listScrollEventHandlerOn(false);
                return;
            }

            var context = {'courses': []},
                nextids = listcourseidsremaining.splice(0, listset);

            context.courses = nextids.map(function (k) {
                return courses[k];
            });

            if (context.courses.length) {
                templates
                    .render('local_course_search/course_search_course_list_set', context)
                    .done(function (html) {
                        $courselisttable.children('tbody').append(html);
                    });
            }
        };

        /**
         * Handle the click on the »Select interests« button.
         *
         * Hide the result area and show the tag preselect area.
         */
        var toggleTagPreselectPageHandler = function () {
            var taglist = '',
                tagarray = [],
                $taggroups = null,
                gl,
                tl;

            // Change state to tag selection.
            if (showtagliststate) {
                $switchDisplayBtn.text($switchDisplayBtn.data('changetoselection'));
                if ($resultarea.find('[href="#tablist"]').hasClass('active')) {
                    $coursesearchform
                        .find('.display')
                        .find('[data-group="tags"]')
                        .removeClass('hidden');
                }

                $taggroups = $searcharea.find('.tag-group');
                gl = $taggroups.length - 1;
                if ($taggroups.length) {
                    $taggroups.each(function (i) {
                        var $group = $(this),
                            $tags = null;

                        $tags = $group.find('.checkbox');

                        tl = $tags.length - 1;
                        if ($tags.length) {
                            $tags.each(function (j) {
                                taglist += $(this).data('id');
                                tagarray.push($(this).data('id'));

                                if (j < tl) {
                                    taglist += ',';
                                }
                            });
                        }

                        if (i < gl) {
                            taglist += '|';
                        }
                    });
                }

                // Remove all search criteria.
                $coursesearchfield.blur();
                $tagarea
                    .find('.btn-tag')
                    .each(function () {
                        $(this).click();
                    });

                save_search_criteria(tagarray);
            } else {
                showtagliststate = !showtagliststate;
                $switchDisplayBtn.text($switchDisplayBtn.data('changetoresults'));
                $coursesearchform
                    .find('.display')
                    .find('[data-group="tags"]')
                    .addClass('hidden');

                // Remove all search criteria.
                $coursesearchfield.blur();
                $tagarea
                    .find('.btn-tag')
                    .each(function () {
                        $(this).click();
                    });

                $coursesearchform.find('.fieldset-hidden').removeClass('fieldset-hidden');

                $resultarea.toggleClass('section-hidden');
                $tagpreselectarea.toggleClass('section-hidden');
            }
        };

        /**
         * Handle the cards/list tab change event.
         *
         * When the cards are shown disable the »Show course tags« checkbox.
         *
         * @param {object} e The event object
         */
        var tabChangeHandler = function (e) {
            var target = $(e.target).attr("href"),
                $showtagscheckbox = $coursesearchform.find('.display').find('[data-group="tags"]'),
                $input = $showtagscheckbox.find('input');

            if (target === '#tabcards') {
                $showtagscheckbox.addClass('hidden');
                listScrollEventHandlerOn(false);
                cardsScrollEventHandlerOn(true);
            } else if (target === '#tablist') {
                $showtagscheckbox.removeClass('hidden');
                cardsScrollEventHandlerOn(false);
                listScrollEventHandlerOn(true);

                if (!listrendered) {
                    var nextids = listcourseidsremaining.splice(0, listfirst);
                    renderListArea({
                        'courses': nextids.map(function (k) {
                            return courses[k];
                        })
                    });
                }
            }
        };

        /**
         * Handle the date picker »changeDate« events.
         *
         * @param {object} e The jQuery event object
         */
        var dateChangeHandler = function (e) {
            var $ele = $(e.target),
                $parent = $ele.parents('.date'),
                dateEuro = e.format("yyyymmdd"),
                dateUser = e.format("dd.mm.yyyy"),
                activityContext = $.extend({}, activityContextObj, {
                    type: $parent.data('type'),
                    group: $parent.data('group'),
                    name: $parent.data('name'),
                    posttext: " " + dateUser
                });

            // Set the value of the related hidden field to the euro formatted date
            // which is returned form the date picker. Internally used because the tag date property is formated this way.
            $ele.siblings('input[type="hidden"]').eq(0).val(dateEuro);

            // Remove or add the related tag in the selected tag list depending on the checked status.
            // Remove an eventually existing tag.
            $tagarea
                .find('button')
                .filter('[data-name="' + $parent.data('name') + '"]')
                .parent('li')
                .remove();

            if (dateUser === "") {
                activityContext.remove = 1;
            } else {
                templates
                    .render('local_course_search/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            filterCourses();
            updateCourseDisplay();
        };

        /**
         * Handle the change event on checkboxes in the search area.
         *
         * When the tag checkbox is changed
         * _ if the tag's state is checked
         *     _ uncheck
         *     _ remove the related tag top right
         *     _ change the course filtering
         * _ if the tag's state is unchecked
         *     _ check
         *     _ add the related tag top right
         *     _ change the course filtering
         */
        var checkboxChangeHandler = function () {
            var $ele = $(this),
                $parent = $ele.parent(),
                checked = $ele.is(":checked"),
                $relatedTag = null,
                activityContext = $.extend({}, activityContextObj, {
                    id: $parent.data('id'),
                    type: $parent.data('type'),
                    group: $parent.data('group'),
                    name: $parent.data('name')
                });

            // Remove or add the related tag in the selected tag list depending on the checked status.
            if (!checked) {
                $relatedTag = $tagarea
                    .find('button')
                    .filter('[data-name="' + $ele.parent().data('name') + '"]')
                    .parent('li')
                    .remove();
                activityContext.remove = 1;
            } else if ($parent.data('group') === 'sort') {
                // If the sort changed then remove the existing sort tag and add the new.
                $relatedTag = $tagarea
                    .find('button')
                    .filter('[data-group="sort"]')
                    .parent('li')
                    .remove();

                // Prepare sort related data.
                activityContext.sort = $parent.data('sort');
                activityContext.name = M.str.local_course_search.sortby + " " +
                    M.str.local_friadmin[sortstrmapping[activityContext.sort]];

                templates
                    .render('local_course_search/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            } else {
                templates
                    .render('local_course_search/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            if ($parent.data('type') === 'course') {
                filterCourses();
            }

            updateCourseDisplay();
        };

        /**
         * Handle the change event for the sort select popup menu to sort the course by columns.
         */
        var sortSelectHandler = function () {
            sortSelect();
        };

        /**
         * Process the column sort.
         *
         * Use a hidden checkbox to trigger the sort. Change the data of the hidden checkbox to the selected option.
         * With the hidden checkbox the central checkbox event system can be used to handle the sorting.
         */
        var sortSelect = function () {
            var $selected = $selectsort.find("option:selected").eq(0),
                $relatedCheckboxLabel = $selectsort.siblings('.hidden').eq(0),
                selecteditem = $selected.val().toLowerCase();

            if (sortbystate === selecteditem) {
                return;
            } else {
                sortbystate = selecteditem;
            }

            $relatedCheckboxLabel.data('sort', selecteditem);
            $relatedCheckboxLabel
                .find('input')
                .trigger('change');
        };

        /**
         * Handle the click event on the course list column titles to change the table sort.
         *
         * @param {object} e The jQuery event object
         */
        var colTitleClickHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $target = $(e.target),
                $relatedCheckboxLabel = $selectsort.siblings('.hidden').eq(0),
                sortwhat = $target.data('sort');

            if (sortbystate === sortwhat) {
                sortascstate = !sortascstate;
                $searcharea.find('[data-group="sortdesc"]').find('input').click();
            } else {
                sortbystate = sortwhat;
            }

            $selectsort.val(sortwhat);

            $relatedCheckboxLabel.data('sort', sortwhat);
            $relatedCheckboxLabel
                .find('input')
                .trigger('change');
        };

        /**
         * Handle the click event on the tags top right.
         *
         * Remove the tag and change the state of the related element in the search tag area.
         */
        var selectedTagClickHandler = function () {
            var $ele = $(this);

            // Change the state of the related element in the serach area.
            if ($ele.data('group') === 'searchquery') {
                $coursesearchform.find('.search-query').val('');
            } else if ($ele.data('group') === 'date-from') {
                $("#date-from").datepicker('update', '');
                $("#date-from-eurodate").val('');
            } else if ($ele.data('group') === 'date-to') {
                $("#date-to").datepicker('update', '');
                $("#date-to-eurodate").val('');
            } else if ($ele.data('group') === 'sort') {
                $selectsort.val('name');
            } else {
                $searcharea
                    .find('label')
                    .filter('[data-name="' + $ele.data('name') + '"]')
                    .find('input').prop('checked', false);
            }

            // Remove the top right tag.
            $ele.remove();

            // Start the related action.
            if ($ele.data('group') === 'sort') {
                sortSelectHandler();
            } else if ($ele.data('type') === 'course') {
                filterCourses();
            } else if ($ele.data('type') === 'display') {
                updateCourseDisplay();
            }
        };

        /**
         * Handle the change event on the checkboxes in the tag preselect area.
         */
        var preselectedCheckboxChangeHandler = function () {
            var $ele = $(this),
                checked = $ele.is(":checked"),
                $group = null,
                group = '',
                displaygroup = '',
                tagcontext = {};

            if (!checked) {
                $coursesearchform.find('[data-id="' + $ele.data('id') + '"]').remove();
            } else {
                group = $ele.data('group');
                displaygroup = group;

                $group = $coursesearchform.find('fieldset[data-group="' + displaygroup + '"]');
                tagcontext = $.extend({}, tagContextObj, {
                    id: $ele.data('id'),
                    type: $ele.data('type'),
                    group: $ele.data('group'),
                    name: $ele.data('name')
                });

                renderSearchTagItem($group, tagcontext);
            }
        };

        /**
         * Handle the keyup event of the search text field.
         *
         * Filter the tags by name that contains the enterd text.
         */
        var tagFilterTextHandler = function (e) {
            var $input = null,
                filter = '',
                $tagLists = null,
                $tags = null;

            $input = $(e.currentTarget);
            filter = $input.val().toLowerCase();
            $tagLists = $tagpreselectarea.find('.unlist');
            $tags = $tagLists.find('label');

            $tags.each(function () {
                var $ele = $(this);
                if ($ele.find('input').data('name').toLowerCase().indexOf(filter) !== -1) {
                    $ele.parent('li').removeClass('hidden');
                } else {
                    $ele.parent('li').addClass('hidden');
                }
            });

            // Open all groups with found tags, hide those without.
            if (filter !== '') {
                $tagpreselectarea
                    .find('.accordion-body')
                    .each(function () {
                        var ele = $(this);
                        if (ele.has('li:not(.hidden)').length) {
                            ele.collapse('show');
                        } else {
                            ele.collapse('hide');
                        }
                    });
            } else {
                $tagpreselectarea
                    .find('.accordion-body')
                    .collapse('hide');
            }
        };

        /**
         * Handle the submit event on the course search form.
         */
        var textSearchHandler = function (e) {
            var $form = $(this),
                text,
                activityContext;

            // Don't trigger form submit action for the page.
            e.preventDefault();

            text = $form.find('.search-query').val();
            activityContext = $.extend({}, activityContextObj, {
                type: 'course',
                group: 'searchquery',
                name: text,
                pretext: 'Search text: '
            });

            // Remove a possibly present search text tag.
            $tagarea.find('li').has('[data-group="searchquery"]').remove();

            // Add the search string as a tag.
            if (text !== '') {
                templates
                    .render('local_course_search/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            filterCourses();
        };

        /**
         * Get the selected tag search text.
         *
         * @returns {string} The entered text
         */
        var getSelectedSearchText = function () {
            return $coursesearchfield.val().toLowerCase();
        };

        /**
         * Collect the selected search criteria in an object by group.
         *
         * @returns {object} The search items
         */
        var getSelectedCourseTagsGrouped = function () {
            var checkeditems,
                searchItemsObj = {};

            checkeditems = $coursesearchform.find('label').has(':checked');
            checkeditems.each(function () {
                var $item = $(this);
                if ($item.data('type') === 'course') {
                    // Add an empty group if not present.
                    if (!searchItemsObj.hasOwnProperty($item.data('group'))) {
                        searchItemsObj[$item.data('group')] = [];
                    }
                    // Add the item to the group.
                    searchItemsObj[$item.data('group')].push($item.data('group') + '-' + $item.data('name'));
                }
            });

            return searchItemsObj;
        };

        /**
         * Get the preselected tags in the course search area.
         *
         * @returns {Array} The tag collection
         */
        var getPreselectedCourseTagObjects = function () {
            var checkeditems,
                searchItems = [];

            checkeditems = $coursesearchform.find('label.checkbox');
            checkeditems.each(function () {
                var $item = $(this);
                if ($item.data('type') === 'course') {
                    searchItems.push({
                        'id': $item.data('id'),
                        'type': $item.data('type'),
                        'name': $item.data('name'),
                        'group': $item.data('group'),
                        'groupid': $item.data('groupid')
                    });
                }
            });

            return searchItems;
        };

        /**
         * Get the date object form the date selector.
         *
         * @returns {Object} The selected dates
         */
        var getSelectedFromToDates = function () {
            var dates = {
                    from: null,
                    to: null
                },
                date;

            // Check and set the from date.
            date = $("#date-from-eurodate").val();
            if (date !== "") {
                dates.from = date;
            }

            // Check and set the to date.
            date = $("#date-to-eurodate").val();
            if (date !== "") {
                dates.to = date;
            }

            return dates;
        };

        /**
         * Get the selected tags that influence the course display.
         *
         * @returns {Array} The selected display tags collection
         */
        var getSelectedDisplayTags = function () {
            var checkeditems,
                displayItems = [];

            checkeditems = $coursesearchform.find('label').has(':checked');
            checkeditems.each(function () {
                var $item = $(this);
                if ($item.data('type') === 'display') {
                    if ($item.data('group') === 'sort') {
                        displayItems.push('sort-' + $item.data('sort'));
                    } else {
                        displayItems.push($item.data('group'));
                    }
                }
            });

            return displayItems;
        };

        /**
         * Sort the displayed courses.
         *
         * Get the active sort criteria and sort the courses.
         */
        var updateCourseDisplay = function () {
            var $list = null,
                $sortedlist = null,
                selectedDisplayTags = getSelectedDisplayTags(),
                what = 'name',
                $coltitle = null;

            // The sort function for the course sort, compare on the defined data attribute.
            var sortFkt = function (a, b) {
                if (sortascstate) {
                    return ($(b).data(col2sortfieldmap[what])) < ($(a).data(col2sortfieldmap[what])) ? 1 : -1;
                } else {
                    return ($(b).data(col2sortfieldmap[what])) > ($(a).data(col2sortfieldmap[what])) ? 1 : -1;
                }
            };

            if (selectedDisplayTags.indexOf('tags') !== -1) {
                $resultarea.find(".course-list").removeClass('tags-hidden');
            } else {
                $resultarea.find(".course-list").addClass('tags-hidden');
            }

            sortascstate = selectedDisplayTags.indexOf('sortdesc') === -1;

            selectedDisplayTags.forEach(function (item) {
                if (item.indexOf('sort-') !== -1) {
                    what = item.replace('sort-', '');
                }
            });

            // Set the CSS class to show the sort arrow.
            $resultarea.find('.course-list-col-titles').find('.sortasc').removeClass('sortasc');
            $resultarea.find('.course-list-col-titles').find('.sortdesc').removeClass('sortdesc');

            $coltitle = $resultarea.find('.course-list-col-titles').find('[data-sort="' + what + '"]');
            if (sortascstate) {
                $coltitle.addClass('sortasc');
            } else {
                $coltitle.addClass('sortdesc');
            }

            $list = $resultarea.find("#tabcards").find('.course-cards').eq(0);
            $list.html($list.children('.course-card-item').sort(sortFkt));
            $list = $resultarea.find("#tablist").find('.course-list').find('tbody').eq(0);
            $list.html($list.children('.course-list-item').sort(sortFkt));

            // Set the »odd« class for alternating row colors only for the visible rows.
            $sortedlist = $resultarea.find("#tablist").find('.course-list');
            $sortedlist.find('tr:not(.hide-item)').each(function (i) {
                if (i % 2) {
                    $(this).removeClass('odd');
                } else {
                    $(this).addClass('odd');
                }
            });
        };

        /**
         * Walk all course items in the course data object and collect the ids of the matching courses.
         * Check if any of the selected search criteria match, if one matches add the id to the show list.
         * The rules for matches are:
         * _ OR the matches within one tag group
         * _ AND the group matches, the freetext and the date
         *
         * Then walk the course nodes in the display area and set the display status.
         */
        var filterCourses = function () {
            var selectedCourseTagsGrouped = getSelectedCourseTagsGrouped(),
                searchText = getSelectedSearchText(),
                fromtoDates = getSelectedFromToDates(), // array with the [from, to] dates
                hasTextSearch = (searchText !== ''),
                courseIDsFiltered = cloneArray(courseids),
                filtered = [],
                thecourse,
                foundany = false;

            // If no search criterion is set show all courses.
            if (!Object.keys(selectedCourseTagsGrouped).length && '' === searchText &&
                fromtoDates.from === null && fromtoDates.to === null) {
                showFilteredCourses(courseIDsFiltered);
                return;
            }

            // Check if the course dates match the chosen dates.
            if (fromtoDates.from !== null || fromtoDates.to !== null) {
                filtered = courseIDsFiltered.filter(function (id) {
                    thecourse = courses[id];
                    if (fromtoDates.from !== null && fromtoDates.to !== null) {
                        if (thecourse.sortdate >= fromtoDates.from && thecourse.sortdate <= fromtoDates.to) {
                            return true;
                        }
                    } else if (fromtoDates.from !== null) {
                        if (thecourse.sortdate >= fromtoDates.from) {
                            return true;
                        }
                    } else if (fromtoDates.to !== null) {
                        if (thecourse.sortdate <= fromtoDates.to) {
                            return true;
                        }
                    } else {
                        return false;
                    }
                });

                if (filtered.length) {
                    foundany = true;
                    courseIDsFiltered = cloneArray(filtered);
                    filtered = [];
                }
            }

            // Check if the text index contains the search string.
            if (hasTextSearch) {
                filtered = courseIDsFiltered.filter(function (id) {
                    thecourse = courses[id];
                    return (thecourse.hasOwnProperty('alltext') && thecourse.alltext &&
                        thecourse.alltext.indexOf(searchText) !== -1);
                });

                courseIDsFiltered = cloneArray(filtered);
                if (filtered.length) {
                    foundany = true;
                    filtered = [];
                } else {
                    foundany = false;
                }
            }

            // Filter the tags. Within a tag group use OR, between the tag groups AND.
            Object.keys(selectedCourseTagsGrouped).forEach(function (key) {
                filtered = courseIDsFiltered.filter(function (id) {
                    // Use OR - any group tag will be positive.
                    var found = false;
                    selectedCourseTagsGrouped[key].forEach(function (item) {
                        if (courses[id].tagcollection.indexOf(item) !== -1) {
                            found = true;
                        }
                    });

                    return found;
                });

                // Use AND - reduce the found courses list to the found courses for the next tag group.
                courseIDsFiltered = cloneArray(filtered);
                if (filtered.length) {
                    foundany = true;
                    filtered = [];
                } else {
                    foundany = false;
                }
            });

            // If tags are selected but no courses match then set the id list to -1.
            if (!foundany) {
                courseIDsFiltered = [-1];
            }

            showFilteredCourses(courseIDsFiltered);
        };

        /**
         * Set the visibility of the courses in the cards and list view.
         *
         * @param {Array} courseIDsToShow The list of course ids to show
         */
        var showFilteredCourses = function (courseIDsToShow) {
            var context,
                nextids;

            if ($cardsarea.hasClass('active')) {
                cardcourseidsremaining = cloneArray(courseIDsToShow);

                context = {'courses': []};
                nextids = cardcourseidsremaining.splice(0, cardsfirst);

                context.courses = nextids.map(function (k) {
                    return courses[k];
                });

                if (context.courses.length) {
                    templates
                        .render('local_course_search/course_search_course_card_set', context)
                        .done(function (html) {
                            $coursecardsul.html(html);
                            cardsScrollEventHandlerOn(true);
                        });
                }
            } else if ($listarea.hasClass('active')) {
                listcourseidsremaining = cloneArray(courseIDsToShow);

                context = {'courses': []};
                nextids = listcourseidsremaining.splice(0, listfirst);

                context.courses = nextids.map(function (k) {
                    return courses[k];
                });

                if (context.courses.length) {
                    templates
                        .render('local_course_search/course_search_course_list_set', context)
                        .done(function (html) {
                            $courselisttable.children('tbody').html(html);
                            listScrollEventHandlerOn(true);
                        });
                }
            }
        };

        /**
         * Set the visibility of the courses in the cards and list view.
         *
         * @param {Array} courseIDsToShow The list of course ids to show
         */
        var showHideCourses = function (courseIDsToShow) {
            var $coursecards = $resultarea.find('.course-cards'),
                $courselist = $resultarea.find('.course-list');

            if (courseIDsToShow.length) {
                // If first item is -1 then hide all courses.
                if (courseIDsToShow[0] === -1) {
                    courseids.forEach(function (item) {
                        $coursecards.find('#coursecarditem-' + item).addClass(hideitemClass);
                        $courselist.find('#courselistitem-' + item).addClass(hideitemClass);
                    });
                } else {
                    courseids.forEach(function (item) {
                        if (courseIDsToShow.indexOf(item) !== -1) {
                            $coursecards.find('#coursecarditem-' + item).removeClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item).removeClass(hideitemClass);
                        } else {
                            $coursecards.find('#coursecarditem-' + item).addClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item).addClass(hideitemClass);
                        }
                    });
                }
            } else {
                courseids.forEach(function (item) {
                    $coursecards.find('#coursecarditem-' + item).removeClass(hideitemClass);
                    $courselist.find('#courselistitem-' + item).removeClass(hideitemClass);
                });
            }
        };

        /**
         * Get the course data for the user viewable courses via ajax.
         */
        var get_coursedata = function () {
            $.when(
                ajax.call([
                    {
                        methodname: 'local_course_search_get_course_data',
                        args: {
                            userid: userid
                        }
                    }
                ])[0]
            ).then(function (response) {
                var coursedata = JSON.parse(response.coursedata),
                    nextids = [];

                courses = coursedata.courses;

                // Create an array with the courses from the »courses« object.
                // This has the courseid as keys and needs to be converted into an array of objects.
                courseids = Object.keys(courses);
                cardcourseidsremaining = courseids.slice();
                listcourseidsremaining = courseids.slice();

                console.log('courses', courses);
                console.log('courseids', courseids.slice());

                nextids = cardcourseidsremaining.splice(0, cardsfirst);
                renderDisplayArea({
                    'courses': nextids.map(function (k) {
                        return courses[k];
                    })
                });

                // And now get the course tags.
                get_all_course_tags();
            });
        };

        /**
         * Save the user preselected tags via ajax.
         *
         * @param {Array} tagarray The array with the selected tag ids
         */
        var save_search_criteria = function (tagarray) {
            var data = {
                sesskey: M.cfg.sesskey,
                tags: tagarray
            };
            $.when(
                ajax.call([
                    {
                        methodname: 'local_course_search_save_search_criteria',
                        args: {
                            data: JSON.stringify(data)
                        }
                    }
                ])[0]
            ).then(function (response) {
                var result = JSON.parse(response.result);
                showtagliststate = !showtagliststate;

                $resultarea.html('');
                get_coursedata(false);

                $coursesearchform.find('.tag-group')
                    .not(':has(label.checkbox)')
                    .addClass('fieldset-hidden');

                $resultarea.toggleClass('section-hidden');
                $tagpreselectarea.toggleClass('section-hidden');
            });
        };

        /**
         * Get the user search criteria via ajax.
         */
        var get_user_search_criteria = function () {
            $.when(
                ajax.call([
                    {
                        methodname: 'local_course_search_get_user_search_criteria',
                        args: {
                            userid: userid
                        }
                    }
                ])[0]
            ).then(function (response) {
                var data = JSON.parse(response.data);

                log.debug('get_user_search_criteria');
            });
        };

        /**
         * Get all course tags for the preselect area via ajax.
         */
        var get_all_course_tags = function () {
            // When the tag preselect area is rendered the first time preselecttagsloaded is set to true.
            if (preselecttagsloaded) {
                return;
            }

            $.when(
                ajax.call([
                    {
                        methodname: 'local_course_search_get_all_course_tags',
                        args: {
                            userid: userid
                        }
                    }
                ])[0]
            ).then(function (response) {
                var data = JSON.parse(response.data);

                renderTagPreselectArea(data);
            });
        };

        /**
         * Set the checked atttribute on the checkboxes for the preselected tags.
         */
        var setCheckboxForPreselectedTags = function () {
            var preselectedtags = getPreselectedCourseTagObjects();

            preselectedtags.forEach(function (tag) {
                $tagpreselectarea
                    .find('[data-id="' + tag.id + '"]')
                    .attr('checked', true);
            });
        };

        /**
         * Activate the dat picker plugin.
         *
         * Load the code for the date picker and initialize the from and to date pickers.
         * Set the event handler for the »changeDate« events.
         */
        var activateDatePicker = function () {
            // Bootstrap 2.x datepicker https://github.com/uxsolutions/bootstrap-datepicker/tree/1.5.
            // require(['local_course_search/Xloader'], function () {
            $("#date-from").datepicker({
                format: "dd.mm.yyyy",
                calendarWeeks: true,
                todayHighlight: true,
                clearBtn: true,
                autoclose: true
            }).on("changeDate", function (e) {
                dateChangeHandler(e);
            });
            $("#date-to").datepicker({
                format: "dd.mm.yyyy",
                calendarWeeks: true,
                todayHighlight: true,
                clearBtn: true,
                autoclose: true
            }).on("changeDate", function (e) {
                dateChangeHandler(e);
            });
            // });
        };

        /**
         * Clone an array
         *
         * @param {Array} inArray
         * @returns {Array}
         */
        var cloneArray = function (inArray) {
            return inArray.slice(0);
        };

        /**
         * Initialize function, called with the PHP requires->js_call_amd command.
         */
        return {
            init: function () {
                log.debug('AMD module init.');

                // Get the relevant DOM elements.
                $catalogarea = $('#catalog-area');
                userid = parseInt($catalogarea.data('userid'), 10);
                $searcharea = $('#search-area');
                $coursesearchform = $('#course-search-form');
                $coursesearchfield = $coursesearchform.find('#course-search');
                $resultarea = $('#result-area');
                $tagpreselectarea = $('#tag-preselect-area');
                $tagarea = $('#tag-area');
                $selectedCourseTags = $tagarea.find('.selected-tags');
                $formsearch = $searcharea.find('.form-search');
                $selectsort = $searcharea.find('#select-sort');
                $switchDisplayBtn = $searcharea.find('#switch-display');

                // Fetch the course data.
                get_coursedata();

                // Set the event handlers.
                $searcharea.on('change', '[type="checkbox"]', checkboxChangeHandler);
                $searcharea.on('change', '#select-sort', sortSelectHandler);
                $resultarea.on('click', 'th.coltitle', colTitleClickHandler);
                $tagarea.on('click', 'button', selectedTagClickHandler);
                $tagpreselectarea.on('change', '[type="checkbox"]', preselectedCheckboxChangeHandler);
                $tagpreselectarea.on('keyup', '#tagFilter', tagFilterTextHandler);
                $coursesearchform.on('submit', textSearchHandler);
                $switchDisplayBtn.on('click', toggleTagPreselectPageHandler);

                // Inititialize the datepicker plugin.
                activateDatePicker();
            }
        };
    }
);
