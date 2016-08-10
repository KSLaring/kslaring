/**
 * Classroom Frikomport format - Java Script - Add more dates
 *
 * @package         course/format
 * @subpackage      classroom_frikomport/YUI
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    10/08/2016
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_classroom = M.core_classroom || {};
// Define a user selectors array for against the cure_user namespace
M.core_classroom.times = [];


M.core_classroom.get_times = function (name) {
    return this.times[name] || null;
};

M.core_classroom.init_from_to = function (Y,name) {
    var from_to = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        /* Button */
        btnFromTo : Y.one('#id_' + name),

        /* From Date    */
        fromDay   : Y.one('#id_from_day'),
        fromMonth : Y.one('#id_from_month'),
        fromYear  : Y.one('#id_from_year'),

        /* To Date      */
        toDay   : Y.one('#id_to_day'),
        toMonth : Y.one('#id_to_month'),
        toYear  : Y.one('#id_to_year'),

        /* Time added   */
        timeTxt : Y.one('#id_time'),

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},

        /**
         * Initialises the user selector object
         * @constructor
         */
        init : function() {
            /* Location Selector  */
            this.btnFromTo.on('click', this.AddFromTo, this);

        },

        AddFromTo : function(e) {
            var timeSave = '';
            var iniDate;
            var endDate;


            /* Getting values   */
            /* Time Added */
            timeSave = this.timeTxt.get('value');

            /* From time    */
            iniDate = this.fromDay.get('value')     + '/' +
                      this.fromMonth.get('value')   + '/' +
                      this.fromYear.get('value');

            /* To time */
            endDate = this.toDay.get('value')     + '/' +
                      this.toMonth.get('value')   + '/' +
                      this.toYear.get('value');


            if (timeSave == '') {
                timeSave = iniDate + ' - ' + endDate;
            }else {
                timeSave = timeSave + '\n' + iniDate + ' - ' + endDate;
            }

            this.timeTxt.set('value',timeSave);
        }
    };

    // Augment the user selector with the EventTarget class so that we can use
    // custom events
    Y.augment(from_to, Y.EventTarget, null, null, {});
    // Initialise the user selector
    from_to.init();
    // Store the user selector so that it can be retrieved
    this.times[name] = from_to;

    window.onbeforeunload = null;

    // Return the user selector
    return from_to;
};