<?php
require_once('../include/wawi_konto.class.php');

$konto = new wawi_konto();

if($konto->getAll())
{
	foreach($konto->result as $row)
	{
		//Zeilen der Tabelle ausgeben
		$row->kontonummer;
		if($row->akiv)
		
	}
}

if($isset($_GET['edit']) || isset($_GET['new']))
{
	//Formular ausgeben
}

if($isset($_GET['delete']))
{
	//Eintrag loeschen
	$konto->delete($id);
}

if(isset($_GET['save']))
{
	//Daten in der DB speichern
	$konto = new wawi_konto();
	$aktiv = isset($_POST['aktiv']);
	
	if(isset($_POST['konto_id']))
	{
		//Update eines bestehenden Datensatzes
		if(!$konto->load($id))
			die('ID nicht gefunden');
		$konto->new = false;
	}
	else
	{
		//Neuer Datensatz
		$konto->new = true;
	}
	
	$konto->beschreibung = $beschreibung;
	//...
	
	if(!$konto->save())
	{
		die('Fehler beim Speichern:'.$konto->errormsg);
	}
}
?>