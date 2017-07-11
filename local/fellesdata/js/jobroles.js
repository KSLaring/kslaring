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
 * Fellesdata Integration - Javascript Jobroles
 *
 * @package         local/fellesdata
 * @subpackage      js
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/06/2016
 * @author          eFaktor     (fbv)
 *
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.fsjobroles = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_fs_jobroles = function (name) {
    return this.fsjobroles[name] || null;
};

M.core_user.init_fs_company = function (Y,name,sjobroles,ajobroles) {
    var fs_jobrole = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        selector : Y.one('#id_' + name),

        jrMapped: Y.one('#id_' + sjobroles),
        noMapped: Y.one('#id_' + ajobroles),

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
            /* Parent list */
            this.selector.on('change', this.loadSelector, this);

            // Hook up the event handler for when the selection changes.
            this.jrMapped.on('keyup', this.handle_selection_mapped_change, this);
            this.jrMapped.on('click', this.handle_selection_mapped_change, this);
            this.jrMapped.on('change', this.handle_selection_mapped_change, this);

            // Hook up the event handler for when the selection changes.
            this.noMapped.on('keyup', this.handle_selection_change, this);
            this.noMapped.on('click', this.handle_selection_change, this);
            this.noMapped.on('change', this.handle_selection_change, this);

            var parent  = this.selector.get('value');
            this.send_query(true,parent);
        },

        loadSelector : function(e) {
            var ksJR  = this.selector.get('value');

            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,ksJR)}, this);
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch,ksJR) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/mapping/fsjobrole.php', {
                method: 'POST',
                data: 'ks_jobrole=' + ksJR + '&sesskey=' + M.cfg.sesskey,
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
                    this.selector.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.selector.addClass('error');
                return new M.core.exception(e);
            }
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_options.
         * @param {object} data the list of users to populate the list box with.
         */
        output_options : function(data) {
            var dataSelector;
            var selMapped;
            var indexMapped;
            var selNoMapped;
            var indexNoMapped;
            var index;
            var infoJR;
            var selectedMapped     = {};
            var selectedNoNoMapped   = {};

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

                /* Job Roles Mapped */
                selMapped  = dataSelector.mapped;
                /* Clean    */
                this.jrMapped.all('option').each(function(option){
                    if (option.get('selected') ||option.get('value') == 0) {
                        selectedMapped[option.get('value')] = option.get('value');
                    }
                    option.remove();
                });
                /* Add the new jobroles    */
                for (indexMapped in selMapped) {
                    infoJR = selMapped[indexMapped];

                    var option = Y.Node.create('<option value="' + infoJR.id + '">' + infoJR.name + '</option>');

                    this.jrMapped.append(option);
                }//for_companies
                /* Mark selected    */
                this.jrMapped.get("options").each( function() {
                    if (selectedMapped[this.get('value')]) {
                        this.setAttribute('selected','selected');
                    }
                });


                /***********************/
                /* Job Roles no mapped */
                selNoMapped = dataSelector.nomapped;
                /* Clean */
                if (this.noMapped) {
                    this.noMapped.all('option').each(function(option){
                        if (option.get('selected') ||option.get('value') == 0) {
                            selectedNoNoMapped[option.get('value')] = option.get('value');
                        }
                        option.remove();
                    });
                    /* Add the new companies    */
                    for (indexNoMapped in selNoMapped) {
                        infoJR = selNoMapped[indexNoMapped];

                        var option = Y.Node.create('<option value="' + infoJR.id + '">' + infoJR.name + '</option>');

                        this.noMapped.append(option);
                    }//for_companies
                    /* Mark selected    */
                    this.noMapped.get("options").each( function() {
                        if (selectedNoNoMapped[this.get('value')]) {
                            this.setAttribute('selected','selected');
                        }
                    });

                }
            }//for_level
        },

        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('fsjobrole_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        /**
         * Returns true if the selection is empty (nothing is selected)
         * @return Boolean check all the options and return whether any are selected.
         */
        is_selection_empty : function() {
            var selection = false;
            this.noMapped.all('option').each(function(){
                if (this.get('selected')) {
                    this.setAttribute('selected','selected');
                    selection = true;
                }
            });
            return !(selection);
        },

        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_mapped_change : function() {
            var isselectionempty = this.is_selection_mapped_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('fs_jobrole_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        is_selection_mapped_empty : function() {
            var selection = false;
            this.jrMapped.all('option').each(function(){
                if (this.get('selected')) {
                    this.setAttribute('selected','selected');
                    selection = true;
                }
            });
            return !(selection);
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
    Y.augment(fs_jobrole, Y.EventTarget, null, null, {});
    // Initialise the user selector
    fs_jobrole.init();
    // Store the user selector so that it can be retrieved
    this.fsjobroles[name] = fs_jobrole;

    window.onbeforeunload = null;

    // Return the user selector
    return fs_jobrole;
};