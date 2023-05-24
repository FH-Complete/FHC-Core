<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Changed Fehler Type from Error to Warning
if($result = $db->db_query("SELECT 1 FROM system.tbl_fehler WHERE fehlertyp_kurzbz='error' AND fehler_kurzbz IN ('AbschlussstatusFehlt', 'AusbildungssemPrestudentUngleichAusbildungssemStatus', 'BewerberNichtZumRtAngetreten', 'GbDatumWeitZurueck', 'InaktiverStudentAktiverStatus')"))
{
	if($db->db_num_rows($result)>0)
	{
	$qry = "UPDATE system.tbl_fehler SET fehlertyp_kurzbz='warning' WHERE fehler_kurzbz IN ('AbschlussstatusFehlt', 'AusbildungssemPrestudentUngleichAusbildungssemStatus', 'BewerberNichtZumRtAngetreten', 'GbDatumWeitZurueck', 'InaktiverStudentAktiverStatus') AND fehlertyp_kurzbz = 'error';";

		if(!$db->db_query($qry))
			echo '<strong>System Tabelle Fehler: '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Bestimmte Fehler mit Typ error zu warnings umgewandelt';
	}
}
