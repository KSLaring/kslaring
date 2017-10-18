<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * DOSKOM Actions library
 *
 * @package         local
 * @subpackage      doskom/lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    04/09/2017
 * @author          eFaktor     (fbv)
 *
 */
define('SOURCE',0);
define('ADD_SOURCE',10);
define('EDIT_SOURCE',11);
define('DELETE_SOURCE',12);
define('ACTIVATE_SOURCE',14);
define('DEACTIVATE_SOURCE',15);

define('COMPANIES',1);
define('ADD_COMPANY',20);
define('EDIT_COMPANY',21);
define('DELETE_COMPANY',22);

class actionsdk {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Check if there is other source with a specific value in a field
     *
     * @param           $source
     * @param           $field
     * @param           $value
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function source_exist($source,$field,$value) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Serch criteria
            $params = array();
            $params['source']   = $source;


            // SQL Instruction
            $sql = " SELECT id
                     FROM   {doskom} 
                     WHERE   $field = '" . $value . "' 
                        AND  id    != :source ";
            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//source_exist

    /**
     * Description
     * Check if there is other company with a specific value in a field
     *
     * @param           $field
     * @param           $value
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    08/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function company_field_exist($field,$value) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Serch criteria
            $params = array();
            $params[$field] = $value;

            // Execute
            $rdo = $DB->get_record('company_data',$params,'id');
            if ($rdo) {
                return true;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_id_exist

    /**
     * Description
     * Apply the action connected with the source
     *
     * @param           $action
     * @param           $data
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function process_action_source($action,$data) {
        /* Variables */
        $error  = false;
        $dkco   = null;

        try {
            switch ($action) {
                case ADD_SOURCE:
                    $error = self::add_source($data);

                    break;
                case EDIT_SOURCE:
                    $error = self::update_source($data);

                    break;
                case DELETE_SOURCE:
                    $error = self::delete_source($data->id);

                    break;
                case ACTIVATE_SOURCE:
                    $error = self::change_status($data,1);

                    break;
                case DEACTIVATE_SOURCE:
                    $error = self::change_status($data,0);

                    break;
            }//action

            return $error;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_action_source

    /**
     * Description
     * Apply the action connected with the company
     *
     * @param           $action
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function process_action_company($action,$data) {
        /* Variables */
        $error  = false;

        try {
            switch ($action) {
                case ADD_COMPANY:
                    $error = self::add_company_source($data);

                    break;
                case EDIT_COMPANY:
                    $error = self::update_company_source($data);

                    break;
                case DELETE_COMPANY:
                    $error = self::delete_company($data->id);

                    break;
                case ACTIVATE_SOURCE:
                    $error = self::change_status($data,1);

                    break;
                case DEACTIVATE_SOURCE:
                    $error = self::change_status($data,0);

                    break;
            }//action

            return $error;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_action_company

    /**
     * Description
     * Get a list of all available sources
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_list_sources() {
        /* Variables */
        global $DB;
        $rdo        = null;
        $lstsources = null;

        try {
            // Ad first element
            $lstsources = array();
            $lstsources[0] = get_string('strselone','local_doskom');

            // Get sourses
            $rdo = $DB->get_records('doskom',null,'api','id,api');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstsources[$instance->id] = $instance->api;
                }//for_Rdo
            }//if_rdo

            return $lstsources;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_list_sources

    /**
     * Description
     * Get source
     *
     * @param           $id
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_source($id) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['id'] = $id;

            // SQL Instruction
            $sql = " SELECT	      dk.*,
                                  count(dkco.companyid) as 'companies'
                     FROM		  {doskom}			dk
                        LEFT JOIN {doskom_company}	dkco	ON dkco.doskomid = dk.id
                     WHERE	      dk.id = :id ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_source

    /**
     * Description
     * Get company data
     *
     * @param           $id
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_doskom_company($id) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['company'] = $id;

            // SQL Instruction
            $sql = " SELECT   co.id ,
                              co.name,
                              co.user,
                              co.token,
                              dkco.id 	  as 'dkco',
                              dk.id 	  as 'source',
                              dkco.active
                     FROM	  {company_data}	co
                        JOIN  {doskom_company}	dkco ON dkco.companyid 	= co.id
                        JOIN  {doskom}		  	dk	ON dk.id 			= dkco.doskomid
                     WHERE 	  co.id = :company; ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_doskom_company


    /**
     * Description
     * Get sources and companies connected with
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     *
     */
    public static function get_sources_companies() {
        /* Variables */
        global $DB;
        $rdo = null;
        $sql = null;

        try {
            // SQL instruction
            $sql = " SELECT	      CONCAT(dk.id,'_',IF(dk_co.companyid,dk_co.companyid,0))	as 'id',
                                  IF(dk_co.companyid,dk_co.companyid,0) 	                as 'companyid',
                                  dk.id 									                as 'dkid',
                                  dk.api,
                                  dk.label,
                                  cd.name,
                                  dk_co.active
                     FROM		  {doskom}		    dk
                        LEFT JOIN {doskom_company}  dk_co ON dk_co.doskomid = dk.id
                        LEFT JOIN {company_data}	cd	  ON  cd.id 		= dk_co.companyid
                     ORDER BY dk.api, cd.name ";

            // Execute
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sources_companies

    /**
     * Description
     * Get all companies and sources connected with
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_companies_sources() {
        /* Variables */
        global $DB;
        $rdo = null;
        $sql = null;

        try {
            // SQL Instruction
            $sql = " SELECT   co.id 	as 'companyid',
                              co.name,
                              dk.id 	as 'dkid',
                              dk.api,
                              dk.label,
                              dkco.active
                     FROM	  {company_data}	co
                        JOIN  {doskom_company}	dkco  ON dkco.companyid = co.id
                        JOIN  {doskom}		  	dk	  ON dk.id 			= dkco.doskomid
                     ORDER BY	co.name, dk.api ";

            // Execute
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cacth
    }//get_companies_sources

    /**
     * Description
     * Display sources table
     *
     * @param           $lstsources
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    05/09/2019
     * @author          eFaktor     (fbv)
     */
    public static function display_sources($lstsources) {
        /* Variables */
        global $OUTPUT;
        $content = '';
        $url     = null;
        $lnk     = null;

        try {
            // Header
            $content .= $OUTPUT->heading(get_string('headersource','local_doskom'));

            // Block doskom
            $content .= html_writer::start_div('block_doskom');
                // Add doskom sources table
                $content .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    // header
                    $content .= self::add_source_header_table();
                    // content
                    $content .= self::add_source_content_table($lstsources);
                $content .= html_writer::end_tag('table');
            $content .= html_writer::end_div();//block_doskom

            // Link to add source / Back
            $content .= html_writer::start_div('block_doskom_lnks');
                $content .= html_writer::start_div('dk_left');
                    $url = new moodle_url('/local/doskom/actions/sources.php',array('a' => ADD_SOURCE));
                    $lnk = '<a href="' . $url . '" class="lnk_add">' . get_string('addsource','local_doskom') . '</a>';
                    $content .= $lnk;
                $content .= html_writer::end_div();//dk_left
                $content .= html_writer::start_div('dk_right');
                    $url = new moodle_url('/admin/settings.php?section=local_doskom');
                    $lnk = '<a href="' . $url . '">' . get_string('back') . '</a>';
                    $content .= $lnk;
                $content .= html_writer::end_div();//dk_right
            $content .= html_writer::end_div();//div_doskom

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_sources

    /**
     * Description
     * Display companies doskom table
     *
     * @param           $lstcompanies
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function display_companies($lstcompanies) {
        /* Variables */
        global $OUTPUT;
        $content = '';
        $url     = null;
        $lnk     = null;

        try {
            // Header
            $content .= $OUTPUT->heading(get_string('headercompany','local_doskom'));

            // Block doskom
            $content .= html_writer::start_div('block_doskom');
                // Add doskom sources table
                $content .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    // header
                    $content .= self::add_company_header_table();
                    // content
                    $content .= self::add_company_content_table($lstcompanies);
                $content .= html_writer::end_tag('table');
            $content .= html_writer::end_div();//block_doskom

            // Link to add source / Back
            $content .= html_writer::start_div('block_doskom_lnks');
                $content .= html_writer::start_div('dk_left');
                    $url = new moodle_url('/local/doskom/actions/companies.php',array('a' => ADD_COMPANY));
                    $lnk = '<a href="' . $url . '" class="lnk_add">' . get_string('addcomp','local_doskom') . '</a>';
                    $content .= $lnk;
                $content .= html_writer::end_div();//dk_left
                $content .= html_writer::start_div('dk_right');
                    $url = new moodle_url('/admin/settings.php?section=local_doskom');
                    $lnk = '<a href="' . $url . '">' . get_string('back') . '</a>';
                    $content .= $lnk;
                $content .= html_writer::end_div();//dk_right
            $content .= html_writer::end_div();//div_doskom

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_companies

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Deactivate or activate the connection between one company and one source
     *
     * @param           $data
     * @param           $status
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function change_status($data,$status) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $error  = false;
        $dkco   = null;

        try {
            // Extract data
            $dkco = new stdClass();
            $dkco->companyid = $data->id;
            $dkco->doskomid  = $data->source;
            $dkco->id        = $data->dkco;
            $dkco->active    = $status;

            // Update source
            $error = $DB->update_record('doskom_company',$dkco);

            return $error;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//change_status

    /**
     * Description
     * Add a new source
     *
     * @param           $data
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_source($data) {
        /* Variables */
        global $DB;

        try {
            $data->timecreated = time();
            $data->id = $DB->insert_record('doskom',$data);

            return $data->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_source

    /**
     * Description
     * Update the source
     *
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/0972017
     * @author          eFaktor     (fbv)
     */
    private static function update_source($data) {
        /* Variables */
        global $DB;

        try {
            // Update source
            return $DB->update_record('doskom',$data);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_source

    /**
     * Description
     * Delete a source
     *
     * @param           $id
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function delete_source($id) {
        /* Variables */
        global $DB;
        $trans  = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Delete source
            $DB->delete_records('doskom',array('id' => $id));

            // Clean doskom_company
            $DB->delete_records('doskom_company',array('doskomid' => $id));

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//delete_source

    /**
     * Description
     * Create a new comapny and its connection with its source
     *
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_company_source($data) {
        /* Variables */
        global $DB;
        $trans  = null;
        $dkco   = null;
        $sql    = null;
        $trans  = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Source - company
            $dkco = new stdClass();
            $dkco->doskomid     = $data->source;
            $dkco->companyid    = $data->id;
            $dkco->active       = (isset($data->active) && $data->active ? 1 :0);
            // Execute
            $dkco->id = $DB->insert_record('doskom_company',$dkco);

            // Company
            unset($data->source);
            unset($data->active);
            unset($data->dkco);
            // SQL instrution to create the company
            $sql = " INSERT INTO {company_data}(id,name,user,token,timecreated) 
                          VALUES("  . $data->id         . ","
                . "'" . $data->name . "',"
                . "'" . $data->user . "',"
                . "'" . $data->token ."'," . time() . ")";
            // Execute
            $DB->execute($sql);

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//add_company_source

    /**
     * Description
     * Update company data
     *
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function update_company_source($data) {
        /* Variables */
        global $DB;
        $trans  = null;
        $dkco   = null;
        $trans  = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Source - company
            $dkco = new stdClass();
            $dkco->doskomid     = $data->source;
            $dkco->companyid    = $data->id;
            $dkco->active       = (isset($data->active) && $data->active ? 1 :0);
            $dkco->id           = $data->dkco;
            // Execute
            $DB->update_record('doskom_company',$dkco);

            // Update company
            unset($data->source);
            unset($data->active);
            unset($data->dkco);
            // Execute
            $DB->update_record('company_data',$data);

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//update_company_source
    
    /**
     * Description
     * Delete a company
     *
     * @param           $id
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function delete_company($id) {
        /* Variables */
        global $DB;
        $trans = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Delete company
            $DB->delete_records('company_data',array('id' => $id));

            // Clean doskom_company
            $DB->delete_records('doskom_company',array('companyid' => $id));

            // Clean user_company
            $DB->delete_records('user_company',array('companyid' => $id));

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//delete_company

    /**
     * Description
     * Companies header table
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_company_header_table() {
        /* Variables */
        $header     = null;
        $strsource  = null;
        $strcompany = null;
        $stractions = null;

        try {
            // Get headers
            $strsource  = get_string('strsource','local_doskom');
            $strcompany = get_string('strcompany','local_doskom');
            $stractions = get_string('stractions','local_doskom');

            // Add headers
            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_doskom'));
                    // Company
                    $header .= html_writer::start_tag('th',array('class' => 'comp'));
                        $header .= $strcompany;
                    $header .= html_writer::end_tag('th');
                    // Source
                    $header .= html_writer::start_tag('th',array('class' => 'source'));
                        $header .= $strsource;
                    $header .= html_writer::end_tag('th');
                    // Actions
                    $header .= html_writer::start_tag('th',array('class' => 'act'));
                        $header .= $stractions;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_company_header_table

    /**
     * Description
     * Companies content table
     * 
     * @param           $lstcompanies
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_company_content_table($lstcompanies) {
        /* Variables */
        global $OUTPUT;
        $strsource  = null;
        $strcompany = null;
        $stractions = null;
        $content    = null;
        $company    = null;
        $edit       = null;
        $delete     = null;
        $hide       = null;
        $show       = null;
        // Link to source
        $url        = null;
        // Actions link
        $urlact     = null;
        // Link delete
        $urldel     = null;

        try {
            // Get headers
            $strsource  = get_string('strsource','local_doskom');
            $strcompany = get_string('strcompany','local_doskom');
            $stractions = get_string('stractions','local_doskom');

            // Icons
            $edit   = $OUTPUT->pix_icon('t/edit', get_string('edit'));
            $delete = $OUTPUT->pix_icon('t/delete',get_string('delete'));
            $hide   = $OUTPUT->pix_icon('t/hide',get_string('hide'));
            $show   = $OUTPUT->pix_icon('t/show',get_string('show'));

            // Link source
            $url = new moodle_url('/local/doskom/actions/sources.php');;
            $url->param('a',EDIT_SOURCE);

            // Link Delete
            $urldel = new moodle_url('/local/doskom/actions/delete.php');;
            $urldel->param('t',COMPANIES);

            // Actions url
            $urlact = new moodle_url('/local/doskom/actions/companies.php');

            // Add content
            if ($lstcompanies) {
                foreach ($lstcompanies as $company) {
                    // Set source id
                    $url->param('id',$company->dkid);

                    // Set company id
                    $urlact->param('id',$company->companyid);

                    $content .=  html_writer::start_tag('tr');
                        // Company
                        $content .= html_writer::start_tag('td',array('class' => 'comp','data-th' => $strcompany));
                            $content .= $company->name;
                        $content .= html_writer::end_tag('td');
                        // Source
                        $content .= html_writer::start_tag('td',array('class' => 'source','data-th' => $strsource));
                            $content .= html_writer::link($url, $company->api); ;
                        $content .= html_writer::end_tag('td');
                        // Actions
                        $content .= html_writer::start_tag('td',array('class' => 'act','data-th' => $stractions));
                            // Edit icon
                            $urlact->param('a',EDIT_COMPANY);
                            $content .= html_writer::link($urlact, $edit,array('class' => 'lnk_act'));
                            // Delete icon
                            $urldel->param('id',$company->companyid);
                            $content .= html_writer::link($urldel, $delete,array('class' => 'lnk_act'));
                            // Show/Hide
                            if ($company->active) {
                                $urlact->param('a',DEACTIVATE_SOURCE);
                                $content .= html_writer::link($urlact, $hide,array('class' => 'lnk_act'));
                            }else {
                                $urlact->param('a',ACTIVATE_SOURCE);
                                $content .= html_writer::link($urlact, $show,array('class' => 'lnk_act'));
                            }//if_activate
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_source
            }//if_lstsources

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_company_content_table

    /**
     * Description
     * Add source header table
     *
     * @return      null|string
     * @throws      Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_source_header_table() {
        /* Variables */
        $header     = null;
        $strsource  = null;
        $strlabel   = null;
        $strcompany = null;
        $stractions = null;

        try {
            // Get headers
            $strsource  = get_string('strsource','local_doskom');
            $strlabel   = get_string('strlabel','local_doskom');
            $strcompany = get_string('strcompany','local_doskom');
            $stractions = get_string('stractions','local_doskom');

            // Add headers
            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_doskom'));
                    // Source
                    $header .= html_writer::start_tag('th',array('class' => 'source'));
                        $header .= $strsource;
                    $header .= html_writer::end_tag('th');
                    // Label
                    $header .= html_writer::start_tag('th',array('class' => 'lbldk'));
                        $header .= $strlabel;
                    $header .= html_writer::end_tag('th');
                    // Company
                    $header .= html_writer::start_tag('th',array('class' => 'comp'));
                        $header .= $strcompany;
                    $header .= html_writer::end_tag('th');
                    // Actions
                    $header .= html_writer::start_tag('th',array('class' => 'act'));
                        $header .= $stractions;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_source_header_table

    /**
     * Description
     * Add source content table
     *
     * @param           $lstsources
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    05/07/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_source_content_table($lstsources) {
        /* Variables */
        global $OUTPUT;
        $content    = null;
        $source     = null;
        $strsource  = null;
        $strlabel   = null;
        $strcompany = null;
        $stractions = null;
        $edit       = null;
        $delete     = null;
        $activate   = null;
        $hide       = null;
        $show       = null;
        // Link to company
        $url        = null;
        // Actions link
        $urlact     = null;
        // Link delete
        $urldel     = null;

        try {
            // Headers
            $strsource  = get_string('strsource','local_doskom');
            $strlabel   = get_string('strlabel','local_doskom');
            $strcompany = get_string('strcompany','local_doskom');
            $stractions = get_string('stractions','local_doskom');

            // Icons
            $edit   = $OUTPUT->pix_icon('t/edit', get_string('edit'));
            $delete = $OUTPUT->pix_icon('t/delete',get_string('delete'));
            $hide   = $OUTPUT->pix_icon('t/hide',get_string('hide'));
            $show   = $OUTPUT->pix_icon('t/show',get_string('show'));

            // Link company
            $url = new moodle_url('/local/doskom/actions/companies.php');
            $url->param('a',EDIT_COMPANY);

            // Actions url
            $urlact = new moodle_url('/local/doskom/actions/sources.php');

            // Link Delete
            $urldel = new moodle_url('/local/doskom/actions/delete.php');;
            $urldel->param('t',SOURCE);

            // Add content
            if ($lstsources) {
                foreach ($lstsources as $source) {
                    // For each one is different
                    $urlact->remove_params('co');
                    // Set source id
                    $urlact->param('id',$source->dkid);

                    // Set company id
                    $url->param('id',$source->companyid);

                    $content .=  html_writer::start_tag('tr');
                        // Source
                        $content .= html_writer::start_tag('td',array('class' => 'source','data-th' => $strsource));
                            $content .= $source->api;
                        $content .= html_writer::end_tag('td');
                        // Label
                        $content .= html_writer::start_tag('td',array('class' => 'lbldk','data-th' => $strlabel));
                            $content .= $source->label;
                        $content .= html_writer::end_tag('td');
                        // Company
                        $content .= html_writer::start_tag('td',array('class' => 'comp','data-th' => $strcompany));
                            $content .= html_writer::link($url, $source->name);
                        $content .= html_writer::end_tag('td');
                        // Actions
                        $content .= html_writer::start_tag('td',array('class' => 'act','data-th' => $stractions));
                            // Edit icon
                            $urlact->param('a',EDIT_SOURCE);
                            $content .= html_writer::link($urlact, $edit,array('class' => 'lnk_act'));
                            // Delete icon
                            $urldel->param('id',$source->dkid);
                            $content .= html_writer::link($urldel, $delete,array('class' => 'lnk_act'));
                            // Show/Hide
                            if ($source->companyid) {
                                $urlact->param('co',$source->companyid);
                                if ($source->active) {
                                    $urlact->param('a',DEACTIVATE_SOURCE);
                                    $content .= html_writer::link($urlact, $hide,array('class' => 'lnk_act'));
                                }else {
                                    $urlact->param('a',ACTIVATE_SOURCE);
                                    $content .= html_writer::link($urlact, $show,array('class' => 'lnk_act'));
                                }//if_activate
                            }else {
                                $content .= html_writer::link($urlact, $show,array('class' => 'lnk_act lnk_disabled'));
                            }//if_companyid
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_source
            }//if_lstsources

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_source_content_table

}//doskom