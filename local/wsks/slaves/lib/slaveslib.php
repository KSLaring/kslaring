<?php
/**
 * Web Services KS - Library
 *
 * @package         local/wsks
 * @subpackage      slaves/lib
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
 * @author          eFaktor     (fbv)
 */
define('ERR_NONE',0);
define('ERR_SLAVE_SERVICE',1);
define('ERR_NO_DOMAINS',2);
define('ERR_GENERIC',3);

class Slaves {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $slave
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the slave system already exists or not
     */
    public static function CheckSlaveSystem($slave) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            /**
             * Search criteria
             */
            $params = array();
            $params['slave'] = $slave;

            /**
             * Execute
             */
            $rdo = $DB->get_record('external_slaves',$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_Else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CheckSlaveSystem

    /**
     * @param           $slaveId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get slave detail
     */
    public static function GetSlave($slaveId) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            /**
             * Search criteria
             */
            $params = array();
            $params['id'] = $slaveId;

            /**
             * Execute
             */
            $rdo = $DB->get_record('external_slaves',$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSlave

    /**
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all slaves systems connected with
     */
    public static function GetSlavesSystems() {
        /* Variables */
        global $DB;
        $lstSlaves  = null;
        $infoSlave  = null;
        $sql        = null;
        $rdo        = null;
        
        try {
            /**
             * Sql Instruction
             */
            $sql = " SELECT	es.id,
                            es.slave,
                            es.token,
                            GROUP_CONCAT(DISTINCT se.name ORDER BY se.name SEPARATOR '#SE#') as 'my_services'
                     FROM		{external_slaves}			es
                        JOIN	{external_slaves_services}	es_se	ON  es_se.slaveid 	= es.id
                        JOIN	{external_services}			se		ON	se.id 			= es_se.serviceid
                     GROUP BY es.id ";

            /**
             * Slaves Systems
             */
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSlavesSystems

    /**
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get available services
     */
    public static function GetServices() {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $lstServices    = null;

        try {
            /**
             * Services List
             */
            $lstServices = array();
            $lstServices[0] = get_string('select','local_wsks');
            
            /**
             * Search Criteria
             */
            $params = array();
            $params['enabled']      = 1;
            $params['component']    = 'local_wsks';

            /**
             * Execute
             */
            $rdo = $DB->get_records('external_services',$params,'name','id,name');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstServices[$instance->id] = $instance->name;
                }
            }
            
            return $lstServices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetServices

    /**
     * @param           $data
     *
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new slave system
     */
    public static function Process_New_SlaveSystem($data) {
        /* Variables */
        global $DB;
        $time       = null;
        $instance   = null;
        $trans      = null;
        $myService  = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /**
             * Local time
             */
            $time = time();

            /**
             * New Slave System
             */
            $instance = new stdClass();
            $instance->slave        = $data->slave;
            $instance->token        = $data->token;
            $instance->timecreated  = $time;
            $instance->timemodified = $time;

            /**
             * Execute
             */
            $instance->id = $DB->insert_record('external_slaves',$instance);

            /**
             * Add services connected with
             */
            foreach ($data->services as $service) {
                if ($service) {
                    $myService = new stdClass();
                    $myService->slaveid     = $instance->id;
                    $myService->serviceid   = $service;

                    /**
                     * Execute
                     */
                    $myService->id = $DB->insert_record('external_slaves_services',$myService);
                }
            }//for_services
            
            /**
             * Commit
             */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /**
             * Rollback
             */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Process_New_SlaveSystem

    /**
     * @param           $slaveId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete a slave system given
     */
    public static function Delete_SlaveSystem($slaveId) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;
        $trans  = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /**
             * Search criteria  
             */
            $params = array();
            $params['id'] = $slaveId;
            
            /**
             * Check if it still exists
             */
            $rdo = $DB->get_record('external_slaves',$params);
            if ($rdo) {
                /**
                 * Delete External Slaves
                 */
                $DB->delete_records('external_slaves',$params);
                /**
                 * Delete Exteranl Slave Service connections
                 */
                unset($params['id']);
                $params['slaveid'] = $slaveId;
                $DB->delete_records('external_slaves_services',$params);
            }

            /**
             * Commit
             */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /**
             * Rollback
             */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Delete_SlaveSystem

    /**
     * @param           $serviceId
     *
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update slaves systems with the new token connected with main service
     */
    public static function Process_Update_SlavesSystems($serviceId) {
        /* Variables */
        global $DB,$USER;
        $pluginInfo = null;
        $lstDomains = null;
        $infoDomain = null;
        $wsService  = null;
        $msgErr     = null;
        $infoLog    = null;

        try {
            /**
             * Plugin Info
             */
            $pluginInfo     = get_config('local_wsks');

            /*
             * Slave Service
             */
            $wsService = $pluginInfo->slaves_service;
            if ($wsService) {
                /**
                 * Get domains to update
                 */
                $lstDomains = self::GetDomains($serviceId);
                if ($lstDomains) {
                    foreach ($lstDomains as $infoDomain) {
                        /**
                         * Update slave system
                         */
                        $infoDomain->response = self::ProcessService($infoDomain,$wsService);
                        /**
                         * Add Log
                         */
                        $infoLog = new stdClass();
                        $infoLog->slaveid       = $infoDomain->id;
                        $infoLog->serviceid     = $serviceId;
                        $infoLog->updated       = $infoDomain->response['updated'];
                        $infoLog->message       = $infoDomain->response['msg_error'];
                        $infoLog->updatedby     = $USER->id;
                        $infoLog->timeupdated   = time();

                        /**
                         * Execute
                         */
                        $DB->insert_record('external_slaves_services_log',$infoLog);

                        if ($infoDomain->response['error'] != '200') {
                            $msgErr = ERR_SLAVE_SERVICE;
                        }
                    }//for_domains

                    if (!$msgErr) {
                        $msgErr = ERR_NONE;
                    }
                }else {
                    $msgErr = ERR_NO_DOMAINS;
                }
            }else {
                $msgErr = ERR_SLAVE_SERVICE;
            }

            return $msgErr;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Process_Update_SlavesSystems


    /**
     * @param           $lstSlaves
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display slaves systems list into a table
     */
    public static function Display_SlavesSystems($lstSlaves) {
        /* Variables */
        $out        = null;
        $addUrl     = null;
        $updateUrl  = null;
        
        try {
            /**
             * Link to Add a new Slave
             * Link to update slaves systems
             */
            $addUrl = new moodle_url('/local/wsks/slaves/classes/add_slave.php');
            $updateUrl = new moodle_url('/local/wsks/slaves/classes/update_slaves.php');

            /* Add Block    */
            if ($lstSlaves) {
                $out .= html_writer::start_div('block_slaves');
                    /**
                     * Add slaves list
                     */
                    $out .= html_writer::start_div('lst_slaves');
                        /* Table    */
                        $out .= html_writer::start_tag('table',array('class' => 'generaltable'));
                            /**
                             * Headers
                             */
                            $out .= self::AddSlaves_Header();
                            /**
                             * Content
                             */
                            $out .= self::AddSlaves_Content($lstSlaves);
                        $out .= html_writer::end_tag('table');
                    $out .= html_writer::end_div();//lst_slaves
                    /**
                     * Link to add an slave system
                     * Link to update slaves systems
                     */
                    $out .= html_writer::start_tag('div',array('class' => 'div_button_slaves'));
                        $out .= html_writer::link($addUrl,get_string('add_slave','local_wsks'),array('class' => 'lnk_slaves'));
                        $out .= html_writer::link($updateUrl,get_string('update_slaves','local_wsks'),array('class' => 'lnk_slaves'));
                    $out .= html_writer::end_tag('div');
                $out .= html_writer::end_div();//block_slaves
            }else {
                /* Non Data */
                $out     = '<h4>' . get_string('no_data', 'local_wsks') . '</h4>';
                /**
                 * Link to ad an slave system
                 */
                $out .= html_writer::start_tag('div',array('class' => 'div_button_slaves'));
                    $out .= html_writer::link($addUrl,get_string('add_slave','local_wsks'),array('class' => 'lnk_slaves'));
                $out .= html_writer::end_tag('div');
            }
            
            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Display_SlavesSystems

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $serviceId
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all slaves connected with the service that has to be updated
     */
    private static function GetDomains($serviceId) {
        /* Variables    */
        global $DB;
        $lstDomains     = array();
        $infoDomain     = null;
        $rdo            = null;
        $params         = null;
        $sql            = null;

        try {
            /**
             * Search criteria
             */
            $params = array();
            $params['service'] = $serviceId;

            /**
             * SQL Instruction
             */
            $sql = " SELECT	es.id,
                            es.slave,
                            es.token,
                            se.name,
                            se_to.token as 'new_token'
                     FROM		{external_slaves}			es
                        JOIN	{external_slaves_services}	es_se	ON  es_se.slaveid 	        = es.id
                                                                    AND es_se.serviceid	        = :service
                        JOIN	{external_services}		    se		ON	se.id 			        = es_se.serviceid
                        JOIN	{external_tokens}			se_to	ON	se_to.externalserviceid	= se.id ";

            /**
             * Execute
             */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* New Domain */
                    $infoDomain = new stdClass();
                    $infoDomain->id             = $instance->id;
                    $infoDomain->server         = trim($instance->slave);
                    $infoDomain->server_token   = trim($instance->token);
                    $infoDomain->service        = trim($instance->name);
                    $infoDomain->token          = trim($instance->new_token);
                    $infoDomain->response       = null;

                    /**
                     * Add domain
                     */
                    $lstDomains[$instance->id] = $infoDomain;
                }
            }//if_rdo

            return $lstDomains;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDomains

    /**
     * @param           $domain
     * @param           $wsService
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the call to slave service to update the service from the main system
     */
    private static function ProcessService($domain,$wsService) {
        /* Variables    */
        global $CFG;
        $server     = null;
        $rdo        = null;
        $params     = null;
        $response   = null;

        try {
            // Params for the web service
            $info = new stdClass();
            $info->name     = $domain->service;
            $info->main     = $CFG->wwwroot;
            $info->token    = $domain->token;

            $params = array('service' => $info);

            // Build end Point Service
            $server =  $domain->server . '/webservice/rest/server.php?wstoken=' .  $domain->server_token . '&wsfunction=' . $wsService .'&moodlewsrestformat=json';

            // Paramters web service
            $fields = http_build_query( $params );
            $fields = str_replace( '&amp;', '&', $fields );

            // Call service
            $ch = curl_init($server);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Length: ' . strlen( $fields ) ) );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields );

            $response = curl_exec( $ch );

            if( $response === false ) {
                $error = curl_error( $ch );
            }

            curl_close( $ch );

            $result = json_decode($response);

            // Conver to array
            if (!is_array($result)) {
                $result = (Array)$result;
            }

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ProcessService

    /**
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add headers to slaves table
     */
    private static function AddSlaves_Header() {
        /* Variables */
        $header         = null;
        $strSite        = null;
        $strToken       = null;
        $strServices    = null;

        try {
            /**
             * Headers
             */
            $strSite        = get_string('site','local_wsks');
            $strToken       = get_string('token','local_wsks');
            $strServices    = get_string('services','local_wsks');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_slaves'));
                    /* Site     */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strSite;
                    $header .= html_writer::end_tag('th');
                    /* Token    */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strToken;
                    $header .= html_writer::end_tag('th');
                    /* Services */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strServices;
                    $header .= html_writer::end_tag('th');
                    /* Action   */
                    $header .= html_writer::start_tag('th',array('class' => 'action'));
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSlaves

    /**
     * @param           $lstSlaves
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    08/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to the slaves table
     */
    private static function AddSlaves_Content($lstSlaves) {
        /* Variables */
        global $OUTPUT;
        $content    = null;
        $urlDel     = null;
        
        try {
            $urlDel = new moodle_url('/local/wsks/slaves/classes/delete_slave.php');
            foreach ($lstSlaves as $slave) {

                $content .= html_writer::start_tag('tr');
                    /* Site     */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $slave->slave;
                    $content .= html_writer::end_tag('td');
                    /* Token    */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $slave->token;
                    $content .= html_writer::end_tag('td');
                    /* Services */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= str_replace('#SE#',"</br>",$slave->my_services);
                    $content .= html_writer::end_tag('td');
                    /* Action   */
                    $content .= html_writer::start_tag('td',array('class' => 'action'));
                        $urlDel->param('id',$slave->id);
                        $content .= html_writer::link($urlDel,
                                                      html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                                      'alt'=>get_string('del_slave','local_wsks'),
                                                      'class'=>'iconsmall')),
                                                      array('title'=>get_string('del_slave','local_wsks')));

                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_slave

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSlaves_Content

}//Slaves
