<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at>, 
 */

	require_once('../../config/cis.config.inc.php');

	session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
	session_start();
	if (!isset($_SESSION['bewerbung/user']) || $_SESSION['bewerbung/user']=='') 
	{
		$_SESSION['request_uri']=$_SERVER['REQUEST_URI'];

		header('Location: registration.php?method=allgemein');
		exit;
	}

	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/konto.class.php');
	require_once('../../include/bankverbindung.class.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/organisationseinheit.class.php');
	
	$person_id = $_SESSION['bewerbung/personId'];

	if(isset($_GET['buchungsnr']))
		$buchungsnr=$_GET['buchungsnr'];
	else
		$buchungsnr='';
	
	$konto=new konto();
	if(!$konto->load($buchungsnr))
		die('Buchungsnummer '.$buchungsnr.' nicht vorhanden');
	if($person_id != $konto->person_id)
		die('keine Berechtigung, um auf Datensatz zuzugreifen');
	
	$studiengang=new studiengang();
	$studiengang->load($konto->studiengang_kz);
	$bankverbindung=new bankverbindung();
	$bankverbindung->load_oe($studiengang->oe_kurzbz);

	$oe=new organisationseinheit();
	$oe->load($studiengang->oe_kurzbz);
	
	$konto->getBuchungstyp();
	$buchungstyp = array();	
	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;
	
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>Zahlungsdetails</title>
				<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
				<link href="../../skin/fhcomplete.css.php" rel="stylesheet" type="text/css">
				<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
			</head>
			<body>';
	
	echo '<h1>Einzahlung für '.$konto->vorname.' '.$konto->nachname.'</h1>
	<table class="tablesorter">
		<thead>
			<tr>
				<th width="40%">Zahlungsinformationen</th>
				<th width="60%"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Buchungstyp</td>
				<td>'.$buchungstyp[$konto->buchungstyp_kurzbz].'</td>
			</tr><tr>
				<td>Buchungstext</td>
				<td>'.$konto->buchungstext.'</td>
			</tr><tr>
				<td>Betrag</td>
				<td>'.abs($konto->betrag).' €</td>
			</tr>
		</tbody>
	</table>
	<table class="tablesorter">
		<thead>
			<tr>
				<th width="40%">Zahlung an</th>
				<th width="60%"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Empfänger</td>
 				<td>'.$oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung.'</td>
			</tr><tr>
				<td>IBAN</td>
				<td>'.$bankverbindung->result[0]->iban.'</td>
			</tr><tr>
				<td>BIC</td>
				<td>'.$bankverbindung->result[0]->bic.'</td>
			</tr><tr>
				<td>Zahlungsreferenz</td>
				<td>'.$konto->zahlungsreferenz.'</td>
			</tr>
		</tbody>
	</table>
</body></html>';	   	
?>
