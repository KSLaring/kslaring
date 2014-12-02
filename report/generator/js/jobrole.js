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
    /* County       */
    var RecuperateCounty;

    /* Level One    */
    var GetSelectedOne;
    var ActivateLevelOne;
    var DeactivateLevelOne;
    /* Level Two    */
    var GetSelectedTwo;
    var ActivateLevelTwo;
    var DeactivateLevelTwo;
    /* Level Three  */
    var ActivateLevelThree;
    var DeactivateLevelThree;
    var SaveLevelThree;

    /**************/
    /* County     */
    /**************/

    /* Recuperate the county and activate the next level    */
    RecuperateCounty = function() {
        var county;
        var levelOne;
        var levelTwo;
        var levelThree;
        var indexOne;
        var indexTwo;
        var companyRef;

        if (Y.one("#id_county").get('value') != 0) {
            /* Level One   */
            levelOne = Y.one("#id_level_one").get('value');
            indexOne = Y.one("#id_level_one").get('selectedIndex');
            /* Level Two   */
            levelTwo = Y.one("#id_level_two").get('value');
            indexTwo = Y.one("#id_level_two").get('selectedIndex');
            /* Level Three  */
            levelThree = Y.one('#id_hidden_level_three').get('value');

            /* County       */
            county = Y.one("#id_county").get('value') + '_';
            /* Activate Level One   */
            ActivateLevelOne(county);

            /* Activate Level One   */
            if (levelOne != 0) {
                /* Select the correct option    */
                Y.one("#id_level_one").get('options').item(indexOne).set('selected',true);
                Y.one("#id_level_one").get('options').item(indexOne).setAttribute('selected');
                /* Activate Level Two   */
                ActivateLevelTwo();
            }//if_levelOne

            /* Activate Level Two */
            if (levelTwo != 0) {
                /* Select the correct option    */
                Y.one("#id_level_two").get('options').item(indexTwo).set('selected',true);
                Y.one("#id_level_two").get('options').item(indexTwo).setAttribute('selected');
                /* Activate Level Two   */
                ActivateLevelThree();
            }//if_level_two

            /* Select Level Three  */
            if (levelThree != 0) {
                Y.one("#id_level_three").get("options").each( function() {
                    companyRef = this.get('value') + '#';
                    if (levelThree.indexOf(companyRef) != -1) {
                        this.set('selected',true);
                        this.setAttribute('selected');
                    }else {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                    }
                });
            }//if_levelThree
        }//if_county

        window.onbeforeunload = null;
    };//RecuperateCounty

    /**************/
    /* LEVEL ONE  */
    /**************/

    /* Get the selected value from Level One   */
    GetSelectedOne = function() {
        var levelOne;
        var index;
        var parentID;

        Y.one("#id_level_one").get("options").each( function() {
            if (this.get('selected') && this.get('value') != 0) {
                levelOne = this.get('value');
                /* Get Parent Id    */
                index       = levelOne.indexOf("_");
                parentID    = 'P' + levelOne.substr(index+1) + '_';
            }else {
                this.set('selected',false);
                this.removeAttribute('selected');
            }///if_selected
        });

        return [parentID];
    };//GetSelectedOne

    /* Deactivate Level One */
    DeactivateLevelOne = function() {
        Y.one("#id_level_one").setAttribute('disabled');
        Y.one("#id_level_one").get("options").each( function() {
            if (this.ancestor('levelOne_tag')) {
                this.unwrap();
                this.show();
            }//if_levelOne_tag
            this.set('selected',false);
            this.removeAttribute('selected');
        });
    };//DeactivateLevelOne

    /* Activate Level   One */
    ActivateLevelOne = function(county) {
        var levelOne;

        /* Deactivate Levels   */
        DeactivateLevelThree();
        DeactivateLevelTwo();
        DeactivateLevelOne();

        /* Activate Level */
        Y.one("#id_level_one").removeAttribute('disabled');
        Y.one("#id_level_one").get("options").each( function() {
            /* Get Company Ref  */
            levelOne = this.get('value');
            /* Get Company ID   */
            if (levelOne != 0) {
                if (levelOne.indexOf(county) == -1) {
                    this.set('selected',false);
                    this.removeAttribute('selected');
                    this.wrap('<levelOne_tag id="levelOne_tag"></levelOne_tag>');
                }//if_different_county
            }//if_levelOne
        });
    };//ActivateLevelOne

    /**************/
    /* LEVEL TWO  */
    /**************/

    /* Set the selected value from Level Two   */
    GetSelectedTwo = function() {
        var levelTwo;
        var index;
        var parentID;

        Y.one("#id_level_two").get("options").each( function() {
            if (this.get('selected') && this.get('value') != 0) {
                levelTwo = this.get('value');
                /* Get Parent Id    */
                index       = levelTwo.indexOf("_");
                parentID    = 'P' + levelTwo.substr(index+1) + '_';
            }else {
                this.set('selected',false);
                this.removeAttribute('selected');
            }///if_selected
        });

        return [parentID];
    };//GetSelectedTwo

    /* Deactivate Level Two */
    DeactivateLevelTwo = function() {
        Y.one("#id_level_two").setAttribute('disabled');
        Y.one("#id_level_two").get("options").each( function() {
            this.set('selected',false);
            this.removeAttribute('selected');
            if (this.ancestor('levelTwo_tag')) {
                this.unwrap();
                this.show();
            }//if_levelTwo_tag
        });
    };//DeactivateLevelTwo

    /* Activate Level   Two */
    ActivateLevelTwo = function() {
        var levelTwo;
        var levelOne;

        /* Get Level One    */
        levelOne = GetSelectedOne();

        /* Deactivate Levels    */
        DeactivateLevelThree();
        DeactivateLevelTwo();

        /* Activate Level       */
        if (levelOne != 0) {
            Y.one("#id_level_two").removeAttribute('disabled');
            Y.one("#id_level_two").get("options").each( function() {
                /* Get Company Ref  */
                levelTwo = this.get('value');

                /* Get Company ID   */
                if (levelTwo != 0) {
                    if (levelTwo.indexOf(levelOne) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<levelTwo_tag id="levelTwo_tag"></levelTwo_tag>');
                    }//if_different_parent
                }//if_levelTwo
            });
        }//if_levelOne
    };//ActivateLevelTwo

    /****************/
    /* LEVEL THREE  */
    /****************/

    /* Deactivate Level Three */
    DeactivateLevelThree = function() {
        Y.one("#id_level_three").setAttribute('disabled');
        Y.one("#id_level_three").removeAttribute('multiple');
        Y.one("#id_level_three").get("options").each( function() {
            this.set('selected',false);
            this.removeAttribute('selected');
            if (this.ancestor('levelThree_tag')) {
                this.unwrap();
                this.show();
            }//if_levelTwo_tag
        });
    };//DeactivateLevelThree

    /* Activate Level   Three */
    ActivateLevelThree = function() {
        var levelTwo;
        var levelThree;

        /* Get Level Two    */
        levelTwo = GetSelectedTwo();

        /* Deactivate Levels    */
        DeactivateLevelThree();

        /* Activate Level       */
        if (levelTwo != 0) {
            Y.one("#id_level_three").removeAttribute('disabled');
            Y.one("#id_level_three").setAttribute('multiple');
            Y.one("#id_level_three").get("options").each( function() {
                /* Get Company Ref  */
                levelThree = this.get('value');

                /* Get Company ID   */
                if (levelThree != 0) {
                    if (levelThree.indexOf(levelTwo) == -1) {
                        this.set('selected',false);
                        this.removeAttribute('selected');
                        this.wrap('<levelThree_tag id="levelThree_tag"></levelThree_tag>');
                    }//if_different_parent
                }//if_levelThree
            });
        }//if_level_two
    };//ActivateLevelThree

    SaveLevelThree = function() {
        /* Get the level three */
        Y.one('#id_hidden_level_three').set('value',0);
        Y.one("#id_level_three").get("options").each( function() {
            var levelThree;

            /* Save the Level Three */
            if (this.get('selected')) {
                if (Y.one('#id_hidden_level_three').get('value') != 0) {
                    levelThree = Y.one('#id_hidden_level_three').get('value') + ',' + this.get('value');
                }else {
                    levelThree = this.get('value');
                }//if_else_hidden_level_three

                /* Save the new Level Three selected    */
                Y.one('#id_hidden_level_three').set('value',levelThree);
            }//if_selected
        });
    };//SaveLevelThree

    /*********************/
    /* EVENTS TO CAPTURE */
    /*********************/

    /* County --> Activate Level One    */
    if (Y.one('#id_county')) {
        RecuperateCounty();

        Y.one('#id_county').on('change', function (e) {
            var county;

            /* Get County ID    */
            county = Y.one('#id_county').get('value') + '_';
            /* Activate Level One   */
            ActivateLevelOne(county);

            Y.one("#id_level_one").focus();
            window.onbeforeunload = null;
        });
    }//if_id_county

    /* Level One --> Activate Level Two */
    if (Y.one('#id_level_one')) {
        Y.one('#id_level_one').on('change', function (e) {
            /* Activate Level Two */
            ActivateLevelTwo();

            Y.one("#id_level_two").focus();
            window.onbeforeunload = null;
        });
    }//if_level_one

    /* Level Two --> Activate Level Three   */
    if (Y.one('#id_level_two')) {
        Y.one('#id_level_two').on('change', function (e) {
            /* Activate Level Three */
            ActivateLevelThree();

            Y.one("#id_level_three").focus();
            window.onbeforeunload = null;
        });
    }//if_level_two

    /* Save Level Three */
    if (Y.one('#id_level_three')) {
        Y.one('#id_level_three').on('change', function (e) {
            /* Save Level Three */
            SaveLevelThree();

            window.onbeforeunload = null;
        });
    }//if_level_three

    window.onbeforeunload = null;
});
