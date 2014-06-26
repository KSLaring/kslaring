<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the certificate module
 *
 * @package    mod
 * @subpackage certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addlinklabel'] = 'Legg til en ekstra aktivitetslenke';
$string['addlinktitle'] = 'Klikk her for legge til en ekstra aktivitetslenke';
$string['areaintro'] = 'Kursbevis introduksjon';
$string['awarded'] = 'Tildelt';
$string['awardedto'] = 'Tildelt til';
$string['back'] = 'Tilbake';
$string['border'] = 'Kantlinje';
$string['borderblack'] = 'Sort';
$string['borderblue'] = 'Blå';
$string['borderbrown'] = 'Brun';
$string['bordercolor'] = 'Farge på kantlinjer';
$string['bordercolor_help'] = 'Siden grafikk kan medføre at pdf-fila blir unødig stor, anbefaler vi at du trykker en kantlinje i stedet for grafikk med kantlinjeornamentikk. (Sjekk at valget for Kantlinjegrafikk er satt til "Nei"). Kantlinje-valget vil trykke en pen kantlinje i tre linjetykkelser basert på valgt farge.';
$string['bordergreen'] = 'Grønn';
$string['borderlines'] = 'Linjer';
$string['borderstyle'] = 'Kantlinjegrafikk';
$string['borderstyle_help'] = 'Kantlinjegrafikk-valget lar deg velge et grafisk bilde for å vise kantlinjer. Grafikken hentes fra certificate/pix/borders mappen. Velg den kantgrafikken som du ønsker langs kursbevisets kanter eller velg "Ingen kantlinje"';
$string['certificate'] = 'Verifisering av kursbeviskode:';
$string['certificate:addinstance'] = 'Legg til et kursbevis';
$string['certificate:manage'] = 'Administrere kursbevis';
$string['certificate:printteacher'] = 'Bli vist som lærer ¨på kursbeviset hvis "Ta med lærers navn"- valget er aktivert';
$string['certificate:student'] = 'Motta et kursbevis';
$string['certificate:view'] = 'Vis et kursbevis';
$string['certificatename'] = 'Kursbevisnavn';
$string['certificatereport'] = 'Kursbevisrapport';
$string['certificatesfor'] = 'Kursbevis for';
$string['certificatetype'] = 'Kursbevis type';
$string['certificatetype_help'] = 'Her bestemmer du hvordan kursbeviset skal se ut. Kursbevisets "type"-mappe har fire standard kursbevistyper:
	A4 med innebygde fonter skriver ut i A4 og bygger inn fontene slik at det blir likt uansett fonter brukeren har på egen pc.
	A4 uten innebygde fonter skriver ut i A4 og er avhengig av at brukeren har samme fonter på egen pc.
	Letter med innebygde fonter skriver ut i Letter størrelse og bygger inn fontene slik at det blir likt uansett fonter brukeren har på egen pc.
	Letter uten innebygde fonter skriver ut i Letter og bygger inn fontene slik at det blir likt uansett fonter brukeren har på egen pc.
	
	Kursbevistyper uten innebygde fonter bruker Helvetica og Times fonter. Dersom du tror at noen brukere ikke har disse fontene, eller fordi akkurat ditt språk bruker bokstaver og symboler som ikke er en del av disse to fontene, bør du bruke en kursbevistype med innebygde fonter. 
	Kursbevistyper med innebygde fonter bruker Dejavusans og Dejavuserif fonter. Dette vil øke filstørrelsen markant, så ikke bruk en kursbevistype med innebygde fonter hvis du ikke absolutt må.
	
	Du kan legge til nye kursbevistyper i certificate/type mappen. Navnet på mappen og nye språkstrenger for den nye kursbevistypen må legges til i kursbevisets språk-fil.';
$string['certify'] = 'Dette bekrefter at';
$string['code'] = 'Kursbeviskode';
$string['completiondate'] = 'Kursfullføring';
$string['course'] = 'For';
$string['coursegrade'] = 'Kurskarakter';
$string['coursename'] = 'Kurs';
$string['coursetimereq'] = 'Minstekrav til varighet (i minutter)';
$string['coursetimereq_help'] = 'Her kan du angi minstekravet til medgått tid, i minutter, som en bruker må være innlogget i kurset før kursbeviset gjøres tilgjengelig for dem.';
$string['credithours'] = 'Varighet';
$string['customtext'] = 'Egendefinert tekst';
$string['customtext_help'] = 'Hvis du ønsker at kursbeviset skal skrive ut med et annet navn enn læreren i kurset, må du la være å skrive ut med lærer eller signaturfil, unntatt underskriftslinje-bildet. Skriv inn lærerens navn i dette tekstfeltet og det blir brukt i stedet. Som standard vises det neders til venstre. Følgende html-tagger er tilgjengelige:
	&lt;br&gt;, &lt;p&gt;, &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;img&gt; (src and width (or height) are mandatory), &lt;a&gt; (href is mandatory), &lt;font&gt; (possible attributes are: color, (hex color code), face, (arial, times, courier, helvetica, symbol)).';
$string['date'] = 'På';
$string['datefmt'] = 'Datoformat';
$string['datefmt_help'] = 'Velg et datoformat for datoen som skal vises på kursbeviset. ELLER, velg siste opsjon for å la datoformatet styres av brukerens valgte språk.';
$string['datehelp'] = 'Dato';
$string['deletissuedcertificates'] = 'Slett tildelte kursbevis';
$string['delivery'] = 'Levering';
$string['delivery_help'] = 'Her velger du hvordan du vil brukeren skal få kursbeviset utstedt.
Åpne i nettleseren: Åpner kursbeviset i et nytt vindu.
Tving nedlasting: Kursbeviset blir lastet ned automatisk.
Etter at brukeren har mottatt kursbeviset kan de klikke på en lenke i kursmenyen for å se dato for når kursbeviset ble skrevet ut og de kan også se mottatte kursbevis.';
$string['designoptions'] = 'Designinnstillinger';
$string['download'] = 'Tving nedlasting';
$string['emailcertificate'] = 'Send som e-post (Må også lagre!)';
$string['emailothers'] = 'E-post til andre';
$string['emailothers_help'] = 'Skriv inn e-postadressene her, separert med komma, for alle som skal varsles når studenter mottar et kursbevis.';
$string['emailstudenttext'] = 'Vedlagt følger kursbeviset for {$a->course}.';
$string['emailteachers'] = 'Send e-post til lærere';
$string['emailteachers_help'] = 'Når aktivert vil lærere bli varslet via e-post hver gang studenter mottar et kursbevis.';
$string['emailteachermail'] = '
{$a->student} har mottatt kursbeviset: \'{$a->certificate}\'
for {$a->course}.

Du kan se på det her:

    {$a->url}';
$string['emailteachermailhtml'] = '
{$a->student} har mottatt kursbeviset: \'<i>{$a->certificate}</i>\'
for {$a->course}.

Du kan se på det her:

    <a href="{$a->url}">Kursbevisrapport</a>.';
$string['entercode'] = 'Skriv inn kursbeviskoden for å verifisere:';
$string['getcertificate'] = 'Hent kursbeviset';
$string['grade'] = 'Karakter';
$string['gradedate'] = 'Karakter datert';
$string['gradefmt'] = 'Karakterformat';
$string['gradefmt_help'] = 'Det er tre tilgjengelige formater hvis du ønsker å ta med karakteren på kursbeviset:
	Karakter i prosent: Viser karakteren som en prosentandel
	Karakterer som poeng: Viser karakteren som en poengverdi
	Bokstavkarakterer: Viser karakteren som en bokstav';
$string['gradeletter'] = 'Bokstavkarakter';
$string['gradepercent'] = 'Karakter i prosent';
$string['gradepoints'] = 'Karakter som poeng';
$string['imagetype'] = 'Bildetype';
$string['incompletemessage'] = 'For å kunne laste ned kursbeviset må du først ha fullført alle påkrevde aktiviteter. Vennligst gå tilbake til kurset og fullfør før du prøver igjen.';
$string['intro'] = 'Introduksjon';
$string['issueoptions'] = 'Tildelingskriterier';
$string['issued'] = 'Tildelt';
$string['issueddate'] = 'Dato tildelt';
$string['landscape'] = 'Liggende';
$string['lastviewed'] = 'Du mottok sist dette kursbeviset den:';
$string['letter'] = 'Letter';
$string['lockingoptions'] = 'Innstillinger for låsing';
$string['modulename'] = 'Kursbevis';
$string['modulenameplural'] = 'Kursbevis';
$string['mycertificates'] = 'Mine kursbevis';
$string['nocertificates'] = 'Det finnes ingen kursbevis';
$string['nocertificatesissued'] = 'Det er foreløpig ikke tildelt noen kursbevis';
$string['nocertificatesreceived'] = 'har ikke mottatt noen kursbevis.';
$string['nofileselected'] = 'Du må velge en fil for opplasting!';
$string['nogrades'] = 'Ingen tilgjengelige karakterer';
$string['notapplicable'] = 'Ikke aktuelt';
$string['notfound'] = 'Kursbeviskoden kunne ikke valideres.';
$string['notissued'] = 'Ikke tildelt';
$string['notissuedyet'] = 'Ikke tildelt ennå';
$string['notreceived'] = 'Du har ikke mottatt dette kursbeviset';
$string['openbrowser'] = 'Åpne i nytt vindu';
$string['opendownload'] = 'Klikk på knappen under for å lagre kursbeviset på din egen datamaskin.';
$string['openemail'] = 'Klikk på knappen under og kursbeviset vil da bli sendt deg som et e-postvedlegg.';
$string['openwindow'] = 'Klikk på knappen under for å åpne kursbeviset i et nytt vindu.';
$string['or'] = 'eller';
$string['orientation'] = 'Papirretning';
$string['orientation_help'] = 'Velg om du ønsker papirretningen liggende eller portrett.';
$string['pluginadministration'] = 'Kursbevisadministrasjon';
$string['pluginname'] = 'Kursbevis';
$string['portrait'] = 'Portrett';
$string['printdate'] = 'Utskriftsdato';
$string['printdate_help'] = 'Dette er datoen som blir vist, hvis utskriftsdato er valgt. Dersom kursets fullføringsdato er valgt, og studenten ikke har fúllført kurset, vil utskriftsdatoen bli benyttet. Du kan også velge utskriftsdato basert på når aktiviteten ble karaktersatt. Dersom et kursbevis er tildelt før aktiviteten er karaktersatt, vil utskriftsdatoen bli brukt.';
$string['printerfriendly'] = 'Skrivervennlig side';
$string['printhours'] = 'Vis kursomfang i timer';
$string['printhours_help'] = 'Her kan du skrive inn kursomfang i timer, som skal vises på kursbeviset.';
$string['printgrade'] = 'Vis karakter';
$string['printgrade_help'] = 'Du kan benytte deg av hvilken som helst karakter fra karakterboka når du skal vise brukerens karakter på kursbeviset. Karakterene vises i den rekkefølgen de forekommer i karakterboka. Velg formatet for karaktervisning under.';
$string['printnumber'] = 'Vis kursbeviskoden';
$string['printnumber_help'] = 'En unik 10-tegns kode av bokstaver og tall som kan vises på kursbeviset. Denne koden kan siden verifiseres mot kodene som er lagret i kursbevisrapporten.';
$string['printoutcome'] = 'Vis læringsutbytte';
$string['printoutcome_help'] = 'Du kan velge ett fra alle læringsutbyttene i kurset og dette blir da vist sammen med brukerens resultat på kursbeviset. Et engelsk eksempel kan være: Assignment Outcome: Proficient.';
$string['printseal'] = 'Segl- eller logobilde';
$string['printseal_help'] = 'Dette valget lar det velge en logo eller et segl so skal vises på kursbeviset. Grafikken hentes fra certificate/pix/seals mappen. Som standard blir bildet plassert i nedre høyre hjørne på kursbeviset.';
$string['printsignature'] = 'Signaturbilde';
$string['printsignature_help'] = 'Dette valget gjør at du kan vise et signaturbilde fra certificate/pix/signatures mappen.  Du kan vise en grafisk representasjon av signaturen din, eller en linje hvor du signerer manuelt. Som standard er dette bildet plassert i nedre venstre hjørne av kursbeviset.';
$string['printteacher'] = 'Vis lærerens navn';
$string['printteacher_help'] = 'For å vise lærers navn korrekt på kursbeviset må du legge til lærerrollen på modulnivå. Dette er viktig hvis det er flere lærere i kurset eller du har mer enn ett kursbevis og ønsker forskjellige lærere på forskjellige kursbevis. Klikk for å endre kursbeviset og deretter på fanen for lokalt tildelte roller. Deretter tildeler du  bruker(e) lærerrollen til kursbeviset (de trenger faktisk ikke være lærer i selve kurset). Alle med denne lokale lærerrollen blir da vist på kursbeviset.';
$string['printwmark'] = 'Vannmerkegrafikk';
$string['printwmark_help'] = 'Et grafisk bilde med vannmerke kan plasseres i bakgrunnen på kursbeviset. Et vannmerke er grafikk som er tonet ned og kan være en logo, et segl, våpenskjold, ord eller hva du måtte finne på å ha i bakgrunnen.';
$string['receivedcerts'] = 'Mottatte kursbevis';
$string['receiveddate'] = 'Dato mottatt';
$string['removecert'] = 'Tildelte kursbevis er fjernet';
$string['report'] = 'Rapport';
$string['reportcert'] = 'Kursbevisrapport';
$string['reportcert_help'] = 'Hvis du velger "Ja" her, vil dette kursbevisets dato mottatt, kursbeviskode og kursnavn vises i brukerens kursbevisrapport. Hvis du velger å vise karakteren på kursbeviset vil også denne tas med i kursbevisrapporten.';
$string['requiredtimenotmet'] = 'Du må bruke minst {$a->requiredtime} minutter på kurset før du kan få tilgang til dette kursbeviset.';
$string['requiredtimenotvalid'] = 'Minstekrav til tidsbruk må være et gyldig tall større enn -0-';
$string['reviewcertificate'] = 'Vis kursbeviset';
$string['savecert'] = 'Lagre kursbeviset';
$string['savecert_help'] = 'Hvis du velger dette, vil det lagres en kopi av hvert kursbevis som utstedes. Det vises også en lenke i kursbevisrapporten til hvert enkelt kursbevis.';
$string['seal'] = 'Segl';
$string['sigline'] = 'linje';
$string['signature'] = 'Signatur';
$string['statement'] = 'har fullført kurset';
$string['summaryofattempts'] = 'Sammendrag av tidligere mottatte kursbevis';
$string['textoptions'] = 'Tekstvalg';
$string['title'] = 'KURSBEVIS';
$string['to'] = 'Tildelt';
$string['typeA4_embedded'] = 'A4 Embedded';
$string['typeA4_non_embedded'] = 'A4 Non-Embedded';
$string['typeletter_embedded'] = 'Letter Embedded';
$string['typeletter_non_embedded'] = 'Letter Non-Embedded';
$string['unsupportedfiletype'] = 'Fila må være i formatene jpg eller png';
$string['uploadimage'] = 'Last opp bilde';
$string['uploadimagedesc'] = 'Denne knappen vil ta deg til et nytt skjermbilde hvor du selv kan laste opp bilder.';
$string['userdateformat'] = 'Brukerens lokale datoformat';
$string['validate'] = 'Verifiser';
$string['verifycertificate'] = 'Verifiser kursbeviset';
$string['viewcertificateviews'] = 'Vis {$a} tildelte kursbevis';
$string['viewed'] = 'Du mottok dette kursbeviset:';
$string['viewtranscript'] = 'Vis kursbevis';
$string['watermark'] = 'Vannmerke';
