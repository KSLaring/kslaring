/**
 * Company Structure - Javascript
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
    if (Y.one('#id_other_company')) {
        Y.one('#id_other_company').on('change', function (e) {
            var linkSel = this.get('options').item(this.get('selectedIndex')).get('value');
            if (linkSel != 0) {
                Y.one('#id_name').setAttribute('disabled');
            }else {
                Y.one('#id_name').removeAttribute('disabled');
            }//if_else_linkSel

            window.onbeforeunload = null;
        });
    }//if_other_company

    window.onbeforeunload = null;
});
