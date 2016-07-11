/**
 * Participants List - Javascript
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_user = M.core_user || {};
// Define a user selectors array for against the cure_user namespace
M.core_user.participants_list = [];

M.core_user.get_participant = function (name) {
    return this.participants_list[name] || null;
};

M.core_user.init_participants_list = function (Y,course) {
    var participant = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,

        /* Attend checkboz  */
        attend_tick : Y.one('#id_tick'),

        /** Used to hold the timeout id of the timeout that waits before doing a search. */
        timeoutid : null,
        /** Stores any in-progress remote requests. */
        iotransactions : {},

        course_id: course,

        init : function() {
            this.attend_tick.on('click', this.TickParticipant, this);
        },

        TickParticipant : function(e) {
            var usersToTick = 0;

            Y.all('input:checked').each(function(checkbox) {
                if (usersToTick == 0) {
                    usersToTick = checkbox.get('value');
                }else {
                    usersToTick = usersToTick + '_#_' + checkbox.get('value');
                }//if_else
            });

            if (usersToTick == 0) {
                alert('There are any users to tick');
            }else {
                //  Trigger an ajax search after a delay.
                this.cancel_timeout();
                this.timeoutid = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(false,usersToTick)}, this);
            }
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch,usersToTick) {
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            var iotrans = Y.io(M.cfg.wwwroot + '/local/participants/attend.php',
                {
                    method: 'POST',
                    data: 'totick=' + usersToTick + '&course=' + this.course_id + '&sesskey=' + M.cfg.sesskey,
                    on: {
                        complete: this.handle_response,
                        //end: Y.one('#id_pepe').set('text','merada')//window.location.reload()
                    },
                    context:this
                }
            );
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
                    this.attend_tick.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.attend_tick.addClass('error');
                return new M.core.exception(e);
            }
        },

        output_options : function(data) {
            var index;
            var dataTicked;
            var ticked;
            var indexTicks;
            var info;
            var label;

            for (index in data.results) {
                /* Get Data */
                dataTicked  = data.results[index];


                /* Extract data */
                ticked      = dataTicked.ticks;
                for (indexTicks in ticked) {
                    info = ticked[indexTicks];

                    Y.one('#UE_' + info.id).set('text',info.attendDate);
                }//for_companies
            }
        },//output_options

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
    Y.augment(participant, Y.EventTarget, null, null, {});
    // Initialise the user selector
    participant.init();
    // Store the user selector so that it can be retrieved
    this.participants_list[name] = participant;

    window.onbeforeunload = null;

    // Return the user selector
    return participant;
};