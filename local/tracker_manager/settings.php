<?php
/**
 * Import Competence Data - Settings .
 *
 * @package         local
 * @subpackage      tracker
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    24/08/2015
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @updateDate  24/08/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add Competence Data Import -- Menu
 */

$url = new moodle_url('/report/manager/import_competence/import.php');


$ADMIN->add('accounts',
            new admin_externalpage('competence_import', get_string('upload_competence_imp', 'report_manager'),
            $url));
