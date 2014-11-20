/**
 * Company Filter - Javascript
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field
 * @copyright       2014 eFaktor    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    11/11/2014
 * @author          eFaktor     (fbv)
 */

YUI().use('node', function(Y) {
    var refProfield = "#id_" + Y.one('#id_input_name').get('value');

    /* County           */
    var RecuperateCountyMunicipality;

    /* Municipality     */
    var ActivateMunicipality;
    var DeactivateMunicipality;


    RecuperateCountyMunicipality = function() {
        var countyRef;
        var index;
        var muniRef;
        var hiddenMuni = Y.one("#id_hidden_muni").get('value');

        if ( hiddenMuni != 0) {
            /* Get County   */
            index = hiddenMuni.indexOf('_');
            countyRef = hiddenMuni.substr(0,index);

            /* Select County        */
            Y.one("#id_sel_county").get("options").each( function() {
                if (this.get('value') == countyRef) {
                    this.set('selected',true);
                    this.setAttribute('selected');
                }
            });//county

            /* Select Municipality  */
            Y.one(refProfield).removeAttribute('disabled');
            Y.one(refProfield).get("options").each( function() {
                muniRef = this.get('value');

                if (muniRef != 0) {
                    this.set('selected',false);
                    this.removeAttribute('selected');

                    if (muniRef == hiddenMuni) {
                        this.set('selected',true);
                        this.setAttribute('selected');
                    }else {
                        if (muniRef.indexOf(countyRef + '_') == -1) {
                            this.set('selected',false);
                            this.removeAttribute('selected');
                            this.wrap('<Municipality_tag id="Municipality_tag"></Municipality_tag>');
                        }//if_different_parent
                    }
                }//if_muniRef
            });//profile

            Y.one("#id_hidden_muni").set('value',0);
        }//if_hidden_muni
    };//RecuperateCountyMunicipality

    /*****************/
    /* Municipality  */
    /*****************/

    /* Deactivate Municipality  */
    DeactivateMunicipality = function() {
        if (Y.one(refProfield)) {
            Y.one(refProfield).setAttribute('disabled');

            Y.one(refProfield).get("options").each( function() {
                this.set('selected',false);
                this.removeAttribute('selected');
                if (this.ancestor('Municipality_tag')) {
                    this.unwrap();
                    this.show();
                }//if_Municipality_Tag
            });
        }
    };//DeactivateMunicipality

    /* Activate Municipality    */
    ActivateMunicipality = function(county) {
        var muniRef;

        /* First Deactivate */
        DeactivateMunicipality();

        /* Activate */
        if (Y.one(refProfield)) {
            Y.one(refProfield).removeAttribute('disabled');
            Y.one(refProfield).get("options").each( function() {
                /* Get municipality ref  */
                muniRef = this.get('value');

                if (muniRef != 0) {
                    if (muniRef.indexOf(county) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<Municipality_tag id="Municipality_tag"></Municipality_tag>');
                    }//if_different_parent
                }//if_!=_0
            });
        }
    };

    /*********************/
    /* EVENTS TO CAPTURE */
    /*********************/

    /* County --> Municipality    */
    if (Y.one('#id_sel_county')) {
        RecuperateCountyMunicipality();

        Y.one('#id_sel_county').on('change', function (e) {
           var county;

            /* Get County   */
            county = this.get('value') + '_';

            /* Activate Municipality    */
            ActivateMunicipality(county);

            Y.one(refProfield).focus();
            window.onbeforeunload = null;
        });//county_change
    }//if_county

    window.onbeforeunload = null;
});