/**
 * Municipalities / Counties - Javascript
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
    /* Counties */
    var RecuperateCounty;

    /* Municipalities   */
    var GetNameMunicipality;
    var ActivateMunicipality;
    var DeactivateMunicipality;


    /**************/
    /* County     */
    /**************/

    /* Recuperate the county and activate the next level    */
    RecuperateCounty = function() {
        var county;
        var muniHidden;
        var muniRef;
        var levelHidden;

        if (Y.one("#id_county").get('value') != 0) {
            /* County       */
            county = Y.one("#id_county").get('value') + '_';
            /* Activate Municipality   */
            ActivateMunicipality(county);
            /* Municipality */
            muniHidden  = Y.one('#id_hidden_munis').get('value');
            /* Select Municipality */
            if (muniHidden != 0) {
                muniRef = county + muniHidden;
                Y.one("#id_munis").get("options").each( function() {
                    if (this.get('value') == muniRef) {
                        this.set('selected',true);
                        this.setAttribute('selected');
                    }else {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                    }
                });
                Y.one('#id_hidden_munis').set('value',0);
            }//if_levelThree

            /* Company Name */
            levelHidden = Y.one('#id_hidden_name').get('value');
            Y.one('#id_name').set('value',levelHidden);
        }//if_county

        window.onbeforeunload = null;
    };//RecuperateCounty


    /*****************/
    /* Municipality */
    /****************/

    /* Deactivate Municipality  */
    DeactivateMunicipality = function() {
        Y.one("#id_munis").setAttribute('disabled');
        Y.one("#id_munis").get("options").each( function() {
            if (this.ancestor('Munis_tag')) {
                this.unwrap();
                this.show();
            }//if_levelOne_tag
            this.set('selected',false);
            this.removeAttribute('selected');
        });

        if (!Y.one('#id_hidden_name')) {
            Y.one('#id_name').set('value','');
        }//if_hidden_name

    };//DeactivateMunicipality

    /* Activate Municipality    */
    ActivateMunicipality = function(county) {
        var muni;

        /* Deactivate Municipality   */
        DeactivateMunicipality();

        /* Activate Municipality */
        Y.one("#id_munis").removeAttribute('disabled');
        Y.one("#id_munis").get("options").each( function() {
            /* Get Municipality Ref  */
            muni = this.get('value');
            /* Get Company ID   */
            if (muni != 0) {
                if (muni.indexOf(county) == -1) {
                    this.set('selected',false);
                    this.removeAttribute('selected');
                    this.wrap('<Munis_tag id="Munis_tag"></Munis_tag>');
                }//if_different_county
            }//if_Municipality
        });
    };//ActivateMunicipality

    /* Get Name Municipality */
    GetNameMunicipality = function() {
        var muniSel = '';
        var levelHidden;

        /* Get Municipality selected    */
        Y.one("#id_munis").get("options").each( function() {
            if (this.get('selected') && this.get('value') != 0) {
                muniSel = this.get('text');
            }//if_selected_not_0
        });

        /* Save Name        */
        if (Y.one('#id_hidden_name')) {
            muniSel = Y.one('#id_hidden_name').get('value');
        }
        Y.one('#id_name').set('value',muniSel);


        window.onbeforeunload = null;
    };//GetNameMunicipality

    /*********************/
    /* EVENTS TO CAPTURE */
    /*********************/

    /* County --> Activate Municipality */
    if (Y.one('#id_county')) {
        RecuperateCounty();

        Y.one('#id_county').on('change', function (e) {
            var county;

            /* Get County ID    */
            county = Y.one('#id_county').get('value') + '_';
            /* Activate Municipality   */
            ActivateMunicipality(county);

            Y.one("#id_munis").focus();
            window.onbeforeunload = null;
        });
    }//if_id_county

    /* Get Name Municipality */
    if (Y.one('#id_munis')) {
        Y.one('#id_munis').on('change', function (e) {
            GetNameMunicipality();
        });
    }//if_munis
    window.onbeforeunload = null;
});
