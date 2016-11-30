<?php
/**
 * Local Course Home PAge Generator  - Draft File -- Home Summary
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    04/09/2014
 * @author          efaktor     (fbv)
 */
define('NO_DEBUG_DISPLAY', true);

require_once('../../config.php');
require_once('../../lib/filelib.php');
require_once('locallib.php');

$relativepath   = get_file_argument();
$forcedownload  = optional_param('forcedownload', 0, PARAM_BOOL);
$preview        = optional_param('preview', null, PARAM_ALPHANUM);

course_page::file_pluginfile_homepage($relativepath, $forcedownload, $preview);