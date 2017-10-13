/*global define: false, M: true, console: false */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates',
        'theme_bootstrapbase/bootstrap',
        // 'local_course_search/cookie',
        'local_course_search/ld_loader',
        'local_course_search/viewstate',
        'local_course_search/is_loader',
        'local_course_search/dp_loader'
    ],
    function ($, notification, log, ajax, templates, bootstrap, _, viewState, InfiniteScroll) {
        "use strict";

        // Add the jQuery object globaly or debugging.
        window.$ = $;

        log.debug('AMD module loaded.');

        if ($('#header').hasClass('not-loggedin')) {
            return;
        }

        // The var nocourses is used as a flag for development - if true no courses are loaded.
        var nocourses = false;
        var sortbystate = 'name',
            sortascstate = true,
            showtagliststate = false,
            actualCourseCount = 0,
            $actualCourseCount = null,
            $actualCourseCountInfo = null,
            courses = {},
            coursesSortArray = [],
            courseids = [],
            courseIDsFiltered = [],
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
            $dpfrom = null,
            $dpto = null,
            $datefrom = null,
            $dateto = null,
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
            },
            viewstatearrayindex = {
                'view': 0,
                'text': 1,
                'tags': 2,
                'date': 3,
                'display': 4
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
            if (viewState.getView() === '0') {
                cardsAddNextItems(cardsfirst);
            } else {
                listAddNextRows(listfirst);
            }
        };

        /** -- check
         * Render the cards area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderCardsArea = function (context) {
            cardsrendered = true;

            cardsAddNextItems();

            // Init infinite scroll.
            cardsInfScroll = new InfiniteScroll($coursecardsul.get(0), {
                path: 'page{{#}}', // hack
                loadOnScroll: false, // disable loading
                history: false,
                onInit: function () {
                    log.debug('cardsInfScroll init');
                }
            });

            cardsScrollEventHandlerOn(true);

            // sortedCourseDisplayUpdate();
        };

        /** -- check
         * Render the list area with a template with the given data.
         *
         * @param {Array|object} context The data for the template
         */
        var renderListArea = function (context) {
            listrendered = true;

            listAddNextRows();

            // Init infinite scroll.
            listInfScroll = new InfiniteScroll($courselisttable.get(0), {
                path: 'page{{#}}', // hack
                loadOnScroll: false, // disable loading
                history: false,
                onInit: function () {
                    log.debug('listInfScroll init');
                }
            });

            listScrollEventHandlerOn(true);
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
         * Handle the InfinitScroll scrollThreshold event.
         *
         * Add content if there is more.
         */
        var cardsScrollThresholdHandler = function () {
            if (cardcourseidsremaining.length) {
                log.debug('card more courses');
            } else {
                log.debug('card all shown');
                cardsScrollEventHandlerOn(false);
                return;
            }

            cardsAddNextItems();
        };

        /**
         * Add the next cards to the view if there is more.
         */
        var cardsAddNextItems = function (amount) {
            if (nocourses) {
                return;
            }
            var set = amount === undefined ? cardsset : amount,
                context = {'courses': []},
                nextids = cardcourseidsremaining.splice(0, set);

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
            if (listcourseidsremaining.length) {
                log.debug('list more courses');
            } else {
                log.debug('list all shown');
                listScrollEventHandlerOn(false);
                return;
            }

            listAddNextRows();
        };

        /**
         * Add the next rows to the view if there is more.
         */
        var listAddNextRows = function (amount) {
            if (nocourses) {
                return;
            }
            var set = amount === undefined ? listset : amount,
                context = {'courses': []},
                nextids = listcourseidsremaining.splice(0, set);

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
         * Handle the cards/list tab change event.
         *
         * When the cards are shown disable the »Show course tags« checkbox.
         *
         * @param {object} e The event object
         */
        var tabChangeHandler = function (e) {
            var target = $(e.target).attr("href");

            if (target === '#tabcards') {
                viewState.setView("0");

                changeView();

                if (!$coursecardsul.children('li').length) {
                    cardsAddNextItems(cardsfirst);
                }
            } else if (target === '#tablist') {
                viewState.setView("1");

                changeView();

                if (!$courselisttable.children('tbody').children('tr').length) {
                    listAddNextRows(listfirst);
                }
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

                saveInterestsAndGetCoursesSequence(tagarray);
            } else {
                showtagliststate = !showtagliststate;
                $switchDisplayBtn.text($switchDisplayBtn.data('changetoresults'));
                $coursesearchform
                    .find('.display')
                    .find('[data-group="tags"]')
                    .addClass('hidden');

                $actualCourseCountInfo.hide();
                cardsScrollEventHandlerOn(false);
                listScrollEventHandlerOn(false);

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
         * Handle the date picker »changeDate« events.
         *
         * @param {object} e The jQuery event object
         */
        var dateChangeHandler = function (e) {
            var $ele = $(e.target),
                // $parent = $ele.parents('.date'),
                $parent = $ele,
                group = $parent.data('group'),
                dateEuro = e.format("yyyymmdd"),
                dateEuroStr = e.format("yyyy-mm-dd"),
                dateUser = e.format("dd.mm.yyyy");

            // Save the changed date.
            viewState.setDate(dateEuroStr === "" ? '0' : dateEuroStr, group);

            // Set the value of the related hidden field to the euro formatted date
            // which is returned form the date picker. Internally used because the tag date property is formated this way.
            $ele.siblings('input[type="hidden"]').eq(0).val(dateEuro);

            if (group.indexOf('from') !== -1) {
                changeDateTag('from');
            } else {
                changeDateTag('to');
            }
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
                id = $parent.data('id');

            // Remove or add the related tag in the selected tag list depending on the checked status.
            if (!checked) {
                // Change the related viewstate.
                if (typeof id === "string" && id.indexOf('d') !== -1) {
                    changeCheckbox('.display', id, 0);
                    viewState.setDisplay('0', (id === 'd1' ? 'desc' : 'showtags'));
                    // Change sort when descending has been clicked.
                    if (id === 'd1') {
                        changeColumnSort();
                    }
                } else {
                    changeCheckbox('.tag-group', id, 0);
                    viewState.setTag(id.toString(), 'remove');
                }
            } else if ($parent.data('group') === 'sort') {
                changeCheckbox('.display', id, 1);
                if (id === "d0") {
                    viewState.setDisplay($parent.data('sort'), 'sort');
                }
            } else {
                if (typeof id === "string" && id.indexOf('d') !== -1) {
                    changeCheckbox('.display', id, 1);
                    viewState.setDisplay('1', (id === 'd1' ? 'desc' : 'showtags'));
                    // Change sort when descending has been clicked.
                    if (id === 'd1') {
                        changeColumnSort();
                    }
                } else {
                    changeCheckbox('.tag-group', id, 1);
                    viewState.setTag(id.toString(), 'add');
                }
            }
        };

        /**
         * Process the column sort.
         *
         * Use a hidden checkbox to trigger the sort. Change the data of the hidden checkbox to the selected option.
         * With the hidden checkbox the central checkbox event system can be used to handle the sorting.
         */
        var sortSelectHandler = function () {
            var $selected = $selectsort.find("option:selected").eq(0),
                $relatedCheckboxLabel = $selectsort.siblings('.hidden').eq(0),
                selecteditem = $selected.val().toLowerCase();

            $relatedCheckboxLabel.data('sort', selecteditem);

            viewState.setDisplay(selecteditem, 'sort');

            changeColumnSort();
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

            // If a user clicks on the same column change the sortorder.
            if (viewState.getDisplaySort() === sortwhat) {
                if (viewState.getDisplayDesc() === '0') {
                    viewState.setDisplay('1', 'desc');
                    changeCheckbox('.display', 'd1', 1);
                } else {
                    viewState.setDisplay('0', 'desc');
                    changeCheckbox('.display', 'd1', 0);
                }
            } else {
                viewState.setDisplay(sortwhat, 'sort');
            }

            changeColumnSort();
        };

        /**
         * Handle the click event on the tags top right.
         *
         * Remove the tag and change the state of the related element in the search tag area.
         */
        var selectedTagClickHandler = function () {
            var $ele = $(this),
                id = 0;

            // Change the state of the related element in the search area.
            if ($ele.data('group') === 'searchquery') {
                $coursesearchform.find('.search-query').val('');
                viewState.setText('');
            } else if ($ele.data('group') === 'date-from') {
                $dpfrom.datepicker('update', '');
                $("#date-from-eurodate").val('');
                viewState.setDate('0', 'date-from');
            } else if ($ele.data('group') === 'date-to') {
                $dpto.datepicker('update', '');
                $("#date-to-eurodate").val('');
                viewState.setDate('0', 'date-to');
            } else if ($ele.data('group') === 'sort') {
                $selectsort.val('name');
                viewState.setDisplay('name', 'sort');
                changeColumnSort();
            } else if ($ele.data('type') === 'display') {
                id = $ele.data('id');
                $searcharea
                    .find('label')
                    .filter('[data-id="' + id + '"]')
                    .find('input').prop('checked', false);
                if (id === 'd1') {
                    viewState.setDisplay('0', 'desc');
                    changeColumnSort();
                } else if (id === 'd2') {
                    viewState.setDisplay('0', 'showtags');
                }
            } else {
                id = $ele.data('id');
                $searcharea
                    .find('label')
                    .filter('[data-id="' + id + '"]')
                    .find('input').prop('checked', false);
                viewState.setTag(id, 'remove');
            }

            // Remove the top right tag.
            $ele.remove();

            // Start the related action.
            if ($ele.data('group') === 'sort') {
                // sortSelectHandler();
            } else if ($ele.data('type') === 'course') {
                // filterCourses();
            } else if ($ele.data('type') === 'display') {
                // sortedCourseDisplayUpdate();
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
                text;

            // Don't trigger form submit action for the page.
            e.preventDefault();

            text = $form.find('.search-query').val();
            viewState.setText(text);

            changeSearchtextTag();
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
        var sortedCourseDisplayUpdate = function () {
            var $list = null,
                $sortedlist = null,
                selectedDisplayTags = getSelectedDisplayTags(),
                what = 'name',
                $coltitle = null;

            // Check if the coursesSortArray has been prepared.
            if (!coursesSortArray.length) {
                var prop;

                for (prop in courses) {
                    if (courses.hasOwnProperty(prop)) {
                        coursesSortArray.push({
                            'id': courses[prop].id,
                            'sortorder': courses[prop].sortorder,
                            'sortdate': courses[prop].sortdate,
                            'availnumber': courses[prop].availnumber,
                            'deadline': courses[prop].deadline,
                            'municipality': (courses[prop].municipality) ?
                                courses[prop].municipality.toLowerCase() : '',
                            'location': (courses[prop].location) ?
                                courses[prop].location.toLowerCase() : ''
                        });
                    }
                }
            }

            // The sort function for the course sort, compare on the defined data attribute.
            var sortFkt = function (a, b) {
                if (sortascstate) {
                    return (b[col2sortfieldmap[what]] < a[col2sortfieldmap[what]]) ? 1 : -1;
                } else {
                    return (b[col2sortfieldmap[what]] > a[col2sortfieldmap[what]]) ? 1 : -1;
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

            coursesSortArray.sort(sortFkt);

            courseids = [];
            coursesSortArray.forEach(function (item) {
                courseids.push('c' + item.id);
            });

            filterCourses();
        };

        /**
         * Sort the displayed courses.
         *
         * Get the active sort criteria and sort the courses.
         */
        var sortedCourseDisplayUpdate_o = function () {
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
            log.debug('enter filterCourses');
            return new Promise(function (resolve, reject) {
                var selectedCourseTagsGrouped = getSelectedCourseTagsGrouped(),
                    searchText = viewState.getText(),
                    fromDate = viewState.getFromDate(),
                    fromDateInt = 0,
                    toDate = viewState.getToDate(),
                    toDateInt = 0,
                    hasTextSearch = (viewState.getText() !== ''),
                    filtered = [],
                    thecourse,
                    sortdateInt,
                    foundany = false;

                courseIDsFiltered = courseids.slice();

                // If no search criterion is set show all courses.
                if (!Object.keys(selectedCourseTagsGrouped).length && !hasTextSearch &&
                    fromDate === "0" && toDate === "0") {
                    resolve(courseIDsFiltered);
                    return;
                }

                // Check if the course dates match the chosen dates.
                if (fromDate !== "0" || toDate !== "0") {
                    console.log(fromDate, toDate);
                    fromDateInt = parseInt(fromDate.replace(/-/g, ''), 10);
                    toDateInt = parseInt(toDate.replace(/-/g, ''), 10);
                    filtered = courseIDsFiltered.filter(function (id) {
                        thecourse = courses[id];
                        sortdateInt = parseInt(thecourse.sortdate, 10);
                        if (fromDateInt && toDateInt) {
                            if (sortdateInt >= fromDateInt && sortdateInt <= toDateInt) {
                                return true;
                            }
                        } else if (fromDateInt) {
                            if (sortdateInt >= fromDateInt) {
                                return true;
                            }
                        } else if (toDateInt) {
                            if (sortdateInt <= toDateInt) {
                                return true;
                            }
                        } else {
                            return false;
                        }
                    });

                    if (filtered.length) {
                        foundany = true;
                        courseIDsFiltered = filtered.slice();
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

                    courseIDsFiltered = filtered.slice();
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
                    courseIDsFiltered = filtered.slice();
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

                resolve(courseIDsFiltered);
            });
        };

        /**
         * Sort the courses to be shown.
         */
        var sortFilteredCourses = function (courseIDsToShow) {
            log.debug('enter sortFilteredCourses');
            return new Promise(function (resolve, reject) {
                var courseIDsSorted = _
                    .chain(_.values(courses))
                    .filter(function (item) {
                        return courseIDsToShow.indexOf('c' + item['id']) !== -1;
                    })
                    .orderBy(col2sortfieldmap[viewState.getDisplaySort()], viewState.getDisplayDesc() === '0' ? 'asc' : 'desc')
                    .flatMap(function (item) {
                        return 'c' + item.id;
                    })
                    .value();

                resolve(courseIDsSorted);
            });
        };

        /**
         * Set the visibility of the courses in the cards and list view.
         *
         * @param {Array} courseIDsToShow The list of course ids to show
         */
        var showFilteredCourses = function (courseIDsToShow) {
            log.debug('enter showFilteredCourses');
            return new Promise(function (resolve, reject) {
                var context,
                    nextids;

                // Clear the course display.
                $coursecardsul.html('');
                if ($courselisttable) {
                    $courselisttable.children('tbody').html('');
                }

                // Check if there are courses to be shown.
                if (!courseIDsToShow.length || courseIDsToShow[0] === -1) {
                    cardcourseidsremaining = [];
                    listcourseidsremaining = [];
                    actualCourseCount = 0;
                    $actualCourseCount.text(actualCourseCount);
                } else {
                    cardcourseidsremaining = courseIDsToShow.slice();
                    listcourseidsremaining = courseIDsToShow.slice();
                    actualCourseCount = courseIDsToShow.length;
                    $actualCourseCount.text(actualCourseCount);
                    context = {'courses': []};

                    if (viewState.getView() === '0') {
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
                    } else if (viewState.getView() === '1') {
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
                }

                // Return the course ids.
                resolve(courseIDsToShow);
            });
        };

        /** -- check
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
        var getCoursedata = function () {
            log.debug('enter getCoursedata');
            return new Promise(function (resolve, reject) {
                if (nocourses) {
                    resolve(false);
                    return;
                }
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
                    var coursedata = JSON.parse(response.coursedata);

                    courses = coursedata.courses;

                    // Create an array with the courses from the »courses« object.
                    // This has the courseid as keys and needs to be converted into an array of objects.
                    courseids = Object.keys(courses);
                    courseIDsFiltered = courseids.slice();
                    cardcourseidsremaining = courseids.slice();
                    listcourseidsremaining = courseids.slice();

                    $resultarea.find('.alert-info').remove();

                    actualCourseCount = courseids.length;
                    $actualCourseCount.text(actualCourseCount);
                    $actualCourseCountInfo.show();

                    console.log('courses', courses);
                    console.log('courseids', courseids.slice());

                    resolve(true);
                });
            });
        };

        /**
         * Get the course data for the user viewable courses via ajax.
         */
        var getCoursedata_o = function () {
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

                // Prefetch the list template.
                templates.render('local_course_search/course_search_course_list', {});

                courses = coursedata.courses;

                // Create an array with the courses from the »courses« object.
                // This has the courseid as keys and needs to be converted into an array of objects.
                courseids = Object.keys(courses);
                cardcourseidsremaining = courseids.slice();
                listcourseidsremaining = courseids.slice();

                actualCourseCount = courseids.length;
                $actualCourseCount.text(actualCourseCount);
                $actualCourseCountInfo.show();

                console.log('courses', courses);
                console.log('courseids', courseids.slice());

                nextids = cardcourseidsremaining.splice(0, cardsfirst);
                renderDisplayArea({
                    'courses': nextids.map(function (k) {
                        return courses[k];
                    })
                });

                // And now get the course tags.
                getAllCourseTags();
            });
        };

        /**
         * Get the course data for the user viewable courses via ajax.
         */
        var get_changed_coursedata = function () {
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
                    context = {},
                    nextids = [];

                courses = coursedata.courses;

                // Create an array with the courses from the »courses« object.
                // This has the courseid as keys and needs to be converted into an array of objects.
                coursesSortArray = [];
                courseids = Object.keys(courses);
                cardcourseidsremaining = courseids.slice();
                listcourseidsremaining = courseids.slice();

                actualCourseCount = courseids.length;
                $actualCourseCount.text(actualCourseCount);
                $actualCourseCountInfo.show();

                console.log('changed courses', courses);
                console.log('changed courseids', courseids.slice());

                // View the cards and list the first set.
                // $navtabs.find('[href="#tabcards"]').trigger('click');
                cardsScrollEventHandlerOn(true);
                cardsAddNextItems(cardsfirst);
            });
        };

        /**
         * Save the user preselected tags via ajax.
         *
         * @param {Array} tagarray The array with the selected tag ids
         */
        var saveInterests = function (tagarray) {
            log.debug('enter saveInterests');
            return new Promise(function (resolve, reject) {
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

                    // Clear the course display.
                    $coursecardsul.html('');
                    if ($courselisttable) {
                        $courselisttable.children('tbody').html('');
                    }

                    $coursesearchform.find('.tag-group')
                        .not(':has(label.checkbox)')
                        .addClass('fieldset-hidden');

                    $resultarea.toggleClass('section-hidden');
                    $tagpreselectarea.toggleClass('section-hidden');

                    resolve(true);
                });
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
        var getAllCourseTags = function () {
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
         *
         * @param {string} lang The Moodle lang string
         */
        var activateDatePicker = function (lang) {
            // Bootstrap 2.x datepicker https://github.com/uxsolutions/bootstrap-datepicker/tree/1.5.
            require(['javascript/locales/bootstrap-datepicker.no', 'javascript/locales/bootstrap-datepicker.de'], function () {
                $dpfrom.datepicker({
                    language: lang,
                    format: "dd.mm.yyyy",
                    calendarWeeks: true,
                    todayHighlight: true,
                    clearBtn: true,
                    autoclose: true
                }).on("changeDate", function (e) {
                    dateChangeHandler(e);
                });
                $dpto.datepicker({
                    language: lang,
                    format: "dd.mm.yyyy",
                    calendarWeeks: true,
                    todayHighlight: true,
                    clearBtn: true,
                    autoclose: true
                }).on("changeDate", function (e) {
                    dateChangeHandler(e);
                });
            });
        };

        /**
         * Activate cards or list view.
         */
        var changeView = function () {
            var $showtagscheckbox = $coursesearchform.find('.display').find('[data-group="tags"]');
            $navtabs.find('.active').removeClass('active');

            if (viewState.getView() === "0") {
                log.debug('Show cards view');
                $showtagscheckbox.addClass('hidden');
                changeListViewState(0);
                changeCardsViewState(1);
            } else if (viewState.getView() === "1") {
                log.debug('Show list view');
                $showtagscheckbox.removeClass('hidden');
                changeCardsViewState(0);
                changeListViewState(1);
            }
        };

        /**
         * changeCardsViewState
         */
        var changeCardsViewState = function (state) {
            if (state) {
                $navtabs.find('a[href="#tabcards"]').parent('li').addClass('active');
                $cardsarea.addClass('active');
                cardsScrollEventHandlerOn(true);
            } else {
                $cardsarea.removeClass('active');
                cardsScrollEventHandlerOn(false);
            }
        };

        /**
         * changeListViewState
         */
        var changeListViewState = function (state) {
            if (state) {
                $navtabs.find('a[href="#tablist"]').parent('li').addClass('active');
                $listarea.addClass('active');
                listScrollEventHandlerOn(true);
            } else {
                $listarea.removeClass('active');
                listScrollEventHandlerOn(false);
            }
        };

        /**
         * Set or remove a tag in the tagarea.
         * identify: '[data-group="searchquery"]'
         *
         * @param {string} identify The identifier for an exsiting tag
         * @param {object} context The date for the template
         */
        var changeTagareaTag = function (identify, context) {
            // Remove the tag related to the identify string.
            if (identify !== '') {
                $tagarea.find('li').has(identify).remove();
            }
            // Add a tag with the given context.
            if (_.isObject(context)) {
                // Add the search string as a tag.
                templates
                    .render('local_course_search/course_search_selected_tags_item', context)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }
        };

        /**
         * Set or remove the search text tag in the tagarea.
         */
        var changeSearchtextTag = function () {
            var text = viewState.getText(),
                activityContext;

            if (text !== '') {
                activityContext = $.extend({}, activityContextObj, {
                    type: 'course',
                    group: 'searchquery',
                    name: text,
                    pretext: M.str.local_course_search.searchtext
                });
            }

            changeTagareaTag('[data-group="searchquery"]', activityContext);
        };

        /**
         * Change a checkbox state.
         *
         * @param {string} groupclass the group class
         * @param {int|string} tagid The tag id
         * @param {int} state The tag state 0|1
         */
        var changeCheckbox = function (groupclass, tagid, state) {
            var $parent = $coursesearchform
                    .find(groupclass)
                    .find('.checkbox[data-id="' + tagid + '"]'),
                identify = '',
                activityContext = null;

            if (state) {
                // Set checkbox to checked.
                $parent
                    .find('input')
                    .prop('checked', true);

                // Set the context for the selected tag item template
                // and render the tag item.
                activityContext = $.extend({}, activityContextObj, {
                    id: $parent.data('id'),
                    type: $parent.data('type'),
                    group: $parent.data('group'),
                    name: $parent.data('name')
                });

                // Prepare sort related data.
                if ($parent.data('group') === 'sort') {
                    activityContext.sort = $parent.data('sort');
                    activityContext.name = M.str.local_course_search.sortby + " " +
                        M.str.local_friadmin[sortstrmapping[activityContext.sort]];
                    identify = '[data-group="sort"]';
                }

                changeTagareaTag(identify, activityContext);
            } else {
                // Set checkbox to unchecked.
                $parent
                    .find('input')
                    .prop('checked', false);

                changeTagareaTag('[data-id="' + $parent.data('id') + '"]', activityContext);
            }
        };

        /**
         * Change the columns sort.
         */
        var changeColumnSort = function () {
            var activityContext;

            $selectsort.val(viewState.getDisplaySort());
            $selectsort.siblings('.hidden').eq(0).data('sort', viewState.getDisplaySort());

            // Set the CSS class to show the sort arrow.
            $resultarea.find('.course-list-col-titles').find('.sortasc').removeClass('sortasc');
            $resultarea.find('.course-list-col-titles').find('.sortdesc').removeClass('sortdesc');

            var $coltitle = $resultarea.find('.course-list-col-titles').find('[data-sort="' + viewState.getDisplaySort() + '"]');
            if (viewState.getDisplayDesc() === "0") {
                $coltitle.addClass('sortasc');
            } else {
                $coltitle.addClass('sortdesc');
            }

            // Set the context for the selected tag item template
            // and render the tag item.
            activityContext = $.extend({}, activityContextObj, {
                id: 'd0',
                type: 'display',
                group: 'sort',
                sort: viewState.getDisplaySort(),
                name: M.str.local_course_search.sortby + " " + M.str.local_friadmin[sortstrmapping[viewState.getDisplaySort()]]
            });

            changeTagareaTag('[data-id="' + activityContext.id + '"]', activityContext);
        };

        /**
         * Set or remove the date tag in the tagarea.
         *
         * @param {string} which ("from"|"to")
         */
        var changeDateTag = function (which) {
            var date = '0',
                text = '',
                dateUser = '',
                activityContext = null;

            if (which === 'from') {
                date = viewState.getFromDate();
            } else if (which === 'to') {
                date = viewState.getToDate();
            }

            if (date !== '0') {
                if (which === 'from') {
                    dateUser = $datefrom.siblings('input[type="hidden"]').eq(0).val();
                    text = $dpfrom.data('name');
                } else if (which === 'to') {
                    dateUser = $dateto.siblings('input[type="hidden"]').eq(0).val();
                    text = $dpto.data('name');
                }

                activityContext = $.extend({}, activityContextObj, {
                    id: 'date-' + which,
                    type: 'course',
                    group: 'date-' + which,
                    name: text,
                    posttext: " " + dateUser
                });

                changeTagareaTag('[data-id="date-' + which + '"]', activityContext);
            } else {
                changeTagareaTag('[data-id="date-' + which + '"]', null);
            }
        };

        var changeDisplayShowTags = function () {
            if (viewState.getDisplayShowtags() === '1') {
                $resultarea.find(".course-list").removeClass('tags-hidden');
            } else {
                $resultarea.find(".course-list").addClass('tags-hidden');
            }
        };

        /**
         * Sequence to work with the saved course data.
         *
         * filter -> sort -> show
         */
        var filterSortShowCoursesSequence = function () {
            filterCourses()
                .then(sortFilteredCourses)
                .then(showFilteredCourses);
        };

        /**
         * Sequence to get and display courses.
         *
         * get -> filter -> sort -> show
         */
        var getFilterSortShowCoursesSequence = function () {
            getCoursedata()
                .then(function (gotCourses) {
                    if (gotCourses) {
                        filterSortShowCoursesSequence();
                    }
                });
        };

        /**
         * Sequence to get and display courses.
         *
         * get -> get course tags | filter -> sort -> show
         */
        var initialGetCoursesAndCourseTagsSequence = function () {
            getCoursedata()
                .then(function (gotCourses) {
                    getAllCourseTags();

                    if (gotCourses) {
                        filterSortShowCoursesSequence();
                    }
                });
        };

        /**
         * Sequence to get and display courses.
         *
         * get -> filter -> sort -> show
         */
        var saveInterestsAndGetCoursesSequence = function (tagarray) {
            saveInterests(tagarray)
                .then(getCoursedata)
                .then(function (gotCourses) {
                    if (gotCourses) {
                        filterSortShowCoursesSequence();
                    }
                });
        };

        /**
         * Set the
         * Viewstate structure - groups separated by »|«, items separated by »,«.
         * '0 (cards) OR 1 (list)|filtertext(search text)|1,2,3 (selected tag list)|2017-10-12(from),0(to)|name(sortbystate),0(desc),0(showtags)'.
         * defaultviewstate = '0|||0,0|name,0,0'
         * viewstatearray = [
         *   "0", // cards|list
         *   "", // filtertext
         *   [], // tagidarray,
         *   ["2017-10-12", "0"], // date from, to
         *   ["name", "0", "0"] // display options: sort, desc, showtags
         * ];
         */
        var setViewWithOptions = function () {
            var activityContext,
                removedTags = [];

            // Activate cards or list view.
            changeView();

            // Set the filter text to the saved search text.
            if (viewState.getText() !== "") {
                var text = viewState.getText();

                log.debug('Set search text to: ' + text);

                $coursesearchfield.val(text);

                changeSearchtextTag();
            }

            // Set the listed tags to checked.
            log.debug('Check tags: ');
            log.debug(viewState.getTags());

            viewState.getTags().forEach(function (tagid) {
                if (tagid !== "") {
                    // Check if the tag might have been removed.
                    if ($coursesearchform.find('label').data('id') === tagid) {
                        changeCheckbox('.tag-group', tagid, 1);
                    } else {
                        removedTags.push(tagid);
                    }
                }
            });

            // If removed tags have been found then rmeove thme from the view state.
            if (removedTags.length) {
                viewState.removeTags(removedTags);
            }

            // Set from, to date.
            if (viewState.getFromDate() !== "0") {
                _.delay(function () {
                    $dpfrom.datepicker('update', viewState.getFromDate().split('-').reverse().join('.'));
                    changeDateTag('from');
                }, 500);
            }
            if (viewState.getToDate() !== "0") {
                _.delay(function () {
                    $dpto.datepicker('update', viewState.getToDate().split('-').reverse().join('.'));
                    changeDateTag('to');
                }, 500);
            }

            // Set the column sort.
            if (viewState.getDisplayDesc() === "1") {
                changeCheckbox('.display', 'd1', 1);
            }

            changeColumnSort();

            // Set show tags.
            if (viewState.getDisplayShowtags() === "1") {
                changeCheckbox('.display', 'd2', 1);
            }
            changeDisplayShowTags();
        };

        /**
         * Initialize function, called with the PHP requires->js_call_amd command.
         */
        return {
            init: function (lang) {
                log.debug('AMD module init with lang ' + lang);

                viewState.init();

                // Get the relevant DOM elements.
                $actualCourseCount = $("#actualCourseCount");
                $actualCourseCountInfo = $("#actualCourseCountInfo");
                $catalogarea = $('#catalog-area');
                userid = parseInt($catalogarea.data('userid'), 10);


                $coursesearchform = $('#course-search-form');
                $coursesearchfield = $coursesearchform.find('#course-search');
                $searcharea = $('#search-area');
                $formsearch = $searcharea.find('.form-search');
                $selectsort = $searcharea.find('#select-sort');
                $datefrom = $("#date-from");
                $dpfrom = $datefrom.parents('[data-group="date-from"]');
                $dateto = $("#date-to");
                $dpto = $dateto.parents('[data-group="date-to"]');
                $switchDisplayBtn = $searcharea.find('#switch-display');

                $resultarea = $('#result-area');
                $navtabs = $resultarea.find('.nav-tabs').eq(0);
                $cardsarea = $resultarea.find('#tabcards');
                $listarea = $resultarea.find('#tablist');
                $coursecardsul = $resultarea.find('#course-cards');
                $courselisttable = $resultarea.find('#course-list');

                $tagarea = $('#tag-area');
                $selectedCourseTags = $tagarea.find('.selected-tags');

                $tagpreselectarea = $('#tag-preselect-area');

                // Set the event handlers.
                $coursesearchform.on('submit', textSearchHandler);
                $searcharea.on('change', '[type="checkbox"]', checkboxChangeHandler);
                $searcharea.on('change', '#select-sort', sortSelectHandler);

                $resultarea.on('shown', 'a[data-toggle="tab"]', tabChangeHandler);
                $resultarea.on('click', 'th.coltitle', colTitleClickHandler);

                $tagarea.on('click', 'button', selectedTagClickHandler);

                $tagpreselectarea.on('change', '[type="checkbox"]', preselectedCheckboxChangeHandler);
                $tagpreselectarea.on('keyup', '#tagFilter', tagFilterTextHandler);

                $switchDisplayBtn.on('click', toggleTagPreselectPageHandler);

                // Init infinite scroll.
                cardsInfScroll = new InfiniteScroll($coursecardsul.get(0), {
                    path: 'page{{#}}', // hack
                    loadOnScroll: false, // disable loading
                    history: false,
                    onInit: function () {
                        log.debug('cardsInfScroll init');
                    }
                });

                // Init infinite scroll.
                listInfScroll = new InfiniteScroll($courselisttable.get(0), {
                    path: 'page{{#}}', // hack
                    loadOnScroll: false, // disable loading
                    history: false,
                    onInit: function () {
                        log.debug('listInfScroll init');
                    }
                });

                $('body').on('viewstate:change', function (e, type) {
                    log.debug('coursesearch:change', type);

                    if (type === 'view') {
                        // Do nothing here.
                    } else if (type === 'showtags') {
                        // Toggle the tag display.
                        changeDisplayShowTags();
                    } else if (type === 'sort' || type === 'desc') {
                        // Change the course sort.
                        sortFilteredCourses(courseIDsFiltered)
                            .then(showFilteredCourses);
                    } else {
                        // Filter, sort and display the courses.
                        filterSortShowCoursesSequence();
                    }
                });

                // Inititialize the datepicker plugin.
                activateDatePicker(lang);

                // Restore the view from the viewState.
                setViewWithOptions();

                // Fetch the course data and show the courses.
                initialGetCoursesAndCourseTagsSequence();
            }
        };
    }
);
