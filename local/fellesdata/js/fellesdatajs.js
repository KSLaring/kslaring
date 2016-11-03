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
    if (Y.one('#id_type').get('value') == 'ge') {
        Y.one('#id_jr_no_generic_ge').set('checked',1);
        window.onbeforeunload = null;
    }else if (Y.one('#id_type').get('value') == 'no') {
        Y.one('#id_jr_generic_no').set('checked',1);
        window.onbeforeunload = null;
    }else {
        Y.one('#id_jr_no_generic_ge').set('checked',1);
        window.onbeforeunload = null;
    }

    /* Mapping Companies Option */
    Y.one('#id_jr_no_generic_ge').on('click', function (e) {
        Y.one('#id_jr_generic_no').set('checked',0);
        window.onbeforeunload = null;
    });

    Y.one('#id_jr_generic_no').on('click', function (e) {
        Y.one('#id_jr_no_generic_ge').set('checked',0);
        window.onbeforeunload = null;
    });


    window.onbeforeunload = null;

});