/*global require: false, define: false, M: true, console: false */
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates', 'local_playground/cookie', 'theme_bootstrapbase/bootstrap'],
    function ($, notification, log, ajax, templates, cookie) {
        "use strict";

        // Add the jQuery object globaly or debugging.
        window.$ = $;

        log.debug('AMD module loaded.');

        var sortbystate = 'name',
            sortascstate = true,
            showtagliststate = false,
            preselectedTagsAddedState = false,
            searcharearenderedstate = false,
            selectedCourseTags = [],
            courses = [],
            $searcharea = null,
            $coursesearchform = null,
            $resultarea = null,
            $tagpreselectarea = null,
            $formsearch = null,
            $tagarea = null,
            $selectedCourseTags = null,
            $switchDisplay = null,
            preselecttagsloaded = false,
            hideitemClass = 'hide-item',
            cookiename = 'preselectedtags',
            // The preselected tags are sotored in a cookie. The tag ids are sepaarated by commy,
            // the sections are separated by »|«.
            preselectedtags = '433,430,431,16,39,108,196,268,352|449,452,450,453|458,459|460,462,463',
            tagContextObj = {id: 0, type: null, group: null, name: null},
            activityContextObj = {id: 0, type: null, group: null, name: null, sort: null, pretext: null, posttext: null, remove: 0};

        var grepselectedCourseTags = function (data) {
            var groups = data.tags.groups,
                found;

            if (groups.length) {
                groups.forEach(function (val) {
                    found = $.grep(val.shown, function (item) {
                        return (item.checked === 1);
                    });

                    if (found.length) {
                        selectedCourseTags = $.merge(selectedCourseTags, found);
                    }
                });

                renderselectedCourseTags({selectedCourseTags: selectedCourseTags});
            }
        };

        var renderselectedCourseTags = function (context) {
            templates
                .render('local_playground/course_search_selected_tags', context)
                .done(function (html) {
                    $tagarea.append(html);
                    $selectedCourseTags = $tagarea.find('.selected-tags');
                });
        };

        var renderSearchArea = function (context) {
            templates
                .render('local_playground/course_search_region_search', context)
                .done(function (html) {
                    $formsearch.after(html);

                    searcharearenderedstate = true;

                    activateDatePicker();
                    addPreselectedTags();
                });
        };

        var renderDisplayArea = function (context) {
            templates
                .render('local_playground/course_search_result_area', context)
                .done(function (html) {
                    $resultarea.append(html);
                    updateCourseDisplay();
                });
        };

        var renderTagPreselectArea = function (context) {
            templates
                .render('local_playground/course_search_tag_preselect_area', context)
                .done(function (html) {
                    $tagpreselectarea.append(html);

                    preselecttagsloaded = true;

                    addPreselectedTags();
                });
        };

        var addPreselectedTags = function () {
            if (preselectedTagsAddedState) {
                return;
            }

            if (!preselecttagsloaded || !searcharearenderedstate) {
                return;
            }

            var tags = cookie.read(cookiename),
                tagarray = [],
                tagarrayextracted = [],
                $taggroups = null;

            // Create an array of arrays from the cookie data.
            if (tags !== null) {
                tagarray = tags.split('|');
                tagarray.forEach(function (ele) {
                    tagarrayextracted.push(ele.split(','));
                });

                console.log(tagarrayextracted);

                $taggroups = $searcharea.find('.tag-group');
                if ($taggroups.length) {
                    $taggroups.each(function (i) {
                        var $group = $(this),
                            $onetag = null,
                            tagcontext = {};

                        tagarrayextracted[i].forEach(function (ele) {
                            $onetag = $tagpreselectarea.find('[data-id="' + ele + '"]');
                            if ($onetag.length) {
                                $onetag.prop('checked', true);
                                tagcontext = $.extend({}, tagContextObj, {
                                    id: $onetag.data('id'),
                                    type: $onetag.data('type'),
                                    group: $onetag.data('group'),
                                    name: $onetag.data('name')
                                });

                                renderSearchTagItem($group, tagcontext);
                            }
                        });
                    });
                }
            }

            preselectedTagsAddedState = true;
        };


        var renderSearchTagItem = function ($where, context) {
            templates
                .render('local_playground/course_search_region_search_groups_item', context)
                .done(function (html) {
                    $where.append(html);
                });
        };

        var activateDatePicker = function () {
            // Bootstrap 2.x datepicker https://github.com/uxsolutions/bootstrap-datepicker/tree/1.5.
            require(['local_playground/Xloader'], function () {
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
            });
        };

        var dateChangeHandler = function (e) {
            var $ele = $(e.target),
                $parent = $ele.parents('.date'),
                dateEuro = e.format("yyyymmdd"),
                dateUser = e.format("dd.mm.yyyy"),
                $relatedTag = null,
                activityContext = $.extend({}, activityContextObj, {
                    type: $parent.data('type'),
                    group: $parent.data('group'),
                    name: $parent.data('name'),
                    posttext: " " + dateUser
                });
            $ele.siblings('input[type="hidden"]').eq(0).val(dateEuro);

            // Remove or add the related tag in the selected tag list depending on the checked status.
            // Remove an eventually exisiting tag.
            $relatedTag = $tagarea
                .find('button')
                .filter('[data-name="' + $parent.data('name') + '"]')
                .parent('li')
                .remove();
            if (dateUser === "") {
                activityContext.remove = 1;
            } else {
                templates
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            updateCourses();
            updateCourseDisplay();
        };

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
            } else if ($ele.parent().data('group') === 'sort') {
                // If the sort changed then remove the exisiting sort tag and add the new.
                $relatedTag = $tagarea
                    .find('button')
                    .filter('[data-group="sort"]')
                    .parent('li')
                    .remove();

                // Prepare sort related data.
                activityContext.sort = $parent.data('sort');
                activityContext.name = "Sort by " + activityContext.sort;
                templates
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            } else {
                templates
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            if ($parent.data('type') === 'course') {
                updateCourses();
            }
            updateCourseDisplay();
        };

        var preselectedCheckboxChangeHandler = function () {
            var $ele = $(this),
                checked = $ele.is(":checked"),
                $group = null,
                group = '',
                tagcontext = {};

            if (!checked) {
                $coursesearchform.find('[data-id="' + $ele.data('id') + '"]').remove();
            } else {
                group = $ele.data('group');

                // Hack to speed up. Add provider as a data property.
                if (group === 'municipality' || group === 'region') {
                    group = 'provider';
                }

                $group = $coursesearchform.find('.' + group);
                tagcontext = $.extend({}, tagContextObj, {
                    id: $ele.data('id'),
                    type: $ele.data('type'),
                    group: $ele.data('group'),
                    name: $ele.data('name')
                });

                console.log(group, $group, tagcontext);
                renderSearchTagItem($group, tagcontext);
            }
        };

        var sortSelectHandler = function () {
            var $ele = $(this),
                $selected = $ele.find("option:selected").eq(0),
                $relatedCheckboxLabel = $ele.siblings('.hidden').eq(0),
                selecteditem = '';

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

        var colTitleClickHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $target = $(e.target),
                $selectSort = $searcharea.find('#select-sort'),
                $selectSortOptions = $selectSort.find('option'),
                $relatedCheckboxLabel = $selectSort.siblings('.hidden').eq(0),
                sortwhat = '';

            sortwhat = $target.data('sort');

            if (sortbystate === sortwhat) {
                sortascstate = !sortascstate;
                $searcharea.find('[data-group="sortdesc"]').find('input').click();
            } else {
                sortbystate = sortwhat;
            }

            $selectSortOptions.find(':selected').prop('selected', false);
            $selectSortOptions.filter(function () {
                return ($(this).val() === sortwhat); // To select the related option
            }).prop('selected', true);

            $relatedCheckboxLabel.data('sort', sortwhat);
            $relatedCheckboxLabel
                .find('input')
                .trigger('change');
        };

        var selectedTagClickHandler = function () {
            var $ele = $(this);

            if ($ele.data('group') === 'searchquery') {
                $coursesearchform.find('.search-query').val('');
            } else if ($ele.data('group') === 'date-from') {
                $("#date-from").datepicker('update', '');
                $("#date-from-eurodate").val('');
            } else if ($ele.data('group') === 'date-to') {
                $("#date-to").datepicker('update', '');
                $("#date-to-eurodate").val('');
            } else {
                $searcharea
                    .find('label')
                    .filter('[data-name="' + $ele.data('name') + '"]')
                    .find('input').prop('checked', false);
            }

            $ele.remove();

            if ($ele.data('type') === 'course') {
                updateCourses();
            } else if ($ele.data('type') === 'display') {
                updateCourseDisplay();
            }
        };

        var handleTagFilterText = function (e) {
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
        };

        var handleTextSearch = function (e) {
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
                    .render('local_playground/course_search_selected_tags_item', activityContext)
                    .done(function (html) {
                        $selectedCourseTags.append(html);
                    });
            }

            updateCourses();
        };

        var getSelectedSearchText = function () {
            return $coursesearchform.find('#course-search').val().toLowerCase();
        };

        var getSelectedCourseTags = function () {
            var checkeditems,
                searchItems = [];

            checkeditems = $coursesearchform.find('label').has(':checked');
            checkeditems.each(function () {
                var $item = $(this);
                if ($item.data('type') === 'course') {
                    searchItems.push($item.data('group') + '-' + $item.data('name'));
                }
            });

            return searchItems;
        };

        var getSelectedFromToDates = function () {
            var dates = {
                    from: null,
                    to: null,
                    datesset: false
                },
                date;

            // Check and set the from date.
            date = $("#date-from-eurodate").val();
            if (date !== "") {
                dates.from = date;
                dates.datesset = true;
            }

            // Check and set the from date.
            date = $("#date-to-eurodate").val();
            if (date !== "") {
                dates.to = date;
                dates.datesset = true;
            }

            return dates;
        };

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

            var sortFkt = function (a, b) {
                if (sortascstate) {
                    return ($(b).data(what)) < ($(a).data(what)) ? 1 : -1;
                } else {
                    return ($(b).data(what)) > ($(a).data(what)) ? 1 : -1;
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
         *
         * Then walk the course nodes in the display area and set the display status.
         *
         */
        var updateCourses = function () {
            var selectedCourseTags = getSelectedCourseTags(),
                searchText = getSelectedSearchText(),
                fromtoDates = getSelectedFromToDates(), // array with the [from, to] dates
                hasTextSearch = (searchText !== ''),
                courseIDsToShow = [],
                expectedmatches,
                matchcounter,
                i,
                li,
                j,
                lj;

            // Loop through all courses and collect the course ids of the courses to be shown.
            // Show the courses only when all search criteria match. Therefore set expectedmatches
            // as the number of matches a course must have to be shown.
            // 1. get the number of selected course tags
            // 2. add 1 if text search is active
            // 3. add 1 if from/to date/s are set
            expectedmatches = hasTextSearch ? selectedCourseTags.length + 1 : selectedCourseTags.length;
            expectedmatches += fromtoDates.datesset ? 1 : 0;
            for (i = 0, li = courses.length; i < li; i++) {
                matchcounter = 0;
                // Check if the text index contains the search string.
                if (hasTextSearch) {
                    if (courses[i].textcollection.indexOf(searchText) !== -1) {
                        matchcounter++;
                    }
                }

                // Loop through all selected checkboxes.
                for (j = 0, lj = selectedCourseTags.length; j < lj; j++) {
                    // Check if the selected item is in the indexed tag collation.
                    if (courses[i].tagcollection.indexOf(selectedCourseTags[j]) !== -1) {
                        matchcounter++;
                    }
                }

                // Check if the course dates match the chosen dates.
                if (fromtoDates.from !== null && fromtoDates.to !== null) {
                    if (courses[i].sortdate >= fromtoDates.from && courses[i].sortdate <= fromtoDates.to) {
                        matchcounter++;
                    }
                } else if (fromtoDates.from !== null) {
                    if (courses[i].sortdate >= fromtoDates.from) {
                        matchcounter++;
                    }
                } else if (fromtoDates.to !== null) {
                    if (courses[i].sortdate <= fromtoDates.to) {
                        matchcounter++;
                    }
                }

                if (matchcounter === expectedmatches) {
                    courseIDsToShow.push(courses[i].id);
                }
            }

            // If tags are selected but no courses match then set the id list to -1.
            if (!courseIDsToShow.length && selectedCourseTags.length) {
                courseIDsToShow.push(-1);
            }

            showHideCourses(courseIDsToShow);
        };

        /**
         * Set the visibility of the courses in the cards and list view.
         *
         * @param courseIDsToShow The list of course ids to show
         */
        var showHideCourses = function (courseIDsToShow) {
            var $coursecards = $resultarea.find('.course-cards'),
                $courselist = $resultarea.find('.course-list');

            if (courseIDsToShow.length) {
                // If first item is -1 then hide all courses.
                if (courseIDsToShow[0] === -1) {
                    courses.forEach(function (item) {
                        $coursecards.find('#coursecarditem-' + item.id).addClass(hideitemClass);
                        $courselist.find('#courselistitem-' + item.id).addClass(hideitemClass);
                    });
                } else {
                    courses.forEach(function (item) {
                        if (courseIDsToShow.indexOf(item.id) !== -1) {
                            $coursecards.find('#coursecarditem-' + item.id).removeClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item.id).removeClass(hideitemClass);
                        } else {
                            $coursecards.find('#coursecarditem-' + item.id).addClass(hideitemClass);
                            $courselist.find('#courselistitem-' + item.id).addClass(hideitemClass);
                        }
                    });
                }
            } else {
                courses.forEach(function (item) {
                    $coursecards.find('#coursecarditem-' + item.id).removeClass(hideitemClass);
                    $courselist.find('#courselistitem-' + item.id).removeClass(hideitemClass);
                });
            }
        };

        var handleToggleTagPreselectPage = function () {
            var taglist = '',
                $taggroups = null,
                gl,
                tl;

            // Save the tag selection in the cookie.
            if (showtagliststate) {
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
                console.log(taglist);
                cookie.create(cookiename, taglist, 14);
            }

            showtagliststate = !showtagliststate;

            $resultarea.toggleClass('hidden');
            $tagpreselectarea.toggleClass('hidden');
        };

        /**
         * Collect all texts from the course title and summary and the tag names into one text string
         * to speed up the text and the tag filter.
         *
         * add
         * {array} tagcollection as [{group}-{name}]
         * {string} textcollection as tagnames + coursename + coursesummary
         *
         * @param {array} data
         * @returns {array} the extended data
         */
        var indexCourseData = function (data) {
            var taglist = [],
                tagcollection = [],
                courseids = [];

            // Collect all text.
            $.each(data, function (i, item) {
                taglist = [];
                tagcollection = [];

                courseids.push(item.id);

                // Collect all tag names.
                $.each(item.tags, function (i, tag) {
                    tagcollection.push(tag.group + '-' + tag.name);
                    taglist.push(tag.name);
                });

                // Collect the tag names and the course name and summary.
                item.tagcollection = tagcollection;
                item.textcollection = taglist.join(',');
                item.textcollection += ' ' + item.name + ' ' + item.summary;
                item.textcollection = item.textcollection.toLowerCase();
            });

            data.courseids = courseids;

            return data;
        };

        return {
            init: function () {
                log.debug('AMD module init.');

                if (cookie.read(cookiename) === null) {
                    cookie.create(cookiename, preselectedtags);
                }

                // Get the relevant DOM elements.
                $searcharea = $('#search-area');
                $coursesearchform = $('#course-search-form');
                $resultarea = $('#result-area');
                $tagpreselectarea = $('#tag-preselect-area');
                $tagarea = $('#tag-area');
                $formsearch = $searcharea.find('.form-search');
                $switchDisplay = $searcharea.find('#switch-display');

                // Get the search data.
                $.getJSON("./course_search.json", function (data) {
                    log.debug(data);

                    grepselectedCourseTags(data);
                    renderSearchArea(data);
                }).fail(notification.exception);

                // Get the course data.
                $.getJSON("./courses.json", function (data) {
                    log.debug(data);

                    courses = indexCourseData(data.courses);
                    data.baseurl = M.cfg.wwwroot;
                    renderDisplayArea(data);
                }).fail(notification.exception);

                // Get all available tags.
                $.getJSON("./tags.json", function (data) {
                    log.debug(data);

                    renderTagPreselectArea(data);
                }).fail(notification.exception);

                // Set the event handlers.
                $searcharea.on('change', '[type="checkbox"]', checkboxChangeHandler);
                $searcharea.on('change', 'select', sortSelectHandler);
                $resultarea.on('click', 'th.coltitle', colTitleClickHandler);
                $tagarea.on('click', 'button', selectedTagClickHandler);
                $tagpreselectarea.on('change', '[type="checkbox"]', preselectedCheckboxChangeHandler);
                $tagpreselectarea.on('keyup', '#tagFilter', handleTagFilterText);
                $coursesearchform.on('submit', handleTextSearch);
                $switchDisplay.on('click', handleToggleTagPreselectPage);
            }
        };
    }
);
