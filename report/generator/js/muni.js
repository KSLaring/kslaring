/**
 * Job Roles - Javascript
 *
 * Description
 *
 * @package         report
 * @subpackage      generator
 * @copyright       2014 eFaktor
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 */
YUI().use('node', function(Y) {
    Y.one('#id_county').on('change', function (e) {
        var county      = Y.one('#id_county').get('value');

        /* Clean List - Municipalities   */
        Y.one('#id_munis').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                this.remove();
            }
        });

        /* Show Municipalities connected with the county*/
        Y.one('#id_hidden_munis').get('options').each(function(){
            var opt = this.get('value');

            if (opt != 0) {
                var id_county = opt.substr(0,2);
                if (id_county == county) {
                    Y.one('#id_munis').appendChild(this);
                }
            }
        });

        Y.one("#id_munis").focus();
        window.onbeforeunload = null;
    });

    Y.one('#id_munis').on('change', function (e) {
        Y.one('#id_municipality_id').set('value',this.get('value'));
    });
});
