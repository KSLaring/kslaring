<?php

/**
 * Single Sign On Web Services - External Lib
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Implements all functions that the Web Service supply
 * We have to implement the next structure for each function:
 * - function xxxx
 * - function xxxx_parameters
 * - function xxxx_return
 */

require_once('../../config.php');
require_once ($CFG->libdir.'/externallib.php');
require_once ('lib/wsdoskomlib.php');

class local_doskom_external extends external_api {
    /*****************/
    /*  wsLogInUser  */
    /*****************/

    /**
     * @static
     * @return              external_function_parameters
     *
     * @creationDate        20/02/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Describes the parameters of the function. The input has the next structure:
     * Array:
     *      - ID
     *      - Username
     *      - PersonSsn
     *      - First Name
     *      - Last Name
     *      - eMail
     *      - City
     *      - Country
     *      - Company
     *      - Company Id
     *      - Course
     *      - Work Place
     *      - User Roles
     *      - Redirect PAge
     *      - LogoutUrl
     *      - Source
     */
    public static function wsLogInUser_parameters() {
        $user_id        = new external_value(PARAM_TEXT,'User ID - Primary Key from Dossier System');
        $username       = new external_value(PARAM_TEXT,'Username - Username from Dossier System');
        $personSSN      = new external_value(PARAM_TEXT,'Security Social Number');
        $first_name     = new external_value(PARAM_TEXT,'First Name');
        $last_name      = new external_value(PARAM_TEXT,'Last Name');
        $eMail          = new external_value(PARAM_TEXT,'eMail');
        $city           = new external_value(PARAM_TEXT,'City');
        $country        = new external_value(PARAM_TEXT,'Country - (Norway, Sweden ...)');
        $lang           = new external_value(PARAM_TEXT,'lang - (no -> Norwegian; en -> Englih; ...)');
        $companyId      = new external_value(PARAM_TEXT,'Company Id');
        $company        = new external_value(PARAM_TEXT,'Company - (Name Company)');
        $course         = new external_value(PARAM_INT,'Course - Course Id Number');
        $work_place     = new external_value(PARAM_TEXT,'Work Place');
        $rol            = new external_value(PARAM_TEXT,'UserRoles - (manager, coursecreator,editingteacher,teacher,student)');
        $action         = new external_value(PARAM_TEXT,'RedirectPage - It indicates where the user must be redirected when he/she log in. Values : main (main page); enrol (start course - teacher/editteacher); courses (course creator page)');
        $back           = new external_value(PARAM_TEXT,'LogoutUrl - Where the user must be redirected when he/she log on');
        $source         = new external_value(PARAM_TEXT,'From they are comming');

        /* USER SSO */
        $user_sso    = new external_single_structure(array(
                                                            'id'             =>  $user_id,
                                                            'username'       =>  $username,
                                                            'ssn'            =>  $personSSN,
                                                            'firstname'      =>  $first_name,
                                                            'lastname'       =>  $last_name,
                                                            'email'          =>  $eMail,
                                                            'city'           =>  $city,
                                                            'country'        =>  $country,
                                                            'lang'           =>  $lang,
                                                            'companyId'      =>  $companyId,
                                                            'company'        =>  $company,
                                                            'course'         =>  $course,
                                                            'workPlace'      =>  $work_place,
                                                            'UserRoles'      =>  $rol,
                                                            'RedirectPage'   =>  $action,
                                                            'LogoutUrl'      =>  $back,
                                                            'source'         =>  $source));

        return new external_function_parameters(array('user_sso'=> $user_sso));
    }//wsLogInUser_parameters

    /**
     * @static
     * @return          external_single_structure
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes what the function returns. Returns the next structure:
     * Array:
     *      - Error.
     *      - Error Message.
     *      - URL
     */
    public static function wsLogInUser_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');
        $url        = new external_value(PARAM_TEXT,'Url Auto Login - Where the user must be redirected when he/she log in');

        $exist_return = new external_single_structure(array(
                                                            'error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'url'           => $url));

        return $exist_return;
    }//wsLogInUser_returns


    /**
     * @static
     * @param           $usersso
     * @return          array
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Authenticate the user.
     */
    public static function wsLogInUser($usersso) {
        /* Variables    */
        $log     = array();
        $infolog = null;

        // Parameters validation
        $params = self::validate_parameters(self::wsLogInUser_parameters(), array('user_sso' => $usersso));


        // Response from web service
        $result     = array();
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['url']          = '';

        try {
            // Library
            require_once('../../user/profile/field/gender/lib/genderlib.php');

            // Doskom log
            $log = array();

            // Log in
            wsdoskom::log_in_user($usersso,$result,$log);
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsLogInUser';
            $infolog->description = 'ERROR --> ' . $ex->getTraceAsString();
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }//try_catch_exception
    }///wsLogInUser

    /********************/
    /* DEACTIVATED USER */
    /********************/

    /**
     * @static
     * @return          external_function_parameters
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes the parameters of the function. The input has the next structure:
     * Array:
     *      - id. User ID - Primary Key from Dossier System
     *      - companyId.
     */
    public static function wsDeActivateUser_parameters() {
        $user_id        = new external_value(PARAM_TEXT,'User ID - Primary Key from Dossier System');
        $companyId      = new external_value(PARAM_TEXT,'Company Id');


        /* USER SSO */
        $user_sso   = new external_single_structure(array(
                                                           'id'             =>  $user_id,
                                                           'companyId'      =>  $companyId));
        $user_lst   = new external_multiple_structure ($user_sso);

        return new external_function_parameters(array('user_lst'=> $user_lst));
    }//wsDeActivateUser_parameters

    /**
     * @static
     * @return          external_single_structure
     *
     * @creationDate    20/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes what the function returns. Returns the next structure:
     * Array:
     *      - error.
     *      - msg_error.
     */
    public static function wsDeActivateUser_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');

        $exist_return = new external_single_structure(array(
                                                            'error'         => $error,
                                                            'msg_error'     => $msg_error));

        return $exist_return;
    }//wsDeActivateUser_returns

    /**
     * Description
     * Deactivates a specific user.
     *
     * @static
     * @param           $userlst
     * @return          array
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     */
    public static function wsDeActivateUser($userlst) {
        /* Variables */
        $log     = array();
        $infolog = null;

        // Parameters validation
        $params = self::validate_parameters(self::wsDeActivateUser_parameters(), array('user_lst' => $userlst));

        // Response web service
        $result     = array();
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['url']          = '';

        try {
            // Deactivate user
            wsdoskom::deactivate_users($userlst,$result,$log);
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage();
            }//if_error

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsDeActivateUser';
            $infolog->description = 'ERROR --> ' . $ex->getTraceAsString();
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }//try_catch
    }//wsDeActivateUser

    /******************/
    /* COURSE CATALOG */
    /******************/

    /**
     * @static
     * @return          external_function_parameters
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes the parameters of the function. The input has the next structure:
     * Array:
     *      - companyId.
     */
    public static function wsGetCourseCatalog_parameters() {
        $companyId      = new external_value(PARAM_INT,'Company Id');

        $company        = new external_single_structure(array('company' => $companyId));

        return new external_function_parameters(array('company'=> $company));
    }//wsGetCourseCatalog_parameters

    /**
     * @static
     * @return          external_single_structure
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Describes what the function returns. Returns the next structure:
     * Array:
     *      - error.
     *      - msg_error.
     *      - catalog. Array:
     *                      - Category Id.
     *                      - Category Name.
     *                      - Category Description.
     *                      - Category Parent.
     *                      - Courses. Array:
     *                                  - Course Id.
     *                                  - Course Name.
     *                                  - Course Summary.
     *                                  - Course Form.
     */
    public static function wsGetCourseCatalog_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');

        /* Course Catalog   */
        /* Course List  */
        $course_id      = new external_value(PARAM_INT,'Course Id. Unique ID');
        $course_name    = new external_value(PARAM_TEXT,'Course Name');
        $course_summary = new external_value(PARAM_TEXT,'Course Summary');
        $course_form    = new external_value(PARAM_TEXT,'Course Form. Online');

        $course_lst = new external_single_structure(array(
                                                          'courseId'        => $course_id,
                                                          'courseName'      => $course_name,
                                                          'courseSummary'   => $course_summary,
                                                          'courseForm'      => $course_form));

        /* Category List    */
        $category_id        = new external_value(PARAM_INT,'Category ID. Unique ID');
        $category_name      = new external_value(PARAM_TEXT,'Category Name');
        $category_desc      = new external_value(PARAM_TEXT,'Category Description');
        $category_parent    = new external_value(PARAM_INT,'Parent Category');

        $category_lst = new external_single_structure(array(
                                                            'categoryId'        => $category_id,
                                                            'categoryName'      => $category_name,
                                                            'categoryDesc'      => $category_desc,
                                                            'categoryParent'    => $category_parent,
                                                            'courses'           => new external_multiple_structure($course_lst)));


        $exist_return = new external_single_structure(array(
                                                            'error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'catalog'       => new external_multiple_structure($category_lst)));


        return $exist_return;
    }//wsGetCourseCatalog_returns

    /**
     * @static
     * @param           $company
     * @return          array
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the course catalog connected with company
     */
    public static function wsGetCourseCatalog($company) {
        /* Variables */
        $log     = array();
        $infolog = null;
        $catlog  = null;

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsGetCourseCatalog_parameters(), array('company' => $company));

        // Web service response
        $result     = array();
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['catalog']      = array();

        try {
            // Get the course catalog
            list($result['catalog'],$catlog) = wsdoskom::get_course_catalog($company['company'],$result,$log);

            // Write log
            wsdoskom::write_log($log);
            // Write catalog log
            if ($catlog) {
                wsdoskom::add_catalog_log($log);
            }//if_catlog

            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage();
            }//if_error

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsGetCourseCatalog';
            $infolog->description = 'ERROR --> ' . $ex->getTraceAsString();
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }//try_catch
    }//wsGetCourseCatalog

    /*********************************/
    /* HISTORICAL COMPLETION COURSES */
    /*********************************/

    /**
     * @static
     * @return          external_function_parameters
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes the parameters of the function. The input has the next structure:
     * Array:
     *      - companyId
     *      - dateFrom  (yyyy.mm.dd)
     *      - dateTo    (yyyy.mm.dd)
     */
    public static function wsGetAccomplishedCourses_parameters() {
        $company_id    = new external_value(PARAM_INT,'Company ID');
        $date_from     = new external_value(PARAM_TEXT,'Date From');
        $date_to       = new external_value(PARAM_TEXT,'Date To');

        /* Search Criteria  */
        $criteria = new external_single_structure(array(
                                                        'companyId' => $company_id,
                                                        'dateFrom'  => $date_from,
                                                        'dateTo'    => $date_to));


        return new external_function_parameters(array('criteria'=> $criteria));
    }//wsGetAccomplishedCourses_parameters


    /**
     * @static
     * @return          external_single_structure
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describes what the function returns. Returns the next structure:
     * Array:
     *      - error.
     *      - msg_error.
     *      - courses.  Array:
     *                      - courseId
     *                      - courseName
     *                      - users. Array:
     *                                  - completionId
     *                                  - userId. Primary Key from Dossier System
     *                                  - completionDate (yyyy.mm.dd)
     */
    public static function wsGetAccomplishedCourses_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');

        /* Users Completed          */
        $completion_id      = new external_value(PARAM_INT,'Completion Course ID. Unique Id ');
        $user_id            = new external_value(PARAM_TEXT,'User ID - Primary Key from Dossier System');
        $completion_date    = new external_value(PARAM_TEXT,'Completion Date (yyyy.mm.dd)');

        $users = new external_single_structure(array(
                                                     'completionId'    => $completion_id,
                                                     'userId'           => $user_id,
                                                     'completionDate'   => $completion_date));
        /* List Completed Courses  */
        $course_id          = new external_value(PARAM_INT,'Course Id');
        $course_name        = new external_value(PARAM_TEXT,'Course Name (Short name or Full name)');

        $courses            = new external_single_structure(array(
                                                                  'courseId'        => $course_id,
                                                                  'courseName'      => $course_name,
                                                                  'users'           => new external_multiple_structure($users)));

        $exist_return = new external_single_structure(array(
                                                            'error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'courses'       => new external_multiple_structure($courses)));


        return $exist_return;
    }//wsGetAccomplishedCourses_returns

    /**
     * @static
     * @param           $criteria
     * @return          array
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the historical completion courses.
     */
    public static function wsGetAccomplishedCourses($criteria) {
        /* Variables */
        $log            = null;
        $infolog        = null;
        $historicallog  = null;

        // Parameters validation
        $params = self::validate_parameters(self::wsGetAccomplishedCourses_parameters(), array('criteria' => $criteria));
        
        // Web service response
        $result     = array();
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['courses']      = array();

        try {
            list($result['courses'],$historicallog) = wsdoskom::get_historical_courses_completion($criteria,$result,&$log);

            // Historical log
            if ($historicallog) {
                wsdoskom::update_log_historical($historicallog);
            }//if_log
            // Write log
            wsdoskom::write_log($log);
            return $result;
        }catch (Exception $ex) {
            if ($result['error'] == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage();
            }//if_error

            // DOSKOM log
            $infolog = new stdClass();
            $infolog->action      = 'wsGetAccomplishedCourses';
            $infolog->description = 'ERROR --> ' . $ex->getTraceAsString();
            $infolog->timecreated = time();
            // Add log
            $log[] = $infolog;
            // Write log
            wsdoskom::write_log($log);

            return $result;
        }//try_catch
    }//wsGetAccomplishedCourses


    /*****************************/
    /*****************************/
    /*****************************/

    /**
     * @static
     * @param           external_description $description
     * @param           mixed $params
     * @return          array|bool|mixed|null
     * @throws          moodle_exception
     * @throws          invalid_parameter_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate parameters received
     */
    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                // special case for PARAM_BOOL - we want true/false instead of the usual 1/0 - we can not be too strict here ;-)
                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                '", the server was expecting "' . $description->type . '" type';
            return self::validate_param($params, $description->type, $description->allownull, $debuginfo);
        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new moodle_exception('generalexceptionmessage','error',null,'Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = self::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                            //we are only interested by exceptions returned by validate_param() and validate_parameters()
                            //(in order to build the path to the faulty attribut)
                            throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = self::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                        //we are only interested by exceptions returned by validate_param() and validate_parameters()
                        //(in order to build the path to the faulty attribut)
                        throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = self::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new moodle_exception('generalexceptionmessage','error',null,'Last Step');
        }
    }//validate_parameters

    /**
     * @static
     * @param           $param
     * @param           $type
     * @param           bool $allownull
     * @param           string $debuginfo
     * @return          mixed|null
     * @throws          moodle_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the parameter
     */
    public static function validate_param($param, $type, $allownull=NULL_NOT_ALLOWED, $debuginfo='') {
        if (is_null($param)) {
            if ($allownull == NULL_ALLOWED) {
                return null;
            } else {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        }
        if (is_array($param) or is_object($param)) {
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        $cleaned = clean_param($param, $type);

        if ($type == PARAM_FLOAT) {
            // Do not detect precision loss here.
            if (is_float($param) or is_int($param)) {
                // These always fit.
            } else if (!is_numeric($param) or !preg_match('/^[\+-]?[0-9]*\.?[0-9]*(e[-+]?[0-9]+)?$/i', (string)$param)) {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        } else if ((string)$param !== (string)$cleaned) {
            // Conversion to string is usually lossless.
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        return $cleaned;
    }
}//local_doskom_external



