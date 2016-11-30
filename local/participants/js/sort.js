/**
 * Participants List - Javascript
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    12/07/2016
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    /* Sort By Firstname   */
    if (Y.one('#firstname')) {
        Y.one('#firstname').on('click',function (e) {
            fieldSort = Y.one('#firstname').get('name');
            sort      = Y.one('#firstname').get('value');

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
    }//if_firstname
    
    /* Sort by lastname */
    if (Y.one('#lastname')) {
        Y.one('#lastname').on('click',function (e) {
            fieldSort = Y.one('#lastname').get('name');
            sort      = Y.one('#lastname').get('value');

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
    }//if_firstname
});
