<?php
/**
 * Course Locations - Install Script
 *
 * Description
 *
 * @package             local
 * @subpackage          course_locations/db
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        27/04/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_course_locations_install() {
    /* Variables    */
    global $DB;
    $tbl_courseLocations        = null;
    $tbl_courseLocationsSector  = null;

    $db_man = $DB->get_manager();

    /* Course Locations Table           */
    if (!$db_man->table_exists('course_locations')) {
        /* Structure table  */
        $tbl_courseLocations = CourseLocations_Install::GetTable_CourseLocations();

        /* Create DB        */
        $db_man->create_table($tbl_courseLocations);
    }//if_tbl_courseLocations
}//xmldb_local_course_locations_install

class CourseLocations_Install {

    /**
     * @return          xmldb_table
     * @throws          Exception
     *
     * @creationDate    28/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the structure table for course_locations
     */
    public static function GetTable_CourseLocations() {
        /* Variables    */
        $tbl_courseLocations = null;

        try {
            /* Create Table */
            $tbl_courseLocations = new xmldb_table('course_locations');

            /* Add Fields   */
            /* Id               --> Primary Key. Auto numeric                       */
            $tbl_courseLocations->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* levelZero        --> Foreign Key --> report_gen_companydata  --> id  */
            $tbl_courseLocations->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelOne         --> Foreign Key --> report_gen_companydata  --> id  */
            $tbl_courseLocations->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* name             --> Location Name. Not null.                        */
            $tbl_courseLocations->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* description      --> Location Description                            */
            $tbl_courseLocations->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
            /* url              --> URL Description                                 */
            $tbl_courseLocations->add_field('url',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* floor            --> Floor. Not Null                                 */
            $tbl_courseLocations->add_field('floor',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* room             --> Room number. Not Null                           */
            $tbl_courseLocations->add_field('room',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* seats            --> Maximum number of seats                         */
            $tbl_courseLocations->add_field('seats',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* street          --> Location Address. Not Null                      */
            $tbl_courseLocations->add_field('street',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* Post code        */
            $tbl_courseLocations->add_field('postcode',XMLDB_TYPE_CHAR,'10',null, null, null,null);
            /* City             */
            $tbl_courseLocations->add_field('city',XMLDB_TYPE_CHAR,'100',null, null, null,null);
            /* urlmap           --> URL Map to the address location                 */
            $tbl_courseLocations->add_field('urlmap',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* post             --> Post address location.                          */
            $tbl_courseLocations->add_field('post',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* contact          --> Contact person                                  */
            $tbl_courseLocations->add_field('contact',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* phone            --> Contact phone                                   */
            $tbl_courseLocations->add_field('phone',XMLDB_TYPE_CHAR,'25',null, null, null,null);
            /* email            --> Contact eMail                                   */
            $tbl_courseLocations->add_field('email',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* comments         --> Comments                                        */
            $tbl_courseLocations->add_field('comments',XMLDB_TYPE_TEXT,null,null, null, null,null);
            /* active           --> Location available or not. Not Null. Default 1  */
            $tbl_courseLocations->add_field('activate',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,1);
            /* createdby        --> Foreign Key --> user --> userid                 */
            $tbl_courseLocations->add_field('createdby',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* timecreated      --> Time created. Not Null.                         */
            $tbl_courseLocations->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* timemodified     --> Time Modified.                                  */
            $tbl_courseLocations->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);


            /* Add Keys */
            $tbl_courseLocations->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tbl_courseLocations->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));
            $tbl_courseLocations->add_key('levelone',XMLDB_KEY_FOREIGN,array('levelone'), 'report_gen_companydata', array('id'));
            $tbl_courseLocations->add_key('createdby',XMLDB_KEY_FOREIGN,array('createdby'), 'user', array('id'));

            return $tbl_courseLocations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTable_CourseLocations
}//CourseLocations_Install