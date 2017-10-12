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
 * Fellesdata Integration - Javascript
 *
 * @package         local/fellesdata
 * @subpackage      js
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/06/2016
 * @author          eFaktor     (fbv)
 *
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.fscompanies = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_fs_to_map = function (name) {
    return this.fscompanies[name] || null;
};

M.core_user.init_fs_company_to_map = function (Y,selectorlevel,hidelevel,selectorparent,hideparent) {
    var fs_company = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        selselector : Y.one('#id_' + selectorlevel),

        hlevel: Y.one('#id_' + hidelevel),

        selparent: Y.one('#id_' + selectorparent),

        hparent: Y.one('#id_' + hideparent),

        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},


        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            this.selselector.on('change', this.load_companies_by_parent, this);

            this.selparent.on('change',this.set_parent,this);

        },

        set_parent : function (e) {
            var parent = this.selparent.get('value');
            if (parent.indexOf('#') != -1) {
                parent = parent.substr(parent.indexOf('#') +1);
            }//if_else
            this.hparent.set('value',parent);
        },

        load_companies_by_parent : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false)}, this);
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {
            var level;

            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            // Get level parent
            level = this.selselector.get('value');
            this.hlevel.set('value',level);

            alert('level --> ' + level);
            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/mapping/fsparent.php', {
                method: 'POST',
                data: 'level' + '=' + level + '&sesskey=' + M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                },
                context:this
            });
            this.iotransactions[iotrans.id] = iotrans;
        },

        /**
         * Handle what happens when we get some data back from the search.
         * @param {int} requestid not used.
         * @param {object} response the list of users that was returned.
         */
        handle_response : function(requestid, response) {
            try {
                delete this.iotransactions[requestid];
                if (!Y.Object.isEmpty(this.iotransactions)) {
                    // More searches pending. Wait until they are all done.
                    return;
                }
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.selselector.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.selselector.addClass('error');
                return new M.core.exception(e);
            }
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_options.
         * @param {object} data the list of users to populate the list box with.
         */
        output_options : function(data) {
            var index;
            var lstparents;
            var indexparent;

            // Clean
            this.selparent.all('option').each(function(option){
                option.remove();
            });

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {
                var result = data.results[index];
                lstparents = result.parents;

                for (indexparent in lstparents) {
                    var option = Y.Node.create('<option value="' + indexparent + '">' + lstparents[indexparent] + '</option>');
                    this.selparent.append(option);
                }//for_courses

            }//for_level
        },

        /**
         * Key up hander for the search text box.
         * @param {Y.Event} e the keyup event.
         */
        handle_keyup : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false)}, this);

            // If enter was pressed, prevent a form submission from happening.
            if (e.keyCode == 13) {
                e.halt();
            }
        },

        /**
         * Cancel the search delay timeout, if there is one.
         */
        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(fs_company, Y.EventTarget, null, null, {});
    // Initialise the user selector
    fs_company.init();
    // Store the user selector so that it can be retrieved
    this.fscompanies[name] = fs_company;

    window.onbeforeunload = null;

    // Return the user selector
    return fs_company;
};