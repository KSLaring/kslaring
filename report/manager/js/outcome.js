/**
 * Outcomes Selector Job Roles - Javascript
 *
 * @package         report
 * @subpackage      manager/js
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    02/03/2015
 * @author          efaktor     (fbv)
 *
 * Description
 *  Search job roles filter
 */

YUI().use('node', function(Y) {
    /* Search Add Job Roles     */
    Y.one('#id_search_add_jobroles').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_role = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'ajobroles' tag    */
        Y.one('#id_ajobroles').get('options').each( function(index) {
            if (this.ancestor('ajobroles')) {
                this.unwrap();
                this.show();
            }//if_ajobroles_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_ajobroles").get("options").each( function() {
            var str_role = this.get('text').toLowerCase();

            if (str_role.indexOf('choose') == -1) {
                if (str_role.indexOf(find_role) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<ajobroles id="ajobroles_tag"></ajobroles>');
                }
            }
        });

        Y.one("#id_search_add_jobroles").focus();
        window.onbeforeunload = null;
    });

    /* Search Selected Job Roles  */
    Y.one('#id_search_sel_jobroles').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_role = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'sjobrole' tag    */
        Y.one('#id_sjobroles').get('options').each( function(index) {
            if (this.ancestor('sjobroles')) {
                this.unwrap();
                this.show();
            }//if_susers_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_sjobroles").get("options").each( function() {
            var str_role = this.get('text').toLowerCase();

            if (str_role.indexOf('choose') == -1) {
                if (str_role.indexOf(find_role) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<sjobroles id="sjobroles_tag"></sjobroles>');
                }
            }
        });

        Y.one("#id_search_sel_jobroles").focus();
        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});
