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
 * Friadmin - Category reports (Javascript)
 *
 * @package         local/friadmin
 * @subpackage      reports/js
 * @copyright       2012        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2017
 * @author          eFaktor     (nas)
 *
 */

M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace.
M.core_user.courses = [];

M.core_user.get_courses_by_category = function (name) {
    return this.courses[name] || null;
};

M.core_user.init_courses = function (Y, course, category) {

    var lst_courses = {
        querydelay: 0.5,

        // Category Selector!
        category: Y.one('#id_' + category),
        // Course Selector!
        course: Y.one('#id_' + course),

        timeoutid : null,

        iotransactions : {},

        init : function() {
            var cat = this.category.get('value');
            if(cat == 0) {
                Y.one('#id_course').setAttribute('disabled', 'disabled');
            } else {
                this.Activate_course();
                Y.one('#id_course').removeAttribute('disabled');
            }

            this.category.on('change', this.Activate_course, this);
        },

        Activate_course : function(e) {
            var cat = this.category.get('value');
            if(cat == 0) {
                Y.one('#id_course').setAttribute('disabled', 'disabled');
            } else {
                Y.one('#id_course').removeAttribute('disabled');
            }
            // Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(cat)}, this);
        },

        send_query : function(cat) {

            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/friadmin/reports/courses.php',
                {
                    method: 'POST',
                    data: 'category=' + cat + '&sesskey=' + M.cfg.sesskey,
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
            var dataCourses;
            var lstCourses;
            var index;
            var indexCourse;
            var info;

            if (Y.one('#id_course')) {
                Y.one('#id_course').all('option').each(function(option){
                    if (option.get('value') != 0) {
                        option.remove();
                    }
                });
            }

            for (index in data.results) {
                dataCourses = data.results[index];
                lstCourses = dataCourses.courses;
                for (indexCourse in lstCourses) {
                    info = lstCourses[indexCourse];
                    var option = Y.Node.create('<option value="' + info.id + '">' + info.name + '</option>');
                    this.course.append(option);
                }

                /* Mark selected    */
                this.course.set('selectedIndex',0);
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
    Y.augment(lst_courses, Y.EventTarget, null, null, {});
    // Initialise the user selector.
    lst_courses.init();
    // Store the user selector so that it can be retrieved.
    this.courses[name] = lst_courses;

    window.onbeforeunload = null;

    // Return the user selector.
    return lst_courses;

};