/**
 The registerattendance changecompletionstate JavaScript

 @namespace Moodle
 @module mod_registerattendance
 **/

/**
 The Friadmin changecompletionstate JavaScript

 @class Moodle.mod_registerattendance.changecompletionstate
 @uses node
 @uses selector-css3
 @constructor
 **/

M.mod_registerattendance = M.mod_registerattendance || {};

M.mod_registerattendance.changecompletionstate = {
    CSS: {},
    SELECTORS: {
        FILTER: '#mform-attendancelist-filter',
        TABLE: '#attendancelist',
        BUTTON: 'button',
        CHECKBOX: 'input[type=checkbox]',
        RADIO: 'input[type=radio]',
        FILTERSUBMIT: 'input[type=submit]',
        SHOWATTENDED: '#fgroup_id_showattendedarr',
        HAVEATTTENDED: '.haveatttended',
        ATTTENDED: '.attended',
        ENROLLED: '.enrolled',
        BULKREGISTER: '#bulkregister',
        REGISTERLISTED: '#registerlisted',
        UNREGISTERLISTED: '#unregisterlisted'
    },
    table: null,
    ajaxurl: M.cfg.wwwroot + '/mod/registerattendance/ajax/changecompletionstate.php',

    /**
     * Initialise changecompletionstate JavaScript
     *
     * @method init
     */
    init: function () {
        var showattended = null,
            bulkregister = null;

        Y.log('mod_registerattendance-changecompletionstate init');

        this.table = Y.one(this.SELECTORS.TABLE);
        if (this.table) {
            this.table.delegate('change', this.value_changed, this.SELECTORS.CHECKBOX, this);
        }

        showattended = Y.one(this.SELECTORS.SHOWATTENDED);
        if (showattended) {
            showattended.delegate('click', this.showattended_clicked, this.SELECTORS.RADIO, this);
        }

        bulkregister = Y.one(this.SELECTORS.BULKREGISTER);
        if (bulkregister) {
            bulkregister.delegate('click', this.bulkregister_clicked, this.SELECTORS.BUTTON, this);
        }
    },

    /**
     * React on value changes in the referenced select menus
     *
     * @method value_changed
     *
     * @param {Object} e
     */
    value_changed: function (e) {
        var userid = e.target.getData('userid'),
            cmid = e.target.getData('cmid'),
            checked = e.target.get('checked'),
            args = {};

        // Y.log('userid: ' + userid + '  cmid: ' + cmid + ' checked: ' + checked);

        if (userid && cmid) {
            args = {
                cmid: cmid,
                userid: userid,
                state: checked ? 1 : 0
            };
            this.performAjaxAction('changestate', args, this.value_changed_feedback, this);
        }
    },

    /**
     * Handle the returned data
     *
     * @param {Number} transactionid
     * @param {Object} response
     * @param {Object} args
     *
     * @method value_changed_feedback
     */
    value_changed_feedback: function (transactionid, response, args) {
        var outcome = this.checkAjaxResponse(transactionid, response, args);

        Y.log(outcome);

        if (!outcome.error) {
            this.value_changed_update_number(outcome.outcome.state, outcome.outcome.amount);
        }
    },

    /**
     * Update the number of attended users displayed at the top of the page.
     *
     * @param {Number} state The state of the checkbox after the change
     */
    value_changed_update_number: function (state, amount) {
        var ele = Y.one(this.SELECTORS.HAVEATTTENDED).one(this.SELECTORS.ATTTENDED),
            val,
            calculated;

        if (ele) {
            val = parseInt(ele.get('text'), 10);

            // If state = 1 then increase the number by 1 else decrease the number.
            if (state) {
                ele.set('text', val + amount);
            } else if (val > 0) {
                calculated = val - amount;
                if (calculated < 0) {
                    calculated = 0;
                }
                ele.set('text', calculated);
            }
        }
    },

    /**
     * Execute a submit when one of the showattended radio buttons has been selected.
     *
     * @method showattended_clicked
     */
    showattended_clicked: function () {
        Y.one(this.SELECTORS.FILTER).one(this.SELECTORS.FILTERSUBMIT).simulate('click');
    },

    /**
     * Bulk change the attended status according to the clicked button.
     *
     * @method bulkregister_clicked
     *
     * @param {Object} e
     */
    bulkregister_clicked: function (e) {
        var id = e.target.get('id'),
            checkboxes = null,
            cmid,
            checked,
            userids = [];

        if (this.table) {
            checkboxes = this.table.all(this.SELECTORS.CHECKBOX);
        }

        if (checkboxes) {
            cmid = parseInt(checkboxes.slice(0, 1).getData('cmid'), 10);

            if ('#' + id === this.SELECTORS.REGISTERLISTED) {
                checked = true;
                checkboxes.each(function (node) {
                    if (!node.get('checked')) {
                        node.set('checked', true);
                        userids.push(node.getData('userid'));
                    }
                });
            } else if ('#' + id === this.SELECTORS.UNREGISTERLISTED) {
                checked = false;
                checkboxes.each(function (node) {
                    if (node.get('checked')) {
                        node.set('checked', false);
                        userids.push(node.getData('userid'));
                    }
                });
            }

            args = {
                cmid: cmid,
                userids: userids,
                state: checked ? 1 : 0
            };
            this.performAjaxAction('bulkchangestate', args, this.value_changed_feedback, this);
        }
    },

    /**
     * Performs an AJAX action.
     *
     * @method performAjaxAction
     *
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
     *
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
            new M.core.exception(outcome.error).show();
        }
        if (outcome.outcome === false) {
            return false;
        }
        return outcome;
    }
};
