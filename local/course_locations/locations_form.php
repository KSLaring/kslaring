<?php
/**
 * Course Locations - Forms
 *
 * @package         local
 * @subpackage      course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      28/04/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once($CFG->dirroot.'/lib/formslib.php');
$PAGE->requires->js('/local/course_locations/js/locations.js');

/**
 * Class locations_search_form
 *
 * @creationDate    28/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Form to search all available locations
 */
class locations_search_form extends moodleform {
    function definition (){
        /* Variables    */
        $levelZero      = null;
        $counties       = null;
        $municipalities = null;

        /* Form         */
        $form               = $this->_form;

        /* Hierarchy    */
        list($myCompetence) = $this->_customdata;

        /* Counties                 */
        $levelZero = implode(',',array_keys($myCompetence));
        $counties = CourseLocations::Get_Companies($levelZero);
        $form->addElement('select',COURSE_LOCATION_COUNTY,get_string('counties', 'local_course_locations'),$counties);
        if (isset($_COOKIE['parentCounty'])) {
            $form->setDefault(COURSE_LOCATION_COUNTY,$_COOKIE['parentCounty']);
        }else {
            $form->setDefault(COURSE_LOCATION_COUNTY,0);
        }//if_cookie
        $form->addRule(COURSE_LOCATION_COUNTY, 'required', 'required', 'nonzero', 'client');
        $form->addRule(COURSE_LOCATION_COUNTY, 'required', 'nonzero', null, 'client');

        /* Municipalities          */
        $municipalities = GetMunicipalities($myCompetence);
        $form->addElement('select',COURSE_LOCATION_MUNICIPALITY,get_string('municipality', 'local_course_locations'),$municipalities);
        if (isset($_COOKIE['parentMunicipality']) && ($_COOKIE['parentMunicipality'])) {
            $form->setDefault(COURSE_LOCATION_MUNICIPALITY ,$_COOKIE['parentMunicipality']);
        }else {
            $form->setDefault(COURSE_LOCATION_MUNICIPALITY ,0);
        }//if_cookie
        $form->disabledIf(COURSE_LOCATION_MUNICIPALITY ,COURSE_LOCATION_COUNTY,'eq',0);

        $form->addElement('checkbox', 'activate', get_string('activate', 'local_course_locations'));
        if (isset($_COOKIE['parentActivate'])) {
            $form->setDefault('activate',$_COOKIE['parentActivate']);
        }else {
            $form->setDefault('activate',1);
        }


        $this->add_action_buttons(true, get_string('search'));
    }//definition
}//locations_search_form

    /**
 * Class            add_location_form
 *
 * @creationDate    28/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Form to create a new location
 */
class add_location_form extends moodleform {
    function definition() {
        /* Variables    */
        $levelZero      = null;
        $counties       = null;
        $municipalities = null;

        /* Form         */
        $form               = $this->_form;

        /* Hierarchy    */
        list($myCompetence,$edit_options) = $this->_customdata;

        /* Locations    */
        $form->addElement('header', 'header_location', get_string('location', 'local_course_locations'));
        $form->setExpanded('header_location',true);
        /* Counties                 */
        $levelZero = implode(',',array_keys($myCompetence));
        $counties = CourseLocations::Get_Companies($levelZero);
        $form->addElement('select',COURSE_LOCATION_COUNTY,get_string('counties', 'local_course_locations'),$counties);
        if (isset($_COOKIE['parentCounty'])) {
            $form->setDefault(COURSE_LOCATION_COUNTY,$_COOKIE['parentCounty']);
        }else {
            $form->setDefault(COURSE_LOCATION_COUNTY,0);
        }//if_cookie

        $form->addRule(COURSE_LOCATION_COUNTY, 'required', 'required', 'nonzero', 'client');
        $form->addRule(COURSE_LOCATION_COUNTY, 'required', 'nonzero', null, 'client');

        /* Municipalities          */
        $municipalities = GetMunicipalities($myCompetence);
        $form->addElement('select',COURSE_LOCATION_MUNICIPALITY,get_string('municipality', 'local_course_locations'),$municipalities);
        if (isset($_COOKIE['parentMunicipality']) && ($_COOKIE['parentMunicipality'])) {
            $form->setDefault(COURSE_LOCATION_MUNICIPALITY ,$_COOKIE['parentMunicipality']);
        }else {
            $form->setDefault(COURSE_LOCATION_MUNICIPALITY ,0);
        }//if_cookie
        $form->disabledIf(COURSE_LOCATION_MUNICIPALITY ,COURSE_LOCATION_COUNTY,'eq',0);
        $form->addRule(COURSE_LOCATION_MUNICIPALITY, 'required', 'required', 'nonzero', 'client');
        $form->addRule(COURSE_LOCATION_MUNICIPALITY, 'required', 'nonzero', null, 'client');

        /* General  */
        $form->addElement('header', 'header_general', get_string('title_general', 'local_course_locations'));
        $form->setExpanded('header_general',true);
        /* Name                 */
        $form->addElement('text','name',get_string('location_name','local_course_locations'));
        $form->addRule('name', 'required', 'required', null, 'client');
        $form->setType('name',PARAM_TEXT);

        /* Description          */

        $form->addElement('editor','description_editor',get_string('location_desc','local_course_locations'),'style="width: 90%;" ',$edit_options);
        $form->setType('description_editor',PARAM_RAW);

        /* Url More information */
        $form->addElement('url','url_desc',get_string('location_url','local_course_locations'),array('size' => 25));
        $form->setType('url_desc',PARAM_URL);

        /* Floor                */
        $form->addElement('text','floor',get_string('location_floor','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('floor', 'required', 'required', null, 'client');
        $form->setType('floor',PARAM_TEXT);

        /* Room                 */
        $form->addElement('text','room',get_string('location_room','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('room', 'required', 'required', null, 'client');
        $form->setType('room',PARAM_TEXT);

        /* Seats                */
        $form->addElement('text','seats',get_string('location_seats','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('seats', 'required', 'required', null, 'client');
        $form->setType('seats',PARAM_INT);
        $form->setDefault('seats',0);

        /* Address              */
        /* Street       */
        $form->addElement('text','street',get_string('location_street','local_course_locations'));
        $form->addRule('street', 'required', 'required', null, 'client');
        $form->setType('street',PARAM_TEXT);
        /* Post Code    */
        $form->addElement('text','postcode',get_string('location_post_code','local_course_locations'));
        $form->addRule('postcode', 'required', 'required', null, 'client');
        $form->setType('postcode',PARAM_TEXT);
        /* City         */
        $form->addElement('text','city',get_string('location_city','local_course_locations'));
        $form->addRule('city', 'required', 'required', null, 'client');
        $form->setType('city',PARAM_TEXT);

        /* Url Map              */
        $form->addElement('url','url_map',get_string('location_map','local_course_locations'),array('size' => 25));
        $form->setType('url_map',PARAM_URL);

        /* Post Address         */
        $form->addElement('text','post_address',get_string('location_post','local_course_locations'));
        $form->setType('post_address',PARAM_TEXT);

        /* Contact Person       */
        $form->addElement('text','contact',get_string('location_contact','local_course_locations'));
        $form->setType('contact',PARAM_TEXT);

        /* Contact Phone        */
        $form->addElement('text','phone',get_string('location_phone','local_course_locations'));
        $form->setType('phone',PARAM_TEXT);

        /* Contact Mail         */
        $form->addElement('text','mail',get_string('location_mail','local_course_locations'));
        $form->setType('mail',PARAM_TEXT);

        /* Comments             */
        $form->addElement('textarea','comments',get_string('location_comments','local_course_locations'),'rows="10" cols="60" style="width: 90%;"');
        $form->setType('textarea',PARAM_RAW);

        /* Activate             */
        $form->addElement('checkbox', 'activate', get_string('activate', 'local_course_locations'));
        $form->setDefault('activate',1);

        $this->add_action_buttons(true, get_string('add'));
    }//definition
}//add_location_form

/**
 * Class            edit_location_form
 *
 * @creationDate    04/05/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Edit location Form
 */
class edit_location_form extends moodleform {
    function definition() {
        /* Form         */
        $form       = $this->_form;

        /* Location     */
        list($location,$edit_options) = $this->_customdata;

        /* Locations */
        $form->addElement('header', 'header_location', get_string('location', 'local_course_locations'));
        $form->setExpanded('header_location',true);

        /* County  */
        $form->addElement('text','county',get_string('location_county','local_course_locations'),'disabled');
        $form->setType('county',PARAM_TEXT);
        /* Municipality */
        $form->addElement('text','muni',get_string('location_muni','local_course_locations'),'disabled');
        $form->setType('muni',PARAM_TEXT);

        /* General  */
        $form->addElement('header', 'header_general', get_string('title_general', 'local_course_locations'));
        $form->setExpanded('header_general',true);
        /* Name                 */
        $form->addElement('text','name',get_string('location_name','local_course_locations'));
        $form->addRule('name', 'required', 'required', null, 'client');
        $form->setType('name',PARAM_TEXT);

        /* Description          */
        $form->addElement('editor','description_editor',get_string('location_desc','local_course_locations'),'style="width: 90%;"',$edit_options);
        $form->setType('description',PARAM_RAW);

        /* Url More information */
        $form->addElement('url','url_desc',get_string('location_url','local_course_locations'),array('size' => 25));
        $form->setType('url_desc',PARAM_URL);

        /* Floor                */
        $form->addElement('text','floor',get_string('location_floor','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('floor', 'required', 'required', null, 'client');
        $form->setType('floor',PARAM_TEXT);

        /* Room                 */
        $form->addElement('text','room',get_string('location_room','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('room', 'required', 'required', null, 'client');
        $form->setType('room',PARAM_TEXT);

        /* Seats                */
        $form->addElement('text','seats',get_string('location_seats','local_course_locations'),array('size' => 10,'maxlength' => 25));
        $form->addRule('seats', 'required', 'required', null, 'client');
        $form->setType('seats',PARAM_INT);

        /* Address              */
        /* Street       */
        $form->addElement('text','street',get_string('location_street','local_course_locations'));
        $form->addRule('street', 'required', 'required', null, 'client');
        $form->setType('street',PARAM_TEXT);
        /* Post Code    */
        $form->addElement('text','postcode',get_string('location_post_code','local_course_locations'));
        $form->addRule('postcode', 'required', 'required', null, 'client');
        $form->setType('postcode',PARAM_TEXT);
        /* City         */
        $form->addElement('text','city',get_string('location_city','local_course_locations'));
        $form->addRule('city', 'required', 'required', null, 'client');
        $form->setType('city',PARAM_TEXT);

        /* Url Map              */
        $form->addElement('url','url_map',get_string('location_map','local_course_locations'),array('size' => 25));
        $form->setType('url_map',PARAM_URL);

        /* Post Address         */
        $form->addElement('text','post_address',get_string('location_post','local_course_locations'));
        $form->setType('post_address',PARAM_TEXT);

        /* Contact Person       */
        $form->addElement('text','contact',get_string('location_contact','local_course_locations'));
        $form->setType('contact',PARAM_TEXT);

        /* Contact Phone        */
        $form->addElement('text','phone',get_string('location_phone','local_course_locations'));
        $form->setType('phone',PARAM_TEXT);

        /* Contact Mail         */
        $form->addElement('text','mail',get_string('location_mail','local_course_locations'));
        $form->setType('mail',PARAM_TEXT);

        /* Comments             */
        $form->addElement('textarea','comments',get_string('location_comments','local_course_locations'),'rows="10" cols="60" style="width: 90%;"');
        $form->setType('textarea',PARAM_RAW);

        /* Activate             */
        $form->addElement('checkbox', 'activate', get_string('activate', 'local_course_locations'));

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$location->id);

        $this->add_action_buttons(true, get_string('btn_save','local_course_locations'));
        $this->set_data($location);
    }//definition
}//edit_location_form


/**
 * @param           $myCompetence
 * @return          array
 *
 * @creationDate    28/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the municipalities connected with the county
 */
function GetMunicipalities($myCompetence) {
    /* Variables    */
    $levelZero      = null;
    $levelOne       = null;
    $municipalities = array();

    if (isset($_COOKIE['parentCounty']) && ($_COOKIE['parentCounty'])) {
        if (array_key_exists($_COOKIE['parentCounty'],$myCompetence)) {
            $levelZero = $myCompetence[$_COOKIE['parentCounty']];
            if ($levelZero->levelOne) {
                $levelOne  = $levelZero->levelOne;
            }//if_levelOne
        }
        $municipalities = CourseLocations::Get_Companies($levelOne,$_COOKIE['parentCounty']);
    }else {
        $municipalities[0] = get_string('select_level_list','local_course_locations');
    }//IF_COOKIE

    return $municipalities;
}//GetMunicipalities
