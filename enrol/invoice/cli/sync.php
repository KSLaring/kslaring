<?php
/**
 * Invoice Enrolment Method - Sync.
 *
 * @package         enrol/invoice
 * @subpackage      cli
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/09/2014
 * @author          efaktor     (fbv)
 *
 * CLI update for invoice enrolments, use for debugging or immediate update
 * of all courses.
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('verbose'=>false, 'help'=>false), array('v'=>'verbose', 'h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Execute invoice course enrol updates.

Options:
-v, --verbose         Print verbose progress information
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php enrol/invoice/cli/sync.php
";

    echo $help;
    die;
}

if (!enrol_is_enabled('invoice')) {
    cli_error('enrol_invoice plugin is disabled, synchronisation stopped', 2);
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

/** @var $plugin enrol_self_plugin */
$plugin = enrol_get_plugin('invoice');

$result = $plugin->sync($trace, null);
$plugin->send_expiry_notifications($trace);

exit($result);