<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['turnitin_link'] = 'https://technikum-wien.turnitin.com/sso/sp/redwood/saml/5IyfmBr2OcSIaWQTKlFCGj/start';

$config['old_abgabe_beurteilung_link'] = 'https://moodle.technikum-wien.at/mod/page/view.php?id=1005052';

$config['PAABGABE_EMAIL_JOB_INTERVAL'] = '1 day';
// used as APP_ROOT.URL_STUDENTS -> cis4
$config['URL_STUDENTS'] = 'cis.php/Cis/Abgabetool/Student';
// used as APP_ROOT.URL_MITARBEITER -> old cis 
$config['URL_MITARBEITER'] = 'index.ci.php/Cis/Abgabetool/Mitarbeiter';

// lehre.tbl_paabgabetyp bezeichnung
$config['ALLOWED_ABGABETYPEN_BETREUER'] = ['Zwischenabgabe', 'Quality Gate 1', 'Quality Gate 2'];
$config['ALLOWED_NOTEN_ABGABETOOL'] = ['Bestanden', 'Nicht bestanden'];