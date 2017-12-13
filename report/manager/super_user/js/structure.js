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
 * Report Competence Manager - Java Script - Company Structure Selector
 *
 * @package         report
 * @subpackage      manager/super_user/js
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/10/2015
 * @author          eFaktor     (fbv)
 */


// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.structure = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_user.get_structure = function (name) {
    return this.structure[name] || null;
};

M.core_user.init_structure = function (Y,name) {
var organization = {
    /** Number of seconds to delay before submitting a query request */
    querydelay : 0.5,
    /* Level Zero Selector   */
    levelZero : Y.one('#id_' + name + '0'),
    /* Level One Selector   */
    levelOne : Y.one('#id_' + name + '1'),
    /* Level Two Selector   */
    levelTwo : Y.one('#id_' + name + '2'),
    /* Level Three Selector */
    levelThree : Y.one('#id_' + name + '3'),

    listbox     : Y.one('#removeselect'),
    lastsearch  : Y.one('#removeselect_searchtext'),

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
        /* Level Zero  */
        this.levelZero.on('change', this.Activate_LevelOne, this);

        /* Level One    */
        this.levelOne.on('change', this.Activate_LevelTwo, this);

        /* Level Two    */
        this.levelTwo.on('change', this.Activate_LevelThree, this);

        /* Level Three  */
        this.levelThree.on('change', this.Reload_Search, this);

        if ((this.levelZero.get('value') != 0) || (this.levelOne.get('value') != 0)
            ||
            (this.levelTwo.get('value') != 0) || (this.levelThree.get('value') != 0)) {
            this.Reload_Search();
        }

    },

    Activate_LevelOne : function(e) {
        var parent  = this.levelZero.get('value');
        var level   = 1;
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Activate_LevelTwo : function(e) {
        var parent      = this.levelOne.get('value');
        var level       = 2;
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Activate_LevelThree : function(e) {
        var parent  = this.levelTwo.get('value');
        var level   = 3;
        //  Trigger an ajax search after a delay.
        this.cancel_timeout();
        this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,parent,level)}, this);
    },

    Reload_Search : function() {
        //  Trigger an ajax search after a delay.
        M.core_user.get_super_user_selector('removeselect').reload_super_users();
        M.core_user.get_super_user_selector('addselect').reload_super_users();
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

        var iotrans = Y.io(M.cfg.wwwroot + '/report/manager/super_user/structure.php', {
            method: 'POST',
            data: 'parent=' + parent + '&level' + '=' + level + '&sesskey=' + M.cfg.sesskey,
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
                this.levelZero.addClass('error');
                return new M.core.ajaxException(data);
            }
            this.output_options(data);
        } catch (e) {
            this.levelZero.addClass('error');
            return new M.core.exception(e);
        }
    },

    /**
     * This method should do the same sort of thing as the PHP method
     * user_selector_base::output_options.
     * @param {object} data the list of users to populate the list box with.
     */
    output_options : function(data) {
        var level;
        var dataSelector;
        var companies;
        var index;
        var indexCompany;
        var infoCompany;
        var selected = {};

        // Clear out the existing options, keeping any ones that are already selected.
        for (index in data.results) {

            /* Get Data */
            dataSelector = data.results[index];

            /* Get level to update  */
            level = dataSelector.name;

            /* To Clean */
            var toClean = dataSelector.clean;
            for (var indexClean in toClean) {
                var clean = toClean[indexClean];

                Y.one("#id_" + clean).all('option').each(function(option){
                    if (option.get('value') != 0) {
                        option.remove();
                    }else {
                        option.setAttribute('selected','selected');
                    }
                });
            }//for_clean

            /* Remove companies */
            Y.one("#id_" + level).all('option').each(function(option){
                if (option.get('selected') ||option.get('value') == 0) {
                    selected[option.get('value')] = option.get('value');
                }
                option.remove();
            });

            /* Add the new companies    */
            companies = dataSelector.items;
            for (indexCompany in companies) {
                infoCompany = companies[indexCompany];

                var option = Y.Node.create('<option value="' + infoCompany.id + '">' + infoCompany.name + '</option>');

                Y.one("#id_" + level).append(option);
            }//for_companies

            /* Mark selected    */
            Y.one("#id_" + level).get("options").each( function() {
                if (selected[this.get('value')]) {
                    this.setAttribute('selected','selected');
                }
            });
        }//for_level

        this.Reload_Search();
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
    Y.augment(organization, Y.EventTarget, null, null, {});
    // Initialise the user selector
    organization.init();
    // Store the user selector so that it can be retrieved
    this.structure[name] = organization;

    window.onbeforeunload = null;

    // Return the user selector
    return organization;
};

