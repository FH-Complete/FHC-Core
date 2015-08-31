<?php
/* Copyright (C) 2014 FH fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/adresse.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/bankverbindung.class.php');
require_once('../include/vertrag.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/lehreinheitmitarbeiter.class.php');
require_once('../include/datum.class.php');
require_once('../include/nation.class.php');

$mitarbeiter_uid = isset($_GET["mitarbeiter_uid"]) ? $_GET["mitarbeiter_uid"] : NULL;
$vertrag_data = isset($_GET["vertrag_id"]) ? $_GET["vertrag_id"] : NULL;

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
echo "<honorarvertraege>";

if($mitarbeiter_uid === NULL)
{
    echo "<error>Mitarbeiter UID fehlt</error>";
}
else if($vertrag_data === NULL)
{
    echo "<error>Vertrags-ID fehlt</error>";
}
else
{
    $ma = new mitarbeiter($mitarbeiter_uid);
    $adresse = new adresse();
    $adresse->load_pers($ma->person_id);
    $kontakt = new kontakt();
    $bankverbindung = new bankverbindung();
    $datum = new datum();
    $nation = new nation($ma->staatsbuergerschaft);
    
    echo "<honorarvertrag>";
    echo "<person>";
	echo "<vorname>".$ma->vorname."</vorname>";
	echo "<nachname>".$ma->nachname."</nachname>";
	echo "<titelpre>".$ma->titelpre."</titelpre>";
	echo "<titelpost>".$ma->titelpost."</titelpost>";
	foreach($adresse->result as $adr)
	{
	    if($adr->heimatadresse)
	    {
		echo "<strasse>".$adr->strasse."</strasse>";
		echo "<plz>".$adr->plz."</plz>";
		echo "<ort>".$adr->ort."</ort>";
	    }
	}
	echo "<gebdat>".$datum->formatDatum($ma->gebdatum,'d.m.Y')."</gebdat>";
	echo "<svnr>".$ma->svnr."</svnr>";
	echo "<staat>".$nation->kurztext."</staat>";
	switch ($ma->familienstand) {
	    case "l":
		$familienstand = "ledig";
		break;
	    case "v":
		$familienstand = "verheiratet";
		break;
	    case "g":
		$familienstand = "geschieden";
		break;
	    case "w":
		$familienstand = "verwitwet";
		break;
	    default:
		$familienstand = "";
		break;
	}
	echo "<familienstand>".$familienstand."</familienstand>";
	echo "<personalnr>".$ma->personalnummer."</personalnr>";
	$kontakt->load_persKontakttyp($ma->person_id, "telefon");
	if(!empty($kontakt->result))
	{
	    echo "<tel>".$kontakt->result[0]->kontakt."</tel>";
	    unset($kontakt->result);
	}
	$kontakt->load_persKontakttyp($ma->person_id, "email");
	if(!empty($kontakt->result))
	{
	    echo "<email>".$kontakt->result[0]->kontakt."</email>";
	    unset($kontakt->result);
	}
    echo "</person>";
    echo "<bankverbindung>";
    $bankverbindung->load_pers($ma->person_id);
    foreach($bankverbindung->result as $bank)
    {
	if($bank->verrechnung)
	{
	    echo "<name>".$bank->name."</name>";
	    echo "<iban>".$bank->iban."</iban>";
	    echo "<bic>".$bank->bic."</bic>";
	    echo "<blz>".$bank->blz."</blz>";
	    echo "<kontonr>".$bank->kontonr."</kontonr>";
	    break;
	}
    }
    echo "</bankverbindung>";
    echo "<vertraege>";

//	$vertrag->getAllStatus($vertrag_id);
    $summe = 0;
    $studiensemester = "";
    foreach($vertrag_data as $vertrag_id)
    {
	$vertrag = new vertrag();
	$vertrag->load($vertrag_id);
	$vertrag->getAllStatus($vertrag_id);
	foreach($vertrag->result as $status)
	{
	    if($vertrag->vertragstyp_kurzbz == "lehre" && $status->vertragsstatus_kurzbz == "genehmigt")
	    {
		$v_temp = new vertrag();
		$v_temp->loadZugeordnet($vertrag_id);
		$lehreinheit = new lehreinheitmitarbeiter($v_temp->result[0]->lehreinheit_id,$ma->uid);
		$le = new lehreinheit($lehreinheit->lehreinheit_id);
		$studiensemester = new studiensemester($le->studiensemester_kurzbz);
		$summe += $v_temp->result[0]->betrag;
		echo "<lehrvertrag>";
		    echo "<genehmigungs_datum>".$datum->formatDatum($status->datum,'d.m.Y')."</genehmigungs_datum>";
		    echo "<aufwandspunkte>".$lehreinheit->semesterstunden."</aufwandspunkte>";
		    echo "<stundensatz>".$lehreinheit->stundensatz."</stundensatz>";
		echo "</lehrvertrag>";
	    }
	    if($vertrag->vertragstyp_kurzbz == "fahrtkosten" && $status->vertragsstatus_kurzbz == "genehmigt")
	    {
		$anzahl_fahrten = explode(";", $vertrag->anmerkung);
		$anzahl_fahrten = explode(" ", $anzahl_fahrten[0]);
		echo "<fahrtkosten>";
		    echo "<genehmigungs_datum>".$datum->formatDatum($status->datum,'d.m.Y')."</genehmigungs_datum>";
		    echo "<summe>".$vertrag->betrag."</summe>";
		    echo "<anzahl_fahrten>".$anzahl_fahrten[0]."</anzahl_fahrten>";
		    echo "<preis_je_fahrt>".$anzahl_fahrten[5]."</preis_je_fahrt>";
		    echo "<abfahrt>";
		    for($i = 7; $i < count($anzahl_fahrten); $i++)
		    {
			echo $anzahl_fahrten[$i]." ";
		    }    
		    echo "</abfahrt>";
		echo "</fahrtkosten>";
	    }
	}
	if($summe != 0)
	    echo "<lehrvertrag_summe>".$summe."</lehrvertrag_summe>";
	$summe = 0;
    }
    echo "</vertraege>";
    echo "<studiensemester>".$studiensemester->bezeichnung."</studiensemester>";
    switch(substr($studiensemester->studiensemester_kurzbz, 0,2))
    {
	case "WS":
	    echo "<zeitraum>von September bis Februar</zeitraum>";
	    break;
	
	case "SS":
	    echo "<zeitraum>von März bis August</zeitraum>";
	    break;
    }
}
echo "</honorarvertrag>";
echo "</honorarvertraege>";