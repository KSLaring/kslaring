/**
 * Fellesdata Integration - Javascript
 *
 * @package         local/fellesdata
 * @subpackage      js
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/02/2016
 * @author          eFaktor     (fbv)
 *
 */


YUI().use('node', function(Y) {
    /* Mark Mapping Companies    */
    if (Y.one('#id_type').get('value') == 'co') {
        Y.one('#id_mapping_co_co').set('checked',1);
        window.onbeforeunload = null;
    }else if (Y.one('#id_type').get('value') == 'jr') {
        Y.one('#id_mapping_jr_jr').set('checked',1);
        window.onbeforeunload = null;
    }else {
        Y.one('#id_mapping_co_co').set('checked',1);
        window.onbeforeunload = null;
    }

    /* Mapping Companies Option */
    Y.one('#id_mapping_co_co').on('click', function (e) {
        Y.one('#id_mapping_jr_jr').set('checked',0);
        /* Deactivate Options Job Role    */
        Y.one('#id_jr_no_generic').set('disabled','disabled');
        Y.one('#id_jr_no_generic').set('checked',0);
        Y.one('#id_jr_generic').set('disabled','disabled');
        window.onbeforeunload = null;
    });


    /* Mapping Job Roles Options    */
    Y.one('#id_mapping_jr_jr').on('click', function (e) {
        Y.one('#id_mapping_co_co').set('checked',0);
        /* Activate Options Job Role    */
        Y.one('#id_jr_no_generic').set('disabled','');
        Y.one('#id_jr_generic').set('disabled','');
        Y.one('#id_jr_no_generic').set('checked',1);
        window.onbeforeunload = null;
    });

    /* Options Job Roles    */
    Y.one('#id_jr_generic').on('click', function (e) {
        Y.one('#id_jr_no_generic').set('checked',0);
        window.onbeforeunload = null;
    });

    Y.one('#id_jr_no_generic').on('click', function (e) {
        Y.one('#id_jr_generic').set('checked',0);
        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});