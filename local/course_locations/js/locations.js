/**
 * Course Locations - Java Script
 *
 * @package         local
 * @subpackage      course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      28/04/2015
 * @author          eFaktor     (fbv)
 *
 */
YUI().use('node', function(Y) {
    /* Variables    */
    var parentCounty;
    var parentMunicipality;
    var parentSector;
    var parentActivate;
    var indexSelected;
    var fieldSort;
    var sort;

    /* County       */
    if (Y.one('#id_county')) {
        Y.one('#id_county').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_county").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentCounty = Y.one("#id_county").get('options').item(indexSelected).get('value');
            }else {
                parentCounty = 0;
            }

            document.cookie = "parentCounty"         + "=" + parentCounty;
            document.cookie = "parentMunicipality"   + "=0";
            document.cookie = "parentSector"         + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_county

    /* Municipality */
    if (Y.one('#id_municipality')) {
        Y.one('#id_municipality').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_municipality").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentMunicipality = Y.one("#id_municipality").get('options').item(indexSelected).get('value');
            }else {
                parentMunicipality = 0;
            }

            document.cookie = "parentMunicipality"   + "=" + parentMunicipality;
            document.cookie = "parentSector"         + "=0";

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_municipality

    /* Sector       */
    if (Y.one('#id_sector')) {
        Y.one('#id_sector').on('change', function (e) {
            /* Get the Selected */
            indexSelected   = this.get('selectedIndex');
            if (Y.one("#id_sector").get('options').item(indexSelected).get('value') != 0) {
                //Getting information of user.
                parentSector = Y.one("#id_sector").get('options').item(indexSelected).get('value');
            }else {
                parentSector = 0;
            }

            document.cookie = "parentSector"   + "=" + parentSector;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_municipality


    /* Activate /Deactivate */
    if (Y.one('#id_activate')) {
        Y.one('#id_activate').on('change', function (e) {

            if (Y.one("#id_activate").get('checked') == '1') {
                parentActivate = 1;
            }else {
                parentActivate = 0;
            }

            document.cookie = "parentActivate"       + "=" + parentActivate;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_activate

    /* Sort By Location Name    */
    if (Y.one('#location')) {
        Y.one('#location').on('click',function (e) {
            fieldSort = Y.one('#location').get('name');
            sort      = Y.one('#location').get('value');

            if (e.currentTarget.get('value') == 'ASC') {
                e.currentTarget.set('value','DESC');
            }else {
                e.currentTarget.set('value','ASC');
            }

            document.cookie = "dir"     + "=" + e.currentTarget.get('value');
            document.cookie = "field"   + "=" + fieldSort;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_id_location_name

    /* Sort By Muni     */
    if (Y.one('#muni')) {
        Y.one('#muni').on('click',function (e) {
            fieldSort = Y.one('#muni').get('name');
            sort      = Y.one('#muni').get('value');

            if (e.currentTarget.get('value') == 'ASC') {
                e.currentTarget.set('value','DESC');
            }else {
                e.currentTarget.set('value','ASC');
            }

            document.cookie = "dir"     + "=" + e.currentTarget.get('value');
            document.cookie = "field"   + "=" + fieldSort;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_id_muni_name

    /* Sort By Address     */
    if (Y.one('#address')) {
        Y.one('#address').on('click',function (e) {
            fieldSort = Y.one('#address').get('name');
            sort      = Y.one('#address').get('value');

            if (e.currentTarget.get('value') == 'ASC') {
                e.currentTarget.set('value','DESC');
            }else {
                e.currentTarget.set('value','ASC');
            }

            document.cookie = "dir"     + "=" + e.currentTarget.get('value');
            document.cookie = "field"   + "=" + fieldSort;

            window.onbeforeunload = null;
            window.location.reload();
        });
    }//if_id_address_name

    window.onbeforeunload = null;
});