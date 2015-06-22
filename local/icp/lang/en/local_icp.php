<?php
/**
 * Inconsistencies Course Completions  - Language Settings (English)
 *
 * @package         local
 * @subpackage      icp/lang
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */

$string['pluginname']                   = 'Review Inconsistencies Course Completions ';
$string['icp:manage']               = 'Manage Course Completions';
$string['delete_are_you_sure']      = 'Are you sure you want to clean up the inconsistencies ?';
$string['none_inconsistencies']     = 'Congratulations! There is no inconsistency connected with course completion. All activity completions and course completions are correct.';
$string['inconsistencies_cleaned']  = '<p>The inconsistent completions have now been fixed.</p><p>You should wait about 30 minutes to see the changes in the Course Completion Report.</p><p>The completion cron must finish first.</p>';
$string['total_users']          = 'Number of users';
$string['description']          = 'Description';
$string['completed_with']       = 'Marked as completed despite inconsistencies';
$string['not_completed_with']   = 'Marked as not completed because of inconsistencies';

$string['clean']            = 'Clean Inconsistencies';

$string['start']    = 'Start';

$string['title_index']           = 'Review Inconsistencies';
$string['users_inconsistencies'] = 'Users with inconsistencies';

$string['still_inconsistencies'] = 'There are still users with inconsistencies. Before starting to find new inconsistencies, you should clean them.';

$string['err_process']  = 'There has been an error during the process. Please, try later.';
$string['info_icp']     = '<p><strong>This plugin checks for inconsistencies in the course completions.</strong></p><p> That might happen when you have courses with a lot of participants and change the course completion settings, the activity completion settings and so on AFTER the users have started to get the course registered as completed. IF you have metalinked courses as dependencies, you have to fix them first with this "Review inconsistencies" plugin and then wait at least 30 min before you proceed. Do that with ALL metalinked courses before you continue.</p><p>If you have opened this page by a mistake, please Cancel now. The plugin will not do any harm, but if everything already are ok, you can skip to proceed.</p>';