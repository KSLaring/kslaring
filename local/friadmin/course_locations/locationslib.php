<?php
/**
 * Course Locations - Library
 *
 * @package         local
 * @subpackage      friadmin/course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/04/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      16706/2915
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate into Friadmin Plugin
 *
 */
define('COURSE_LOCATION_COUNTY','county');
define('COURSE_LOCATION_MUNICIPALITY','municipality');
define('COURSE_LOCATION_SECTOR','sector');
define('SORT_BY_LOCATION','location');
define('SORT_BY_MUNI','muni');
define('SORT_BY_ADDRESS','address');

class CourseLocations {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/


    /**
     * @param           $user_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence locations for the user
     *
     * My Competence. Object
     *          --> jobRoles
     *          --> levelZero.      Counties split by coma.
     *          --> levelOne.       Municipalities split by comma.
     */
    public static function Get_MyCompetence_old($user_id) {
        /* Variables    */
        global $DB;
        $myJobRoles     = null;
        $myCompetence   = null;
        $levelZero      = null;
        $levelOne       = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Get My Job Roles */
            $myJobRoles = self::Get_MyJobRoles($user_id);

            /* Get My Competence Locations  */
            if ($myJobRoles) {
                /* SQL Instruction  */
                $sql = " SELECT     GROUP_CONCAT(DISTINCT IF(levelzero,levelzero,0) ORDER BY levelone SEPARATOR ',') 	as 'levelzero',
                                    GROUP_CONCAT(DISTINCT IF(levelone,levelone,0) ORDER BY levelone SEPARATOR ',') 	as 'levelone'
                         FROM	    {report_gen_jobrole_relation}
                         WHERE		jobroleid 	IN ($myJobRoles) ";

                /* Execute  */
                $rdo = $DB->get_record_sql($sql);
                if ($rdo) {
                    if (isset($rdo->levelzero)) {
                        $myCompetence = new stdClass();
                        $myCompetence->jobRoles     = $myJobRoles;
                        $myCompetence->levelZero    = null;
                        $myCompetence->levelOne     = null;

                        /* Level Zero    */
                        $levelZero = explode(',',$rdo->levelzero);
                        $levelZero = array_flip($levelZero);
                        if (array_key_exists(0,$levelZero)) {
                            $myCompetence->levelZero = 0;
                        }else {
                            $myCompetence->levelZero = $rdo->levelzero;

                            /* Level One    */
                            $levelOne = explode(',',$rdo->levelone);
                            $levelOne = array_flip($levelOne);
                            if (array_key_exists(0,$levelOne)) {
                                $myCompetence->levelOne = 0;
                            }else {
                                $myCompetence->levelOne = $rdo->levelone;
                            }//if_all_levelOne
                        }//if_all_levelOne
                    }
                }//if_rdo
            }//if_MyJobRoles

            return $myCompetence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompetence

    /**
     * @param           $userId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    01/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence locations for the user. Based only on his/her companies.
     */
    public static function Get_MyCompetence($userId) {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $myCompetence   = null;

        try {
            /* Criteria */
            $params = array();
            $params['user'] = $userId;

            /* SQL Instruction */
            $sql = " SELECT	GROUP_CONCAT(DISTINCT 	uicd.companyid 		ORDER BY uicd.companyid 	SEPARATOR ',')	as 'levelthree',
                            GROUP_CONCAT(DISTINCT 	cr_two.parentid  	ORDER BY cr_two.parentid 	SEPARATOR ',') 	as 'leveltwo',
                            GROUP_CONCAT(DISTINCT 	cr_one.parentid  	ORDER BY cr_one.parentid 	SEPARATOR ',') 	as 'levelone',
                            GROUP_CONCAT(DISTINCT 	cr_zero.parentid  	ORDER BY cr_zero.parentid 	SEPARATOR ',') 	as 'levelzero',
                            GROUP_CONCAT(DISTINCT	uicd.jobroles		ORDER BY uicd.jobroles		SEPARATOR ',')	as 'jobroles'
                     FROM		{user_info_competence_data} 		uicd
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}   	cr_two	ON 	cr_two.companyid 		= uicd.companyid
                        JOIN	{report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                            AND co_two.hierarchylevel 	= 2
                        -- LEVEL ONE
                        JOIN	{report_gen_company_relation}   	cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                        JOIN	{report_gen_companydata}			co_one	ON 	co_one.id 				= cr_one.parentid
                                                                            AND co_one.hierarchylevel 	= 1
                        -- LEVEL ZERO
                        JOIN	{report_gen_company_relation} 	    cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                        JOIN	{report_gen_companydata}	  		co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                            AND co_zero.hierarchylevel 	= 0
                     WHERE		uicd.userid = :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if (($rdo->jobroles)  ||
                    ($rdo->levelzero) ||
                    ($rdo->levelone)  ||
                    ($rdo->leveltwo)  ||
                    ($rdo->levelthree)) {
                    /* Competence   Info    */
                    $myCompetence = new stdClass();
                    $myCompetence->jobRoles     = $rdo->jobroles;
                    $myCompetence->levelZero    = $rdo->levelzero;
                    $myCompetence->levelOne     = $rdo->levelone;
                    $myCompetence->levelTwo     = $rdo->leveltwo;
                    $myCompetence->levelThree   = $rdo->levelthree;
                }//if_data
            }//if_rdo

            return $myCompetence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompetence

    /**
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with user
     */
    public static function Get_MyJobRoles($user_id) {
        /* Variables    */
        global $DB;
        $myJobRoles = null;
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT uicd.jobroles ORDER BY uicd.jobroles SEPARATOR ',') as 'jobroles'
                     FROM		{user_info_competence_data} 	uicd
                     WHERE		uicd.userid = :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->jobroles;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyJobRoles

    /**
     * @param               $level
     * @param       null    $in
     * @param       null    $parent
     * @return              array
     * @throws              Exception
     *
     * @creationDate        28/04/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get the companies
     */
    public static function Get_Companies($level,$in=null,$parent=null) {
        /* Variables    */
        global $DB;
        $companies  = array();
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            /* Search Criteria  */
            $params          = array();
            $params['level'] = $level;

            /* List Companies   */
            $companies[0] = get_string('select_level_list','local_friadmin');

            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT  rcd.id,
                                          rcd.name,
                                          rcd.industrycode
                     FROM       {report_gen_companydata} rcd ";

            /* Parents  */
            if ($parent) {
                $sql .= " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                    AND   rcr.parentid  IN ($parent) ";
            }//if_level

            /* Level        */
            $sql .= " WHERE rcd.hierarchylevel = :level ";

            /* Companies In */
            if ($in) {
                $sql .= " AND     rcd.id IN ($in) ";
            }//if_companies_in


            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companies[$instance->id] = $instance->industrycode . ' - '. $instance->name;
                }//for_rdo_company
            }//if_Rdo

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Companies

    /**
     * @param           $filter
     * @return          int
     * @throws          Exception
     *
     * @creationDate    29/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the total amount of locations
     */
    public static function Get_TotalLocationsList($filter) {
        /* Variables    */
        global $DB;
        $sql    = null;
        $rdo    = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT		count(cl.id) as 'total'
                     FROM		{course_locations}	cl
                     WHERE		cl.activate		= :activate";

            /* County Filter        */
            if ($filter['county']) {
                $sql .= " AND		cl.levelzero    = :county ";
            }//if_muni_filter

            /* Municipality Filter  */
            if ($filter['muni']) {
                $sql .= " AND		cl.levelone		= :muni ";
            }//if_muni_filter

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$filter);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalLocationsList

    /**
     * @param           $filter
     * @param           $limit_from
     * @param           $limit_num
     * @param           $sort
     * @param           $fieldSort
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the locations based on search criteria
     *
     * Locations
     *          [id]
     *              --> id
     *              --> municipality
     *              --> name
     *              --> address
     *              --> detail
     *              --> contact
     *              --> status
     *              --> activate
     */
    public static function Get_LocationsList($filter,$limit_from,$limit_num,$sort,$fieldSort) {
        /* Variables    */
        global $DB;
        $locations  = array();
        $info       = null;
        $strAddress = null;
        $strDetail  = null;
        $strContact = null;
        $sql        = null;
        $rdo        = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT		cl.id,
                                levelone.name 	as 'levelone',
                                cl.name,
                                cl.street,
                                cl.postcode,
                                cl.city,
                                cl.floor,
                                cl.room,
                                cl.seats,
                                cl.contact,
                                cl.phone,
                                cl.email,
                                cl.activate
                     FROM		{course_locations}	cl
                        JOIN	{report_gen_companydata}	levelone	ON levelone.id 	= cl.levelone
                     WHERE		cl.activate		= :activate
                        AND     cl.levelzero    = :county ";


            /* Municipality Filter  */
            if ($filter['muni']) {
                $sql .= " AND		cl.levelone		= :muni ";
            }//if_muni_filter

            /* Order    */
            switch ($fieldSort) {
                case SORT_BY_LOCATION:
                    $sql .= " ORDER BY cl.name " . $sort;

                    break;
                case SORT_BY_MUNI :
                    $sql .= " ORDER BY levelone.name " . $sort;

                    break;
                case SORT_BY_ADDRESS:
                    $sql .= " ORDER BY cl.city " . $sort. ", cl.street " . $sort;
                    break;
                default:
                    $sql .= " ORDER BY 	levelone.name " . $sort . ", cl.name " . $sort;

                    break;
            }//switch_sort

            /* Execute      */
            $rdo = $DB->get_records_sql($sql,$filter,$limit_from,$limit_num);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Address  */
                    $strAddress     = $instance->street;
                    $strAddress    .= "</br>";
                    $strAddress    .= $instance->postcode . ' ' . $instance->city;
                    /* Detail   */
                    $strDetail      = get_string('location_floor','local_friadmin') . ': ' . $instance->floor;
                    $strDetail     .= "</br>";
                    $strDetail     .=  get_string('location_room','local_friadmin') . ': ' . $instance->room;
                    $strDetail     .= "</br>";
                    $strDetail     .=  get_string('location_seats','local_friadmin') . ': ' . $instance->seats;
                    /* Contact  */
                    $strContact     = $instance->contact;
                    $strContact    .= "</br>";
                    $strContact    .= $instance->email;
                    $strContact    .= "</br>";
                    $strContact    .= $instance->phone;

                    /* Location Info    */
                    $info               = new stdClass();
                    $info->id           = $instance->id;
                    $info->municipality = $instance->levelone;
                    $info->name         = $instance->name;
                    $info->address      = $strAddress;
                    $info->detail       = $strDetail;
                    $info->contact      = $strContact;
                    $info->activate     = $instance->activate;
                    if ($instance->activate) {
                        $instance->status = get_string('activate','local_friadmin');
                    }else {
                        $instance->status = get_string('deactivate','local_friadmin');
                    }//if_Activate

                    /* Add Location */
                    $locations[$instance->id] = $info;
                }//for_rdo_location
            }//if_rdo

            return $locations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_LocationsList

    /**
     * @param           $filter
     * @param           $sort
     * @param           $fieldsort
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses locations
     *
     * Courses List     Array
     *          [id]
     *              --> course.         Course id
     *              --> name.           Course name
     *              --> start.          Course Start Date.
     *              --> maxSeats.       Max Seats.
     *              --> length.         Course Length. (Course Format Options)
     *              --> municipality.   Municipality name
     *              --> county.         County name
     *              --> sectors.        Sectors name separated by coma.
     *              --> location.       Location name.
     */
    public static function Get_CoursesLocations_List($filter,$sort,$fieldsort) {
        /* Variables    */
        global $DB;
        $coursesLst = array();
        $info       = null;
        $sql        = null;
        $sqlWhere   = null;
        $rdo        = null;

        try {

            /* SQL Instruction  */
            $sql = " SELECT		c.id,
                                c.fullname,
                                c.startdate,
                                cle.length,
                                cl.name as 'location',
                                cl.seats,
                                co.name as 'county',
                                mu.name as 'municipality',
                                cse.sectors
                     FROM		{course}					c
                         JOIN	(
                                 SELECT		cfo.courseid,
                                            cfo.value as 'length'
                                 FROM 		{course_format_options}	cfo
                                 WHERE		cfo.format 	LIKE '%frikomport%'
                                    AND		cfo.name 	= 'length'
                                ) cle ON cle.courseid = c.id
                         JOIN	(
                                 SELECT		cfo.courseid,
                                            cfo.value as 'location'
                                 FROM 		{course_format_options}	cfo
                                 WHERE		cfo.name 	= 'course_location'
                                ) cfl ON cfl.courseid = c.id
                         JOIN	{course_locations}			    cl 			ON	cl.id 		= cfl.location
                         JOIN	{report_gen_companydata}		co			ON  co.id    	= cl.levelzero
                         JOIN	{report_gen_companydata}		mu			ON  mu.id 		= cl.levelone
                         JOIN	(
                                 SELECT		cfo.courseid,
                                            cfo.value as 'sectors'
                                 FROM 		{course_format_options}	cfo
                                 WHERE		cfo.name 	= 'course_sector'
                                ) cse ON cse.courseid = c.id
                      ";

            /* Add Search Criteria  */
            /* County       */
            if ($filter['county']) {
                if (!$sqlWhere) {
                    $sqlWhere = " WHERE cl.levelzero = :county ";
                }else {
                    $sqlWhere .= " AND cl.levelzero = :county ";
                }//if_else
            }//if_filterCounty

            /* Municipality */
            if ($filter['muni']) {
                if (!$sqlWhere) {
                    $sqlWhere = " WHERE cl.levelone = :muni ";
                }else {
                    $sqlWhere .= " AND cl.levelone = :muni ";
                }//if_else
            }//if_filterMuni

            /* Sector       */
            if ($filter['sector']) {
                if (!$sqlWhere) {
                    $sqlWhere = " WHERE     cse.sectors 	LIKE '"     . $filter['sector'] . ",%'" .
                        " OR    cse.sectors  	LIKE '%,"   . $filter['sector'] . "' " .
                        " OR    cse.sectors 	LIKE '%,"   . $filter['sector'] . ",%'" .
                        " OR    cse.sectors 	= :sector ";
                }else {
                    $sqlWhere .= " AND       cse.sectors 	LIKE '"     . $filter['sector'] . ",%'" .
                        " OR    cse.sectors  	LIKE '%,"   . $filter['sector'] . "' " .
                        " OR    cse.sectors 	LIKE '%,"   . $filter['sector'] . ",%'" .
                        " OR    cse.sectors 	= :sector ";
                }//if_else
            }//if_filterSector

            /* Course       */
            if ($filter['course']) {
                if (!$sqlWhere) {
                    $sqlWhere = 'WHERE c.fullname like "%' . $filter['course'] . '%"';
                }else {
                    $sqlWhere .= 'AND c.fullname like "%' . $filter['course'] . '%"';
                }//if_else
            }//if_filterCourse

            /* Date (Start Date???)        */
            if ($filter['fromDate'] && $filter['toDate']) {
                if (!$sqlWhere) {
                    $sqlWhere = " WHERE c.startdate BETWEEN :fromDate AND :toDate ";
                }else {
                    $sqlWhere .= " AND c.startdate BETWEEN :fromDate AND :toDate  ";
                }//if_else
            }else {
                if ($filter['fromDate']) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE c.startdate >= :fromDate ";
                    }else {
                        $sqlWhere .= " AND c.startdate >= :fromDate  ";
                    }//if_else
                }else {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE c.startdate <= :toDate ";
                    }else {
                        $sqlWhere .= " AND c.startdate <= :toDate  ";
                    }//if_else
                }
            }//if_filerDate

            /* Add Criterias    */
            if ($sqlWhere) {
                $sql .= $sqlWhere;
            }//if_sqlWhere

            /* Add Order    */
            $sql .= " ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$filter);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Course Location */
                    $info = new stdClass();
                    $info->course       = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->start        = $instance->startdate;
                    $info->maxSeats     = $instance->seats;
                    $info->length       = $instance->length;
                    $info->municipality = $instance->municipality;
                    $info->county       = $instance->county;
                    $info->sectors      = null;
                    if ($instance->sectors) {
                        $info->sectors = self::get_sectors_name($instance->sectors);;
                    }
                    $info->location     = $instance->location;

                    $coursesLst[$instance->courseid] = $info;
                }//for_eachCourse
            }//if_Rdo

            return $coursesLst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesLocations_List

    /**
     * @param           $dataForm
     * @param           $user_id
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Insert a new Location
     */
    public static function Add_NewLocation($dataForm,$user_id) {
        /* Variables    */
        global $DB;
        $newLocation    = null;

        try {
            /* New Location */
            $newLocation = new stdClass();
            /* County       */
            $newLocation->levelzero     = $dataForm[COURSE_LOCATION_COUNTY];
            /* Municipality */
            $newLocation->levelone      = $dataForm[COURSE_LOCATION_MUNICIPALITY];
            /* Name         */
            $newLocation->name          = $dataForm['name'];
            /* Description  */
            $newLocation->description   = $dataForm['description'];
            /* Url Desc */
            $newLocation->url           = $dataForm['url_desc'];
            /* Floor        */
            $newLocation->floor         = $dataForm['floor'];
            /* Room         */
            $newLocation->room          = $dataForm['room'];
            /* Seats        */
            $newLocation->seats         = $dataForm['seats'];
            /* Street       */
            $newLocation->street        = $dataForm['street'];
            /* Post Code    */
            $newLocation->postcode      = $dataForm['postcode'];
            /* City         */
            $newLocation->city          = $dataForm['city'];
            /* Url Map          */
            $newLocation->urlmap        = $dataForm['url_map'];
            /* Post Address     */
            $newLocation->post          = $dataForm['post_address'];
            /* Contact Person   */
            $newLocation->contact       = $dataForm['contact'];
            /* Contact Phone    */
            $newLocation->phone         = $dataForm['phone'];
            /* Contact eMail    */
            $newLocation->email         = $dataForm['mail'];
            /* Comments         */
            $newLocation->comments      = $dataForm['comments'];
            /* Activate         */
            if (isset($dataForm['activate']) && ($dataForm['activate'])) {
                $newLocation->activate     = 1;
            }else {
                $newLocation->activate     = 0;
            }//if_checkbox_Activate

            /* Created By   */
            $newLocation->createdby     = $user_id;
            /* Time Created */
            $newLocation->timecreated   = time();

            $DB->insert_record('course_locations',$newLocation);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_NewLocation

    /**
     * @param           $company
     * @return          null
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the county name
     */
    public static function Get_CompanyLevelName($company) {
        /* Variables    */
        global $DB;
        $rdo    = null;

        try {
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company),'name,industrycode');
            if ($rdo) {
                return $rdo->industrycode . ' - '. $rdo->name;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CountyName

    /**
     * @param           $locationId
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all information connected with a specific location
     */
    public static function Get_LocationDetail($locationId) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['location'] = $locationId;

            /* SQL Instruction  */
            $sql = " SELECT		    cl.id,
                                    levelzero.name 	as 'county',
                                    levelone.name 	as 'muni',
                                    cl.name,
                                    cl.description,
                                    cl.url          as 'url_desc',
                                    cl.floor,
                                    cl.room,
                                    cl.seats,
                                    cl.street,
                                    cl.postcode,
                                    cl.city,
                                    cl.urlmap       as 'url_map',
                                    cl.post         as 'post_address',
                                    cl.contact,
                                    cl.phone,
                                    cl.email        as 'mail',
                                    cl.comments,
                                    cl.activate,
                                    GROUP_CONCAT(DISTINCT clo.courseid ORDER BY clo.courseid) as 'courses'
                     FROM		    {course_locations}		    cl
                        JOIN	    {report_gen_companydata}		levelzero	ON  levelzero.id    = cl.levelzero
                        JOIN	    {report_gen_companydata}		levelone	ON  levelone.id 	= cl.levelone
                        LEFT JOIN 	(
                                        SELECT		cfo.courseid,
                                                    cfo.value as 'location'
                                        FROM		{course_format_options} cfo
                                        WHERE		cfo.name	= 'course_location'
                                    )	clo ON clo.location = cl.id
                     WHERE		cl.id = :location ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_LocationDetail

    /**
     * @param           $dataForm
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update location
     */
    public static function Update_Location($dataForm) {
        /* Variables    */
        global $DB;
        $location = null;

        try {
            /* New Location */
            $location = new stdClass();
            /* Location Id  */
            $location->id            = $dataForm['id'];
            /* Name         */
            $location->name          = $dataForm['name'];
            /* Description  */
            $location->description   = $dataForm['description'];
            /* Url Desc */
            $location->url           = $dataForm['url_desc'];
            /* Floor        */
            $location->floor         = $dataForm['floor'];
            /* Room         */
            $location->room          = $dataForm['room'];
            /* Seats        */
            $location->seats         = $dataForm['seats'];
            /* Street       */
            $location->street        = $dataForm['street'];
            /* Post Code    */
            $location->postcode      = $dataForm['postcode'];
            /* City         */
            $location->city          = $dataForm['city'];
            /* Url Map          */
            $location->urlmap        = $dataForm['url_map'];
            /* Post Address     */
            $location->post          = $dataForm['post_address'];
            /* Contact Person   */
            $location->contact       = $dataForm['contact'];
            /* Contact Phone    */
            $location->phone         = $dataForm['phone'];
            /* Contact eMail    */
            $location->email         = $dataForm['mail'];
            /* Comments         */
            $location->comments      = $dataForm['comments'];
            /* Activate         */
            if (isset($dataForm['activate']) && ($dataForm['activate'])) {
                $location->activate     = 1;
            }else {
                $location->activate     = 0;
            }//if_checkbox_Activate

            /* Time Created */
            $location->timemodified   = time();

            $DB->update_record('course_locations',$location);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//Update_Location

    /**
     * @param           $locationId
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Change status
     */
    public static function ChangeStatus_Location($locationId) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['location'] = $locationId;

            /* SQL Instruction  */
            $sql = " UPDATE {course_locations}
                        SET activate = !activate
                     WHERE  id = :location ";

            /* Execute  */
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//ChangeStatus_Location

    /**
     * @param           $locationId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the location is connected with some courses
     */
    public static function Has_CoursesConnected($locationId) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['name']     = 'course_location';
            $params['location'] = $locationId;

            /* SQL Instruction  */
            $sql = " SELECT		cfo.courseid
                    FROM		{course_format_options} cfo
                    WHERE		cfo.name	= :name
                      AND       cfo.value   = :location";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Has_CoursesConnected

    /**
     * @param           $locationId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the location
     */
    public static function Delete_Location($locationId) {
        /* Variables    */
        global $DB;
        $params = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['id'] = $locationId;

            /* Execute  */
            $DB->delete_records('course_locations',$params);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_Location

    /**
     * @param           $county
     * @param           $locations
     * @param           $totalLocations
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @param           $fieldSort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the locations table
     *
     * Locations
     *          [id]
     *              --> id
     *              --> municipality
     *              --> name
     *              --> address
     *              --> detail
     *              --> contact
     *              --> status
     *              --> activate
     */
    public static function Print_LocationsList($county,$locations,$totalLocations,$page,$perpage,$sort,$fieldSort) {
        /* Variables    */
        global $OUTPUT;
        $out_report         = '';
        $urlReturn          = null;
        $url                = null;

        try {
            /* Url  */
            $url            = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' =>$sort));
            /* Url To Back  */
            $urlReturn     = new moodle_url('/local/friadmin/course_locations/index.php');

            /* Locations Report */
            $out_report .= html_writer::start_div('locations_rpt_div');
                /* Header   */
                $out_report .= html_writer::start_div('header_location');
                    /* Title    */
                    $out_report .= '<h3>';
                        $out_report .= get_string('exist_locations', 'local_friadmin') . ' - ' . $county;
                    $out_report .= '</h3>';
                $out_report .= html_writer::end_div();//header_location

                if (!$locations) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'local_friadmin');
                    $out_report .= '</h3>';
                }else {
                    $out_report .= '</br>';
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($urlReturn,get_string('return_to_selection','local_friadmin'),array('class' => 'link_return'));

                    /* Paging Bar  */
                    $out_report .= $OUTPUT->paging_bar($totalLocations, $page, $perpage, $url);

                    /* Location List    */
                    $out_report .= html_writer::start_div('location_list');
                        /* Locations Table */
                        $out_report .= html_writer::start_tag('table');
                            /* Header Table     */
                            $out_report .= self::AddHeader_TableLocations($sort,$fieldSort);
                            /* Content Table    */
                            $out_report .= self::AddContent_TableLocations($locations,$page,$perpage,$sort);
                        $out_report .= html_writer::end_tag('table');
                    $out_report .= html_writer::end_div();//location_list
                }//if_locations
            $out_report .= html_writer::end_div();//locations_rpt_div

            /* Paging Bar  */
            $out_report .= $OUTPUT->paging_bar($totalLocations, $page, $perpage, $url);

            /* Return To Selection Page */
            $out_report .= html_writer::link($urlReturn,get_string('return_to_selection','local_friadmin'),array('class' => 'link_return'));
            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_LocationsList

    /**
     * @param           $location
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print location detail view
     */
    public static function Print_LocationView($location,$page,$perpage,$sort) {
        /* Variables    */
        $out_report         = '';
        $urlReturn          = null;
        $urlEdit            = null;

        try {
            /* Return  */
            $urlReturn     = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort));
            /* Edit     */
            $urlEdit = new moodle_url('/local/friadmin/course_locations/edit_location.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'id' => $location->id));

            /* Location Detail Panel    */
            $out_report .= html_writer::start_div('locations_rpt_div');
                /* Header   */
                $out_report .= html_writer::start_div('header_detail_location');
                    /* Title    */
                    $out_report .= '<h3>';
                        $out_report .= $location->name;
                    $out_report .= '</h3>';
                $out_report .= html_writer::end_div();//header_location

                /* Description  */
                $out_report .= '<h5>';
                    $out_report .= str_replace('</p>','',str_replace('<p>','',$location->description));
                $out_report .= '</h5>';

                /* Location Detail */
                $out_report .= self::Add_ContentDetail($location);

                /* Return To Selection Page */
                $out_report .= html_writer::start_tag('div',array('class' => 'location_advance_set'));
                    $out_report .= html_writer::link($urlReturn,get_string('lnk_back','local_friadmin'));
                $out_report .= html_writer::end_tag('div'); //div_location_advance_set
                /* Edit option */
                $out_report .= html_writer::start_tag('div',array('class' => 'location_advance_set'));
                    $out_report .= html_writer::link($urlEdit,get_string('edit','local_friadmin'));
                $out_report .= html_writer::end_tag('div'); //div_expiration
            $out_report .= html_writer::end_div();//locations_rpt_div

            $out_report .= '</br>';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_LocationView


    /** / */
    public static function download_this_course_data($coursesdata, $from, $to, $category) {
        // Variables.
        global $CFG;
        $row = 0;
        $time = null;
        $name = null;
        $export = null;
        $myxls = null;

        try {
            require_once($CFG->dirroot . '/lib/excellib.class.php');

            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename('Participants_List_Summary' . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Search criterias.
            $myxls = $export->add_worksheet('Filter');

            self::add_participants_excel_filter($myxls, $row, $from, $to, $category);

            // Raw.
            $myxls = $export->add_worksheet('Content');

            // Headers.
            self::add_participants_header_excel($myxls, $row, $coursesdata);
            // Content.
            self::add_participants_content_excel($coursesdata, $myxls, $row, $from, $to);

            $export->close();

            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }//download_participants_list

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $sectors
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the sectors names split by comma
     */
    private static function get_sectors_name($sectors) {
        /* Variables    */
        global $DB;
        $sectorsName    = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT CONCAT(rgc.industrycode,' - ', rgc.name) ORDER BY rgc.industrycode, rgc.name SEPARATOR ', ') as 'sectors'
                     FROM		{report_gen_companydata}	rgc
                     WHERE      rgc.id IN ($sectors) ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                $sectorsName = $rdo->sectors;
            }//if_rdo

            return $sectorsName;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sectors_name

    /**
     * @param           $sort
     * @param           $fieldSort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header locations table
     */
    private static function AddHeader_TableLocations($sort,$fieldSort) {
        /* Variables    */
        $header             = '';
        $strName            = get_string('location_name','local_friadmin');
        $strMuni            = get_string('location_muni','local_friadmin');
        $strAddress         = get_string('location_address','local_friadmin');
        $strDetail          = get_string('location_detail','local_friadmin');
        $strContact         = get_string('location_contact_inf','local_friadmin');
        $dir                = null;
        $sortImg            = null;
        $sortAsc            = new moodle_url('/pix/t/sort_asc.png');
        $sortDesc           = new moodle_url('/pix/t/sort_desc.png');
        $sortImgLocation    = null;
        $dirLocation        = null;
        $sortImgAddress     = null;
        $dirAddress         = null;
        $sortImgMuni        = null;
        $dirMuni            = null;

        try {

            /* Correct Image    */
            if ($sort == 'ASC') {
                $sortImg = $sortDesc;
            }else {
                $sortImg = $sortAsc;
            }//if_sort

            switch ($fieldSort) {
                case SORT_BY_LOCATION:
                    /* Correct Sort Order Location  */
                    $sortImgLocation    = $sortImg;
                    $dirLocation        = $sort;

                    /* Correct Sort Order Address   */
                    $sortImgAddress     = $sortDesc;
                    $dirAddress         = 'ASC';
                    /* Correct  Sort Order Muni */
                    $sortImgMuni        = $sortDesc;
                    $dirMuni            = 'ASC';

                    break;
                case SORT_BY_MUNI:
                    /* Correct  Sort Order Muni */
                    $sortImgMuni        = $sortImg;
                    $dirMuni            = $sort;

                    /* Correct Sort Order Location  */
                    $sortImgLocation    = $sortDesc;
                    $dirLocation        = 'ASC';
                    /* Correct Sort Order Address   */
                    $sortImgAddress     = $sortDesc;
                    $dirAddress         = 'ASC';

                    break;
                case SORT_BY_ADDRESS:
                    /* Correct Sort Order Address   */
                    $sortImgAddress     = $sortImg;
                    $dirAddress         = $sort;

                    /* Correct Sort Order Location  */
                    $sortImgLocation    = $sortDesc;
                    $dirLocation        = 'ASC';
                    /* Correct  Sort Order Muni */
                    $sortImgMuni        = $sortDesc;
                    $dirMuni            = 'ASC';

                    break;
                default:
                    /* Correct Sort Order Location  */
                    $sortImgLocation    = $sortDesc;
                    $dirLocation        = 'ASC';
                    /* Correct Sort Order Address   */
                    $sortImgAddress     = $sortDesc;
                    $dirAddress         = 'ASC';
                    /* Correct  Sort Order Muni */
                    $sortImgMuni        = $sortDesc;
                    $dirMuni            = 'ASC';

                    break;
            }//fieldSort

            /* Build Header */
            $header .=  html_writer::start_tag('thead');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    /* Name         */
                    $header .= html_writer::start_tag('th',array('class' => 'detail'));
                        $header .= '<button class="button_order" id="' . SORT_BY_LOCATION . '" value="' . $dirLocation . '" name="' . SORT_BY_LOCATION. '">';
                            $header .= $strName;
                            $header .= '<img id="' . SORT_BY_LOCATION . '_img'. '" src='. $sortImgLocation . '>';
                        $header .= '</button>';
                    $header .= html_writer::end_tag('th');

                    /* Address      */
                    $header .= html_writer::start_tag('th',array('class' => 'detail'));
                        $header .= '<button class="button_order" id="' . SORT_BY_ADDRESS . '" value="' . $dirAddress . '" name="' . SORT_BY_ADDRESS. '">';
                            $header .= $strAddress;
                            $header .= '<img id="' . SORT_BY_ADDRESS . '_img'. '" src='. $sortImgAddress . '>';
                        $header .= '</button>';
                    $header .= html_writer::end_tag('th');

                    /* Municipality */
                    $header .= html_writer::start_tag('th',array('class' => 'detail'));
                        $header .= '<button class="button_order" id="' . SORT_BY_MUNI . '" value="' . $dirMuni . '" name="' . SORT_BY_MUNI. '">';
                            $header .= $strMuni;
                            $header .= '<img id="' . SORT_BY_MUNI . '_img'. '" src='. $sortImgMuni . '>';
                        $header .= '</button>';
                    $header .= html_writer::end_tag('th');

                    /* Contact      */
                    $header .= html_writer::start_tag('th',array('class' => 'detail'));
                        $header .= $strContact;
                    $header .= html_writer::end_tag('th');
                    /* Detail       */
                    $header .= html_writer::start_tag('th',array('class' => 'detail'));
                        $header .= $strDetail;
                    $header .= html_writer::end_tag('th');
                    /* Actions      */
                    $header .= html_writer::start_tag('th',array('class' => 'action'));
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_TableLocations

    /**
     * @param           $locations
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content locations table
     */
    private static function AddContent_TableLocations($locations,$page,$perpage,$sort) {
        /* Variables    */
        $content    = '';
        $color      = 'r0';
        $strName            = get_string('location_name','local_friadmin');
        $strMuni            = get_string('location_muni','local_friadmin');
        $strAddress         = get_string('location_address','local_friadmin');
        $strDetail          = get_string('location_detail','local_friadmin');
        $strContact         = get_string('location_contact_inf','local_friadmin');

        try {
            foreach ($locations as $location) {
                $content .= html_writer::start_tag('tr',array('class' => $color));
                    /* Name         */
                    $content .= html_writer::start_tag('td',array('class' => 'detail','data-th'=>$strName));
                        $content .= $location->name;
                    $content .= html_writer::end_tag('td');
                    /* Address      */
                    $content .= html_writer::start_tag('td',array('class' => 'detail','data-th'=>$strAddress));
                        $content .= $location->address;
                    $content .= html_writer::end_tag('td');
                    /* Municipality */
                    $content .= html_writer::start_tag('td',array('class' => 'detail','data-th'=>$strMuni));
                        $content .= $location->municipality;
                    $content .= html_writer::end_tag('td');
                    /* Contact      */
                    $content .= html_writer::start_tag('td',array('class' => 'detail','data-th'=>$strContact));
                        $content .= $location->contact;
                    $content .= html_writer::end_tag('td');
                    /* Detail       */
                    $content .= html_writer::start_tag('td',array('class' => 'detail','data-th'=>$strDetail));
                        $content .= $location->detail;
                    $content .= html_writer::end_tag('td');
                    /* Actions      */
                    $content .= html_writer::start_tag('td',array('class' => 'action','data-th' =>' '));
                        /* View Details Link    */
                        $content .= self::ViewDetail_LinkAction($location->id,$page,$perpage,$sort);
                        /* Activate / Deactivate Link   */
                        $content .= self::ActivateDeactivate_LinkAction($location->id,$location->activate,$page,$perpage,$sort);
                        /* Edit Link    */
                        $content .= self::Edit_LinkAction($location->id,$page,$perpage,$sort);
                        /* Delete Link  */
                        $content .= self::Delete_LinkAction($location->id,$page,$perpage,$sort);
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');

                /* Change Color */
                if ($color == 'r0') {
                    $color = 'r2';
                }else {
                    $color = 'r0';
                }
            }//for_location

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_TableLocations

    /**
     * @param           $locationId
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get view detail location link
     */
    private static function ViewDetail_LinkAction($locationId,$page,$perpage,$sort) {
        /* Variables    */
        global $OUTPUT;
        $urlView = null;
        $strAlt  = null;
        $outLnk  = '';

        try {
            /* Build URL    */
            $urlView = new moodle_url('/local/friadmin/course_locations/view.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'id' => $locationId));

            /* Build Action Link    */
            $strAlt = get_string('view_location','local_friadmin');
            $outLnk .= html_writer::start_div('lnk_edit');
                $outLnk .= html_writer::link($urlView,
                                             html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/viewdetails'),'alt'=>$strAlt,'class'=>'iconsmall')),
                                             array('title'=>$strAlt));
            $outLnk .= html_writer::end_div();//lnk_edit

            return $outLnk;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ViewDetail_LinkAction

    /**
     * @param           $locationId
     * @param           $activate
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the activate/deactivate location link
     */
    private static function ActivateDeactivate_LinkAction($locationId,$activate,$page,$perpage,$sort) {
        /* Variables    */
        global $OUTPUT;
        $urlAct = null;
        $srcAct = null;
        $strAlt = null;
        $outLnk = '';

        try {
            /* Build URL    */
            $urlAct = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage, 'sort' => $sort,'act' => 1,'id' => $locationId));

            /* Get PIX      */
            if ($activate) {
                $strAlt = get_string('deactivate','local_friadmin');
                $srcAct = $OUTPUT->pix_url('t/hide');
            }else {
                $strAlt = get_string('activate','local_friadmin');
                $srcAct = $OUTPUT->pix_url('t/show');
            }//if_activate

            /* Build Action Link    */
            $outLnk .= html_writer::start_div('lnk_edit');
                $outLnk .= html_writer::link($urlAct,
                                             html_writer::empty_tag('img', array('src'=>$srcAct,'alt'=>$strAlt,'class'=>'iconsmall')),
                                             array('title'=>$strAlt));
            $outLnk .= html_writer::end_div();//lnk_edit

            return $outLnk;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ActivateDeactivate_LinkAction

    /**
     * @param           $locationId
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get edit location link
     */
    private static function Edit_LinkAction($locationId,$page,$perpage,$sort) {
        /* Variables    */
        global $OUTPUT;
        $urlEdit = null;
        $strAlt  = null;
        $outLnk  = '';

        try {
            /* Build URL    */
            $urlEdit = new moodle_url('/local/friadmin/course_locations/edit_location.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'id' => $locationId));

            /* Build Action Link    */
            $strAlt = get_string('edit_location','local_friadmin');
            $outLnk .= html_writer::start_div('lnk_edit');
                $outLnk .= html_writer::link($urlEdit,
                                             html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),'alt'=>$strAlt,'class'=>'iconsmall')),
                                             array('title'=>$strAlt));
            $outLnk .= html_writer::end_div();//lnk_edit

            return $outLnk;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Edit_LinkAction

    /**
     * @param           $locationId
     * @param           $page
     * @param           $perpage
     * @param           $sort
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get delete location link
     */
    private static function Delete_LinkAction($locationId,$page,$perpage,$sort) {
        /* Variables    */
        global $OUTPUT;
        $urlDelete  = null;
        $strAlt     = null;
        $outLnk     = '';


        try {
            /* Build URL */
            $urlDelete = new moodle_url('/local/friadmin/course_locations/delete_location.php',array('page' => $page, 'perpage' => $perpage, 'sort' => $sort,'id' => $locationId));

            /* Build Action Link    */
            $strAlt = get_string('del_location','local_friadmin');
            $outLnk .= html_writer::start_div('lnk_edit');
                $outLnk .= html_writer::link($urlDelete,
                                             html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),'alt'=>$strAlt,'class'=>'iconsmall')),
                                             array('title'=>$strAlt));
            $outLnk .= html_writer::end_div();//lnk_edit

            return $outLnk;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_LinkAction

    /**
     * @param           $location
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the location detail content to the panel
     */
    private static function Add_ContentDetail($location) {
        /* Variables    */
        $content = '';
        $strDetail          = get_string('location_detail','local_friadmin');
        $infoDetail         = null;
        $strCourses         = get_string('courses');
        $strComments        = get_string('location_comments','local_friadmin');
        $strAddress         = get_string('location_address','local_friadmin');
        $infoAddress        = null;
        $strContact         = get_string('location_contact_inf','local_friadmin');
        $infoContact        = null;
        $coursesLink        = null;

        try {
            $content .= html_writer::start_div('userprofile');
                $content .= html_writer::start_div('descriptionbox');
                    $content .= '</br>';
                    /* Content      */
                    $content .= html_writer::start_tag('dl', array('class' => 'list'));
                        /* Detail */
                        $infoDetail      = get_string('location_floor','local_friadmin') . ': ' . $location->floor;
                        $infoDetail     .= "</br>";
                        $infoDetail     .=  get_string('location_room','local_friadmin') . ': ' . $location->room;
                        $infoDetail     .= "</br>";
                        $infoDetail     .=  get_string('location_seats','local_friadmin') . ': ' . $location->seats;
                        $content .= html_writer::tag('dt', $strDetail);
                        $content .= html_writer::tag('dd', $infoDetail);

                        /* Address  */
                        $infoAddress     = $location->street;
                        $infoAddress    .= "</br>";
                        $infoAddress    .= $location->postcode . ' ' . $location->city;
                        $infoAddress    .= "</br>";
                        $infoAddress    .= $location->muni;
                        $content .= html_writer::tag('dt', $strAddress);
                        $content .= html_writer::tag('dd', $infoAddress);

                        /* Courses  */
                        if ($location->courses) {
                            $coursesLink = self::Get_CoursesLink($location->courses);
                            $coursesLink = implode(',',$coursesLink);
                        }//if_courses

                        $content .= html_writer::tag('dt', $strCourses);
                        $content .= html_writer::tag('dd', $coursesLink);

                        /* Comments */
                        $content .= html_writer::tag('dt', $strComments);
                        $content .= html_writer::tag('dd', $location->comments);

                        /* Contact  */
                        $infoContact     = $location->contact;
                        $infoContact    .= "</br>";
                        $infoContact    .= $location->mail;
                        $infoContact    .= "</br>";
                        $infoContact    .= $location->phone;
                        $content .= html_writer::tag('dt', $strContact);
                        $content .= html_writer::tag('dd', $infoContact);
                    $content .= html_writer::end_tag('dl');
                    $content .= '</br>';
                $content .= html_writer::end_div();//descriptionbox
            $content .= html_writer::end_div();//userprofile

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_ContentDetail

    /**
     * @param           $courses_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the link for all the courses
     */
    private static function Get_CoursesLink($courses_lst) {
        /* Variables    */
        global $DB;
        $coursesLink    = array();
        $link           = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT     id,
                                fullname
                     FROM       {course}
                     WHERE      id IN ($courses_lst)
                     ORDER BY   fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $link = new moodle_url('/course/view.php',array('id' => $instance->id));
                    $coursesLink[$instance->id] = html_writer::link($link,$instance->fullname);
                }//for_each_course
            }//if_rdo

            return $coursesLink;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesLink
}//CourseLocations
