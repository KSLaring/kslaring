<?php
/**
 * Fellesdata Status Integration - Cron
 *
 * @package         local/fellesdata_status
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

class STATUS_CRON {
    /***********/
    /* PUBLIC  */
    /***********/

    public static function competence_data($plugin) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $industry   = null;
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Get KS competence data . ' . "\n";
            
            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);
            
            // Service parameters
            $params = array();
            $params['industry'] = "1201";//(String)$industry;

            echo "Industry: " . $industry . " - " . $params['industry'] . "</br>";

            // Cal service
            $response = self::process_service($plugin,WS_COMPETENCE,$params);
            
            if ($response) {
                if ($response['error'] == '200') {
                    echo "COMPETENCE: " . "</br>" . $response['competence'] . "</br>";
                }else {
                    // Log
                    $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                }//if_no_error
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Response null . ' . "\n";
            }//if_else_response



            // Cal service
            $params = array();
            $params['code'] = "1201";//(String)$industry;
            $response = self::process_service($plugin,'ws_competence',array('code' => '1201'));

            if ($response) {
                if ($response['error'] == '200') {
                    echo "COMPETENCE CODE: " . "</br>" . $response['competence'] . "</br>";
                }else {
                    // Log
                    $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                }//if_no_error
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Response null . ' . "\n";
            }//if_else_response

            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Get KS competence data . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Get KS competence data . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            
            throw $ex;
        }//try_catch
    }//competence_data
    
    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * KS Web Services to import data from KS site and synchronize data between fellesdata and KS
     *
     * @param           $plugin
     * @param           $service
     * @param           $params
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_service($plugin,$service,$params) {
        /* Variables    */
        global $CFG;
        $domain         = null;
        $token          = null;
        $server         = null;
        $error          = false;

        try {
            // Data to call Service
            $domain     = $plugin->ks_point;
            $token      = $plugin->kss_token;

            // Build end Point Service
            $server = $domain . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $service .'&moodlewsrestformat=json';

            // Paramters web service
            $fields = http_build_query( $params );
            $fields = str_replace( '&amp;', '&', $fields );

            echo "--> " . $fields . "</br>";
            echo "Length: " . strlen( $fields ) . "</br>";

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
            // Log
            $dbLog = "ERROR: " . $ex->getMessage() .  "\n\n";
            $dbLog .= $ex->getTraceAsString() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Error calling web service . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//process_ks_service
}//STATUS_CRON