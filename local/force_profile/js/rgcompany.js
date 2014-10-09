/**
 * Force Profile Job Roles - Javascript
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014 eFaktor
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    /* COMPANIES    */
    Y.one('#id_comp_county').on('change', function (e) {
        var county      = Y.one('#id_comp_county').get('value');

        /* Clean List - Municipalities   */
        Y.one('#id_comp_munis').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Clean the list   - Job Roles */
        Y.one('#id_profile_field_rgcompany').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Show Municipalities connected with the county*/
        Y.one('#id_comp_munis_hidden').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                var id_county = opt.substr(0,2);
                if (id_county == county) {
                    Y.one('#id_comp_munis').appendChild(this);
                }
            }
        });

        Y.one("#id_comp_munis").focus();

        window.onbeforeunload = null;
    });

    /* COMPANIES - MUNIS    */
    Y.one('#id_comp_munis').on('change', function (e) {
        var muni = Y.one('#id_comp_munis').get('value');

        /* Clean the list   */
        Y.one('#id_profile_field_rgcompany').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Show Job Roles connected with Municipality   */
        Y.one('#id_comp_hidden').get('options').each(function(){
            var company = this.get('value');

            if (company != 0) {
                if (company.indexOf(muni + '_') != -1) {
                    Y.one('#id_profile_field_rgcompany').appendChild(this);
                    Y.one('#id_profile_field_rgcompany').removeAttribute('disabled');
                }
            }
        });

        window.onbeforeunload = null;
    });

    /* Save new Company */
    Y.one('#id_profile_field_rgcompany').on('change', function (e) {
        Y.one('#id_company_id').set('value',this.get('value'));
    });

    window.onbeforeunload = null;
});
