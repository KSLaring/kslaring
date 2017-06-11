<?php
/**
 * Fellesdata Suspicious - Library
 *
 * @package         local/fellesdata
 * @subpackage      suspicious
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/12/2016
 * @author          eFaktor     (fbv)
 *
 */
define('ERR_PROCESS',1);
define('ERR_FILE',2);
define('ERR_PARAMS',3);
define('NONE_ERROR',4);

define('APPROVED',5);
define('REJECTED',6);

define('IMP_SUSP_USERS','IMP_USERS');
define('IMP_SUSP_COMPANIES','IMP_COMPANIES');
define('IMP_SUSP_JOBROLES','IMP_JOBROLES');
define('IMP_SUSP_MANAGERS_REPORTERS','IMP_MANAGERS_REPORTERS');
define('IMP_SUSP_COMPETENCE_JR','IMP_COMPETENCE_JR');

class suspicious {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Check if the synchronization can be triggered
     *
     * @creationDate        29/12/2016
     * @author              eFaktor     (fbv)
     *
     * @param       String  $type    Type of synchronization to check
     *
     * @return              bool
     * @throws              Exception
     */
    public static function run_synchronization($type) {
        /* Variables */
        global $DB;
        $sync   = true;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['approve']  = 0;
            $params['reject']   = 0;
            $params['impfs']    = $type;

            // Sql instruction
            $sql = " SELECT count(*) as 'total'
                     FROM 	mdl_fs_suspicious
                     WHERE	approved = :approve
                        AND rejected = :reject
                        AND impfs    = :impfs ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->total) {
                    $sync = false;
                }else {
                    $sync = true;
                }
            }else {
                $sync = true;
            }//if_rdo

            return $sync;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//run_synchronization

    /**
     * Description
     * Check if the file contain suspicious data
     * 
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     * 
     * @param       String $type   Type of file
     * @param       String $file   File
     *
     * @return      bool
     * @throws      Exception
     */
    public static function check_for_suspicious_data($type,$file) {
        /* Variables */
        $content    = null;
        $suspicious = false;
        $plugin     = null;

        try {
            // Get plugin info
            $plugin = get_config('local_fellesdata');

            // Max deletec actions
            $options = array('5','10','20','40','50','75','100','125','150','175','200','250','300','400','500','600','700','800','900','1000');

            // Get content
            $content = file_get_contents($file);

            // Count delete actions
            $delete = substr_count($content,"delete");
            if ($delete) {
                switch ($type) {
                    case TRADIS_FS_USERS:
                        if ($delete >= $options[$plugin->max_users]) {
                            $suspicious = true;
                        }//if_suspicious

                        break;
                    case TRADIS_FS_USERS_JOBROLES:
                        if ($delete >= $options[$plugin->max_comp]) {
                            $suspicious = true;
                        }//if_suspicious

                        break;
                    default:
                        if ($delete >= $options[$plugin->max_rest]) {
                            $suspicious = true;
                        }//if_suspicious

                        break;
                }//switch_type
            }//if_delete

            return $suspicious;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_for_suspicious_data

    /**
     * @param       String  $type       Type of file
     * @param       object  $plugin     Plugin settings
     * 
     * @return      null|string
     * @throws      Exception
     * @throws      dml_transaction_exception
     */
    public static function mark_suspicious_file($type,$plugin) {
        /* Variables */
        global $DB,$CFG;
        $instance           = null;
        $instanceApprove    = null;
        $instanceReject     = null;
        $remainder          = null;
        $options            = null;
        $time               = null;
        $newLocation        = null;
        $trans              = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // calculate when the remainder has to be sent
            $options   = array('12','24','36','48');
            $remainder = strtotime('+' . $options[$plugin->send_remainder] . ' hours');

            // New instance
            $instance = new stdClass();
            $instance->file             = $type . '_' . $time . '.txt';
            $instance->path             = $CFG->dataroot . '/' . $plugin->suspicious_path;
            $instance->token            = self::generate_token($type);
            $instance->detected         = $time;
            $instance->approved         = 0;
            $instance->rejected         = 0;
            $instance->notificationsent = null;
            $instance->remainder        = $remainder;

            switch ($type) {
                case TRADIS_FS_USERS:
                    $instance->impfs = IMP_USERS;

                    break;
                case TRADIS_FS_USERS_JOBROLES:
                    $instance->impfs = IMP_COMPETENCE_JR;

                    break;
                case TRADIS_FS_MANAGERS_REPORTERS:
                    $instance->impfs = IMP_MANAGERS_REPORTERS;

                    break;
                case TRADIS_FS_JOBROLES:
                    $instance->impfs = IMP_JOBROLES;

                    break;
                case TRADIS_FS_COMPANIES:
                    $instance->impfs = IMP_COMPANIES;

                    break;
            }//switch_type

            // Insert suspicious
            $instance->id = $DB->insert_record('fs_suspicious',$instance);

            // Approve action
            $instanceApprove = new stdClass();
            $instanceApprove->token          = self::generate_token($type,true);
            $instanceApprove->action         = 1;
            $instanceApprove->suspiciousid   = $instance->id;
            // Execute
            $DB->insert_record('fs_suspicious_action',$instanceApprove);

            // Reject action
            $instanceReject = new stdClass();
            $instanceReject->token          = self::generate_token($type,true);
            $instanceReject->action         = 2;
            $instanceReject->suspiciousid   = $instance->id;
            // Execute
            $DB->insert_record('fs_suspicious_action',$instanceReject);

            // Commit
            $trans->allow_commit();

            // Return new location of the file
            $newLocation = $instance->path . '/' . $instance->file;

            return $newLocation;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//mark_suspicious_file

    /**
     * Description
     * Send notifications of all suspicious files found
     *
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param       Object  $plugin     Plugins settings
     * @param       boolean $remainder  If we need to send a remainder notification
     *
     * @throws              Exception
     */
    public static function send_suspicious_notifications($plugin,$remainder = null) {
        /* Variables */
        global $SITE;
        $notifications      = null;
        $infoUser           = null;
        $admin              = null;
        $notifyTo           = null;
        $strSubject         = null;
        $strBody            = null;
        $strBodyHtml        = null;
        $strMiddle          = null;
        $strBodyEnd         = null;

        try {
            // generate the token for old version
            self::update_old_suspicious();

            // get notifications to sent
            $notifications = self::get_suspicious_notifications($remainder);

            // Send notifications
            if ($notifications) {
                $admin = get_admin();
                // Get people to send notifications
                if ($plugin->suspicious_notify) {
                    $notifyTo = explode(',',$plugin->suspicious_notify);
                }//if_suspicious

                // None to notify then send to the admin site
                if (!$notifyTo) {
                    $notifyTo = array();
                    $notifyTo[] = $admin->email;
                }//if_notify

                // All notifications with the right language
                foreach ($notifyTo as $to) {
                    // Cleaning variables
                    $strSubject = null;
                    $strBody    = null;
                    $strMiddle  = null;
                    $strBodyEnd = null;

                    $infoUser = get_complete_user_data('email',$to);
                    if (!$infoUser) {
                        $admin->email   = $to;
                        $infoUser       = $admin;
                    }//if_indoUser

                    // Subject
                    if ($remainder) {
                        $strSubject = (string)new lang_string('subj_suspicious_remainder','local_fellesdata',$SITE->shortname,$infoUser->lang);
                    }else {
                        $strSubject = (string)new lang_string('subj_suspicious','local_fellesdata',$SITE->shortname,$infoUser->lang);
                    }//if_remainder

                    // Body
                    $strBody = (string)new lang_string('body_suspicious','local_fellesdata',null,$infoUser->lang);

                    // All suspicious files in the same email
                    foreach ($notifications as $notify) {
                        // Info to send
                        $aux = new stdClass();
                        $aux->file      = $notify->file;
                        $aux->marked    = $notify->marked;
                        // Links with the right language string
                        $aux->approve = "<a href='" . $notify->approve . "'>" . (string)new lang_string('approve','local_fellesdata',null,$infoUser->lang) . "</a>" ;
                        $aux->reject = "<a href='" . $notify->reject . "'>" . (string)new lang_string('reject','local_fellesdata',null,$infoUser->lang) . "</a>" ;

                        // Build body message
                        $strMiddle .= (string)new lang_string('body_suspicious_middle','local_fellesdata',$aux,$infoUser->lang);
                    }//notifications

                    // End body message
                    $strBodyEnd = (string)new lang_string('body_suspicious_end','local_fellesdata',null,$infoUser->lang);
                    $strBody .= $strMiddle . $strBodyEnd;

                    // Send notification
                    email_to_user($infoUser, $SITE->shortname, $strSubject, $strBody, $strBody);
                }//for_notify

                // Update notifications as sent
                self::update_as_sent($notifications,$plugin,$remainder);
            }//if_notifications
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//send_suspicious_notifications

    /**
     * Description
     * Check if the action link is valid
     *
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param   array   $args   Parameters from the action link
     * @param   int     $error
     *
     * @return          int
     * @throws          Exception
     */
    public static function check_action_link($args,&$error) {
        /* Variables */

        try {
            // Check params link
            if (self::check_link($args)) {
                // Check file
                if (self::check_file($args)) {
                    $error = NONE_ERROR;
                }else {
                    $error = ERR_FILE;
                }
            }else {
                $error = ERR_PARAMS;
            }//if_else
        }catch (Exception $ex) {
            $error = ERR_PROCESS;
            
            throw $ex;
        }//try_catch
    }//check_action_link

    /**
     * Description
     * Check if the link to download the file is correct
     * 
     * @creationDate    18/01/2017
     * @author          eFaktor     (fbv)
     * 
     * @param   array   $args
     * @param   int     $error
     * 
     * @throws          Exception
     */
    public static function check_download_link($args,&$error) {
        try {
            // Check params link
            if (!self::check_lnk_download($args)) {
                $error = ERR_PARAMS;
            }else {
                $error = NONE_ERROR; 
            }//if_else
        }catch (Exception $ex) {
            $error = ERR_PROCESS;

            throw $ex;
        }//try_catch
    }//check_download_link
    
    /**
     * Description
     * Apply the action to the suspicious file
     *
     * @creationDate        28/12/2016
     * @author              eFaktor     (fbv)
     *
     * @param       array  $args     Parameters connected with suspicious file
     * @param       String $error
     *
     * @throws             Exception
     */
    public static function apply_action($args,&$error) {
        /* Variables */

        try {
            // Apply action
            switch ($args[0]) {
                case '1':
                    // Approve file
                    self::approve_data($args[2],$error);
                    
                    break;
                
                case '2':
                    // reject file
                    self::reject_data($args[2],$error);
                    
                    break;
                
                default:
                    $error = ERR_PROCESS;

                    break;
            }//switch
        }catch (Exception $ex) {
            $error = ERR_PROCESS;

            throw $ex;
        }//try_catch
    }//apply_action

    /**
     * Description
     * Get name of the suspicious file
     *
     * @creationDate        28/12/2016
     * @author              eFaktor     (fbv)
     *
     * @param       Integer $fileId     Instance id
     *
     * @return              null
     * @throws              Exception
     */
    public static function get_name($fileId) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['id'] = $fileId;

            // Execute
            $rdo = $DB->get_record('fs_suspicious',$params,'file');
            if ($rdo) {
                return $rdo->file;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_name

    /**
     * Description
     * Get suspicious files
     *
     * @creationDate        29/12/2016
     * @author              eFaktor     (fbv)
     *
     * @param   null $from  Date from
     * @param   null $to    DAte to
     * 
     * @return       array
     * @throws       Exception
     */
    public static function get_suspicious_files($from = null,$to = null) {
        /* Variables */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $suspicious = array();
        $info       = null;
        $status     = null;

        try {
            // Search criteria
            $params = array();
            $params['from'] = $from;
            $params['to']   = $to + (3600 * 24);

            // Sql Instruction
            $sql = " SELECT fs.id,
                            fs.file,
                            fs.impfs,
                            fs.detected,
                            fs.approved,
                            fs.rejected,
                            fs.notificationsent
                     FROM	{fs_suspicious}	fs
                      ";

            if ($from && $to) {
                $sql .= " WHERE	fs.detected BETWEEN :from AND :to ";
            }

            // Order by
            $sql .= " ORDER BY fs.detected ";
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info suspicious file
                    $info = new stdClass();
                    $info->file     = $instance->file;
                    $info->id       = $instance->id;
                    $info->detected = userdate($instance->detected,'%d.%m.%Y', 99, false);
                    $info->sent     = userdate($instance->notificationsent,'%d.%m.%Y', 99, false);

                    // status
                    if (!$instance->approved && !$instance->rejected) {
                        $info->status    = get_string('status_wait','local_fellesdata');
                        $info->toapprove = 1;
                        $info->toreject  = 1;
                    }else if ($instance->approved){
                        $info->status    = get_string('status_app','local_fellesdata');
                        $info->toapprove = 0;
                        $info->toreject  = 0;
                    }else {
                        $info->status    = get_string('status_rej','local_fellesdata');
                        $info->toapprove = 0;
                        $info->toreject  = 0;
                    }//if_status

                    // Connected with
                    switch ($instance->impfs) {
                        case IMP_USERS:
                            $info->sync = get_string('sync_users','local_fellesdata');

                            break;

                        case IMP_COMPETENCE_JR:
                            $info->sync = get_string('sync_competence','local_fellesdata');
                            
                            break;
                        
                        case IMP_MANAGERS_REPORTERS:
                            $info->sync = get_string('sync_managers','local_fellesdata');

                            break;

                        case IMP_COMPANIES:
                            $info->file = TRADIS_FS_COMPANIES;
                            $info->sync = get_string('sync_company','local_fellesdata');

                            break;

                        case IMP_JOBROLES:
                            $info->sync = get_string('sync_jobroles','local_fellesdata');

                            break;
                        
                        default:
                            $info->file = $instance->file;
                            $info->sync = ' - ';
                            
                            break;
                    }//switch_connected_With

                    // add
                    $suspicious[$instance->id] = $info;
                }//for_Each
            }//if_rdo
            
            return $suspicious;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_suspicious_files

    /**
     * Description
     * Display suspicious table
     *
     * @creationDate    29/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param    array  $suspicious  Suspicious files
     * @param    int    $from        Date from
     * @param    int    $to          Date to    
     *
     * @return          string
     * @throws          Exception
     */
    public static function display_suspicious_table($suspicious,$from = 0, $to = 0) {
        /* Variables */
        $out = '';

        try {
            $out .= html_writer::start_div('block_suspicious');
            if ($suspicious) {
                $out .= html_writer::start_div('block_suspicious_content');
                    $out .= html_writer::start_tag('table',array('class' => 'generaltable'));
                        // Header
                        $out .= self::add_header_suspicious_table();
                        // Content
                        $out .= self::add_content_suspicious_table($suspicious,$from,$to);
                    $out .= html_writer::end_tag('table');
                $out .= html_writer::end_div();//block_suspicious_content
            }else {
                //no data
                $out .= html_writer::start_div('block_suspicious_content');
                    $out .= '<h5>' . get_string('no_data','local_fellesdata'). '</h5>';
                $out .= html_writer::end_div();//block_suspicious_content
            }//if_suspicious
            $out .= html_writer::end_div();//block_suspicious

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_suspicious_table

    /**
     * Description
     * Display the link to download the file
     * 
     * @creationDate    25/01/2017
     * @author          eFaktor     (fbv)
     * 
     * @param           integer $suspicious Id file
     * 
     * @return                  string
     * @throws                  Exception
     */
    public static function display_download_link($suspicious) {
        /* Variables */
        $out = '';
        $lnk    = null;
        $url    = null;
        
        try {
            $out .= html_writer::start_div('block_suspicious');
                if ($suspicious) {
                    // Name file
                    $name = suspicious::get_name($suspicious);
                    
                    // Download lnk
                    $url = new moodle_url('/local/fellesdata/suspicious/download.php',array('id' => $suspicious,'csv' => 1));
                    $lnk = '<a href="' . $url . '">' . $name . '</a>';
                    $out .= html_writer::start_div('block_suspicious_content');
                            $out .= get_string('to_download','local_fellesdata',$lnk);
                    $out .= html_writer::end_div();//block_suspicious_content
                }else {
                    //no data
                    $out .= html_writer::start_div('block_suspicious_content');
                        $out .= '<h5>' . get_string('no_data','local_fellesdata'). '</h5>';
                    $out .= html_writer::end_div();//block_suspicious_content
                }//if_suspicious
            $out .= html_writer::end_div();//block_suspicious

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_download_file

    /**
     * Description
     * Download the file connected with
     *
     * @creationDate    16/01/17
     * @author          eFaktor     (fbv)
     * 
     * @param       int $suspicious     Suspicious id file
     *
     * @return          bool
     * @throws          Exception
     */
    public static function download_suspicious_file($suspicious) {
        /* Variables */
        global $DB;
        $path   = null;
        $file   = null;
        $name   = null;
        $rdo    = null;
        $export = null;
        
        try {
            // Get the info from DB
            $rdo = $DB->get_record('fs_suspicious',array('id' => $suspicious),'file,path');
            if ($rdo) {
                // Path file
                $path = $rdo->path . '/' . $rdo->file;

                $export = new csv_export_writer();
                $export->set_filename($rdo->file);

                // Open file and add the content
                $file = fopen($path,'r');
                while (($data = fgetcsv($file)) !== FALSE) {
                    $export->add_data($data);
                }

                // Download file
                $export->download_file();
            }else {
                return false;
            }

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_suspicious_file



    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Add the header to suspicious table
     *
     * @creationDate    29/12/2016
     * @author          eFaktor     (fbv)
     *
     * @return          string
     * @throws          Exception
     */
    private static function add_header_suspicious_table() {
        /* Variables */
        $header     = '';
        $strFile    = null;
        $strWait    = null;
        $strWith    = null;
        $strStatus  = null;
        $strAct     = null;

        try {
            // Get headers
            $strFile    = get_string('rpt_file','local_fellesdata');
            $strWait    = get_string('rpt_since','local_fellesdata');
            $strWith    = get_string('rpt_connected','local_fellesdata');
            $strStatus  = get_string('rpt_status','local_fellesdata');
            $strAct     = get_string('rpt_act','local_fellesdata');

            $header .= html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_suspicious'));
                    // File
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strFile;
                    $header .= html_writer::end_tag('th');
                    // Waiting
                    $header .= html_writer::start_tag('th',array('class' => 'date'));
                        $header .= $strWait;
                    $header .= html_writer::end_tag('th');
                    // Connected With
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strWith;
                    $header .= html_writer::end_tag('th');
                    // Status
                    $header .= html_writer::start_tag('th',array('class' => 'date'));
                        $header .= $strStatus;
                    $header .= html_writer::end_tag('th');
                    // Action
                    $header .= html_writer::start_tag('th',array('class' => 'action'));
                        $header .= $strAct;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_suspicious_table

    /**
     * Description
     * Add the content to the table
     *
     * @creationDate      29/12/2016
     * @author            eFaktor   (fbv)
     *
     * @param       array  $suspicious   Suspicious files
     * @param       int    $from        Date from
     * @param       int    $to          Date to
     *
     * @return             string
     * @throws             Exception
     */
    private static function add_content_suspicious_table($suspicious,$from=0,$to=0) {
        /* Variables */
        $content    = '';
        $urlFile    = null;
        $lnkFile    = null;
        $urlApp     = null;
        $lnkApp     = null;
        $urlRej     = null;
        $lnkRej     = null;
        $class      = null;
        $classFile  = null;

        try {
            // Url file to download
            $urlFile = new moodle_url('/local/fellesdata/suspicious/index.php',array('csv' => 1));
            // Url approve
            $urlApp = new moodle_url('/local/fellesdata/suspicious/index.php',array('a' => 1));
            // Url reject
            $urlRej = new moodle_url('/local/fellesdata/suspicious/index.php',array('a' => 2));
            
            if ($from && $to) {
                // Extra params to approve link
                $urlApp->param('f',$from);
                $urlApp->param('t',$to);
                // Extra params to reject link
                $urlRej->param('f',$from);
                $urlRej->param('t',$to);
            }
            
            foreach ($suspicious as $info) {
                $class  = '';
                $content .= html_writer::start_tag('tr');
                    // links enables or not
                    if (!$info->toapprove) {
                        $class      = 'link_suspicious_disabled';
                        $classFile  = 'link_suspicious_file_disabled';
                    }else if(!$info->toreject) {
                        $class      = 'link_suspicious_disabled';
                        $classFile  = 'link_suspicious_file_disabled';
                    } else {
                        $class      = 'link_suspicious';
                        $classFile  = 'link_suspicious';
                    }//if_to_approve

                    // File
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        // url to download the file
                        $urlFile->param('id',$info->id);
                        $lnkFile = '<a href="' . $urlFile . '" class="' . $classFile . '">' . $info->file . "</a>";
                        $content .= $lnkFile;
                    $content .= html_writer::end_tag('td');
                    // Waiting
                    $content .= html_writer::start_tag('td',array('class' => 'date'));
                        $content .= $info->detected;
                    $content .= html_writer::end_tag('td');
                    // Connected with
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $info->sync;
                    $content .= html_writer::end_tag('td');
                    // Status
                    $content .= html_writer::start_tag('td',array('class' => 'date'));
                        $content .= $info->status;
                    $content .= html_writer::end_tag('td');
                    // Action
                    $content .= html_writer::start_tag('td',array('class' => 'action'));
                        // Url approve
                        $urlApp->param('id',$info->id);
                        $lnkApp = '<a href="' . $urlApp . '" class="' . $class . '">' . get_string('approve','local_fellesdata') . "</a>";

                        // Url reject
                        $urlRej->param('id',$info->id);
                        $lnkRej = '<a href="' . $urlRej . '" class="' . $class . '">' . get_string('reject','local_fellesdata') . "</a>";

                        $content .= $lnkApp . $lnkRej;
                    $content .= html_writer::end_tag('td');
                $content .=html_writer::end_tag('tr');
            }//info

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_suspicious_table

    /**
     * Description
     * Approve suspicious file for its processing
     *
     * @creationDate         28/12/2016
     * @author               eFaktor        (fbv)
     *
     * @param       Integer  $suspiciousId    Instance id
     * @param       String   $error
     *
     * @throws               Exception
     */
    private static function approve_data($suspiciousId,&$error) {
        /* Variables */
        global $DB,$CFG;
        $params     = null;
        $rdo        = null;
        $pathFile   = null;

        try {
            // Check if already exists
            $params =array();
            $params['id']           = $suspiciousId;
            $params['approved']     = 0;
            $params['rejected']     = 0;

            $rdo = $DB->get_record('fs_suspicious',$params);
            if ($rdo) {
                // Build file
                $pathFile = $rdo->path . '/' . $rdo->file;
                if (file_exists($pathFile)) {
                    // Get content
                    $content = file($pathFile);

                    FS::save_temporary_fellesdata($content,$rdo->impfs);

                    //Move file
                    $backup = $CFG->dataroot . '/fellesdata/backup';
                    if (!file_exists($backup)) {
                        mkdir($backup);
                    }
                    $backup .= '/' . $rdo->file;

                    copy($pathFile,$backup);
                    unlink($pathFile);

                    // Update
                    $rdo->approved = 1;

                    // Execute
                    $DB->update_record('fs_suspicious',$rdo);

                    $error = APPROVED;
                }else {
                    $error = ERR_FILE;
                }//if_else_file
            }else {
                $error = ERR_FILE;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//approve_data

    /**
     * Description
     * Reject the file
     *
     * @creationDate    28/12/2016
     * @author          eFaktor (fbv)
     *
     * @param    Integer  $suspiciousId     Instance id
     * @param    String   $error            Type of error
     *
     * @throws          Exception
     */
    private static function reject_data($suspiciousId,&$error) {
        /* Variables */
        global $DB;
        $params     = null;
        $rdo        = null;

        try {
            // Check if already exists
            $params =array();
            $params['id']           = $suspiciousId;
            $params['approved']     = 0;
            $params['rejected']     = 0;
            
            $rdo = $DB->get_record('fs_suspicious',$params);
            if ($rdo) {
                // Update
                $rdo->rejected = 1;
                
                // Execute
                $DB->update_record('fs_suspicious',$rdo);
                
                $error = REJECTED;
            }else {
                $error = ERR_FILE;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//reject_data

    /**
     * Description
     * Check if the link is valid
     *
     * @creationDate        28/12/2016
     * @author              eFaktor     (fbv)
     *
     * @param       array   $args Parameters from the action link
     *
     * @return              bool
     * @throws              Exception
     */
    private static function check_link($args) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['suspiciousid'] = $args[2];
            $params['action']       = $args[0];
            $params['token']        = $args[1];

            // Execute
            $rdo = $DB->get_record('fs_suspicious_action',$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_link


    /**
     * Description
     * Check if the link to download the file is correct
     * 
     * @creationDate    18/01/2017
     * @author          eFaktor     (fbv)
     * 
     * @param           array $args
     * 
     * @return                bool
     * @throws                Exception
     */
    private static function check_lnk_download($args) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['id']    = $args[1];
            $params['token'] = $args[0];

            // Execute
            $rdo = $DB->get_record('fs_suspicious',$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_lnk_download
    
    /**
     * Description
     * Check if the file connected exists
     *
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param       array $args Parameters from the action link
     *
     * @return            bool
     * @throws            Exception
     */
    private static function check_file($args) {
        /* Variables */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;
        $file   = null;

        try {
            // Search criteria
            $params = array();
            $params['suspicious']   = $args[2];
            $params['action']       = $args[0];
            $params['token']        = $args[1];

            // SQL Instruction
            $sql = " SELECT	  fs.id,
                              fs.file,
                              fs.path
                     FROM	  {fs_suspicious}			fs
                        JOIN  {fs_suspicious_action}	fs_act  ON  fs_act.suspiciousid = fs.id 
                                                                AND	fs_act.action = :action
                                                                AND	fs_act.token  = :token
                     WHERE 	  fs.id = :suspicious ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Check if the file exists in the right location
                $file = $rdo->path . '/' . $rdo->file;
                if (file_exists($file)) {
                    return true;
                }else {
                    return false;
                }//if_file_exists
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_file

    /**
     * Description
     * Generate the token connected with the file for old version
     *
     * @creationDate    18/01/2017
     * @author          eFaktor     (fbv)
     *
     * @throws          Exception
     */
    private static function update_old_suspicious() {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            // Get old suspicious
            $rdo = $DB->get_records('fs_suspicious',array('token' => 0),'id,token');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $instance->token = self::generate_token('old');

                    // Execute
                    $DB->update_record('fs_suspicious',$instance);
                }
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_token_old_version

    /**
     * Description
     * Get notifications to send
     *
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param           boolean $remainder      Remainder date
     * @return          array
     * @throws          Exception
     */
    private static function get_suspicious_notifications($remainder = null) {
        /* Variables */
        global $DB, $CFG;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $suspicious     = array();
        $info           = null;
        $time           = null;
        $download       = null;

        try {
            // Local time
            $time = time();

            // Search criteria
            $params = array();
            $params['approved']     = 0;
            $params['rejected']     = 0;

            // Sql instruction
            $sql = " SELECT	  fs.id,
                              fs.file,
                              fs.token,
                              fs.detected,
                              fs_app.token as 'approve',
                              fs_rej.token as 'reject'
                     FROM	  {fs_suspicious}			fs
                        -- aprove action
                        JOIN  {fs_suspicious_action} 	fs_app 	ON 	fs_app.suspiciousid = fs.id
                                                                AND	fs_app.action 		= 1
                        -- reject action
                        JOIN  {fs_suspicious_action}	fs_rej	ON	fs_rej.suspiciousid = fs_app.suspiciousid
                                                                AND fs_rej.action		= 2
                     WHERE	  fs.approved  = 0
                          AND fs.rejected  = 0
                           ";

            // Remainder
            if ($remainder) {
                $params['remainder'] = $time;
                
                $sql .= " AND fs.remainder <= :remainder";
            }else {
                $sql .= "  AND (fs.notificationsent IS NULL
                               OR
                               fs.notificationsent = '') ";
            }
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                // url's to approve or reject the file
                foreach ($rdo as $instance) {
                    $info = new stdClass();
                    $info->id       = $instance->id;
                    $download       = $CFG->wwwroot . '/local/fellesdata/suspicious/download.php/'. $instance->token . '/' . $instance->id;
                    $info->file     = '<a href="' . $download . '">' . $instance->file . '</a>';
                    $info->marked   = userdate($instance->detected ,'%d.%m.%Y', 99, false);
                    $info->approve  = $CFG->wwwroot . '/local/fellesdata/suspicious/action.php/1/'. $instance->approve . '/' . $instance->id;
                    $info->reject   = $CFG->wwwroot . '/local/fellesdata/suspicious/action.php/2/'. $instance->reject . '/' . $instance->id;

                    //Add to the list
                    $suspicious[$instance->id] = $info;
                }//for
            }//if_rdo

            return $suspicious;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_suspicious_notifications


    /**
     * Description
     * Update notifications as sent
     *
     * @creationDate    28/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param       array   $suspicious   Suspicious notifications
     * @param       Object  Plugin info
     * @param       boolean $remainder
     *
     * @throws            Exception
     */
    private static function update_as_sent($suspicious,$plugin,$remainder = null) {
        /* Variables */
        global $DB;
        $instance = null;
        $options  = null;
        $toSend   = null;

        try {
            if ($suspicious) {
                foreach ($suspicious as $key=>$info) {
                    // Instance to update
                    $instance = new stdClass();
                    $instance->id               = $key;
                    if ($remainder) {
                        $instance->remaindersent = time();
                    }else {
                        $instance->notificationsent = time();
                    }

                    // Update when it has to be sent the next remainder
                    $options   = array('12','24','36','48');
                    $toSend    = strtotime('+' . $options[$plugin->send_remainder] . ' hours');
                    $instance->remainder = $toSend;

                    // Execute
                    $DB->update_record('fs_suspicious',$instance);
                }//for_suspicious
            }//if_suspicious
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_as_sent
    
    /**
     * Description
     * Generate an internal token
     *
     * @creationDate    27/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param                $type
     * @param           bool $action     Action table
     * @return          bool|mixed|null|string
     *
     * @throws          Exception
     */
    private static function generate_token($type,$action = false) {
        /* Variables        */
        global $DB,$CFG;
        $ticket = null;
        $token  = null;
        $remind = null;
        $tbl    = null;

        try {
            if ($action) {
                $tbl = 'fs_suspicious_action';
            }else {
                $tbl = 'fs_suspicious';
            }
            // Ticket - something long and unique
            $token  = uniqid(mt_rand(),1);
            $ticket = random_string() . $type . '_' . time() . '_' . $token . random_string();
            $remind = self::generate_hash($ticket);
            $remind = str_replace('/','.',$remind);

            // Check if it already exists
            while ($DB->record_exists($tbl,array('token' => $token))) {
                // Ticket - something long and unique
                $token  = uniqid(mt_rand(),1);
                $ticket = random_string() . $type . '_' . time() . '_' . $token . random_string();
                $remind = self::generate_hash($ticket);
                $remind = str_replace('/','.',$remind);
            }//while

            return $remind;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generate_token_action

    /**
     * Description
     * Generate a hash for sensitive values
     *
     * @creationDate    27/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $value
     * @return          bool|null|string
     * @throws          Exception
     */
    private static function generate_hash($value) {
        /* Variables    */
        $cost               = 10;
        $required_salt_len  = 22;
        $buffer             = '';
        $buffer_valid       = false;
        $hash_format        = null;
        $salt               = null;
        $ret                = null;
        $hash               = null;

        try {
            // Generate hash
            $hash_format        = sprintf("$2y$%02d$", $cost);
            $raw_length         = (int) ($required_salt_len * 3 / 4 + 1);

            if (function_exists('mcrypt_create_iv')) {
                $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_length);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && file_exists('/dev/urandom')) {
                $f = @fopen('/dev/urandom', 'r');
                if ($f) {
                    $read = strlen($buffer);
                    while ($read < $raw_length) {
                        $buffer .= fread($f, $raw_length - $read);
                        $read = strlen($buffer);
                    }
                    fclose($f);
                    if ($read >= $raw_length) {
                        $buffer_valid = true;
                    }
                }
            }

            if (!$buffer_valid || strlen($buffer) < $raw_length) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $raw_length; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            $salt = str_replace('+', '.', base64_encode($buffer));

            $salt = substr($salt, 0, $required_salt_len);

            $hash = $hash_format . $salt;

            $ret = crypt($value, $hash);

            if (!is_string($ret) || strlen($ret) <= 13) {
                return false;
            }

            return $ret;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//generate_hash
}//suspicious