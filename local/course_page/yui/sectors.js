/**
 * Course Page - Java Script Locations / Sector
 *
 * @package         local
 * @subpackage      course_page/YUI
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    21/03/2016
 * @author          eFaktor     (fbv)
 */

// Define the core_user namespace if it has not already been defined
M.core_coursepage = M.core_coursepage || {};
// Define a user selectors array for against the cure_user namespace
M.core_coursepage.sectors = [];

/**
 * Retrieves an instantiated user selector or null if there isn't one by the requested name
 * @param {string} name The name of the selector to retrieve
 * @return bool
 */
M.core_coursepage.get_sectors = function (name) {
    return this.sectors[name] || null;
};

M.core_coursepage.init_sectors = function (Y,location,sector) {
    var sectors = {
        /** Number of seconds to delay before submitting a query request */
        querydelay : 0.5,
        /* Location Selector */
        selLocation : Y.one('#id_' + location),
        /* Sector Selector   */
        selSector : Y.one('#id_' + sector),

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
            /* Location Selector  */
            this.selLocation.on('change', this.ReloadSectors, this);

            this.selSector.on('change', this.Sectors, this);

        },

        Sectors: function(e) {
            var my_sector = 0;

            this.selSector.all('option').each(function(option){
                if (option.get('selected') && option.get('value') != 0) {
                    if (my_sector != 0) {
                        my_sector =  my_sector + ',' + option.get('value');
                    }else {
                        my_sector =  option.get('value');
                    }
                }
            });

            document.cookie = "sectors=" + my_sector;
        },

        ReloadSectors : function(e) {
            //  Trigger an ajax search after a delay.
            this.cancel_timeout();
            this.timeoutid  = Y.later(this.querydelay * 1000, e, function(obj){obj.send_query(true)}, this);
        },

        /**
         * Fires off the ajax search request.
         */
        send_query : function(forceresearch) {
            var location;
            // Cancel any pending timeout.
            this.cancel_timeout();

            // Try to cancel existing transactions.
            Y.Object.each(this.iotransactions, function(trans) {
                trans.abort();
            });

            /* Get Location Selected    */
            location  = this.selLocation.get('value');

            var iotrans = Y.io(M.cfg.wwwroot + '/local/course_page/sectors.php', {
                method: 'POST',
                data: 'lo=' + location + '&sesskey=' + M.cfg.sesskey,
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
                    this.selLocation.addClass('error');
                    return new M.core.ajaxException(data);
                }
                this.output_options(data);
            } catch (e) {
                this.selLocation.addClass('error');
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
            var lstSectors;
            var index;
            var indexSector;
            var infoSector;
            var selected = {};

            // Clear out the existing options, keeping any ones that are already selected.
            for (index in data.results) {
                /* Get Data */
                dataSelector = data.results[index];

                /* Remove companies */
                this.selSector.all('option').each(function(option){
                    if (option.get('selected') ||option.get('value') == 0) {
                        selected[option.get('value')] = option.get('value');
                    }
                    option.remove();
                });
                document.cookie = "sectors=0";

                /* Add the new sectors    */
                lstSectors = dataSelector.items;

                for (indexSector in lstSectors) {
                    infoSector = lstSectors[indexSector];

                    var option = Y.Node.create('<option value="' + infoSector.id + '">' + infoSector.sector + '</option>');

                    this.selSector.append(option);
                }//for_companies

                /* Mark selected    */
                this.selSector.get("options").each( function() {
                    if (selected[this.get('value')]) {
                        this.setAttribute('selected','selected');
                    }
                });
            }//for_level
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
    Y.augment(sectors, Y.EventTarget, null, null, {});
    // Initialise the user selector
    sectors.init();
    // Store the user selector so that it can be retrieved
    this.sectors[name] = sectors;

    window.onbeforeunload = null;

    // Return the user selector
    return sectors;
};