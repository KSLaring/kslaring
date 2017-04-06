/*global define: false, M: true, console: false */
// define(['jquery', 'jqueryui', 'core/notification', 'core/log', 'core/ajax', 'core/templates', 'theme_bootstrapbase/bootstrap'],
//     function ($, $ui, notification, log, ajax, templates) {
define(['jquery', 'core/notification', 'core/log', 'core/ajax', 'core/templates', 'theme_bootstrapbase/bootstrap'],
    function ($, notification, log, ajax, templates) {
        "use strict";

        log.debug('AMD module loaded.');

        var selectedCourseTags = [],
            courses = [],
            $searcharea = null,
            $coursesearchform = null,
            $formsearch = null,
            $tagarea = null,
            $selectedCourseTags = null,
            $resultarea = null,
            hideitemClass = 'hide-item',
            activityContextObj = {type: null, group: null, name: null, pretext: null, posttext: null, remove: 0};

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

        var renderTags = function (context) {
            templates
                .render('local_playground/course_search_region_search', context)
                .done(function (html) {
                    $formsearch.after(html);
                    activateDatePicker();
                });
        };

        var renderResults = function (context) {
            templates
                .render('local_playground/course_search_results', context)
                .done(function (html) {
                    $resultarea.append(html);
                    updateCourseDisplay();
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

        var checkboxClickHandler = function () {
            var $ele = $(this),
                $parent = $ele.parent(),
                checked = $ele.is(":checked"),
                $relatedTag = null,
                activityContext = $.extend({}, activityContextObj, {
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
                dates.datesset = true
            }

            // Check and set the from date.
            date = $("#date-to-eurodate").val();
            if (date !== "") {
                dates.to = date;
                dates.datesset = true
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
                    displayItems.push($item.data('group'));
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
                asc = true,
                what = 'name';

            var sortFkt = function (a, b) {
                if (asc) {
                    return ($(b).data(what)) < ($(a).data(what)) ? 1 : -1;
                } else {
                    return ($(b).data(what)) > ($(a).data(what)) ? 1 : -1;
                }
            };

            if (selectedDisplayTags.indexOf('tags') !== -1) {
                $resultarea.find(".course-list").removeClass(' tags-hidden');
            } else {
                $resultarea.find(".course-list").addClass(' tags-hidden');
            }

            if (selectedDisplayTags.indexOf('sortdesc') !== -1) {
                asc = false;
            }

            if (selectedDisplayTags.indexOf('date') !== -1) {
                what = 'date';
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

                // Get the relevant DOM elements.
                $searcharea = $('#search-area');
                $coursesearchform = $('#course-search-form');
                $tagarea = $('#tag-area');
                $resultarea = $('#result-area');
                $formsearch = $searcharea.find('.form-search');

                // Get the search data.
                $.getJSON("./course_search.json", function (data) {
                    log.debug(data);

                    grepselectedCourseTags(data);
                    renderTags(data);
                }).fail(notification.exception);

                // Get the course data.
                $.getJSON("./courses.json", function (data) {
                    log.debug(data);

                    courses = indexCourseData(data.courses);
                    data.baseurl = M.cfg.wwwroot;
                    renderResults(data);
                }).fail(notification.exception);

                // Set the event handlers.
                $searcharea.on('click', '[type="checkbox"]', checkboxClickHandler);
                $tagarea.on('click', 'button', selectedTagClickHandler);
                $coursesearchform.on('submit', handleTextSearch);
            }
        };
    }
);
