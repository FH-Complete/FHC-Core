<?php

/* Copyright (C) 2012 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors:		Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/webservicerecht.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehreinheitmitarbeiter.class.php');
require_once('../include/lehreinheitgruppe.class.php');
require_once('../include/studiengang.class.php');

ini_set("soap.wsdl_cache_enabled", "0");

$SOAPServer = new SoapServer(APP_ROOT."/soap/lehrveranstaltung.wsdl.php?".microtime(true));
$SOAPServer->addFunction("getLehrveranstaltungFromId");
$SOAPServer->addFunction("getLehrveranstaltungFromStudiengang");
$SOAPServer->handle();

/*
 *
 * Funktion getLehrveranstaltungFromId liefert eine LV  zurück
 * @param lehrveranstaltung_id - Lehrveranstaltungs ID -> Pflichtfeld
 * @param semester - SemesterKurzbz -> Optional
 * @param authentifizierung - Array mit Username und Passwort -> Pflichtfeld
 *
 */
function getLehrveranstaltungFromId($lehrveranstaltung_id, $semester, $authentifizierung)
{
    if($lehrveranstaltung_id == '')
        return new SOAPFault("Server", "lehrveranstaltungs_id must be set");

    $user = $authentifizierung->username;
    $passwort = $authentifizierung->passwort;
    $lv_id = $lehrveranstaltung_id;

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");

    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht();
    if(!$recht->isUserAuthorized($user, 'getLehrveranstaltungFromId'))
        return new SoapFault("Server", "No permission");

    // Daten für Lehrveranstaltung
    $lv = new lehrveranstaltung();
    if(!$lv->load($lv_id))
        return new SoapFault("Server", "Error loading Lv");

    class foo{};
    $mitarbeiterlehreinheit = array(); // uids aller mitarbeiter
    $gruppelehreinheit = array(); // objekte aller gruppen

    // wenn semester nicht übergeben wurde, gib nur bezeichnung und lehreverzeichnis aus
    if($semester != '')
    {
        // hole alle Lehreinheiten von Lehrveranstaltung
        $lehreinheit = new lehreinheit();
        if(!$lehreinheit->load_lehreinheiten($lv_id, $semester))
            return new SoapFault("Server", $lehreinheit->errormsg);

        foreach($lehreinheit->lehreinheiten as $l)
        {
            // alle mitarbeiter einer lehreinheit
            $mitarbeiter = new lehreinheitmitarbeiter();
            $mitarbeiter->getLehreinheitmitarbeiter($l->lehreinheit_id);
            foreach($mitarbeiter->lehreinheitmitarbeiter as $m)
                $mitarbeiterlehreinheit[]=$m->mitarbeiter_uid;

            // alle gruppen einer lehreinheit
            $gruppe = new lehreinheitgruppe();
            $gruppe->getLehreinheitgruppe($l->lehreinheit_id);
            foreach($gruppe->lehreinheitgruppe as $g)
            {
                $grp = new foo();
                $grp->studiengang_kz = $g->studiengang_kz;
                $grp->semester=$g->semester;
                $grp->verband=$g->verband;
                $grp->gruppe=$g->gruppe;
                $grp->grupppe_kurzbz=$g->gruppe_kurzbz;
                $gruppelehreinheit[] = $grp;
            }
        }
    }

    $LvObject = new foo();
    $LvObject->bezeichnung = $lv->bezeichnung;
    $LvObject->lehreverzeichnis = $lv->lehreverzeichnis;
    $LvObject->lektoren = $mitarbeiterlehreinheit;
    $LvObject->gruppen= $gruppelehreinheit;

    // lösche alle Attribute für die user keine Berechtigung hat
    $LvObject = $recht->clearResponse($user, 'getLehrveranstaltungFromId', $LvObject);

    return $LvObject;
}

/*
 * Funktion getLehrveranstaltungFromStudiengang liefert alle Lv Infos eines Studienganges und Semesters
 * @param studiengang - OE von Studiengang -> Pflichtfeld
 * @param semester - Semester_kurzbz -> Pflichtfeld
 * @param ausbildungssemester - Ausbildungssemester -> Optional
 * @param authentifizierung - Array mit Username und Passwort -> Pflichtfeld
 *
*/
function getLehrveranstaltungFromStudiengang($studiengang, $semester, $ausbildungssemester, $authentifizierung)
{

    $user = $authentifizierung->username;
    $passwort = $authentifizierung->passwort;

    if($studiengang == '' || $semester == '')
        return new SOAPFault("Server", "studiengang | semester must be set");

    // User authentifizieren
    if(!check_user($user, $passwort))
        return new SoapFault("Server", "Invalid Credentials");

    // darf user überhaupt was von Methode sehen
    $recht = new webservicerecht();
    if(!$recht->isUserAuthorized($user, 'getLehrveranstaltungFromStudiengang'))
        return new SoapFault("Server", "No permission");

    // Daten für Lehrveranstaltung
    $lehrveranstaltung = new lehrveranstaltung();
    $stud = new studiengang();

    if(!$stud->load($studiengang))
        return new SoapFault ("Server", "Error loading Studiengang");

    if(!$lehrveranstaltung->load_lva_le($stud->studiengang_kz, $semester, $ausbildungssemester))
        return new SoapFault("Server", "Error loading Lv");

    class bar{};
    $lvFromStudiengang= array();

    foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
    {
        $mitarbeiterlehreinheit = array(); // uids aller mitarbeiter der lehreinheit
        $gruppelehreinheit = array(); // ids aller grupper der lehreinheit

        // hole alle Lehreinheiten von Lehrveranstaltung
        $lehreinheit = new lehreinheit();
        if(!$lehreinheit->load_lehreinheiten($lv->lehrveranstaltung_id, $semester))
            return new SoapFault("Server", $lehreinheit->errormsg);




        foreach($lehreinheit->lehreinheiten as $l)
        {
            // alle mitarbeiter der lehreinheit
            $mitarbeiter = new lehreinheitmitarbeiter();
            $mitarbeiter->getLehreinheitmitarbeiter($l->lehreinheit_id);
            foreach($mitarbeiter->lehreinheitmitarbeiter as $m)
                $mitarbeiterlehreinheit[]=$m->mitarbeiter_uid;

            // alle gruppen der lehreinheit
            $gruppe = new lehreinheitgruppe();
            $gruppe->getLehreinheitgruppe($l->lehreinheit_id);
            foreach($gruppe->lehreinheitgruppe as $g)
            {
                $grp = new bar();
                $grp->studiengang_kz = $g->studiengang_kz;
                $grp->semester=$g->semester;
                $grp->verband=$g->verband;
                $grp->gruppe=$g->gruppe;
                $grp->grupppe_kurzbz=$g->gruppe_kurzbz;
                $gruppelehreinheit[] = $grp;
            }
        }

        // LV Object für Rückgabe
        $lehrveranstaltungen = new bar();
        $lehrveranstaltungen->bezeichnung = $lv->bezeichnung;
        $lehrveranstaltungen->lehreverzeichnis = $lv->lehreverzeichnis;
        $lehrveranstaltungen->lektoren = $mitarbeiterlehreinheit;
        $lehrveranstaltungen->gruppen = $gruppelehreinheit;

        $lehrveranstaltungen = $recht->clearResponse($user, 'getLehrveranstaltungFromStudiengang', $lehrveranstaltungen);

        $lvFromStudiengang[] = $lehrveranstaltungen;
    }
    return ($lvFromStudiengang);
}
?>
