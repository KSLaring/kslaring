/**
 The Friadmin courselist JavaScript

 @namespace Moodle
 @module local_friadmin-courselist
 **/

/**
 The Friadmin courselist JavaScript

 @class Moodle.local_friadmin.courselist
 @uses node
 @uses selector-css3
 @constructor
 **/

M.local_friadmin = M.local_friadmin || {};

M.local_friadmin.courselist = {
    CSS: {},
    SELECTORS: {
        COURSEFILTER_FORM: '#mform-coursefilter',
        SEL_MUNICIPALITY: 'id_selmunicipality',
        SEL_SELECTOR: 'id_selsector',
        SEL_LOCATION: 'id_sellocation',
        CHANGE_ELEMENTS: '#id_selmunicipality, #id_selsector',
        SUBMIT_BTN: '#id_submitbutton'
    },
    form: null,
    ajaxurl: M.cfg.wwwroot + '/local/friadmin/ajax/courselist.php',

    /**
     * Initialise Courselist JavaScript
     *
     * @method init
     */
    init: function () {
        Y.log('local_friadmin-courselist init');

        this.form = Y.one(this.SELECTORS.COURSEFILTER_FORM);
        if (this.form) {
            this.form.delegate('valuechange', this.value_changed, this.SELECTORS.CHANGE_ELEMENTS, this);
        }
    },

    /**
     * React on value changes in the referenced select menus
     *
     * @param e
     * @method value_changed
     */
    value_changed: function (e) {
        // console.log('local_friadmin-courselist value changed');
        // console.log(e.target, e.target.getAttribute('id'));
        // console.log(e.newVal);

        var menuid = e.target.getAttribute('id'),
            municipalityid,
            args = {};

        if (menuid === this.SELECTORS.SEL_MUNICIPALITY) {
            args = {municipalityid: e.newVal};
            this.performAjaxAction('municipalitychange', args, this.municipaltiy_change, this);
        } else if (menuid === this.SELECTORS.SEL_SELECTOR) {
            municipalityid = Y.one('#' + this.SELECTORS.SEL_MUNICIPALITY).get('value');
            args = {municipalityid: municipalityid, sectorid: e.newVal};
            this.performAjaxAction('sectorchange', args, this.sector_change, this);
        }
    },

    /**
     * Handle the returned data
     *
     * @param {int} transactionid
     * @param {object} response
     * @param {object} args
     *
     * @method municipaltiy_change
     */
    municipaltiy_change: function (transactionid, response, args) {
        var outcome = this.checkAjaxResponse(transactionid, response, args);

        // console.log(outcome, outcome.outcome);

        this.change_menu(this.SELECTORS.SEL_SELECTOR, outcome.outcome.sector);
        this.change_menu(this.SELECTORS.SEL_LOCATION, outcome.outcome.location);
    },

    /**
     * Handle the returned data
     *
     * @param {int} transactionid
     * @param {object} response
     * @param {object} args
     *
     * @method sector_change
     */
    sector_change: function (transactionid, response, args) {
        var outcome = this.checkAjaxResponse(transactionid, response, args);

        // console.log(outcome, outcome.outcome);

        this.change_menu(this.SELECTORS.SEL_LOCATION, outcome.outcome.location);
    },

    /**
     * Change a select menu.
     * Remove all existing options except the first, then create new options from the data.
     *
     * @param {string} selector
     * @param {object} data
     */
    change_menu: function (selector, data) {
        var menu = Y.one('#' +  selector),
            count = 0;

        if (menu) {
            menu.all('option').each(
                function (node) {
                    if (count === 0) {
                        count++;
                    } else {
                        node.remove();
                    }
                }
            );

            Y.Object.each(data,
                function (value, key) {
                    Y.Node
                        .create('<option value="' + key + '">' + value + '</option>')
                        .appendTo(menu);
                });
        }
    },

    /**
     * Performs an AJAX action.
     *
     * @method performAjaxAction
     * @param {String} action The action to perform.
     * @param {Object} args The arguments to pass through with teh request.
     * @param {Function} callback The function to call when all is done.
     * @param {Object} context The object to use as the context for the callback.
     */
    performAjaxAction: function (action, args, callback, context) {
        var io = new Y.IO();
        args.action = action;
        args.ajax = '1';
        args.sesskey = M.cfg.sesskey;
        if (callback === null) {
            callback = function () {
                Y.log("'Action '" + action + "' completed", 'debug', 'moodle-course-management');
            };
        }
        io.send(this.ajaxurl, {
            method: 'POST',
            on: {
                complete: callback
            },
            context: context,
            data: build_querystring(args),
            'arguments': args
        });
    },

    /**
     * Checks and parses an AJAX response for an item.
     *
     * @method checkAjaxResponse
     * @protected
     * @param {Number} transactionid The transaction ID of the AJAX request (unique)
     * @param {Object} response The response from the AJAX request.
     * @param {Object} args The arguments given to the request.
     * @return {Object|Boolean}
     */
    checkAjaxResponse: function (transactionid, response, args) {
        if (response.status !== 200) {
            return false;
        }
        if (transactionid === null || args === null) {
            return false;
        }
        var outcome = Y.JSON.parse(response.responseText);
        if (outcome.error !== false) {
            new M.core.exception(outcome);
        }
        if (outcome.outcome === false) {
            return false;
        }
        return outcome;
    }
};
