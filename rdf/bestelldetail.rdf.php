<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/datum.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/wawi_bestellung.class.php');
require_once('../include/wawi_bestelldetail.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/adresse.class.php');
require_once('../include/firma.class.php');
require_once('../include/standort.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/wawi_aufteilung.class.php');
require_once('../include/studiengang.class.php');

if (isset($_REQUEST["xmlformat"]) && $_REQUEST["xmlformat"] == "xml")
{

	$bestellung = new wawi_bestellung();
	if(isset($_GET['id']))
	{
		if(!$bestellung->load($_GET['id']))
			die('Bestellung wurde nicht gefunden');

		$besteller = new benutzer();
		if(!$besteller->load($bestellung->besteller_uid))
			die('Besteller konnte nicht geladen werden');

		$konto = new wawi_konto();
		$konto->load($bestellung->konto_id);

		$kostenstelle = new wawi_kostenstelle();
		$kostenstelle->load($bestellung->kostenstelle_id);

		$rechnungsadresse = new adresse();
		$rechnungsadresse->load($bestellung->rechnungsadresse);

		$lieferadresse = new adresse();
		$lieferadresse->load($bestellung->lieferadresse);

		$aufteilung = new wawi_aufteilung();
		$aufteilung->getAufteilungFromBestellung($bestellung->bestellung_id);

		$studiengang = new studiengang();

		$firma = new firma();
		$standort = new standort();
		$empfaengeradresse = new adresse();
		if($bestellung->firma_id!='')
		{
			$firma->load($bestellung->firma_id);
			$kundennummer = $firma->get_kundennummer($bestellung->firma_id, $kostenstelle->oe_kurzbz);

			$standort->load_firma($firma->firma_id);
			if(isset($standort->result[0]))
				$standort = $standort->result[0];

			$empfaengeradresse->load($standort->adresse_id);
			$kontakt = new kontakt();
			$kontakt->loadFirmaKontakttyp($standort->standort_id, 'telefon');
			$telefon = $kontakt->kontakt;
			$kontakt = new kontakt();
			$kontakt->loadFirmaKontakttyp($standort->standort_id, 'fax');
			$fax = $kontakt->kontakt;
		}
		else
		{
			$telefon='';
			$fax='';
			$kundennummer='';
		}
		$datum_obj = new datum();

		header("Content-type: application/xhtml+xml");
		echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

		echo "\n<bestellungen><bestellung>\n";
		echo "	<bestell_nr><![CDATA[$bestellung->bestell_nr]]></bestell_nr>\n";
		echo "	<titel><![CDATA[$bestellung->titel]]></titel>\n";
		echo "	<liefertermin><![CDATA[$bestellung->liefertermin]]></liefertermin>\n";
		echo "	<kundennummer><![CDATA[$kundennummer]]></kundennummer>\n";
		echo "	<kontaktperson>\n";
		echo "		<titelpre><![CDATA[$besteller->titelpre]]></titelpre>\n";
		echo "		<vorname><![CDATA[$besteller->vorname]]></vorname>\n";
		echo "		<nachname><![CDATA[$besteller->nachname]]></nachname>\n";
		echo "		<titelpost><![CDATA[$besteller->titelpost]]></titelpost>\n";
		echo "		<email><![CDATA[",$besteller->uid,'@',DOMAIN,"]]></email>\n";
		echo "	</kontaktperson>\n";
		echo "	<konto><![CDATA[",$konto->kurzbz,"]]></konto>\n";
		echo "	<kostenstelle><![CDATA[$kostenstelle->bezeichnung]]></kostenstelle>\n";
		echo "	<rechnungsadresse>\n";
		echo "		<name><![CDATA[$rechnungsadresse->name]]></name>\n";
		echo "		<strasse><![CDATA[$rechnungsadresse->strasse]]></strasse>\n";
		echo "		<plz><![CDATA[$rechnungsadresse->plz]]></plz>\n";
		echo "		<ort><![CDATA[$rechnungsadresse->ort]]></ort>\n";
		echo "	</rechnungsadresse>\n";
		echo "	<lieferadresse>\n";
		echo "		<name><![CDATA[$lieferadresse->name]]></name>\n";
		echo "		<strasse><![CDATA[$lieferadresse->strasse]]></strasse>\n";
		echo "		<plz><![CDATA[$lieferadresse->plz]]></plz>\n";
		echo "		<ort><![CDATA[$lieferadresse->ort]]></ort>\n";
		echo "	</lieferadresse>\n";
		echo "	<empfaenger>\n";
		echo "		<name><![CDATA[$firma->name]]></name>\n";
		echo "		<strasse><![CDATA[$empfaengeradresse->strasse]]></strasse>\n";
		echo "		<plz><![CDATA[$empfaengeradresse->plz]]></plz>\n";
		echo "		<ort><![CDATA[$empfaengeradresse->ort]]></ort>\n";
		echo "		<telefon><![CDATA[$telefon]]></telefon>\n";
		echo "		<fax><![CDATA[$fax]]></fax>\n";
		echo "	</empfaenger>\n";

		$details = new wawi_bestelldetail();
		$details->getAllDetailsFromBestellung($bestellung->bestellung_id);
		$summe_netto=0;
		$summe_brutto=0;
		$summe_mwst=0;

		$i=0;
		$pagebreakposition=30;
		$pagebreak=false;
		echo "	<details>\n";
		foreach($details->result as $row)
		{
			//wenn die Bezeichnung zu lange ist, dann muss die Seite frueher umbrechen
			if(!$pagebreak && mb_strlen($row->beschreibung)>60)
			{
				//echo "reduce";
				$pagebreakposition--;
			}

			//echo "pos:".$pagebreakposition;
			//echo "i:".$i;
			if(!$pagebreak && $i>$pagebreakposition)
			{
				$pagebreak=true;
				echo "</details>\n";
				echo "<details_1>\n";
			}
			echo "		<detail>\n";
			echo "			<position><![CDATA[$row->position]]></position>\n";
			echo "			<menge><![CDATA[$row->menge]]></menge>\n";
			echo "			<verpackungseinheit><![CDATA[$row->verpackungseinheit]]></verpackungseinheit>\n";
			echo "			<beschreibung><![CDATA[$row->beschreibung]]></beschreibung>\n";
			echo "			<artikelnummer><![CDATA[$row->artikelnummer]]></artikelnummer>\n";
			echo "			<preisprove><![CDATA[",number_format($row->preisprove,2,',','.'),"]]></preisprove>\n";
			echo "			<mwst><![CDATA[",number_format($row->mwst,2,',','.'),"]]></mwst>\n";
			$summe_brutto_detail=$row->menge*$row->preisprove/100*($row->mwst+100);
			$summe_netto_detail=$row->menge*$row->preisprove;
			echo "			<summe_brutto><![CDATA[",number_format($summe_brutto_detail,2,',','.'),"]]></summe_brutto>\n";
			echo "			<summe_netto><![CDATA[",number_format($summe_netto_detail,2,',','.'),"]]></summe_netto>\n";
			echo "		</detail>\n";
			$summe_brutto+=$summe_brutto_detail;
			$summe_netto+=$row->menge*$row->preisprove;
			$summe_mwst+=$row->menge*$row->preisprove/100*$row->mwst;
			$i++;
		}
		//echo "pos:".$pagebreakposition;
		//if($i>$pagebreakposition)
		if($pagebreak)
			echo "	</details_1>\n";
		else
			echo "	</details>\n";

		echo "	<aufteilungen_1>\n";
		$anzAufteilungen = sizeof($aufteilung->result);
		$i = 0;
		$aufteilbreak=false;
		foreach($aufteilung->result as $aufteilung_row)
		{
			if($i==15 && !$aufteilbreak)
			{
				$aufteilbreak=true;
				echo '</aufteilungen_1>';
				echo '<aufteilungen_2>';
			}

			// Aufteilung nur auf Studiengaenge
			if($studiengang->getStudiengangFromOe($aufteilung_row->oe_kurzbz))
			{
				// Diplomstudiengänge nicht laden, Dummy Studiengaenge (stgkz>10000) nicht laden, Studiengang EAS (Ausserordentliche) nicht laden
				if($studiengang->typ !='d' && $studiengang->studiengang_kz>0 && $studiengang->studiengang_kz<10000 && $aufteilung_row->oe_kurzbz!='eas')
				{
					echo "		<aufteilung>\n";
					echo "			<oe><![CDATA[".strtoupper($aufteilung_row->oe_kurzbz)."]]></oe>\n";
					echo "			<prozent><![CDATA[$aufteilung_row->anteil]]></prozent>\n";
					echo "		</aufteilung>\n";
					$i++;
				}
			}
		}
		if($i>15)
			echo "	</aufteilungen_2>\n";
		else
		 	echo "	</aufteilungen_1>\n";

		echo "	<datum><![CDATA[",date('d.m.Y'),"]]></datum>\n";
		echo "	<erstelldatum><![CDATA[",$datum_obj->formatDatum($bestellung->insertamum, 'd.m.Y'),"]]></erstelldatum>\n";
		echo "	<summe_netto>",number_format($summe_netto,2,',','.'),"</summe_netto>\n";
		echo "	<summe_mwst>",number_format($summe_mwst,2,',','.'),"</summe_mwst>\n";
		echo "	<summe_brutto>",number_format($summe_brutto,2,',','.'),"</summe_brutto>\n";
		echo "</bestellung></bestellungen>";
	}
	else
		die('Parameter id missing');
}
else
	die('RDF not implemented!  Use Parameter xmlformat=xml');

?>
