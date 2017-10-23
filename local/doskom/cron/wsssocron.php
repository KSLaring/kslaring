<?php
/**
 * WSDOSKOM  Cron - Library
 *
 * @package         local/wsdoskom
 * @subpackage      cron
 * @copyright       2015        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

define ('EXITS_USERNAME','manager');
define ('ERROR_SERVICE',0);
define ('ERROR_PROCESS',1);

class wsdoskom_cron {

    /**
     * Description
     * WSSSO - Cron
     * To import users
     *
     * @return          bool
     *
     * @creationDate    12/01/2015
     * @author          eFaktor     (fbv)
     */
    public static function cron() {
        /* Variables    */
        $companies      = null;
        $active         = null;
        $log            = null;
        $infolog        = null;
        $time           = null;
        $user           = null;
        $error          = null;

        try {
            // Local time
            $time = time();

            // Doskom log
            $log = array();

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'wsdoskom_cron';
            $infolog->description   = 'Start cron';
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Get the companies
            $companies = doskom::doskom_companies($log);
            if ($companies) {
                if ($companies->active) {
                    // extract only companies are active
                    $active = $companies->active;

                    foreach ($active as $company) {
                        list($company->import,$error)         = self::call_ws($company,$log);

                        //Add company
                        if (!$error) {
                            if ($company->import) {
                                // DOSKOM LOG
                                $infolog = new stdClass();
                                $infolog->action        = 'wsdoskom_cron';
                                $infolog->description   = 'Start import doskom';
                                $infolog->timecreated   = $time;
                                // Add log
                                $log[] = $infolog;

                                //Import doskom
                                doskom::import_doskom($company,$log);

                                // DOSKOM LOG
                                $infolog = new stdClass();
                                $infolog->action        = 'wsdoskom_cron';
                                $infolog->description   = 'Finish import doskom';
                                $infolog->timecreated   = $time;
                                // Add log
                                $log[] = $infolog;

                                // Clean data
                                doskom::clean_temporary($log);
                            }
                        }else {
                            // Error send notification
                            self::send_notifications(ERROR_SERVICE);
                        }
                    }//for_companies
                }else {
                    // DOSKOM LOG
                    $infolog = new stdClass();
                    $infolog->action        = 'wsdoskom_cron';
                    $infolog->description   = 'There is any company active';
                    $infolog->timecreated   = $time;
                    // Add log
                    $log[] = $infolog;

                }//if_companies_active
            }else {
                // DOSKOM LOG
                $infolog = new stdClass();
                $infolog->action        = 'wsdoskom_cron';
                $infolog->description   = 'There is none company connected with doskom';
                $infolog->timecreated   = $time;
                // Add log
                $log[] = $infolog;
            }//if_companies

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'wsdoskom_cron';
            $infolog->description   = 'FINISH cron';
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Write log
            doskom::write_doskom_log($log);

            return true;
        }catch (Exception $ex) {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action         = 'ERROR wsdoskom_cron';
            $infolog->description    = 'ERROR FINISH cron';
            $infolog->description   .= $ex->getTraceAsString();
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Write log
            doskom::write_doskom_log($log);

            return false;
        }//try_catch
    }//cron

    /************/
    /* PRIVATE  */
    /************/

    /**
     * @throws          Exception
     *
     * @creationDate    15/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function send_notifications($error) {
        /* Variables */
        global $SITE, $USER;
        $plugin     = null;
        $notifyto   = null;
        $subject    = null;
        $body       = null;
        $time       = null;

        try {
            // Plugin Info
            $plugin     = get_config('local_doskom');

            // Notifications
            if ($plugin->mail_notification) {
                // get notifications
                $notifyto   = explode(',',$plugin->mail_notification);

                // time local
                $time = userdate(time(),'%d.%m.%Y', 99, false);

                switch ($error) {
                    case ERROR_SERVICE:
                        // Subject
                        $subject = (string)new lang_string('errorws_subject','local_doskom',$SITE->shortname,$USER->lang);
                        // Body
                        $body = (string)new lang_string('errorws_body','local_doskom',$time,$USER->lang);

                        break;
                    case ERROR_PROCESS:
                        // Subject
                        $subject = (string)new lang_string('errorprocess_subject','local_doskom',$SITE->shortname,$USER->lang);
                        // Body
                        $body = (string)new lang_string('errorprocess_body','local_doskom',$time,$USER->lang);

                        break;
                }//switch_Error

                // send
                foreach ($notifyto as $to) {
                    $USER->email    = $to;
                    email_to_user($USER, $SITE->shortname, $subject, $body,$body);
                }//for_Each
            }//if_mail_notifications
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//send_notifications

    /**
     * Description
     * Call the Web Services to get the users
     *
     * @param   int     $company  Id company
     * @param           $log
     *
     * @return          mixed|null            Service response
     * @throws          Exception
     *
     * @creationDate    05/02/2015
     * @author          eFaktor     (fbv)
     */
    private static function call_ws($company,&$log) {
        /* Variables    */
        $urlWs          = null;
        $response       = null;
        $infolog        = null;
        $time           = null;

        try {
            // Local time
            $time = time();

            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'call_ws';
            $infolog->description   = 'Call web service for company ' . $company->id;
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Build url end point
            $leng = strlen($company->api);
            if (substr($company->api,($leng-1)) == '/') {
                $urlWs = $company->api . $company->id .'/personalia/no';
            }else {
                $urlWs = $company->api . '/' . $company->id .'/personalia/no';
            }

            // Call Web Service
            $ch = curl_init($urlWs);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, false );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'User-Agent: Moodle 1.0',
                    'Content-Type: application/json ',
                    'DOSSIER_USER: ' . $company->user,
                    'DOSSIER_PASSWORD: ' . $company->token)
            );

            $response   = curl_exec( $ch );
            curl_close( $ch );

            // Format Data
            if ($response === false) {
                // DOSKOM LOG
                $infolog = new stdClass();
                $infolog->action        = 'call_ws';
                $infolog->description   = 'Error in the response ' . $company->id;
                $infolog->timecreated   = $time;
                // Add log
                $log[] = $infolog;

                return array(null,true);
            }else {
                $response = json_decode($response);
                if (isset($response->status)) {
                    // DOSKOM LOG
                    $infolog = new stdClass();
                    $infolog->action        = 'call_ws';
                    $infolog->description   = 'Error in the response ' . $company->id . ' Error -->  ' . $response->msg;
                    $infolog->timecreated   = $time;
                    // Add log
                    $log[] = $infolog;

                    return array(null,true);
                }else {
                    // DOSKOM LOG
                    $infolog = new stdClass();
                    $infolog->action        = 'call_ws';
                    $infolog->description   = 'OK. Response ' . $company->id;
                    $infolog->timecreated   = $time;
                    // Add log
                    $log[] = $infolog;

                    return array($response,false);
                }
            }//if_response
        }catch (Exception $ex) {
            // DOSKOM LOG
            $infolog = new stdClass();
            $infolog->action        = 'call_ws';
            $infolog->description   = 'ERROR. Call web service for company ' . $company->id;
            $infolog->description   .= ' ERROR: ' . $ex->getTraceAsString();
            $infolog->timecreated   = $time;
            // Add log
            $log[] = $infolog;

            // Error send notification
            self::send_notifications(ERROR_PROCESS);

            throw $ex;
        }//try_catch
    }//Call_WS_PILOT
}//WSSSO_Cron