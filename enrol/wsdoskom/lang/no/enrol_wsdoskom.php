<?php

/**
 * Single Sign On Enrolment Plugin - Language Strings (Norwegian)
 *
 * @package         enrol/wsdoskom
 * @subpackage      lang/no
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 */

$string['pluginname']       = 'Dossier Læring innmelding';
$string['pluginname_desc']  = 'Dossier Læring innmelding:
<ul>
    <li>Direkte innlogging fra Dossier Læring.</li>
</ul>';

$string['wsdoskom:config']     = 'Konfigurer Dossier Læring innmelding instanser';
$string['wsdoskom:manage']     = 'Administrere innmeldte brukere';
$string['wsdoskom:unenrol']    = 'Utmelding av brukere fra kurset';

$string['show_applications']        = 'Vis søknader';

$string['defaultrole']              = 'Standard tildelt rolle ved innmelding';
$string['defaultrole_desc']         = 'Velg den rollen som brukeren skal tildeles ved innmelding fra Dossier Læring';

$string['enrol_period']             = 'Innmeldingens varighet';
$string['enrol_period_desc']        = 'Standard varighet for hvor lenge en innmelding skal være gyldig (i sekunder). Dersom satt til null, vil innmeldingsperioden være uendelig.';
$string['enrol_period_help']        = 'Varighet for innmeldingen, regnet fra innmeldingstidspunktet, når brukeren melder seg på selv. Dersom deaktivert, vil innmeldingsvarigheten være uendelig.';

$string['role']                     = 'Standard tildelt rolle';
$string['enrol_start_date']         = 'Start dato';
$string['enrol_start_date_help']    = 'Hvis aktivert, vil brukerne kunne melde seg på selv først fra denne datoen.';

$string['enrol_end_date']           = 'Utløpsdato';
$string['enrol_end_date_help']      = 'Hvis aktivert, vil brukerne kunne melde seg på selv fram til denne datoen. Etter denne datoen behandles ikke søknadene.';

$string['enrol_end_date_error']     = 'Utløpsdatoen kan ikke være tidligere enn startdatoen';

$string['required']                 = 'Dette feltet er obligatorisk';

$string['sel_company']      = 'Available companies';
$string['company']          = 'Company';
$string['not_sel_company']  = 'None companies';
$string['selected_company'] = 'Selected companies';

$string['maxenrolled']      = 'Maks antall påmeldte brukere';
$string['maxenrolled_help'] = 'Angir maks antall brukere som kan melde seg på selv. -0- betyr at det ikke er noen øvre grense.';

$string['add_all']      = 'Add all';
$string['remove_all']   = 'Remove all';