<?php
/**
 * Micro Learning Plugin - Install Script
 *
 * Description
 *
 * @package         local
 * @subpackage      microlearning/db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_microlearning_install() {
    global $DB;

    $db_man = $DB->get_manager();

    /* Microlearnign Table  */
    $tbl_micro_learning = MicroInstall::Get_MicroLearning_Table();
    if (!$db_man->table_exists('microlearning')) {
        $db_man->create_table($tbl_micro_learning);
    }//if_microlearnign

    /* Microlearning Activities Table   */
    $tbl_micro_activities = MicroInstall::Get_MicrolearnigActivities_Table();
    if (!$db_man->table_exists('microlearning_activities')) {
        $db_man->create_table($tbl_micro_activities);
    }//if_microlearnign_activities

    /* Microlearning Calendar Mode Table    */
    $tbl_micro_calendar = MicroInstall::Get_MicrolearningCalendar_Table();
    if (!$db_man->table_exists('microlearning_calendar_mode')) {
        $db_man->create_table($tbl_micro_calendar);
    }//if_microlearnign_calendar_mode

    /* Microlearning Activity Mode Table    */
    $tbl_micro_activity = MicroInstall::Get_MicroLearningActivity_Table();
    if (!$db_man->table_exists('microlearning_activity_mode')) {
        $db_man->create_table($tbl_micro_activity);
    }//if_microlearnign_activity_mode

    /* Microlearning Users Table       */
    $tbl_micro_users = MicroInstall::Get_MicrolearningUsers_Table();
    if (!$db_man->table_exists('microlearning_users')) {
        $db_man->create_table($tbl_micro_users);
    }//if_microlearnign_users

    /* Microlearning Deliveries Table   */
    $tbl_micro_deliveries = MicroInstall::Get_MicrolearningDeliveries_Table();
    if (!$db_man->table_exists('microlearning_deliveries')) {
        $db_man->create_table($tbl_micro_deliveries);
    }//if_microlearnign_deliveries

    /* Last time executed   */
    set_config('lastexecution', 0, 'local_microlearning');
    return true;
}//xmldb_local_microlearning_install


class MicroInstall {
    /**
     * @return          null|xmldb_table
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the structure table for Microlearning table
     */
    public static function Get_MicroLearning_Table() {
        /* Variables    */
        $tbl_micro_learning = null;

        /* Create Table */
        $tbl_micro_learning = new xmldb_table('microlearning');

        /* Add Fields   */
        /* Id                   -->     Primary Key                 */
        $tbl_micro_learning->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Course Id            -->     Foreign Key - Course Table  */
        $tbl_micro_learning->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Module               --> Type of Activity                    */
        $tbl_micro_learning->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
        /* Type                 -->     Not Null.Index              */
        $tbl_micro_learning->add_field('type',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);
        /* Activate                 -->     True/False                          */
        $tbl_micro_learning->add_field('activate',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,1);
        /* Duplicated From  */
        $tbl_micro_learning->add_field('duplicated_from',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Add automatically new users  */
        $tbl_micro_learning->add_field('addusers',XMLDB_TYPE_INTEGER,1,null,XMLDB_NOTNULL,null,0);
        /* Timecreated          -->     Date Created                        */
        $tbl_micro_learning->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

        /* Add Keys */
        $tbl_micro_learning->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_learning->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'), 'course', array('id'));


        return $tbl_micro_learning;
    }//Get_MicroLearning_Table

    /**
     * @return          null|xmldb_table
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the structure table for Microlearning Activities List - Table
     */
    public static function Get_MicrolearnigActivities_Table() {
        /* Variables    */
        $tbl_micro_activities = null;

        /* Create Table */
        $tbl_micro_activities = new xmldb_table('microlearning_activities');

        /* Add Fields   */
        /* Id                   --> Primary Key                         */
        $tbl_micro_activities->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Microlearnig Id          -->     Foreign Key - Microlearning table                       */
        $tbl_micro_activities->add_field('microid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* MicroModeId      --> Foreign Key     --> Activity Mode or Calendar Mode tables   */
        $tbl_micro_activities->add_field('micromodeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Token    */
        $tbl_micro_activities->add_field('microkey',XMLDB_TYPE_CHAR,'100',null,XMLDB_NOTNULL,null,null);
        /* Activity Id          --> Not Null                            */
        $tbl_micro_activities->add_field('activityid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Module               --> Activity name                    */
        $tbl_micro_activities->add_field('name',XMLDB_TYPE_CHAR,'250',null, XMLDB_NOTNULL, null,null);
        /* Type Activity                                             */
        $tbl_micro_activities->add_field('module',XMLDB_TYPE_CHAR,'20',null, XMLDB_NOTNULL, null,null);

        /* Add Keys     */
        $tbl_micro_activities->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_activities->add_key('microid',XMLDB_KEY_FOREIGN,array('microid'), 'microlearning', array('id'));

        return $tbl_micro_activities;
    }//Get_MicrolearnigActivitiesList_Table

    /**
     * @return          null
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the structure table for Microlearning Calendar Mode
     */
    public static function Get_MicrolearningCalendar_Table() {
        /* Variables    */
        $tbl_micro_calendar = null;

        /* Create Table */
        $tbl_micro_calendar = new xmldb_table('microlearning_calendar_mode');

        /* Add Fields   */
        /* Id                       -->     Primary Key                                             */
        $tbl_micro_calendar->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Microlearnig Id          -->     Foreign Key - Microlearning table                       */
        $tbl_micro_calendar->add_field('microid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Token    */
        $tbl_micro_calendar->add_field('microkey',XMLDB_TYPE_CHAR,'100',null,XMLDB_NOTNULL,null,null);
        /* Date to Send             -->     Date to send the activity                               */
        $tbl_micro_calendar->add_field('datesend',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Date to Send not After   -->     Send if the selected activity is not done after X days  */
        $tbl_micro_calendar->add_field('dateafter',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Days After               -->     X Days after                                            */
        $tbl_micro_calendar->add_field('daysafter',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Activity not After       -->     Activity is not done after X days                       */
        $tbl_micro_calendar->add_field('activityafter',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Subject              -->     Not Null.                   */
        $tbl_micro_calendar->add_field('subject',XMLDB_TYPE_TEXT,null,null, null);
        /* Body                 -->     Not Null.                   */
        $tbl_micro_calendar->add_field('body',XMLDB_TYPE_TEXT,null,null, null);
        /* Timesent             -->     Delivery Date                       */
        $tbl_micro_calendar->add_field('timesent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

        /* Add Keys */
        $tbl_micro_calendar->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_calendar->add_key('microid',XMLDB_KEY_FOREIGN,array('microid'), 'microlearning', array('id'));

        return $tbl_micro_calendar;
    }//Get_MicrolearningCalendar_Table


    /**
     * @return          null
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the structure table for Microlearnig Activity Mode
     */
    public static function Get_MicroLearningActivity_Table() {
        /* Variables    */
        $tbl_micro_activity = null;

        /* Create Table */
        $tbl_micro_activity = new xmldb_table('microlearning_activity_mode');

        /* Add fields       */
        /* Id                                       -->     Primary key.                                            */
        $tbl_micro_activity->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Microlearning Id                         -->     Foreign Key  Microlearning table                        */
        $tbl_micro_activity->add_field('microid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Token    */
        $tbl_micro_activity->add_field('microkey',XMLDB_TYPE_CHAR,'100',null,XMLDB_NOTNULL,null,null);
        /* Send After Enrol                         -->     Send X Days after enrolement                            */
        $tbl_micro_activity->add_field('afterenrol',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Send After Completion                    -->     Send X Days after completion an activity                */
        $tbl_micro_activity->add_field('aftercompletion',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Activity Completion                      -->     After completion an activity                            */
        $tbl_micro_activity->add_field('tocomplete',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Send X days after activity not completed -->     Send X Ddays after an activity is not completed         */
        $tbl_micro_activity->add_field('afternotcompletion',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Activity Not Completed                   -->     After not completed activity                            */
        $tbl_micro_activity->add_field('notcomplete',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Subject              -->     Not Null.                   */
        $tbl_micro_activity->add_field('subject',XMLDB_TYPE_TEXT,null,null, null);
        /* Body                 -->     Not Null.                   */
        $tbl_micro_activity->add_field('body',XMLDB_TYPE_TEXT,null,null, null);
        /* Timesent             -->     Delivery Date                       */
        $tbl_micro_activity->add_field('timesent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

        /* Add Index */
        $tbl_micro_activity->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_activity->add_key('microid',XMLDB_KEY_FOREIGN,array('microid'), 'microlearning', array('id'));

        return $tbl_micro_activity;
    }//Get_MicroLearningActivity_Table

    /**
     * @return          null
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the table structure for Microlearnig Users
     */
    public static function Get_MicrolearningUsers_Table() {
        /* Variables        */
        $tbl_micro_users = null;

        /* Create Table */
        $tbl_micro_users = new xmldb_table('microlearning_users');

        /* Add fields   */
        /* Id                   -->     Primary Key                         */
        $tbl_micro_users->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Microlearning Id     -->     Foreign Key - Microlearning table   */
        $tbl_micro_users->add_field('microid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Userid               -->     Foreign Key - User table            */
        $tbl_micro_users->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

        /* Add Index   */
        $tbl_micro_users->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_users->add_key('microid',XMLDB_KEY_FOREIGN,array('microid'), 'microlearning', array('id'));
        $tbl_micro_users->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

        return $tbl_micro_users;
    }//Get_MicrolearningUsers_Table



    /**
     * @return          null|xmldb_table
     *
     * @creationDate    16/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the table structure for Microlearning Deliveries
     */
    public static function Get_MicrolearningDeliveries_Table() {
        /* Variables        */
        $tbl_micro_deliveries = null;

        /* Create Table */
        $tbl_micro_deliveries = new xmldb_table('microlearning_deliveries');

        /* Add fields   */
        /* Id               --> Primary Key                                                 */
        $tbl_micro_deliveries->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* Microlearnig Id          -->     Foreign Key - Microlearning table                       */
        $tbl_micro_deliveries->add_field('microid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* MicroModeId      --> Foreign Key     --> Activity Mode or Calendar Mode tables   */
        $tbl_micro_deliveries->add_field('micromodeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* User Id          --> Foreign Key     --> User and Microlearning Users Tables     */
        $tbl_micro_deliveries->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
        /* Sent             --> True/False                                                  */
        $tbl_micro_deliveries->add_field('sent',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
        /* Messages */
        $tbl_micro_deliveries->add_field('message',XMLDB_TYPE_TEXT,null,null, null, null,null);
        /* Time to Send     --> Not Null                                                    */
        $tbl_micro_deliveries->add_field('timetosend',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Time Sent        */
        $tbl_micro_deliveries->add_field('timesent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
        /* Time Modified    */
        $tbl_micro_deliveries->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

        /* Add index   */
        $tbl_micro_deliveries->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $tbl_micro_deliveries->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));
        $tbl_micro_deliveries->add_key('microid',XMLDB_KEY_FOREIGN,array('microid'), 'microlearning', array('id'));

        return $tbl_micro_deliveries;
    }//Get_MicrolearningDeliveries_Table
}//MicroInstall
