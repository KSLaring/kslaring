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
M.core_user.get_fs_companies = function (name) {
    return this.fscompanies[name] || null;
};

M.core_user.init_fs_company = function (Y,name,level,scompanies,acompanies) {
    var fs_company = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        selector : Y.one('#id_' + name),

        mylevel: level,

        parentSelector: Y.one('#id_' + scompanies),
        noParentSelector: Y.one('#id_' + acompanies),

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
            this.parentSelector.on('keyup', this.handle_selection_parent_change, this);
            this.parentSelector.on('click', this.handle_selection_parent_change, this);
            this.parentSelector.on('change', this.handle_selection_parent_change, this);

            // Hook up the event handler for when the selection changes.
            this.noParentSelector.on('keyup', this.handle_selection_change, this);
            this.noParentSelector.on('click', this.handle_selection_change, this);
            this.noParentSelector.on('change', this.handle_selection_change, this);

            var parent  = this.selector.get('value');
            this.send_query(true,parent,this.mylevel);

        },

        loadSelector : function(e) {
            var parent  = this.selector.get('value');

            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,this.mylevel)}, this);
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch,parent,level) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/fellesdata/mapping/fscompany.php', {
                method: 'POST',
                data: 'parent=' + parent + '&level' + '=' + this.mylevel + '&sesskey=' + M.cfg.sesskey,
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
            var withParent;
            var indexParent;
            var withoutParent;
            var indexNoParent;
            var index;
            var infoCompany;
            var selectedParents     = {};
            var selectedNoParents   = {};

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {

                /* Get Data */
                dataSelector = data.results[index];

               /* FS Companies - with Parent */
                withParent  = dataSelector.withparent;
                /* Clean    */
                this.parentSelector.all('option').each(function(option){
                    if (option.get('selected') ||option.get('value') == 0) {
                        selectedParents[option.get('value')] = option.get('value');
                    }
                    option.remove();
                });
                /* Add the new companies    */
                for (indexParent in withParent) {
                    infoCompany = withParent[indexParent];

                    var option = Y.Node.create('<option value="' + infoCompany.id + '">' + infoCompany.name + '</option>');

                    this.parentSelector.append(option);
                }//for_companies
                /* Mark selected    */
                this.parentSelector.get("options").each( function() {
                    if (selectedParents[this.get('value')]) {
                        this.setAttribute('selected','selected');
                    }
                });


                /*****************************/
                /* FS Comapnies - no Parents */
                withoutParent = dataSelector.noparent;
                /* Clean */
                if (this.noParentSelector) {
                    this.noParentSelector.all('option').each(function(option){
                        if (option.get('selected') ||option.get('value') == 0) {
                            selectedNoParents[option.get('value')] = option.get('value');
                        }
                        option.remove();
                    });
                    /* Add the new companies    */
                    for (indexNoParent in withoutParent) {
                        infoCompany = withoutParent[indexNoParent];

                        var option = Y.Node.create('<option value="' + infoCompany.id + '">' + infoCompany.name + '</option>');

                        this.noParentSelector.append(option);
                    }//for_companies
                    /* Mark selected    */
                    this.noParentSelector.get("options").each( function() {
                        if (selectedNoParents[this.get('value')]) {
                            this.setAttribute('selected','selected');
                        }
                    });

                }
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
        },


        /**
         * Handles when the selection has changed. If the selection has changed from
         * empty to not-empty, or vice versa, then fire the event handlers.
         */
        handle_selection_change : function() {
            var isselectionempty = this.is_selection_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('fscompanies_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        /**
         * Returns true if the selection is empty (nothing is selected)
         * @return Boolean check all the options and return whether any are selected.
         */
        is_selection_empty : function() {
            var selection = false;
            this.noParentSelector.all('option').each(function(){
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
        handle_selection_parent_change : function() {
            var isselectionempty = this.is_selection_parent_empty();
            if (isselectionempty !== this.selectionempty) {
                this.fire('fscompanies_selector:selectionchanged', isselectionempty);
            }
            this.selectionempty = isselectionempty;
        },

        is_selection_parent_empty : function() {
            var selection = false;
            this.parentSelector.all('option').each(function(){
                if (this.get('selected')) {
                    this.setAttribute('selected','selected');
                    selection = true;
                }
            });
            return !(selection);
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