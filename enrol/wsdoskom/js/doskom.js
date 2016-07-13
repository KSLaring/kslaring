/**
 * Single Sign On Enrolment Plugin - Javascript
 *
 * @package         enrol
 * @subpackage      wsdoskom/js
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    27/02/2015
 * @author          efaktor     (fbv)
 *
 * Description
 *  Search company filter
 */
YUI().use('node', function(Y) {
    /* Search Add Companies     */
    Y.one('#id_search_add_companies').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_company = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'ausers' tag    */
        Y.one('#id_acompanies').get('options').each( function(index) {
            if (this.ancestor('acompanies')) {
                this.unwrap();
                this.show();
            }//if_ausers_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_acompanies").get("options").each( function() {
            var str_user = this.get('text').toLowerCase();

            if (str_user.indexOf('choose') == -1) {
                if (str_user.indexOf(find_company) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<acompanies id="acompanies_tag"></acompanies>');
                }
            }
        });

        Y.one("#id_search_add_companies").focus();
        window.onbeforeunload = null;
    });

    /* Search Selected Companies  */
    Y.one('#id_search_sel_companies').on('keyup', function (e) {
        lastKeyPressCode = e.charCode;
        var find_company = e.currentTarget.get('value').toLowerCase();
        /* 1. Remove the 'susers' tag    */
        Y.one('#id_scompanies').get('options').each( function(index) {
            if (this.ancestor('scompanies')) {
                this.unwrap();
                this.show();
            }//if_susers_tag

        });
        /* 3. New search                    */
        var first = false;
        Y.one("#id_scompanies").get("options").each( function() {
            var str_company = this.get('text').toLowerCase();

            if (str_company.indexOf('choose') == -1) {
                if (str_company.indexOf(find_company) == -1) {
                    this.removeAttribute('selected');
                    this.wrap('<scompanies id="scompanies_tag"></scompanies>');
                }
            }
        });

        Y.one("#id_search_sel_companies").focus();
        window.onbeforeunload = null;
    });

    window.onbeforeunload = null;
});