// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Friadmin - Category reports - (Javascript)
 *
 * @package         local/friadmin
 * @subpackage      reports/js
 * @copyright       2012        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    28/08/2017
 * @author          eFaktor     (fbv)
 *
 */

M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace.
M.core_user.report = [];

M.core_user.get_data_report = function (name) {
    return this.report[name] || null;
};


M.core_user.init_managercourse_report = function (Y, parent, category, course,depth) {
    var data_rpt = {
        querydelay: 0.5,

        // Parent category
        parentcat: Y.one('#id_' + parent),

        // Category Selector!
        category: Y.one('#id_' + category),

        // Course Selector!
        course: Y.one('#id_' + course),
        hcourse: Y.one('#id_hcourse'),

        // depth
        depth: Y.one('#id_' + depth),

        // clean button
        clean: Y.one('#id_submitbutton2'),

        timeoutid : null,

        iotransactions : {},

        init : function() {
            // Category change
            this.category.on('change', this.get_data_report, this);

            // Course change
            this.course.on('change',this.get_data_course,this);

            if (this.clean) {
                this.clean.on('click',this.clean_data,this);
            }

        },


        get_data_course : function (e) {
            // Get course selected
            var course = this.course.get('value');
            if (course.indexOf('#') != -1) {
                course = course.substr(course.indexOf('#') +1);
            }//if_else

            this.hcourse.set('value',course);
        },

        get_data_report : function(e) {
            // First get sub-categories
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_cat(false)}, this);

        },

        clean_data: function(e) {
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query_cat(true)}, this);
        },

        send_query_courses : function() {
            var cat;

            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            // Get category selected
            cat = Y.one('#id_parentcat').get('value');
            if (cat.indexOf('#') != -1) {
                cat = cat.substr(cat.indexOf('#') +1);
            }//if_else

            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/course_report/categoriescourses.php',
                {
                    method: 'POST',
                    data: 'parent=' + cat + '&type=cou' + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_response
                    },
                    context:this
                }
            );
            this.iotransactions[iotrans.id] = iotrans;
        },

        handle_response : function(requestid, response) {
            try {
                delete this.iotransactions[requestid];
                if (!Y.Object.isEmpty(this.iotransactions)) {
                    // More searches pending. Wait until they are all done.
                    return;
                }
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.category.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.category.addClass('error');
                return new M.core.exception(e);
            }
        },

        output_options : function(data) {
            var courses;
            var result;
            var index;
            var indexcourse;
            var selected;
            var marked = 0;

            // Keep course selected && remove old content
            selected = this.course.get('selectedIndex');
            this.course.all('option').each(function(option){
                option.remove();
            });

            // Load Courses
            for (index in data.results) {
                result      = data.results[index];
                courses     = result.mycourses;
                for (indexcourse in courses) {
                    var option = Y.Node.create('<option value="' + indexcourse + '">' + courses[indexcourse] + '</option>');
                    this.course.append(option);

                    // Mark selected
                    if (selected != 0) {
                        if (indexcourse == selected) {
                            this.course.set('selectedIndex',indexcourse);
                            marked = 1;
                        }
                    }else {
                        this.course.set('selectedIndex',0);
                    }
                }//for_courses
            }//for_results

            // Mark option --> Select one
            if (marked != 1) {
                this.course.set('selectedIndex',0);
            }
        },

        send_query_cat : function(toclean) {
            var parent;
            var depth;
            var node;
            var allspan;
            var allbr;

            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            // Remove error messages
            node = Y.one('#fitem_id_course_list');
            // clean span error
            allspan = node.all('span');
            allspan.each(function (snode) {
                snode.remove();
            });
            // clean br
            allbr = node.all('br');
            allbr.each(function (snode) {
                snode.remove();
            });

            if (toclean) {
                parent = 0;
                depth  = 1;
                this.parentcat.set('value',0);

                // Clean courses selector
                if (this.course) {
                    this.course.all('option').each(function(option){
                        if (option.get('value') != 0) {
                            option.remove();
                        }

                    });
                    this.course.set('selectedIndex',0);
                    this.depth.set('value',0);
                }//if_this_Course
            }else {
                // Get category selected
                parent = this.category.get('value');
                if (parent.indexOf('#') != -1) {
                    parent = parent.substr(parent.indexOf('#') +1);
                }//if_else

                depth  = parseInt(this.depth.get('value')) + 1;
            }

            var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/course_report/categoriescourses.php',
                {
                    method: 'POST',
                    data: 'parent=' + parent + '&depth=' + depth + '&type=cat' + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_response_category
                    },
                    context:this
                }
            );
            this.iotransactions[iotrans.id] = iotrans;
        },

        handle_response_category : function(requestid, response) {
            try {
                delete this.iotransactions[requestid];
                if (!Y.Object.isEmpty(this.iotransactions)) {
                    // More searches pending. Wait until they are all done.
                    return;
                }
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.category.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options_category(data);
            } catch (e) {
                this.category.addClass('error');
                return new M.core.exception(e);
            }
        },

        output_options_category : function(data) {
            var categories;
            var result;
            var index;
            var indexcat;
            var selected;
            var marked = 0;
            var parentname;

            // Keep category selected
            selected = this.category.get('selectedIndex');
            this.category.all('option').each(function(option){
                option.remove();
            });

            // Load Categories/Subcategories
            for (index in data.results) {
                result      = data.results[index];
                categories  = result.categories;

                for (indexcat in categories) {
                    var option = Y.Node.create('<option value="' + indexcat + '">' + categories[indexcat] + '</option>');
                    this.category.append(option);

                    // Mark selected
                    if (selected != 0) {
                        if (indexcat == selected) {
                            this.category.set('selectedIndex',indexcat);
                            marked = 1;
                        }
                    }else {
                        this.category.set('selectedIndex',0);
                    }

                }//for_categories


                // Add parent information to the form
                if (result.parentcat.id == 0) {
                    this.parentcat.set('value','');
                    Y.one('#id_parentcat').set('value',0);
                }else {
                    if (this.parentcat.get('value') == '') {
                        this.parentcat.set('value',result.parentcat.name);
                        Y.one('#id_parentcat').set('value',result.parentcat.id);
                    }else {
                        parentname = this.parentcat.get('value') + '/' + result.parentcat.name;
                        this.parentcat.set('value',parentname);
                        Y.one('#id_parentcat').set('value',result.parentcat.id);
                    }
                }
            }//for_results

            // Mark option --> Select one
            if (marked != 1) {
                this.category.set('selectedIndex',0);
            }

            // Get courses connected with
            if (this.course) {
                if (Y.one('#id_parentcat').get('value') != 0) {
                    this.send_query_courses();
                }
            }
        },

        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        }
    };

    // Augment the user selector with the EventTarget class so that we can use custom events.
    Y.augment(data_rpt, Y.EventTarget, null, null, {});
    // Initialise the user selector.
    data_rpt.init();
    // Store the user selector so that it can be retrieved.
    this.report[name] = data_rpt;

    window.onbeforeunload = null;

    // Return the user selector.
    return data_rpt;
};