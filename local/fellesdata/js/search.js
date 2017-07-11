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
 * Fellesdata Integration - Javascript Search
 *
 * @package         local/fellesdata
 * @subpackage      js
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/06/2016
 * @author          eFaktor     (fbv)
 *
 */
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.search_selectors = [];

M.core_user.get_search_selector = function (name) {
    return this.search_selectors[name] || null;
};

M.core_user.init_search_selector = function (Y, name, hash, level, lastsearch) {
    var search_selector = {
        /** This id/name used for this control in the HTML. */
        name : name,
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /** The input element that contains the search term. */
        searchfield : Y.one('#' + name + '_searchtext'),
        /** The select element that contains the list of users. */
        listbox : Y.one('#id_' + name),
        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},
        /** The last string that we searched for, so we can avoid unnecessary repeat searches. */
        lastsearch : lastsearch,
        /** Whether any options where selected last time we checked. Used by
         *  handle_selection_change to track when this status changes. */
        selectionempty : true,
        /* Level */
        hierarchy : level,
        /* Company */
        ks_parent : Y.one('#id_ks_parent'),

        /**
         * Initialises the user manager object
         * @constructor
         */
        init : function() {
            // Hook up the event handler for when the search text changes.
            this.searchfield.on('keyup', this.handle_keyup, this);

            // Hook up the event handler for when the selection changes.
            this.listbox.on('keyup', this.handle_selection_change, this);
            this.listbox.on('click', this.handle_selection_change, this);
            this.listbox.on('change', this.handle_selection_change, this);

            // Define our custom event.
            this.selectionempty = this.is_selection_empty();

            this.send_query(true);
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
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('search_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {

            // Cancel any pending timeout.
            this.cancel_timeout();

            var value   = this.get_search_text();

            this.searchfield.set('class', '');
            if (this.lastsearch == value && !forceresearch) {
                return;
            }

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var parent = this.ks_parent.get('value');
            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/mapping/search.php', {
                method: 'POST',
                data: 'level=' + this.hierarchy + '&parent=' + parent + '&search=' + value + '&selectorid=' + hash + '&sesskey=' +M.cfg.sesskey,
                on: {
                    complete: this.handle_response
                },
                context:this
            });
            this.iotransactions[iotrans.id] = iotrans;

            this.lastsearch = value;
            this.listbox.setStyle('background','url(' + M.util.image_url('i/loading', 'moodle') + ') no-repeat center center');
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
                this.listbox.setStyle('background','');
                var data = Y.JSON.parse(response.responseText);
                if (data.error) {
                    this.searchfield.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            }catch (e) {
                this.listbox.setStyle('background','');
                this.searchfield.addClass('error');
                return new M.core.exception(e);
            }
        },

        /**
         * This method should do the same sort of thing as the PHP method
         * user_selector_base::output_options.
         * @param {object} data the list of users to populate the list box with.
         */
        output_options : function(data) {
            // Clear out the existing options, keeping any ones that are already selected.
            var selected_companies = {};
            var index;
            var indexCompany;
            var infoCompany;
            var dataSelector;
            var fsCompanies;

            /* Clean */
            this.listbox.all('option').each(function(option){
                if (option.get('selected') ||option.get('value') == 0) {
                    selected_companies[option.get('value')] = option.get('value');
                }
                option.remove();
            });

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

                /* FS Companies - with Parent */
                fsCompanies  = dataSelector.comp;

                /* Add the new companies    */
                for (indexCompany in fsCompanies) {
                    infoCompany = fsCompanies[indexCompany];

                    var option = Y.Node.create('<option value="' + infoCompany.id + '">' + infoCompany.name + '</option>');

                    this.listbox.append(option);
                }//for_companies
                /* Mark selected    */
                this.listbox.get("options").each( function() {
                    if (selected_companies[this.get('value')]) {
                        this.setAttribute('selected','selected');
                    }
                });
            }

            this.handle_selection_change();
        },
        /**
         * Replace
         * @param {string} str
         * @param {string} search The search term
         * @return string
         */
        insert_search_into_str : function(str, search) {
            return str.replace("%%SEARCHTERM%%", search);
        },

        /**
         * Cancel the search delay timeout, if there is one.
         */
        cancel_timeout : function() {
            if (this.timeoutid) {
                clearTimeout(this.timeoutid);
                this.timeoutid = null;
            }
        },
        /**
         * Gets the search text
         * @return String the value to search for, with leading and trailing whitespace trimmed.
         */
        get_search_text : function() {
            return this.searchfield.get('value').toString().replace(/^ +| +$/, '');
        },
        /**
         * Returns true if the selection is empty (nothing is selected)
         * @return Boolean check all the options and return whether any are selected.
         */
        is_selection_empty : function() {
            var selection = false;
            this.listbox.all('option').each(function(){
                if (this.get('selected')) {
                    this.setAttribute('selected','selected');
                    selection = true;
                }
            });
            return !(selection);
        },
        /**
         * @param {string} name The name of the option to retrieve
         * @return the value of one of the option checkboxes.
         */
        get_option : function(name) {
            var checkbox = Y.one('#userselector_' + name + 'id');
            if (checkbox) {
                return (checkbox.get('checked'));
            } else {
                return false;
            }
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(search_selector, Y.EventTarget, null, null, {});
    // Initialise the user selector
    search_selector.init();
    // Store the user selector so that it can be retrieved
    this.search_selectors[name] = search_selector;

    window.onbeforeunload = null;

    // Return the user selector
    return search_selector;
};