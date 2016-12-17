<?php

defined('MOODLE_INTERNAL') || die;

/**
 * SCORM in a lightbox
 *
 * @package         local
 * @subpackage      edit_switch
 * @copyright       2014 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 */

function scorm_lightbox_simple_play($scorm, $user, $context, $cmid) {
    global $DB;

    $result = false;

    // If this user can view reports, don't skipview so they can see links to reports.
    if (has_capability('mod/scorm:viewreport', $context)) {
        return $result;
    }

    if ($scorm->scormtype != SCORM_TYPE_LOCAL && $scorm->updatefreq == SCORM_UPDATE_EVERYTIME) {
        scorm_parse($scorm, false);
    }
    $scoes = $DB->get_records_select('scorm_scoes', 'scorm = ? AND ' .
        $DB->sql_isnotempty('scorm_scoes', 'launch', false, true), array($scorm->id), 'sortorder, id', 'id');

    if ($scoes) {
        $orgidentifier = '';
        if ($sco = scorm_get_sco($scorm->launch, SCO_ONLY)) {
            if (($sco->organization == '') && ($sco->launch == '')) {
                $orgidentifier = $sco->identifier;
            } else {
                $orgidentifier = $sco->organization;
            }
        }
        if ($scorm->skipview >= SCORM_SKIPVIEW_FIRST) {
            $sco = current($scoes);
            $url = new moodle_url('/local/scorm_lightbox/player.php', array('a' => $scorm->id,
                'currentorg' => $orgidentifier,
                'scoid' => $sco->id));
            if ($scorm->skipview == SCORM_SKIPVIEW_ALWAYS || !scorm_has_tracks($scorm->id, $user->id)) {
                if (!empty($scorm->forcenewattempt)) {
                    $result = scorm_get_toc($user, $scorm, $cmid, TOCFULLURL, $orgidentifier);
                    if ($result->incomplete === false) {
                        $url->param('newattempt', 'on');
                    }
                }
                redirect($url);
            }
        }
    }

    return $result;
}
