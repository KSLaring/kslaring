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
                        $company->import         = self::call_ws($company,$log);

                        //Add company
                        if ($company->import) {
                            doskom::import_doskom($company,$log);

                           // Clean data
                            doskom::clean_temporary($log);
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

            // Write log
            doskom::write_doskom_log($log);

            return true;
        }catch (Exception $ex) {
           return false;
        }//try_catch
    }//cron

    /************/
    /* PRIVATE  */
    /************/


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
            $urlWs = $company->api . '/' . $company->id .'/personalia/no';

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

                return null;
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

                    return null;
                }else {
                    // DOSKOM LOG
                    $infolog = new stdClass();
                    $infolog->action        = 'call_ws';
                    $infolog->description   = 'OK. Response ' . $company->id;
                    $infolog->timecreated   = $time;
                    // Add log
                    $log[] = $infolog;

                    return $response;
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

            throw $ex;
        }//try_catch
    }//Call_WS_PILOT
}//WSSSO_Cron